<?php
/** Statistiques à l'écran sur une période, et rapports PDF (noir et blanc, logo UK seul en couleur). */

[$du, $au] = periode_demandee();

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
    's' => statistiques($du, $au),
    'courbe' => series_mensuelles($du, $au),
], 'Statistiques et rapports', 'rapports');
