<?php
/**
 * Migration unique des données de l'ancien site Joomla vers la nouvelle base.
 *
 * Usage (depuis la racine du projet) :
 *   php install/migrer_joomla.php [base_joomla] [prefixe]
 * Par défaut : base « exeltis », préfixe « trcto_ ».
 *
 * Le script (ré)crée toutes les tables de la nouvelle base à partir de install/schema.sql,
 * puis importe domaines, types, auteurs, ouvrages, exemplaires, contenus de l'accueil,
 * et crée un compte administrateur dont les identifiants sont écrits dans install/IDENTIFIANTS.txt.
 */

if (PHP_SAPI !== 'cli') {
    exit('À lancer en ligne de commande.');
}

require dirname(__DIR__) . '/app/bootstrap.php';

$srcDb  = $argv[1] ?? 'exeltis';
$prefix = $argv[2] ?? 'trcto_';
$cfg    = $GLOBALS['config']['db'];

// 1. Création de la base cible et des tables
$serveur = new PDO("mysql:host={$cfg['host']};charset=utf8mb4", $cfg['user'], $cfg['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$serveur->exec("CREATE DATABASE IF NOT EXISTS `{$cfg['name']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
foreach (preg_split('/;\s*\n/', file_get_contents(__DIR__ . '/schema.sql')) as $instr) {
    $instr = trim(preg_replace('/^--.*$/m', '', $instr));
    if ($instr !== '') {
        db()->exec($instr);
    }
}
echo "Tables créées dans « {$cfg['name']} ».\n";

$src = new PDO("mysql:host={$cfg['host']};dbname=$srcDb;charset=utf8mb4", $cfg['user'], $cfg['password'], [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);
$t = fn(string $nom) => $prefix . $nom;
$srcRows = fn(string $sql, array $p = []) => (function () use ($src, $sql, $p) {
    $st = $src->prepare($sql);
    $st->execute($p);
    return $st->fetchAll();
})();
$srcVal = fn(string $sql, array $p = []) => (function () use ($src, $sql, $p) {
    $st = $src->prepare($sql);
    $st->execute($p);
    return $st->fetchColumn();
})();

// Les modules Joomla pointent vers « index.php?Itemid=N » : on traduit via les chemins du menu
$cheminsMenu = [];
foreach ($srcRows("SELECT id, path, home FROM {$t('menu')} WHERE client_id = 0 AND published = 1") as $mi) {
    $cheminsMenu[(int) $mi['id']] = $mi['home'] ? '' : $mi['path'];
}

/** Réécrit les liens relatifs de l'ancien site avec la balise {base} (racine du nouveau site). */
$relocaliser = function (string $html) use ($cheminsMenu): string {
    $html = preg_replace('#<form class="biblio-search".*?</form>#s', '{recherche}', $html);
    $html = preg_replace_callback('#href="index\.php\?Itemid=(\d+)"#', function ($m) use ($cheminsMenu) {
        $p = $cheminsMenu[(int) $m[1]] ?? '';
        return 'href="{base}/index.php' . ($p !== '' ? '/' . $p : '') . '"';
    }, $html);
    $html = preg_replace('#href="index\.php"#', 'href="{base}/index.php"', $html);
    return preg_replace('#(href|src)="(/biblio/)?(images|index\.php)/#', '$1="{base}/$3/', $html);
};

// 2. Domaines (sous-catégories de « Ouvrages ») + icônes du module « Nos domaines »
$racine = $srcVal("SELECT id FROM {$t('categories')} WHERE extension = 'com_content' AND alias = 'ouvrages'");
$htmlDomaines = (string) $srcVal("SELECT content FROM {$t('modules')} WHERE module = 'mod_custom' AND content LIKE '%biblio-domaines%' LIMIT 1");
preg_match_all('#<i class="fa (fa-[a-z0-9-]+)".*?href="index\.php\?Itemid=(\d+)"#s', $htmlDomaines, $m, PREG_SET_ORDER);
$icones = [];
foreach ($m as $x) {
    $icones[basename($cheminsMenu[(int) $x[2]] ?? '')] = $x[1];
}
$mapDomaine = [];
foreach ($srcRows("SELECT id, title, alias, description FROM {$t('categories')} WHERE parent_id = ? AND published = 1 ORDER BY lft", [$racine]) as $i => $c) {
    $mapDomaine[$c['id']] = insert('domaines', [
        'nom' => $c['title'],
        'slug' => $c['alias'],
        'description' => trim(strip_tags($c['description'])),
        'icone' => $icones[$c['alias']] ?? 'fa-book',
        'ordre' => $i + 1,
    ]);
}
echo count($mapDomaine) . " domaines.\n";

// 3. Types (tags enfants de « Types de documents ») ; pluriel repris des libellés du menu
$parentTypes = $srcVal("SELECT id FROM {$t('tags')} WHERE title = 'Types de documents'");
$pluriels = [];
foreach ($srcRows("SELECT title, link FROM {$t('menu')} WHERE link LIKE 'index.php?option=com_tags&view=tag&id[0]=%' AND published = 1") as $mi) {
    $pluriels[(int) substr($mi['link'], strrpos($mi['link'], '=') + 1)] = $mi['title'];
}
$mapType = [];
$typeParNom = [];
foreach ($srcRows("SELECT id, title, alias, description FROM {$t('tags')} WHERE parent_id = ? AND published = 1 ORDER BY lft", [$parentTypes]) as $i => $tg) {
    $mapType[$tg['id']] = insert('types', [
        'nom' => $tg['title'],
        'nom_pluriel' => $pluriels[$tg['id']] ?? $tg['title'] . 's',
        'slug' => $tg['alias'],
        'description' => trim(strip_tags($tg['description'])),
        'ordre' => $i + 1,
    ]);
    $typeParNom[$tg['title']] = $mapType[$tg['id']];
}
echo count($mapType) . " types.\n";

// 4. Auteurs (tags enfants de « Auteurs »)
$parentAuteurs = $srcVal("SELECT id FROM {$t('tags')} WHERE title = 'Auteurs' AND parent_id = 1");
$mapAuteur = [];
foreach ($srcRows("SELECT id, title, alias FROM {$t('tags')} WHERE parent_id = ? AND published = 1 ORDER BY title", [$parentAuteurs]) as $tg) {
    $mapAuteur[$tg['id']] = insert('auteurs', ['nom' => $tg['title'], 'slug' => $tg['alias']]);
}
echo count($mapAuteur) . " auteurs.\n";

// 5. Ouvrages (articles des domaines) + champs personnalisés + exemplaires
$champs = [];
foreach ($srcRows("SELECT id, name FROM {$t('fields')} WHERE context = 'com_content.article'") as $f) {
    $champs[$f['id']] = $f['name'];
}
$nbOuvrages = $nbEx = 0;
$ids = implode(',', array_map('intval', array_keys($mapDomaine)));
foreach ($srcRows("SELECT * FROM {$t('content')} WHERE state = 1 AND catid IN ($ids) ORDER BY id") as $a) {
    $f = [];
    foreach ($srcRows("SELECT field_id, value FROM {$t('fields_values')} WHERE item_id = ?", [$a['id']]) as $v) {
        if (isset($champs[$v['field_id']])) {
            $f[$champs[$v['field_id']]] = $v['value'];
        }
    }
    $img = json_decode($a['images'] ?: '{}', true);

    $typeId = null;
    $tags = $srcRows("SELECT tag_id FROM {$t('contentitem_tag_map')} WHERE type_alias = 'com_content.article' AND content_item_id = ?", [$a['id']]);
    foreach ($tags as $tg) {
        $typeId ??= $mapType[$tg['tag_id']] ?? null;
    }
    $typeId ??= $typeParNom[$f['type-document'] ?? ''] ?? null;

    $id = insert('ouvrages', [
        'titre' => $a['title'],
        'slug' => $a['alias'],
        'domaine_id' => $mapDomaine[$a['catid']],
        'type_id' => $typeId,
        'resume' => trim($a['introtext']),
        'description' => trim($a['fulltext']),
        'editeur' => $f['editeur'] ?? '',
        'annee' => $f['annee'] ?? '',
        'isbn' => $f['isbn'] ?? '',
        'cote' => $f['cote'] ?? '',
        'pages' => is_numeric($f['pages'] ?? '') ? (int) $f['pages'] : null,
        'langue' => $f['langue'] ?? 'Français',
        'image' => $img['image_intro'] ?? ($img['image_fulltext'] ?? ''),
        'consultation_sur_place' => ($f['disponibilite'] ?? '') === 'Consultation sur place' ? 1 : 0,
        'coup_de_coeur' => (int) $a['featured'],
        'publie' => 1,
        'cree_le' => $a['created'],
    ]);
    $nbOuvrages++;

    // Auteurs : ordre du champ texte « auteurs », sinon tags
    $noms = array_filter(array_map('trim', explode(',', $f['auteurs'] ?? '')));
    if (!$noms) {
        foreach ($tags as $tg) {
            if (isset($mapAuteur[$tg['tag_id']])) {
                $noms[] = val('SELECT nom FROM auteurs WHERE id = ?', [$mapAuteur[$tg['tag_id']]]);
            }
        }
    }
    lier_auteurs($id, $noms);

    // Exemplaires : codes « COTE-1, COTE-2… »
    $n = max(0, (int) ($f['exemplaires'] ?? 1));
    $prefixe = ($f['cote'] ?? '') !== '' ? $f['cote'] : sprintf('EX%04d', $id);
    for ($i = 1; $i <= $n; $i++) {
        insert('exemplaires', ['ouvrage_id' => $id, 'code' => "$prefixe-$i"]);
        $nbEx++;
    }
}
echo "$nbOuvrages ouvrages, $nbEx exemplaires.\n";

// Auteurs sans ouvrage (tags orphelins) : on les garde, ils restent invisibles côté public.

// 6. Diaporama de l'accueil (contenu du Smart Slider d'origine)
$slides = [
    ["Bienvenue à la\nbibliothèque", 'Université de Kara — consultez en ligne l’ensemble de nos ouvrages, classés par domaine, par type et par auteur.', 'Parcourir le catalogue', 'catalogue', 'images/biblio/photo1.jpg'],
    ["Explorez nos\ndomaines", 'Informatique, sciences, médecine, droit, économie, littérature, histoire, philosophie… trouvez rapidement les ouvrages de votre discipline.', 'Voir les domaines', 'domaines', 'images/biblio/photo3.jpg'],
    ["Livres, thèses,\nrevues et mémoires", 'Manuels universitaires, romans, dictionnaires, revues, thèses et mémoires : filtrez le catalogue par type de document.', 'Voir les types', 'types', 'images/biblio/photo8.jpg'],
    ["Découvrez\nles auteurs", 'De Victor Hugo à Cheikh Anta Diop, retrouvez tous les ouvrages d’un auteur en un clic.', 'Index des auteurs', 'auteurs', 'images/biblio/photo2.jpg'],
];
foreach ($slides as $i => [$titre, $texte, $bouton, $lien, $image]) {
    insert('slides', compact('titre', 'texte', 'bouton', 'lien', 'image') + ['ordre' => $i + 1]);
}

// 7. Citations (module « Paroles d'auteurs »)
$params = json_decode((string) $srcVal("SELECT params FROM {$t('modules')} WHERE module = 'mod_jt_testimonial' AND published = 1 AND title LIKE 'Paroles%' LIMIT 1"), true);
$i = 0;
foreach ($params['testimonials_items'] ?? [] as $c) {
    insert('citations', ['texte' => $c['testimonial'], 'auteur' => $c['name'], 'source' => $c['position'], 'image' => $c['img'], 'ordre' => ++$i]);
}
echo "$i citations.\n";

// 8. Blocs éditables repris des modules « Custom »
$module = fn(int $id) => (string) $srcVal("SELECT content FROM {$t('modules')} WHERE id = ?", [$id]);
$blocs = [
    'accueil_apropos' => ['Accueil — À propos de la bibliothèque', $module(331)],
    'accueil_types'   => ['Accueil — Introduction des types de documents', ''],
    'appel_contact'   => ['Accueil — Bandeau « Vous ne trouvez pas un ouvrage ? »', $module(338)],
    'pied_infos'      => ['Pied de page — Présentation et coordonnées', $module(109)],
    'pied_horaires'   => ['Pied de page — Horaires d’ouverture', $module(340)],
    'pied_copyright'  => ['Pied de page — Copyright', $module(112)],
    'contact_infos'   => ['Page contact — Informations complémentaires', ''],
];
// Introduction des types : on garde l'en-tête du module et on génère la liste depuis la base
if (preg_match('#^(.*?)<div>\s*<ul class="biblio-types">#s', $module(333), $mm)) {
    $blocs['accueil_types'][1] = preg_replace('#^\s*<div class="special-work-wrap">\s*<div class="[^"]*special-work">\s*#', '', $mm[1]);
}
$contact = $srcRows("SELECT * FROM {$t('contact_details')} WHERE published = 1 ORDER BY id LIMIT 1")[0] ?? [];
$blocs['contact_infos'][1] = $contact['misc'] ?? '';
foreach ($blocs as $cle => [$titre, $contenu]) {
    insert('blocs', ['cle' => $cle, 'titre' => $titre, 'contenu' => trim($relocaliser($contenu))]);
}

$reglages = [
    'contact_nom'       => $contact['name'] ?? 'Bibliothèque universitaire',
    'contact_role'      => $contact['con_position'] ?? '',
    'contact_adresse'   => $contact['address'] ?? '',
    'contact_ville'     => $contact['suburb'] ?? '',
    'contact_pays'      => $contact['country'] ?? '',
    'contact_telephone' => $contact['telephone'] ?? '',
];
if (!empty($contact['email_to'])) {
    $reglages['contact_email'] = $contact['email_to'];
}
foreach ($reglages as $cle => $valeur) {
    q('REPLACE INTO reglages (cle, valeur) VALUES (?, ?)', [$cle, $valeur]);
}

// 9. Compte administrateur initial
$motDePasse = rtrim(strtr(base64_encode(random_bytes(12)), '+/', 'Kx'), '=');
insert('utilisateurs', [
    'role' => 'admin',
    'nom' => 'Administrateur',
    'prenom' => '',
    'email' => 'admin@biblio.local',
    'mot_de_passe' => password_hash($motDePasse, PASSWORD_DEFAULT),
]);
file_put_contents(__DIR__ . '/IDENTIFIANTS.txt',
    "Compte administrateur créé par la migration (" . date('d/m/Y H:i') . ")\n"
    . "Connexion : <adresse du site>/admin/  (ex. http://localhost/biblio/admin/)\n"
    . "E-mail     : admin@biblio.local\n"
    . "Mot de passe : $motDePasse\n\n"
    . "Changez ce mot de passe dès la première connexion (Mon compte), puis supprimez ce fichier.\n");

echo "Compte administrateur créé : identifiants dans install/IDENTIFIANTS.txt\n";
echo "Migration terminée.\n";
