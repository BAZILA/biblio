<?php
/** Gestion des ouvrages et de leurs exemplaires. */

/** Interrompt l'action si l'ouvrage appartient à un autre centre documentaire. */
function exiger_site(array $ouvrage): void
{
    if (!site_modifiable($ouvrage['site_id'] ?? null)) {
        flash('erreur', '« ' . ($ouvrage['titre'] ?? 'Cet ouvrage') . ' » appartient à ' . (nom_site((int) $ouvrage['site_id']) ?: 'un autre centre')
            . ' : vous pouvez le consulter, pas le modifier.');
        redirect(admin_url('ouvrages'));
    }
}

// ---------- Exemplaires (section « exemplaires ») ----------
if ($section === 'exemplaires') {
    exiger_post();
    // « Remettre » relève du droit de retirer, « Remplacer » de celui d'ajouter, « Renuméroter » de celui de modifier
    exiger('exemplaires.' . (['remettre' => 'retirer', 'remplacer' => 'ajouter', 'renumeroter' => 'modifier'][$action] ?? $action));
    if ($action === 'ajouter') {
        $o = row('SELECT * FROM ouvrages WHERE id = ?', [$id]) ?? admin_introuvable('Ouvrage');
        exiger_site($o);
        $o = enregistrer_cote_saisie($o);
        $n = max(1, min(50, (int) ($_POST['nombre'] ?? 1)));
        $prefixe = prefixe_exemplaire($o);
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

    // La cote a changé : on recale les codes des exemplaires dessus (cote-1, cote-2…)
    if ($action === 'renumeroter') {
        $o = row('SELECT * FROM ouvrages WHERE id = ?', [$id]) ?? admin_introuvable('Ouvrage');
        exiger_site($o);
        $o = enregistrer_cote_saisie($o);
        $prefixe = prefixe_exemplaire($o);
        $liste = rows('SELECT id, code FROM exemplaires WHERE ouvrage_id = ? AND remplace_par IS NULL ORDER BY code, id', [$id]);
        $ids = array_column($liste, 'id');
        $changes = 0;
        if ($liste) {
            $trous = implode(',', array_fill(0, count($ids), '?'));
            db()->beginTransaction();
            // Codes provisoires : sans eux, renommer « cote-2 » en « cote-1 » buterait sur l'unicité
            foreach ($liste as $x) {
                q('UPDATE exemplaires SET code = ? WHERE id = ?', ['~tmp' . $x['id'], $x['id']]);
            }
            $num = 0;
            foreach ($liste as $x) {
                do {
                    $num++;
                    $code = "$prefixe-$num";
                } while (val("SELECT 1 FROM exemplaires WHERE code = ? AND id NOT IN ($trous)", array_merge([$code], $ids)));
                q('UPDATE exemplaires SET code = ? WHERE id = ?', [$code, $x['id']]);
                $changes += $code !== $x['code'] ? 1 : 0;
            }
            db()->commit();
        }
        flash($changes ? 'succes' : 'info', $changes
            ? pluriel($changes, 'exemplaire renuméroté', 'exemplaires renumérotés') . " d’après la cote : $prefixe-1, $prefixe-2… Pensez à refaire les étiquettes."
            : 'Les codes suivent déjà la cote : rien à changer.');
        redirect(admin_url('ouvrages/modifier/' . $id) . '#exemplaires');
    }

    $ex = row('SELECT * FROM exemplaires WHERE id = ?', [$id]) ?? admin_introuvable('Exemplaire');
    exiger_site(row('SELECT * FROM ouvrages WHERE id = ?', [$ex['ouvrage_id']]) ?? []);
    $retourOuvrage = admin_url('ouvrages/modifier/' . $ex['ouvrage_id']) . '#exemplaires';

    if ($action === 'modifier') {
        $code = post_str('code', '');
        $etat = post_str('etat', 'bon');
        $erreur = '';
        if ($code === '' || !isset(ETATS_EXEMPLAIRE_TOUS[$etat])) {
            $erreur = 'Code ou état invalide.';
        } elseif (val('SELECT 1 FROM exemplaires WHERE code = ? AND id <> ?', [$code, $id])) {
            $erreur = "Le code « $code » est déjà utilisé.";
        } else {
            q('UPDATE exemplaires SET code = ?, etat = ?, notes = ? WHERE id = ?', [$code, $etat, mb_substr(post_str('notes', ''), 0, 255), $id]);
        }
        // Enregistrement automatique de la ligne (sans recharger la page) : réponse courte au lieu d'une redirection
        if (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'fetch') {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['ok' => $erreur === '', 'erreur' => $erreur], JSON_UNESCAPED_UNICODE);
            exit;
        }
        flash($erreur ? 'erreur' : 'succes', $erreur ?: "Exemplaire $code mis à jour.");
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
            if ($ex['remplace_par']) {
                flash('erreur', 'Cet exemplaire a été remplacé : son code sert au nouvel exemplaire. Donnez-lui d’abord un autre code.');
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

    // Un exemplaire retiré est remplacé par un neuf qui reprend son code : l'ancienne ligne garde ses prêts, la neuve repart à zéro
    if ($action === 'remplacer') {
        if ($ex['statut'] !== 'retire' || $ex['remplace_par']) {
            flash('erreur', $ex['remplace_par'] ? 'Cet exemplaire a déjà été remplacé.' : 'Retirez d’abord cet exemplaire de la circulation.');
            redirect($retourOuvrage);
        }
        $code = $ex['code'];
        $archive = code_exemplaire_archive($code);
        db()->beginTransaction();
        q('UPDATE exemplaires SET code = ? WHERE id = ?', [$archive, $id]);
        $neuf = insert('exemplaires', [
            'ouvrage_id' => $ex['ouvrage_id'], 'code' => $code, 'etat' => 'neuf', 'statut' => 'disponible',
            'notes' => mb_substr("Remplace « $archive »", 0, 255),
        ]);
        q('UPDATE exemplaires SET remplace_par = ? WHERE id = ?', [$neuf, $id]);
        liberer_exemplaire($neuf);
        db()->commit();
        flash('succes', "Nouvel exemplaire $code créé, sans prêt à son compteur. L’ancien est archivé sous « $archive » et garde son historique.");
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
// Désactiver plutôt que supprimer : l'ouvrage quitte le site et le prêt, son historique reste
if ($action === 'desactiver' || $action === 'reactiver') {
    exiger_post();
    exiger('ouvrages.desactiver');
    $o = row('SELECT * FROM ouvrages WHERE id = ?', [$id]) ?? admin_introuvable('Ouvrage');
    if ($action === 'desactiver') {
        $n = desactiver_ouvrage($id);
        $enCours = (int) val('SELECT COUNT(*) FROM prets p JOIN exemplaires e ON e.id = p.exemplaire_id WHERE e.ouvrage_id = ? AND p.date_retour IS NULL', [$id]);
        flash('succes', "« {$o['titre']} » est désactivé : il n’apparaît plus sur le site et ne peut plus être prêté ni réservé."
            . ($n ? ' ' . pluriel($n, 'réservation annulée', 'réservations annulées') . ' (lecteurs prévenus).' : '')
            . ($enCours ? ' ' . pluriel($enCours, 'prêt en cours', 'prêts en cours') . ' : les retours s’enregistrent normalement.' : ''));
    } else {
        reactiver_ouvrage($id);
        flash('succes', "« {$o['titre']} » est réactivé et de nouveau visible sur le site.");
    }
    redirect(admin_url('ouvrages/modifier/' . $id));
}

// Suppression définitive : seulement pour une fiche jamais prêtée (créée par erreur, doublon)
if ($action === 'supprimer') {
    exiger_post();
    exiger('ouvrages.supprimer');
    $o = row('SELECT * FROM ouvrages WHERE id = ?', [$id]) ?? admin_introuvable('Ouvrage');
    if (val('SELECT COUNT(*) FROM prets p JOIN exemplaires e ON e.id = p.exemplaire_id WHERE e.ouvrage_id = ?', [$id])) {
        flash('erreur', 'Cet ouvrage a un historique de prêts : désactivez-le plutôt que de le supprimer.');
        redirect(admin_url('ouvrages/modifier/' . $id));
    }
    q('DELETE FROM ouvrages WHERE id = ?', [$id]);
    q('DELETE a FROM auteurs a LEFT JOIN ouvrage_auteur oa ON oa.auteur_id = a.id WHERE oa.auteur_id IS NULL');
    supprimer_couverture($o['image']);
    flash('succes', "« {$o['titre']} » a été supprimé du catalogue.");
    redirect(admin_url('ouvrages'));
}

if ($action === 'nouveau' || $action === 'modifier') {
    exiger($action === 'nouveau' ? 'ouvrages.ajouter' : 'ouvrages.voir');
    $lectureSeule = $action === 'modifier' && !peut('ouvrages.modifier');
    $vide = [
        'id' => 0, 'titre' => '', 'sous_titre' => '', 'slug' => '', 'domaine_id' => '', 'type_id' => '', 'resume' => '', 'description' => '',
        'collection' => '', 'numero_collection' => '', 'editeur' => '', 'annee' => '', 'edition' => '', 'tome' => '', 'volume' => '',
        'site_id' => site_par_defaut(),
        'isbn' => '', 'issn' => '', 'indice_dewey' => '', 'cote' => '', 'emplacement' => reglage('emplacement_defaut'),
        'pages' => '', 'langue' => 'Français', 'image' => '',
        'consultation_sur_place' => 0, 'coup_de_coeur' => 0, 'publie' => 1, 'actif' => 1, 'auteurs_txt' => '',
    ];
    if ($action === 'modifier') {
        $o = ouvrage_par_id($id) ?? admin_introuvable('Ouvrage');
        // Fiche d'un autre centre : invisible, sauf droit « catalogue des autres centres » qui l'ouvre en lecture seule
        if (!site_modifiable($o['site_id'])) {
            if (!peut('sites.catalogue')) {
                admin_introuvable('Ouvrage');
            }
            $lectureSeule = true;
        }
        $o['auteurs_txt'] = implode("\n", array_column($o['auteurs'], 'nom'));
        $o['collection'] = (string) ($o['collection_nom'] ?? ''); // saisie libre, comme les auteurs
        // Les paragraphes simples sont présentés en texte brut pour l'édition
        $o['resume'] = html_vers_texte((string) $o['resume']);
        $o['description'] = html_vers_texte((string) $o['description']);
    } else {
        $o = $vide;
        $o['domaine_id'] = get_str('domaine', '');
    }
    $erreurs = [];
    $coteAvant = (string) $o['cote'];

    if (is_post()) {
        csrf_verify();
        exiger($action === 'nouveau' ? 'ouvrages.ajouter' : 'ouvrages.modifier');
        if ($lectureSeule) {
            flash('erreur', 'Cet ouvrage appartient à un autre centre documentaire : vous pouvez le consulter, pas le modifier.');
            redirect(admin_url('ouvrages/modifier/' . $id));
        }
        // Centre : celui de l'agent ; seul un agent « tous les centres » peut le choisir
        $o['site_id'] = mon_site() ?: ((int) post_str('site_id') ?: (int) $o['site_id']);
        if ($o['site_id'] && !val('SELECT 1 FROM sites WHERE id = ?', [(int) $o['site_id']])) {
            $erreurs['site_id'] = 'Choisissez un centre documentaire.';
        }
        foreach (['titre', 'sous_titre', 'slug', 'domaine_id', 'type_id', 'collection', 'numero_collection', 'resume', 'description',
                  'editeur', 'annee', 'edition', 'tome', 'volume', 'isbn', 'issn', 'indice_dewey', 'cote', 'emplacement',
                  'pages', 'langue', 'auteurs_txt'] as $k) {
            $o[$k] = post_str($k, '');
        }
        foreach (['consultation_sur_place', 'coup_de_coeur', 'publie'] as $k) {
            $o[$k] = isset($_POST[$k]) ? 1 : 0;
        }
        if (!$o['actif']) {
            $o['publie'] = $o['coup_de_coeur'] = 0; // un ouvrage désactivé reste hors du site
        }
        $o['slug'] = slugify($o['slug'] !== '' ? $o['slug'] : $o['titre']);

        if ($o['titre'] === '') {
            $erreurs['titre'] = 'Le titre est obligatoire.';
        }
        if (!val('SELECT 1 FROM domaines WHERE id = ?', [(int) $o['domaine_id']])) {
            $erreurs['domaine_id'] = 'Choisissez un domaine.';
        }
        // Deux ouvrages peuvent porter le même titre (éditions, tomes…) : l'adresse web est simplement numérotée, jamais refusée
        $slugDemande = $o['slug'];
        for ($base = $o['slug'], $n = 2;
             val('SELECT 1 FROM ouvrages WHERE domaine_id = ? AND slug = ? AND id <> ?', [(int) $o['domaine_id'], $o['slug'], (int) $o['id']]);
             $n++) {
            $o['slug'] = "$base-$n";
        }
        $slugAjuste = $o['slug'] !== $slugDemande ? $o['slug'] : '';
        // Le type de document est obligatoire : il classe l'ouvrage dans le catalogue et le menu du site
        if (!val('SELECT 1 FROM types WHERE id = ?', [(int) $o['type_id']])) {
            $erreurs['type_id'] = 'Choisissez un type de document.';
        }
        // Collection : saisie libre, comme les auteurs — une collection inconnue est créée avec l'ouvrage
        $o['collection'] = trim(preg_replace('/\s+/u', ' ', $o['collection']));
        $collectionConnue = $o['collection'] === '' || val('SELECT 1 FROM collections WHERE LOWER(TRIM(nom)) = LOWER(?)', [$o['collection']]);
        if (!$collectionConnue && !peut('collections.ajouter')) {
            $erreurs['collection'] = 'Votre rôle ne permet pas de créer des collections : choisissez-en une de la liste.';
        }
        $o['indice_dewey'] = trim($o['indice_dewey']);
        if ($o['indice_dewey'] !== '' && !preg_match('/^\d{1,3}(\.\d{1,6})?$/', $o['indice_dewey'])) {
            $erreurs['indice_dewey'] = 'Chiffres seulement, avec une décimale si besoin : 500, 530 ou 005.1.';
        }
        // Un auteur absent de la liste est créé avec l'ouvrage : il faut alors le droit d'ajouter des auteurs
        $nouveaux = array_filter(array_map('trim', preg_split('/[\r\n]+|\s*;\s*/', $o['auteurs_txt'])),
            fn($nom) => $nom !== '' && !val('SELECT 1 FROM auteurs WHERE nom = ?', [$nom]));
        if ($nouveaux && !peut('auteurs.ajouter')) {
            $erreurs['auteurs_txt'] = 'Votre rôle ne permet pas de créer des auteurs : ' . implode(', ', $nouveaux) . '. Choisissez des auteurs existants.';
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
                'titre' => $o['titre'], 'sous_titre' => $o['sous_titre'], 'slug' => $o['slug'], 'domaine_id' => (int) $o['domaine_id'],
                'site_id' => (int) $o['site_id'] ?: null,
                'type_id' => (int) $o['type_id'],
                'collection_id' => collection_par_nom($o['collection']), 'numero_collection' => $o['numero_collection'],
                // Texte simple ou HTML léger, nettoyé (pas de scripts ni d'attributs d'événement)
                'resume' => texte_vers_html($o['resume']), 'description' => texte_vers_html($o['description']),
                'editeur' => $o['editeur'], 'annee' => $o['annee'], 'edition' => $o['edition'], 'tome' => $o['tome'], 'volume' => $o['volume'],
                'isbn' => $o['isbn'], 'issn' => $o['issn'], 'indice_dewey' => $o['indice_dewey'],
                'cote' => $o['cote'], 'emplacement' => $o['emplacement'],
                'pages' => $o['pages'] !== '' ? (int) $o['pages'] : null, 'langue' => $o['langue'],
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

            if ($action === 'nouveau' && peut('exemplaires.ajouter') && ($n = (int) ($_POST['nb_exemplaires'] ?? 0)) > 0) {
                $prefixe = prefixe_exemplaire($o);
                for ($i = 1, $num = 1; $i <= min($n, 50); $i++, $num++) {
                    while (val('SELECT 1 FROM exemplaires WHERE code = ?', ["$prefixe-$num"])) {
                        $num++;
                    }
                    insert('exemplaires', ['ouvrage_id' => $o['id'], 'code' => "$prefixe-$num", 'etat' => 'neuf']);
                }
            }
            flash('succes', "« {$o['titre']} » enregistré.");
            if ($slugAjuste !== '') {
                flash('info', 'Un autre ouvrage de ce domaine porte déjà ce titre : l’adresse web de cette fiche est « ' . $slugAjuste . ' ».');
            }
            // Les codes déjà attribués ne bougent pas tout seuls : on propose de les recaler sur la nouvelle cote
            if ($action === 'modifier' && $coteAvant !== $o['cote']
                && val('SELECT 1 FROM exemplaires WHERE ouvrage_id = ? AND remplace_par IS NULL', [(int) $o['id']])) {
                flash('info', 'La cote a changé : les codes des exemplaires portent encore l’ancienne. Le bouton « Renuméroter d’après la cote », sous la liste des exemplaires, les recale en '
                    . prefixe_exemplaire($o) . '-1, ' . prefixe_exemplaire($o) . '-2…');
            }
            redirect(admin_url('ouvrages/modifier/' . $o['id']));
        }
    }

    $exemplaires = $o['id'] ? rows('SELECT e.*, (SELECT r.code FROM exemplaires r WHERE r.id = e.remplace_par) AS code_remplacant,
                                           (SELECT COUNT(*) FROM prets p WHERE p.exemplaire_id = e.id) AS nb_prets,
                                           (SELECT CONCAT(u.prenom, \' \', u.nom) FROM prets p JOIN utilisateurs u ON u.id = p.lecteur_id
                                            WHERE p.exemplaire_id = e.id AND p.date_retour IS NULL LIMIT 1) AS emprunteur
                                    FROM exemplaires e WHERE e.ouvrage_id = ? ORDER BY e.code', [$o['id']]) : [];
    admin_page('ouvrage_form', [
        'o' => $o,
        'erreurs' => $erreurs,
        'lectureSeule' => $lectureSeule,
        'exemplaires' => $exemplaires,
        'domaines' => rows('SELECT id, nom FROM domaines ORDER BY ordre, nom'),
        'types' => rows('SELECT id, nom FROM types ORDER BY ordre, nom'),
        // Choix du centre : réservé aux agents qui travaillent sur tous les centres
        'sites' => mon_site() === 0 ? sites_options() : [],
        'file' => $o['id'] ? reservations("r.ouvrage_id = ? AND r.statut IN ('en_attente','disponible')", [$o['id']]) : [],
    ], $o['id'] ? $o['titre'] : 'Nouvel ouvrage', 'ouvrages');
}

// Liste
$q = get_str('q', '');
$domaine = (int) ($_GET['domaine'] ?? 0);
$type = (int) ($_GET['type'] ?? 0);
$etat = get_str('etat', '');
$site = (int) ($_GET['site'] ?? 0);
$where = ['1'];
$params = [];
// Cloisonnement : un agent ne voit que le fonds de son centre, sauf droit « catalogue des autres centres »
if (mon_site() && !peut('sites.catalogue')) {
    $where[] = 'o.site_id = ?';
    $params[] = mon_site();
} elseif ($site) {
    $where[] = 'o.site_id = ?';
    $params[] = $site;
}
if ($q !== '') {
    $where[] = '(o.titre LIKE ? OR o.sous_titre LIKE ? OR o.cote LIKE ? OR o.indice_dewey LIKE ? OR o.isbn LIKE ? OR o.issn LIKE ? OR o.editeur LIKE ?
                 OR EXISTS (SELECT 1 FROM collections c2 WHERE c2.id = o.collection_id AND c2.nom LIKE ?)
                 OR EXISTS (SELECT 1 FROM ouvrage_auteur oa JOIN auteurs a ON a.id = oa.auteur_id WHERE oa.ouvrage_id = o.id AND a.nom LIKE ?)
                 OR EXISTS (SELECT 1 FROM exemplaires e WHERE e.ouvrage_id = o.id AND e.code LIKE ?))';
    array_push($params, ...array_fill(0, 10, "%$q%"));
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
    $where[] = 'o.publie = 0 AND o.actif = 1';
} elseif ($etat === 'desactive') {
    $where[] = 'o.actif = 0';
} elseif ($etat === 'coeur') {
    $where[] = 'o.coup_de_coeur = 1';
} elseif ($etat === 'sans-type') {
    $where[] = 'o.type_id IS NULL'; // fiches d'avant le type obligatoire, à compléter
}
$w = implode(' AND ', $where);
$pg = pagination_admin((int) val("SELECT COUNT(*) FROM ouvrages o WHERE $w", $params), 30);
$liste = enrichir(rows(OUVRAGE_SELECT . " WHERE $w ORDER BY o.titre LIMIT {$pg['parPage']} OFFSET {$pg['offset']}", $params));

admin_page('ouvrages', [
    'liste' => $liste, 'pg' => $pg, 'q' => $q, 'domaine' => $domaine, 'type' => $type, 'etat' => $etat, 'site' => $site,
    'domaines' => rows('SELECT id, nom FROM domaines ORDER BY ordre, nom'),
    'types' => rows('SELECT id, nom FROM types ORDER BY ordre, nom'),
    // Filtre par centre : seulement si l'agent en voit plusieurs
    'sites' => (mon_site() === 0 || peut('sites.catalogue')) && count(sites()) > 1 ? sites_options(false) : [],
    'sansType' => (int) val('SELECT COUNT(*) FROM ouvrages WHERE type_id IS NULL' . filtre_site('site_id')),
], 'Ouvrages', 'ouvrages');
