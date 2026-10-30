<?php
/** Statistiques à l'écran sur une période, et rapports PDF (noir et blanc, logo UK seul en couleur). */

[$du, $au] = periode_demandee();
// Filtres communs à l'écran et aux PDF : centre documentaire et faculté du lecteur
$faculte = get_str('faculte');
$siteFiltre = (int) ($_GET['site'] ?? 0);
filtres_rapport(['site' => $siteFiltre, 'faculte' => $faculte]);

if ($action === 'pdf') {
    exiger('rapports.exporter');
    $type = get_str('type');
    if (!isset(RAPPORTS[$type])) {
        admin_introuvable('Rapport');
    }
    if (RAPPORTS[$type][2]) {
        exiger(...RAPPORTS[$type][2]);
    }
    rapport_pdf($type, $du, $au);
}

admin_page('rapports', [
    'du' => $du,
    'au' => $au,
    'faculte' => $faculte,
    'site' => $siteFiltre,
    'sites' => mon_site() === 0 && count(sites()) > 1 ? sites_options(false) : [],
    'facultes' => array_column(rows("SELECT DISTINCT faculte FROM utilisateurs WHERE role = 'lecteur' AND faculte <> '' ORDER BY faculte"), 'faculte'),
    's' => statistiques($du, $au),
    'courbe' => series_mensuelles($du, $au),
], 'Statistiques et rapports', 'rapports');
