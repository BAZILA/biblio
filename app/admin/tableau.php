<?php
/**
 * Tableau de bord personnalisable : chaque compte choisit les blocs affichés et leur ordre (bouton « Personnaliser »).
 * Seuls les blocs permis par le rôle sont proposés : un agent de prêt, un éditeur du site ou un administrateur
 * n'ont donc pas le même tableau de bord. Préférences enregistrées dans utilisateurs.preferences (JSON).
 */

/** Blocs disponibles : [libellé, icône, permissions (l'une suffit ; vide = tout le monde), taille, description] */
const BLOCS_TABLEAU = [
    'raccourcis'   => ['Raccourcis', 'fa-bolt', [], 'large', 'Boutons vers vos actions les plus fréquentes, selon vos droits.'],
    'chiffres'     => ['Chiffres clés', 'fa-gauge', [], 'large', 'Ouvrages, exemplaires, prêts, retards, lecteurs.'],
    'reservations' => ['Réservations à préparer', 'fa-hand-holding', ['reservations.voir'], 'large', 'Exemplaires à mettre de côté pour les lecteurs.'],
    'retards'      => ['Prêts en retard', 'fa-clock', ['prets.voir'], 'large', 'Lecteurs à relancer, avec leurs coordonnées.'],
    'demandes'     => ['Demandes de prolongation', 'fa-calendar-plus', ['demandes.voir'], 'large', 'Demandes des lecteurs en attente de décision.'],
    'echeances'    => ['À rendre d’ici 2 jours', 'fa-hourglass-half', ['prets.voir'], 'demi', 'Prêts dont l’échéance approche.'],
    'populaires'   => ['Les plus empruntés', 'fa-ranking-star', ['prets.voir', 'ouvrages.voir'], 'demi', 'Ouvrages les plus prêtés sur 12 mois.'],
    'inscriptions' => ['Comptes lecteurs à valider', 'fa-user-check', ['lecteurs.voir'], 'demi', 'Demandes de compte des enseignants, du personnel et des visiteurs.'],
    'messages'     => ['Messages non lus', 'fa-envelope', ['messages.voir'], 'demi', 'Derniers messages reçus par la page Contact.'],
    'actualites'   => ['Actualités et évènements', 'fa-newspaper', ['actualites.voir'], 'demi', 'Évènements à venir, brouillons, diaporama de l’accueil.'],
    'securite'     => ['Sécurité (24 heures)', 'fa-shield-halved', ['audit.voir'], 'demi', 'Connexions refusées ou bloquées, d’après le journal d’audit.'],
    'courbe'       => ['Activité des 12 derniers mois', 'fa-chart-line', ['prets.voir'], 'large', 'Courbe des prêts et des retours, mois par mois.'],
];

/** Blocs que le rôle de l'utilisateur permet. */
function blocs_permis(): array
{
    return array_filter(BLOCS_TABLEAU, fn($b) => !$b[2] || peut(...$b[2]));
}

/** Préférences enregistrées : ['ordre' => [...], 'caches' => [...]] */
function preferences_tableau(array $u): array
{
    $p = json_decode((string) ($u['preferences'] ?? ''), true);
    return is_array($p['tableau'] ?? null) ? $p['tableau'] + ['ordre' => [], 'caches' => []] : ['ordre' => [], 'caches' => []];
}

/** Tous les blocs permis dans l'ordre choisi (les nouveaux blocs à la fin), avec leur visibilité. */
function blocs_ordonnes(array $u): array
{
    $permis = blocs_permis();
    $pref = preferences_tableau($u);
    $ordre = array_values(array_unique(array_merge(array_intersect($pref['ordre'], array_keys($permis)), array_keys($permis))));
    $liste = [];
    foreach ($ordre as $cle) {
        $liste[$cle] = $permis[$cle] + [5 => !in_array($cle, $pref['caches'], true)];
    }
    return $liste;
}

function enregistrer_preferences(int $id, ?array $tableau): void
{
    $p = json_decode((string) val('SELECT preferences FROM utilisateurs WHERE id = ?', [$id]), true) ?: [];
    if ($tableau === null) {
        unset($p['tableau']);
    } else {
        $p['tableau'] = $tableau;
    }
    q('UPDATE utilisateurs SET preferences = ? WHERE id = ?', [$p ? json_encode($p, JSON_UNESCAPED_UNICODE) : null, $id]);
}

if ($action === 'personnaliser') {
    exiger_post();
    if (isset($_POST['reinitialiser'])) {
        enregistrer_preferences((int) $moi['id'], null);
        flash('succes', 'Tableau de bord remis à l’affichage par défaut.');
    } else {
        $permis = array_keys(blocs_permis());
        $ordre = array_values(array_intersect(array_map('strval', (array) ($_POST['ordre'] ?? [])), $permis));
        $visibles = array_intersect(array_map('strval', (array) ($_POST['visibles'] ?? [])), $permis);
        enregistrer_preferences((int) $moi['id'], ['ordre' => $ordre, 'caches' => array_values(array_diff($permis, $visibles))]);
        flash('succes', 'Tableau de bord enregistré : ' . pluriel(count($visibles), 'bloc affiché', 'blocs affichés') . '.');
    }
    redirect(admin_url());
}

