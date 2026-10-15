<?php
/**
 * Guichet : enregistrer un prêt (lecteur + code d'exemplaire) ou un retour (code d'exemplaire).
 * guichet/chercher?q= renvoie les lecteurs actifs correspondants (JSON) pour l'auto-complétion.
 */

if ($action === 'chercher') {
    $q = '%' . get_str('q', '') . '%';
    $res = rows("SELECT id, nom, prenom, matricule, email, categorie FROM utilisateurs
                 WHERE role = 'lecteur' AND actif = 1
                   AND (CONCAT(prenom, ' ', nom) LIKE ? OR CONCAT(nom, ' ', prenom) LIKE ? OR matricule LIKE ? OR email LIKE ?)
                 ORDER BY nom, prenom LIMIT 12", [$q, $q, $q, $q]);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array_map(fn($l) => [
        'id' => (int) $l['id'],
        'nom' => nom_complet($l),
        'detail' => implode(' · ', array_filter([LIBELLES_CATEGORIE[$l['categorie']] ?? '', $l['matricule'], $l['email']])),
    ], $res), JSON_UNESCAPED_UNICODE);
    exit;
}

if ($action === 'pret') {
    exiger_post();
    $lecteurId = (int) ($_POST['lecteur_id'] ?? 0);
    $code = post_str('code', '');
    if (!$lecteurId || $code === '') {
        flash('erreur', 'Choisissez un lecteur dans la liste et saisissez le code de l’exemplaire.');
    } else {
        try {
            $pretId = enregistrer_pret($code, $lecteurId, (int) $moi['id']);
            $p = prets('p.id = ?', [$pretId])[0];
            flash('succes', "Prêt enregistré : « {$p['titre']} » ({$p['exemplaire_code']}) à rendre le " . date_fr($p['date_retour_prevue']) . '.');
        } catch (RuntimeException $ex) {
            flash('erreur', $ex->getMessage());
        }
    }
    redirect(admin_url('guichet', $lecteurId ? ['lecteur' => $lecteurId] : []));
}

if ($action === 'retour') {
    exiger_post();
    try {
        $p = retour_par_code(post_str('code', ''));
        $suite = row("SELECT r.id, u.prenom, u.nom FROM reservations r JOIN utilisateurs u ON u.id = r.lecteur_id
                      JOIN exemplaires e ON e.id = r.exemplaire_id WHERE e.code = ? AND r.statut = 'disponible'", [post_str('code')]);
        flash('succes', "Retour enregistré : « {$p['titre']} » rendu par " . trim($p['prenom'] . ' ' . $p['nom']) . '.'
            . ($suite ? ' Attention : à mettre de côté pour ' . trim($suite['prenom'] . ' ' . $suite['nom']) . ' (réservation).' : ''));
    } catch (RuntimeException $ex) {
        flash('erreur', $ex->getMessage());
    }
    redirect(admin_url('guichet', isset($_POST['lecteur']) ? ['lecteur' => (int) $_POST['lecteur']] : []));
}

$lecteur = null;
$pretsLecteur = $reservationsLecteur = [];
if (!empty($_GET['lecteur'])) {
    $lecteur = row("SELECT * FROM utilisateurs WHERE id = ? AND role = 'lecteur'", [(int) $_GET['lecteur']]);
    if ($lecteur) {
        $pretsLecteur = prets('p.lecteur_id = ? AND p.date_retour IS NULL', [$lecteur['id']], 'p.date_retour_prevue');
        $reservationsLecteur = reservations("r.lecteur_id = ? AND r.statut IN ('en_attente','disponible')", [$lecteur['id']]);
    }
}

admin_page('guichet', [
    'lecteur' => $lecteur,
    'pretsLecteur' => $pretsLecteur,
    'reservationsLecteur' => $reservationsLecteur,
    'codePropose' => get_str('code', ''),
    'derniers' => prets('1', [], 'p.id DESC', 8),
], 'Guichet', 'guichet');
