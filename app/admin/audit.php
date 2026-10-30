<?php
/** Journal d'audit : consultation filtrée et export CSV. Le journal ne se modifie pas et ne se vide pas. */

exiger('audit.voir');

const CATEGORIES_AUDIT = ['action' => 'Actions', 'consultation' => 'Pages consultées', 'connexion' => 'Connexions', 'securite' => 'Sécurité (refus, blocages)', 'export' => 'Exports et téléchargements'];

$f = [
    'du' => get_str('du', date('Y-m-d', strtotime('-30 days'))), 'au' => get_str('au', date('Y-m-d')),
    'categorie' => get_str('categorie'), 'espace' => get_str('espace'), 'statut' => get_str('statut'),
    'acteur' => get_str('acteur'), 'q' => get_str('q'),
];
$where = ['a.le >= ?', 'a.le < ? + INTERVAL 1 DAY'];
$params = [date_naissance_iso($f['du']) ?? date('Y-m-d', strtotime('-30 days')), date_naissance_iso($f['au']) ?? date('Y-m-d')];
if (isset(CATEGORIES_AUDIT[$f['categorie']])) {
    $where[] = 'a.categorie = ?';
    $params[] = $f['categorie'];
}
if (in_array($f['espace'], ['admin', 'site'], true)) {
    $where[] = 'a.espace = ?';
    $params[] = $f['espace'];
}
if (in_array($f['statut'], ['succes', 'erreur', 'info'], true)) {
    $where[] = 'a.statut = ?';
    $params[] = $f['statut'];
}
if ($f['acteur'] !== '') {
    $where[] = 'a.acteur = ?';
    $params[] = $f['acteur'];
}
foreach (array_filter(preg_split('/\s+/u', $f['q']) ?: []) as $mot) {
    $where[] = '(a.action LIKE ? OR a.resultat LIKE ? OR a.details LIKE ? OR a.acteur LIKE ? OR a.ip LIKE ?)';
    array_push($params, ...array_fill(0, 5, "%$mot%"));
}
$sql = 'FROM audit a WHERE ' . implode(' AND ', $where);

if ($action === 'exporter') {
    exiger('audit.exporter');
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="journal-audit-' . $params[0] . '-au-' . $params[1] . '.csv"');
    $sortie = fopen('php://output', 'w');
    fwrite($sortie, "\xEF\xBB\xBF");
    fputcsv($sortie, ['Date', 'Qui', 'Espace', 'Catégorie', 'Action', 'Résultat', 'Statut', 'Adresse IP', 'Détails', 'Adresse'], ';', '"', '');
    foreach (rows("SELECT a.* $sql ORDER BY a.id DESC LIMIT 100000", $params) as $l) {
        fputcsv($sortie, [$l['le'], $l['acteur'], $l['espace'], CATEGORIES_AUDIT[$l['categorie']] ?? $l['categorie'], $l['action'], $l['resultat'],
            $l['statut'], $l['ip'], (string) $l['details'], $l['chemin']], ';', '"', '');
    }
    fclose($sortie);
    exit;
}

$pg = pagination_admin((int) val("SELECT COUNT(*) $sql", $params), 50);
admin_page('audit', [
    'f' => $f,
    'lignes' => rows("SELECT a.* $sql ORDER BY a.id DESC LIMIT {$pg['parPage']} OFFSET {$pg['offset']}", $params),
    'pg' => $pg,
    'acteurs' => array_column(rows('SELECT DISTINCT acteur FROM audit ORDER BY acteur'), 'acteur'),
    'resume' => row("SELECT COUNT(*) AS total, SUM(categorie = 'action') AS actions, SUM(categorie = 'securite') AS securite,
                            COUNT(DISTINCT utilisateur_id) AS personnes $sql", $params),
], 'Journal d’audit', 'audit');
