<?php
/**
 * Import d'auteurs ou d'ouvrages depuis un fichier : envoi → aperçu ligne par ligne → confirmation.
 * L'analyse est gardée en session entre l'aperçu et la confirmation.
 */

$quoi = in_array(get_str('quoi') ?: post_str('quoi'), ['auteurs', 'ouvrages'], true) ? (get_str('quoi') ?: post_str('quoi')) : 'ouvrages';
// Chaque type d'import a son droit : on bascule sur celui qui est permis
if (!peut("import.$quoi") && peut('import.' . ($quoi === 'auteurs' ? 'ouvrages' : 'auteurs'))) {
    $quoi = $quoi === 'auteurs' ? 'ouvrages' : 'auteurs';
}
exiger("import.$quoi");

if ($action === 'modele') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="modele-' . $quoi . '.csv"');
    echo "\xEF\xBB\xBF" . modele_import($quoi); // BOM : Excel ouvre le fichier en UTF-8
    exit;
}

if ($action === 'analyser') {
    exiger_post();
    try {
        $lignes = lire_fichier_import('fichier');
        $analyse = $quoi === 'auteurs' ? analyser_auteurs($lignes) : analyser_ouvrages($lignes);
        $_SESSION['import'] = ['quoi' => $quoi, 'analyse' => $analyse, 'fichier' => (string) ($_FILES['fichier']['name'] ?? '')];
        redirect(admin_url('import/apercu'));
    } catch (RuntimeException $ex) {
        flash('erreur', $ex->getMessage());
        redirect(admin_url('import', ['quoi' => $quoi]));
    }
}

if ($action === 'confirmer') {
    exiger_post();
    $import = $_SESSION['import'] ?? null;
    unset($_SESSION['import']);
    if (!$import) {
        flash('erreur', 'Aucun import en attente : envoyez de nouveau le fichier.');
        redirect(admin_url('import'));
    }
    exiger('import.' . $import['quoi']);
    if ($import['quoi'] === 'auteurs') {
        $n = importer_auteurs($import['analyse']);
        flash('succes', pluriel($n, 'auteur importé', 'auteurs importés') . '.');
        redirect(admin_url('auteurs'));
    }
    $b = importer_ouvrages($import['analyse']);
    flash('succes', pluriel($b['ouvrages'], 'ouvrage importé', 'ouvrages importés') . ', ' . pluriel($b['exemplaires'], 'exemplaire créé', 'exemplaires créés')
        . ($b['domaines'] ? ', ' . pluriel($b['domaines'], 'domaine créé', 'domaines créés') : '')
        . ($b['types'] ? ', ' . pluriel($b['types'], 'type créé', 'types créés') : '') . '.');
    redirect(admin_url('ouvrages'));
}

if ($action === 'apercu') {
    $import = $_SESSION['import'] ?? null;
    if (!$import) {
        redirect(admin_url('import'));
    }
    admin_page('import_apercu', $import, 'Aperçu de l’import', 'import');
}

admin_page('import', ['quoi' => $quoi], 'Importer', 'import');
