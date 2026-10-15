<?php
/**
 * Guichet : prêt (lecteur + ouvrage choisis dans des listes) et retour (liste des prêts en cours).
 * guichet/exemplaires?ouvrage=&lecteur= (JSON) remplit la liste des exemplaires de l'ouvrage choisi.
 * Un code d'exemplaire saisi ou scanné reste possible.
 */

if ($action === 'exemplaires') {
    exiger('prets.ajouter', 'reservations.remettre');
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(exemplaires_pretables((int) get_str('ouvrage'), (int) get_str('lecteur')), JSON_UNESCAPED_UNICODE);
    exit;
}

/** Exemplaires prêtables : ceux mis de côté pour ce lecteur d'abord, puis les disponibles (meilleur état d'abord). */
function exemplaires_pretables(int $ouvrageId, int $lecteurId): array
{
    return rows("SELECT e.code, e.etat, (r.id IS NOT NULL) AS pour_lui
                 FROM exemplaires e
                 LEFT JOIN reservations r ON r.exemplaire_id = e.id AND r.statut = 'disponible' AND r.lecteur_id = ?
                 WHERE e.ouvrage_id = ? AND (e.statut = 'disponible' OR r.id IS NOT NULL)
                 ORDER BY pour_lui DESC, FIELD(e.etat, 'neuf', 'bon', 'use', 'abime'), e.code", [$lecteurId, $ouvrageId]);
}

if ($action === 'pret') {
    exiger_post();
    exiger('prets.ajouter', 'reservations.remettre');
    $lecteurId = (int) ($_POST['lecteur_id'] ?? 0);
    $ouvrageId = (int) ($_POST['ouvrage_id'] ?? 0);
    // Priorité : code scanné, puis exemplaire choisi, puis le meilleur exemplaire disponible de l'ouvrage
    $code = post_str('code_scan') ?: post_str('code');
    if ($code === '' && $ouvrageId) {
        $code = (string) (exemplaires_pretables($ouvrageId, $lecteurId)[0]['code'] ?? '');
        if ($code === '') {
            flash('erreur', 'Aucun exemplaire disponible pour cet ouvrage : le lecteur peut le réserver.');
            redirect(admin_url('guichet', array_filter(['lecteur' => $lecteurId, 'ouvrage' => $ouvrageId])));
        }
    }
    // Sans « Prêts : ajouter », seul un exemplaire mis de côté pour ce lecteur peut lui être remis
    if ($code !== '' && !peut('prets.ajouter') && !val("SELECT 1 FROM reservations r JOIN exemplaires e ON e.id = r.exemplaire_id
            WHERE e.code = ? AND r.lecteur_id = ? AND r.statut = 'disponible'", [$code, $lecteurId])) {
        exiger('prets.ajouter');
    }
    if (!$lecteurId || $code === '') {
        flash('erreur', 'Choisissez un lecteur et un ouvrage.');
    } else {
        try {
            $pretId = enregistrer_pret($code, $lecteurId, (int) $moi['id'], [
                'echeance' => post_str('echeance'),
                'etat'     => post_str('etat'),
                'notes'    => post_str('notes'),
            ]);
            $p = prets('p.id = ?', [$pretId])[0];
            flash('succes', "Prêt enregistré : « {$p['titre']} » (exemplaire {$p['exemplaire_code']}) pour " . trim($p['prenom'] . ' ' . $p['nom'])
                . ', à rendre le ' . date_fr($p['date_retour_prevue']) . '.' . ($p['email'] ? '' : ' Lecteur sans e-mail : indiquez-lui la date de retour.'));
        } catch (RuntimeException $ex) {
            flash('erreur', $ex->getMessage());
            redirect(admin_url('guichet', array_filter(['lecteur' => $lecteurId, 'ouvrage' => $ouvrageId])));
        }
    }
    redirect(retour_sur(post_str('retour'), admin_url('guichet', $lecteurId ? ['lecteur' => $lecteurId] : [])));
}

if ($action === 'retour') {
    exiger_post();
    exiger('prets.retour');
    try {
        $codeScan = post_str('code');
        $pretId = $codeScan !== '' ? pret_par_code($codeScan) : (int) ($_POST['pret_id'] ?? 0);
        if (!$pretId) {
            throw new RuntimeException('Choisissez le prêt à clore dans la liste.');
        }
        $p = prets('p.id = ? AND p.date_retour IS NULL', [$pretId])[0] ?? null;
        if (!$p) {
            throw new RuntimeException('Ce prêt est introuvable ou déjà rendu.');
        }
        $devenir = enregistrer_retour($pretId, constat_retour_post((int) $moi['id']));
        $message = "Retour enregistré : « {$p['titre']} » ({$p['exemplaire_code']}) rendu par " . trim($p['prenom'] . ' ' . $p['nom'])
            . ' — ' . mb_strtolower(ETATS_RETOUR[post_str('etat_retour', 'conforme')] ?? '') . '. ';
        if ($devenir === 'reserve') {
            $suite = row("SELECT u.prenom, u.nom FROM reservations r JOIN utilisateurs u ON u.id = r.lecteur_id
                          WHERE r.exemplaire_id = ? AND r.statut = 'disponible'", [$p['exemplaire_id']]);
            $message .= 'À mettre de côté pour ' . trim(($suite['prenom'] ?? '') . ' ' . ($suite['nom'] ?? '')) . ' (réservation).';
        } else {
            $message .= DEVENIR_EXEMPLAIRE[$devenir];
        }
        flash('succes', $message);
    } catch (RuntimeException $ex) {
        flash('erreur', $ex->getMessage());
    }
    redirect(retour_sur(post_str('retour'), admin_url('guichet', isset($_POST['lecteur']) ? ['lecteur' => (int) $_POST['lecteur']] : [])));
}

$lecteur = null;
$pretsLecteur = $reservationsLecteur = [];
if (get_str('lecteur') !== '') {
    $lecteur = row("SELECT * FROM utilisateurs WHERE id = ? AND role = 'lecteur'", [(int) get_str('lecteur')]);
    if ($lecteur) {
        $pretsLecteur = prets('p.lecteur_id = ? AND p.date_retour IS NULL', [$lecteur['id']], 'p.date_retour_prevue');
        $reservationsLecteur = reservations("r.lecteur_id = ? AND r.statut IN ('en_attente','disponible')", [$lecteur['id']]);
    }
}
$rapide = $_SESSION['lecteur_rapide'] ?? null;
unset($_SESSION['lecteur_rapide']);

admin_page('guichet', [
    'lecteur' => $lecteur,
    'ouvrageChoisi' => (int) get_str('ouvrage'),
    'lecteurs' => rows("SELECT id, nom, prenom, matricule, categorie, actif FROM utilisateurs WHERE role = 'lecteur' AND (actif = 1 OR id = ?)
                        ORDER BY nom, prenom", [(int) ($lecteur['id'] ?? 0)]),
    'ouvrages' => enrichir(rows(OUVRAGE_SELECT . ' WHERE o.actif = 1 ORDER BY o.titre')),
    'pretsEnCours' => prets('p.date_retour IS NULL', [], 'u.nom, u.prenom, o.titre'),
    'pretsLecteur' => $pretsLecteur,
    'reservationsLecteur' => $reservationsLecteur,
    'nouveauLecteur' => get_str('nouveau_lecteur') !== '',
    'rapide' => $rapide,
    'derniers' => prets('1', [], 'p.id DESC', 8),
], 'Guichet', 'guichet');