$blocs = blocs_ordonnes($moi);
$affiches = array_keys(array_filter($blocs, fn($b) => $b[5]));
$voit = fn(string $cle) => in_array($cle, $affiches, true);

// Données : seulement pour les blocs affichés
$donnees = ['blocs' => $blocs, 'affiches' => $affiches, 'role' => role_de()];
if ($voit('chiffres')) {
    $donnees['stats'] = [
        'ouvrages'    => (int) val('SELECT COUNT(*) FROM ouvrages'),
        'exemplaires' => (int) val("SELECT COUNT(*) FROM exemplaires WHERE statut <> 'retire'"),
        'dispo'       => (int) val("SELECT COUNT(*) FROM exemplaires WHERE statut = 'disponible'"),
        'prets'       => (int) val('SELECT COUNT(*) FROM prets WHERE date_retour IS NULL'),
        'retards'     => (int) val('SELECT COUNT(*) FROM prets WHERE date_retour IS NULL AND date_retour_prevue < CURDATE()'),
        'lecteurs'    => (int) val("SELECT COUNT(*) FROM utilisateurs WHERE role = 'lecteur' AND actif = 1"),
        'a_valider'   => (int) val("SELECT COUNT(*) FROM utilisateurs WHERE role = 'lecteur' AND en_attente = 1"),
        'prets_mois'  => (int) val("SELECT COUNT(*) FROM prets WHERE date_pret >= DATE_FORMAT(CURDATE(), '%Y-%m-01')"),
        'actualites'  => peut('actualites.voir') ? (int) val('SELECT COUNT(*) FROM actualites a WHERE ' . ACTUALITE_VISIBLE) : 0,
    ];
}
if ($voit('retards')) {
    $donnees['retards'] = prets('p.date_retour IS NULL AND p.date_retour_prevue < CURDATE()', [], 'p.date_retour_prevue', 8);
    $donnees['nbRetards'] = (int) val('SELECT COUNT(*) FROM prets WHERE date_retour IS NULL AND date_retour_prevue < CURDATE()');
}
if ($voit('demandes')) {
    $donnees['demandes'] = demandes_prolongation("dp.statut = 'en_attente'", [], 'dp.demandee_le LIMIT 5');
}
if ($voit('reservations')) {
    $donnees['aRetirer'] = reservations("r.statut = 'disponible'", [], 'r.expire_le');
    $donnees['enAttente'] = (int) val("SELECT COUNT(*) FROM reservations WHERE statut = 'en_attente'");
}
if ($voit('echeances')) {
    $donnees['echeances'] = prets('p.date_retour IS NULL AND p.date_retour_prevue BETWEEN CURDATE() AND CURDATE() + INTERVAL 2 DAY', [], 'p.date_retour_prevue', 8);
}
if ($voit('inscriptions')) {
    $donnees['inscriptions'] = rows("SELECT * FROM utilisateurs WHERE role = 'lecteur' AND en_attente = 1 ORDER BY cree_le DESC LIMIT 5");
}
if ($voit('messages')) {
    $donnees['messages'] = rows('SELECT * FROM messages WHERE lu = 0 ORDER BY recu_le DESC LIMIT 5');
}
if ($voit('populaires')) {
    $donnees['populaires'] = rows('SELECT o.id, o.titre, COUNT(*) AS nb FROM prets p JOIN exemplaires e ON e.id = p.exemplaire_id
                                   JOIN ouvrages o ON o.id = e.ouvrage_id WHERE p.date_pret >= CURDATE() - INTERVAL 1 YEAR
                                   GROUP BY o.id ORDER BY nb DESC, o.titre LIMIT 5');
}
if ($voit('actualites')) {
    $donnees['actus'] = [
        'avenir' => evenements_a_venir(3),
        'brouillons' => (int) val('SELECT COUNT(*) FROM actualites WHERE publie = 0'),
        'programmees' => (int) val('SELECT COUNT(*) FROM actualites WHERE publie = 1 AND date_publication > NOW()'),
        'diaporama' => (int) val('SELECT COUNT(*) FROM actualites a WHERE a.au_diaporama = 1 AND ' . ACTUALITE_VISIBLE),
        'dernieres' => actualites('1', [], 'a.date_publication DESC, a.id DESC', 3),
    ];
}
if ($voit('securite')) {
    $donnees['securite'] = rows("SELECT le, action, resultat, ip FROM audit WHERE categorie = 'securite' AND le > NOW() - INTERVAL 1 DAY ORDER BY id DESC LIMIT 5");
    $donnees['nbSecurite'] = (int) val("SELECT COUNT(*) FROM audit WHERE categorie = 'securite' AND le > NOW() - INTERVAL 1 DAY");
}
if ($voit('courbe')) {
    $donnees['courbe'] = series_mensuelles(date('Y-m-01', strtotime('first day of -11 months')), date('Y-m-d'));
}

admin_page('tableau', $donnees, 'Tableau de bord', 'tableau');
