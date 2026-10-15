<?php
/** Gestion des ouvrages et de leurs exemplaires. */

const ETATS_EXEMPLAIRE = ['neuf' => 'Neuf', 'bon' => 'Bon état', 'use' => 'Usé', 'abime' => 'Abîmé'];

// ---------- Exemplaires (section « exemplaires ») ----------
if ($section === 'exemplaires') {
    exiger_post();
    if ($action === 'ajouter') {
        $o = row('SELECT * FROM ouvrages WHERE id = ?', [$id]) ?? admin_introuvable('Ouvrage');
        $n = max(1, min(50, (int) ($_POST['nombre'] ?? 1)));
        $prefixe = $o['cote'] !== '' ? $o['cote'] : sprintf('EX%04d', $o['id']);
        $num = 1;
        for ($i = 0; $i < $n; $i++) {
            while (val('SELECT 1 FROM exemplaires WHERE code = ?', ["$prefixe-$num"])) {
                $num++;
            }
            $exId = insert('exemplaires', ['ouvrage_id' => $id, 'code' => "$prefixe-$num", 'etat' => 'neuf']);
            db()->beginTransaction();
            liberer_exemplaire($exId); // sert une éventuelle réservation en attente
            db()->commit();
        }
        flash('succes', pluriel($n, 'exemplaire ajouté', 'exemplaires ajoutés') . '.');
        redirect(admin_url('ouvrages/modifier/' . $id) . '#exemplaires');
    }

    $ex = row('SELECT * FROM exemplaires WHERE id = ?', [$id]) ?? admin_introuvable('Exemplaire');
    $retourOuvrage = admin_url('ouvrages/modifier/' . $ex['ouvrage_id']) . '#exemplaires';

    if ($action === 'modifier') {
        $code = post_str('code', '');
        $etat = post_str('etat', 'bon');
        if ($code === '' || !isset(ETATS_EXEMPLAIRE[$etat])) {
            flash('erreur', 'Code ou état invalide.');
        } elseif (val('SELECT 1 FROM exemplaires WHERE code = ? AND id <> ?', [$code, $id])) {
            flash('erreur', "Le code « $code » est déjà utilisé.");
        } else {
            q('UPDATE exemplaires SET code = ?, etat = ?, notes = ? WHERE id = ?', [$code, $etat, mb_substr(post_str('notes', ''), 0, 255), $id]);
            flash('succes', "Exemplaire $code mis à jour.");
        }
        redirect($retourOuvrage);
    }

    if ($action === 'retirer' || $action === 'remettre') {
        if ($action === 'retirer') {
            if ($ex['statut'] === 'prete') {
                flash('erreur', 'Cet exemplaire est en prêt : enregistrez d’abord son retour.');
                redirect($retourOuvrage);
            }
            db()->beginTransaction();
            q("UPDATE exemplaires SET statut = 'retire' WHERE id = ?", [$id]);
            // Une réservation qui comptait sur cet exemplaire repasse en tête de file… et reçoit un autre exemplaire libre s'il y en a un
            q("UPDATE reservations SET statut = 'en_attente', exemplaire_id = NULL, disponible_le = NULL, expire_le = NULL
               WHERE exemplaire_id = ? AND statut = 'disponible'", [$id]);
            servir_file_attente((int) $ex['ouvrage_id']);
            db()->commit();
            flash('succes', "Exemplaire {$ex['code']} retiré de la circulation.");
        } else {
            if ($ex['statut'] !== 'retire') {
                flash('erreur', 'Seul un exemplaire retiré peut être remis en circulation.');
                redirect($retourOuvrage);
            }
            db()->beginTransaction();
            q("UPDATE exemplaires SET statut = 'disponible' WHERE id = ?", [$id]);
            liberer_exemplaire($id);
            db()->commit();
            flash('succes', "Exemplaire {$ex['code']} remis en circulation.");
        }
        redirect($retourOuvrage);
    }

    if ($action === 'supprimer') {
        if (val('SELECT COUNT(*) FROM prets WHERE exemplaire_id = ?', [$id])) {
            flash('erreur', 'Cet exemplaire a un historique de prêts : retirez-le de la circulation plutôt que de le supprimer.');
        } else {
            db()->beginTransaction();
            q('UPDATE reservations SET exemplaire_id = NULL, statut = IF(statut = \'disponible\', \'en_attente\', statut) WHERE exemplaire_id = ?', [$id]);
            q('DELETE FROM exemplaires WHERE id = ?', [$id]);
            servir_file_attente((int) $ex['ouvrage_id']);
            db()->commit();
            flash('succes', "Exemplaire {$ex['code']} supprimé.");
        }
        redirect($retourOuvrage);
    }
    admin_introuvable('Action');
}

// ---------- Ouvrages ----------
if ($action === 'supprimer') {
    exiger_post();
    $o = row('SELECT * FROM ouvrages WHERE id = ?', [$id]) ?? admin_introuvable('Ouvrage');
    if (val('SELECT COUNT(*) FROM prets p JOIN exemplaires e ON e.id = p.exemplaire_id WHERE e.ouvrage_id = ?', [$id])) {
        flash('erreur', 'Cet ouvrage a un historique de prêts : dépubliez-le plutôt que de le supprimer.');
        redirect(admin_url('ouvrages/modifier/' . $id));
    }
    q('DELETE FROM ouvrages WHERE id = ?', [$id]);
    q('DELETE a FROM auteurs a LEFT JOIN ouvrage_auteur oa ON oa.auteur_id = a.id WHERE oa.auteur_id IS NULL');
    supprimer_couverture($o['image']);
    flash('succes', "« {$o['titre']} » a été supprimé du catalogue.");
    redirect(admin_url('ouvrages'));
}

if ($action === 'nouveau' || $action === 'modifier') {
    $vide = [
        'id' => 0, 'titre' => '', 'slug' => '', 'domaine_id' => '', 'type_id' => '', 'resume' => '', 'description' => '',
        'editeur' => '', 'annee' => '', 'isbn' => '', 'cote' => '', 'pages' => '', 'langue' => 'Français', 'image' => '',
        'consultation_sur_place' => 0, 'coup_de_coeur' => 0, 'publie' => 1, 'auteurs_txt' => '',
    ];
    if ($action === 'modifier') {
        $o = ouvrage_par_id($id) ?? admin_introuvable('Ouvrage');
        $o['auteurs_txt'] = implode("\n", array_column($o['auteurs'], 'nom'));
        // Les paragraphes simples sont présentés en texte brut pour l'édition
        $o['resume'] = html_vers_texte((string) $o['resume']);
        $o['description'] = html_vers_texte((string) $o['description']);
    } else {
        $o = $vide;
        $o['domaine_id'] = get_str('domaine', '');
    }
    $erreurs = [];

    if (is_post()) {
        csrf_verify();
        foreach (['titre', 'slug', 'domaine_id', 'type_id', 'resume', 'description', 'editeur', 'annee', 'isbn', 'cote', 'pages', 'langue', 'auteurs_txt'] as $k) {
            $o[$k] = post_str($k, '');
        }
        foreach (['consultation_sur_place', 'coup_de_coeur', 'publie'] as $k) {
            $o[$k] = isset($_POST[$k]) ? 1 : 0;
        }
        $o['slug'] = slugify($o['slug'] !== '' ? $o['slug'] : $o['titre']);

        if ($o['titre'] === '') {
            $erreurs['titre'] = 'Le titre est obligatoire.';
        }
        if (!val('SELECT 1 FROM domaines WHERE id = ?', [(int) $o['domaine_id']])) {
            $erreurs['domaine_id'] = 'Choisissez un domaine.';
        } elseif (val('SELECT 1 FROM ouvrages WHERE domaine_id = ? AND slug = ? AND id <> ?', [(int) $o['domaine_id'], $o['slug'], (int) $o['id']])) {
            $erreurs['slug'] = 'Un autre ouvrage de ce domaine utilise déjà cette adresse.';
        }
        if ($o['pages'] !== '' && !ctype_digit($o['pages'])) {
            $erreurs['pages'] = 'Nombre entier attendu.';
        }
        $nouvelleImage = null;
        if (!$erreurs) {
            try {
                $nouvelleImage = televerser_image('image', 'ouvrages', $o['slug']);
            } catch (RuntimeException $ex) {
                $erreurs['image'] = $ex->getMessage();
            }
        }

        if (!$erreurs) {
            $data = [
                'titre' => $o['titre'], 'slug' => $o['slug'], 'domaine_id' => (int) $o['domaine_id'],
                'type_id' => $o['type_id'] !== '' ? (int) $o['type_id'] : null,
                // Texte simple ou HTML léger, nettoyé (pas de scripts ni d'attributs d'événement)
                'resume' => texte_vers_html($o['resume']), 'description' => texte_vers_html($o['description']),
                'editeur' => $o['editeur'], 'annee' => $o['annee'],
                'isbn' => $o['isbn'], 'cote' => $o['cote'], 'pages' => $o['pages'] !== '' ? (int) $o['pages'] : null, 'langue' => $o['langue'],
                'consultation_sur_place' => $o['consultation_sur_place'], 'coup_de_coeur' => $o['coup_de_coeur'], 'publie' => $o['publie'],
            ];
            if ($nouvelleImage) {
                $data['image'] = $nouvelleImage;
            } elseif (isset($_POST['retirer_image'])) {
                $data['image'] = '';
            }
            db()->beginTransaction();
            if ($o['id']) {
                update('ouvrages', (int) $o['id'], $data);
            } else {
                $o['id'] = insert('ouvrages', $data);
            }
            lier_auteurs((int) $o['id'], preg_split('/[\r\n]+|\s*;\s*/', $o['auteurs_txt']));
            q('DELETE a FROM auteurs a LEFT JOIN ouvrage_auteur oa ON oa.auteur_id = a.id WHERE oa.auteur_id IS NULL');
            db()->commit();
            if (array_key_exists('image', $data) && $o['image'] !== '' && $o['image'] !== $data['image']) {
                supprimer_couverture($o['image']); // ancienne couverture remplacée ou retirée
            }

            if ($action === 'nouveau' && ($n = (int) ($_POST['nb_exemplaires'] ?? 0)) > 0) {
                $prefixe = $o['cote'] !== '' ? $o['cote'] : sprintf('EX%04d', $o['id']);
                for ($i = 1, $num = 1; $i <= min($n, 50); $i++, $num++) {
                    while (val('SELECT 1 FROM exemplaires WHERE code = ?', ["$prefixe-$num"])) {
                        $num++;
                    }
                    insert('exemplaires', ['ouvrage_id' => $o['id'], 'code' => "$prefixe-$num", 'etat' => 'neuf']);
                }
            }
            flash('succes', "« {$o['titre']} » enregistré.");
            redirect(admin_url('ouvrages/modifier/' . $o['id']));
        }
    }

    $exemplaires = $o['id'] ? rows('SELECT e.*, (SELECT COUNT(*) FROM prets p WHERE p.exemplaire_id = e.id) AS nb_prets,
                                           (SELECT CONCAT(u.prenom, \' \', u.nom) FROM prets p JOIN utilisateurs u ON u.id = p.lecteur_id
                                            WHERE p.exemplaire_id = e.id AND p.date_retour IS NULL LIMIT 1) AS emprunteur
                                    FROM exemplaires e WHERE e.ouvrage_id = ? ORDER BY e.code', [$o['id']]) : [];
    admin_page('ouvrage_form', [
        'o' => $o,
        'erreurs' => $erreurs,
        'exemplaires' => $exemplaires,
        'domaines' => rows('SELECT id, nom FROM domaines ORDER BY ordre, nom'),
        'types' => rows('SELECT id, nom FROM types ORDER BY ordre, nom'),
        'file' => $o['id'] ? reservations("r.ouvrage_id = ? AND r.statut IN ('en_attente','disponible')", [$o['id']]) : [],
    ], $o['id'] ? $o['titre'] : 'Nouvel ouvrage', 'ouvrages');
}

// Liste
$q = get_str('q', '');
$domaine = (int) ($_GET['domaine'] ?? 0);
$type = (int) ($_GET['type'] ?? 0);
$etat = get_str('etat', '');
$where = ['1'];
$params = [];
if ($q !== '') {
    $where[] = '(o.titre LIKE ? OR o.cote LIKE ? OR o.isbn LIKE ? OR o.editeur LIKE ?
                 OR EXISTS (SELECT 1 FROM ouvrage_auteur oa JOIN auteurs a ON a.id = oa.auteur_id WHERE oa.ouvrage_id = o.id AND a.nom LIKE ?)
                 OR EXISTS (SELECT 1 FROM exemplaires e WHERE e.ouvrage_id = o.id AND e.code LIKE ?))';
    array_push($params, ...array_fill(0, 6, "%$q%"));
}
if ($domaine) {
    $where[] = 'o.domaine_id = ?';
    $params[] = $domaine;
}
if ($type) {
    $where[] = 'o.type_id = ?';
    $params[] = $type;
}
if ($etat === 'brouillon') {
    $where[] = 'o.publie = 0';
} elseif ($etat === 'coeur') {
    $where[] = 'o.coup_de_coeur = 1';
}
$w = implode(' AND ', $where);
$pg = pagination_admin((int) val("SELECT COUNT(*) FROM ouvrages o WHERE $w", $params), 30);
$liste = enrichir(rows(OUVRAGE_SELECT . " WHERE $w ORDER BY o.titre LIMIT {$pg['parPage']} OFFSET {$pg['offset']}", $params));

admin_page('ouvrages', [
    'liste' => $liste, 'pg' => $pg, 'q' => $q, 'domaine' => $domaine, 'type' => $type, 'etat' => $etat,
    'domaines' => rows('SELECT id, nom FROM domaines ORDER BY ordre, nom'),
    'types' => rows('SELECT id, nom FROM types ORDER BY ordre, nom'),
], 'Ouvrages', 'ouvrages');
