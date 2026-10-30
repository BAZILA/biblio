<?php
/**
 * Ajoute le diaporama et les citations de démonstration de la page d'accueil (install/contenu_accueil.sql).
 *   php install/ajouter_contenu_accueil.php              : ajoute seulement ce qui est vide (rien n'est doublé)
 *   php install/ajouter_contenu_accueil.php --remplacer  : remplace le diaporama et les citations existants
 * Ensuite, tout se modifie dans l'administration (Site → Diaporama d'accueil, Citations).
 */
if (PHP_SAPI !== 'cli') {
    exit('À lancer en ligne de commande.');
}
require dirname(__DIR__) . '/app/bootstrap.php';

$remplacer = in_array('--remplacer', $argv, true);
$sql = file_get_contents(__DIR__ . '/contenu_accueil.sql');
preg_match('/INSERT INTO slides .*?;\n/s', $sql, $slides);
preg_match('/INSERT INTO citations .*?;\n/s', $sql, $citations);

foreach (['slides' => [$slides[0], 'diapositive(s)'], 'citations' => [$citations[0], 'citation(s)']] as $table => [$instr, $libelle]) {
    $existantes = (int) val("SELECT COUNT(*) FROM $table");
    if ($existantes && !$remplacer) {
        echo ucfirst($table) . " : $existantes $libelle déjà présente(s), rien d'ajouté (--remplacer pour les remplacer).\n";
        continue;
    }
    if ($existantes) {
        db()->exec("DELETE FROM $table");
    }
    db()->exec($instr);
    echo ucfirst($table) . ' : ' . val("SELECT COUNT(*) FROM $table") . " $libelle ajoutée(s).\n";
}

// Images utilisées : signale celles qui manqueraient sur le serveur
foreach (array_unique(array_merge(array_column(rows('SELECT image FROM slides'), 'image'), array_column(rows('SELECT image FROM citations'), 'image'))) as $image) {
    if ($image !== '' && !is_file(ROOT_DIR . '/' . $image)) {
        echo "ATTENTION : image absente sur le serveur : $image\n";
    }
}
