<?php
/**
 * Import d'auteurs, d'ouvrages ou de la liste des étudiants depuis un fichier : envoi → aperçu ligne par ligne → confirmation.
 * L'analyse est gardée en session entre l'aperçu et la confirmation.
 */

const TYPES_IMPORT = ['ouvrages', 'auteurs', 'collections', 'etudiants'];

$quoi = get_str('quoi') ?: post_str('quoi');
$quoi = in_array($quoi, TYPES_IMPORT, true) ? $quoi : 'ouvrages';
// Chaque type d'import a son droit : on bascule sur le premier qui est permis
if (!peut("import.$quoi")) {
    foreach (TYPES_IMPORT as $autre) {
        if (peut("import.$autre")) {
            $quoi = $autre;
            break;
        }
    }
}
exiger("import.$quoi");

if ($action === 'modele') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="modele-' . $quoi . '.csv"');
    echo "\xEF\xBB\xBF" . ($quoi === 'etudiants' ? modele_import_etudiants() : modele_import($quoi)); // BOM : Excel ouvre le fichier en UTF-8
    exit;
}

if ($action === 'analyser') {
    exiger_post();
    try {
        if ($quoi === 'etudiants') {
            $analyse = analyser_etudiants(lire_fichier_import('fichier', IMPORT_ETUDIANTS_LIGNES_MAX, IMPORT_ETUDIANTS_TAILLE_MAX));
        } else {
            $lignes = lire_fichier_import('fichier');
            $analyse = match ($quoi) {
                'auteurs' => analyser_auteurs($lignes),
                'collections' => analyser_collections($lignes),
                default => analyser_ouvrages($lignes),
            };
        }
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
    if ($import['quoi'] === 'etudiants') {
        $b = importer_etudiants($import['analyse']);
        flash('succes', 'Liste des étudiants mise à jour : ' . pluriel($b['nouveaux'], 'étudiant ajouté', 'étudiants ajoutés')
            . ', ' . pluriel($b['maj'], 'fiche corrigée', 'fiches corrigées') . '. Personne n’a été retiré de la liste.');
        redirect(admin_url(peut('etudiants.voir') ? 'etudiants' : 'import', peut('etudiants.voir') ? [] : ['quoi' => 'etudiants']));
    }
    if ($import['quoi'] === 'auteurs') {
        $n = importer_auteurs($import['analyse']);
        flash('succes', pluriel($n, 'auteur importé', 'auteurs importés') . '.');
        redirect(admin_url('auteurs'));
    }
    if ($import['quoi'] === 'collections') {
        $n = importer_collections($import['analyse']);
        flash('succes', pluriel($n, 'collection importée', 'collections importées') . '.');
        redirect(admin_url(peut('collections.voir') ? 'collections' : 'import', peut('collections.voir') ? [] : ['quoi' => 'collections']));
    }
    $b = importer_ouvrages($import['analyse']);
    flash('succes', pluriel($b['ouvrages'], 'ouvrage importé', 'ouvrages importés') . ', ' . pluriel($b['exemplaires'], 'exemplaire créé', 'exemplaires créés')
        . ($b['domaines'] ? ', ' . pluriel($b['domaines'], 'domaine créé', 'domaines créés') : '')
        . ($b['types'] ? ', ' . pluriel($b['types'], 'type créé', 'types créés') : '')
        . ($b['collections'] ? ', ' . pluriel($b['collections'], 'collection créée', 'collections créées') : '') . '.');
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
