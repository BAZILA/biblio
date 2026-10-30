<?php
/**
 * Import de listes depuis un fichier (CSV exporté d'Excel, ou texte simple).
 * Lecture tolérante : séparateur ; , ou tabulation, encodage UTF-8 ou Windows (Excel), en-têtes avec ou sans accents.
 */

const IMPORT_TAILLE_MAX = 2 * 1024 * 1024;
const IMPORT_LIGNES_MAX = 2000;
// Liste des étudiants : toute l'université en un seul fichier
const IMPORT_ETUDIANTS_LIGNES_MAX = 40000;
const IMPORT_ETUDIANTS_TAILLE_MAX = 8 * 1024 * 1024;

/** Colonnes reconnues pour les ouvrages : nom normalisé de l'en-tête → champ. */
const IMPORT_COLONNES_OUVRAGES = [
    'titre' => 'titre', 'sous_titre' => 'sous_titre', 'auteurs' => 'auteurs', 'auteur' => 'auteurs', 'auteur_s' => 'auteurs',
    'domaine' => 'domaine', 'type' => 'type', 'type_de_document' => 'type',
    'collection' => 'collection', 'numero_collection' => 'numero_collection', 'n_dans_la_collection' => 'numero_collection',
    'edition' => 'edition', 'mention_d_edition' => 'edition', 'tome' => 'tome', 'volume' => 'volume', 'n_de_volume' => 'volume',
    'editeur' => 'editeur', 'editeur_origine' => 'editeur', 'annee' => 'annee', 'annee_de_publication' => 'annee',
    'isbn' => 'isbn', 'issn' => 'issn', 'indice_dewey' => 'indice_dewey', 'dewey' => 'indice_dewey', 'indice' => 'indice_dewey',
    'cote' => 'cote', 'emplacement' => 'emplacement', 'localisation' => 'emplacement',
    'pages' => 'pages', 'nombre_de_pages' => 'pages', 'langue' => 'langue',
    'exemplaires' => 'exemplaires', 'nombre_d_exemplaires' => 'exemplaires', 'resume' => 'resume', 'image' => 'image', 'couverture' => 'image',
    'consultation_sur_place' => 'sur_place', 'sur_place' => 'sur_place', 'coup_de_coeur' => 'coup_de_coeur', 'publie' => 'publie',
];

function normaliser_entete(string $s): string
{
    return trim(str_replace('-', '_', slugify($s)), '_');
}

/** Clé de comparaison : minuscules, sans accents ni ponctuation (« Médecine & Santé » = « medecine sante »). */
function cle_comparaison(string $s): string
{
    return slugify($s);
}

/** Lit le fichier envoyé et renvoie ses lignes (tableaux de cellules), en UTF-8. */
function lire_fichier_import(string $champ, int $lignesMax = IMPORT_LIGNES_MAX, int $tailleMax = IMPORT_TAILLE_MAX): array
{
    $f = $_FILES[$champ] ?? null;
    if (!$f || $f['error'] === UPLOAD_ERR_NO_FILE) {
        throw new RuntimeException('Choisissez un fichier.');
    }
    if ($f['error'] !== UPLOAD_ERR_OK || $f['size'] > $tailleMax) {
        throw new RuntimeException('Fichier refusé : ' . round($tailleMax / 1048576) . ' Mo maximum.');
    }
    $texte = (string) file_get_contents($f['tmp_name']);
    if (str_starts_with($texte, "\xEF\xBB\xBF")) {
        $texte = substr($texte, 3);
    } elseif (!mb_check_encoding($texte, 'UTF-8')) {
        $texte = mb_convert_encoding($texte, 'UTF-8', 'Windows-1252'); // CSV enregistré par Excel
    }
    $texte = str_replace(["\r\n", "\r"], "\n", $texte);
    $premiere = strtok($texte, "\n") ?: '';
    $separateurs = [';' => substr_count($premiere, ';'), "\t" => substr_count($premiere, "\t"), ',' => substr_count($premiere, ',')];
    arsort($separateurs);
    $sep = reset($separateurs) > 0 ? key($separateurs) : ';';

    $lignes = [];
    $flux = fopen('php://temp', 'r+');
    fwrite($flux, $texte);
    rewind($flux);
    while (($cellules = fgetcsv($flux, 0, $sep, '"', '')) !== false) {
        $cellules = array_map(fn($c) => trim((string) $c), $cellules);
        if (implode('', $cellules) === '') {
            continue;
        }
        $lignes[] = $cellules;
        if (count($lignes) > $lignesMax + 1) {
            throw new RuntimeException('Fichier trop long : ' . $lignesMax . ' lignes au maximum par import.');
        }
    }
    fclose($flux);
    if (!$lignes) {
        throw new RuntimeException('Le fichier est vide.');
    }
    return $lignes;
}

/** Analyse une liste d'auteurs (un nom par ligne, première colonne). */
function analyser_auteurs(array $lignes): array
{
    if (in_array(normaliser_entete($lignes[0][0]), ['nom', 'auteur', 'auteurs', 'nom_de_l_auteur'], true)) {
        array_shift($lignes);
    }
    $existants = [];
    foreach (rows('SELECT nom FROM auteurs') as $a) {
        $existants[cle_comparaison($a['nom'])] = true;
    }
    $resultat = [];
    $vus = [];
    foreach ($lignes as $i => $cellules) {
        $nom = preg_replace('/\s+/u', ' ', $cellules[0]);
        $cle = cle_comparaison($nom);
        $statut = 'nouveau';
        $message = '';
        if ($nom === '' || mb_strlen($nom) > 190) {
            [$statut, $message] = ['erreur', 'Nom vide ou trop long.'];
        } elseif (isset($existants[$cle])) {
            [$statut, $message] = ['ignore', 'Déjà dans l’index des auteurs.'];
        } elseif (isset($vus[$cle])) {
            [$statut, $message] = ['ignore', 'En double dans le fichier.'];
        }
        $vus[$cle] = true;
        $resultat[] = ['ligne' => $i + 1, 'nom' => $nom, 'statut' => $statut, 'message' => $message];
    }
    return $resultat;
}

function importer_auteurs(array $analyse): int
{
    $n = 0;
    foreach ($analyse as $l) {
        if ($l['statut'] === 'nouveau') {
            $slug = 'auteur-' . slugify($l['nom']);
            for ($base = $slug, $i = 2; val('SELECT 1 FROM auteurs WHERE slug = ?', [$slug]); $i++) {
                $slug = "$base-$i";
            }
            insert('auteurs', ['nom' => $l['nom'], 'slug' => $slug]);
            $n++;
        }
    }
    return $n;
}

/** Colonnes reconnues pour les collections éditoriales. */
const IMPORT_COLONNES_COLLECTIONS = [
    'nom' => 'nom', 'collection' => 'nom', 'nom_de_la_collection' => 'nom',
    'editeur' => 'editeur', 'description' => 'description',
];

/** Analyse un fichier de collections : « nom » obligatoire, « editeur » et « description » facultatifs. */
function analyser_collections(array $lignes): array
{
    $entetes = array_map(fn($h) => IMPORT_COLONNES_COLLECTIONS[normaliser_entete($h)] ?? null, $lignes[0] ?? []);
    if (in_array('nom', $entetes, true)) {
        array_shift($lignes);
    } else {
        $entetes = ['nom', 'editeur', 'description']; // fichier sans en-tête : colonnes dans cet ordre
    }
    $existants = [];
    foreach (rows('SELECT nom FROM collections') as $c) {
        $existants[cle_comparaison($c['nom'])] = true;
    }
    $resultat = [];
    $vus = [];
    foreach ($lignes as $i => $cellules) {
        $v = [];
        foreach ($entetes as $j => $champ) {
            if ($champ !== null && !isset($v[$champ])) {
                $v[$champ] = trim((string) ($cellules[$j] ?? ''));
            }
        }
        $nom = preg_replace('/\s+/u', ' ', $v['nom'] ?? '');
        $cle = cle_comparaison($nom);
        $statut = 'nouveau';
        $message = '';
        if ($nom === '' || mb_strlen($nom) > 190) {
            [$statut, $message] = ['erreur', 'Nom vide ou trop long.'];
        } elseif (isset($existants[$cle])) {
            [$statut, $message] = ['ignore', 'Déjà dans la liste des collections.'];
        } elseif (isset($vus[$cle])) {
            [$statut, $message] = ['ignore', 'En double dans le fichier.'];
        }
        $vus[$cle] = true;
        $resultat[] = ['ligne' => $i + 1, 'nom' => $nom, 'editeur' => $v['editeur'] ?? '', 'description' => $v['description'] ?? '',
            'statut' => $statut, 'message' => $message];
    }
    return $resultat;
}

function importer_collections(array $analyse): int
{
    $n = 0;
    foreach ($analyse as $l) {
        if ($l['statut'] === 'nouveau') {
            creer_collection($l['nom'], $l['editeur'], $l['description']);
            $n++;
        }
    }
    return $n;
}

/** Retrouve une collection par son nom (sans tenir compte des accents ni de la casse) ou la crée. */
function collection_par_nom(string $nom): ?int
{
    $nom = trim(preg_replace('/\s+/u', ' ', $nom));
    if ($nom === '') {
        return null;
    }
    foreach (rows('SELECT id, nom FROM collections') as $c) {
        if (cle_comparaison($c['nom']) === cle_comparaison($nom)) {
            return (int) $c['id'];
        }
    }
    return creer_collection($nom);
}

function creer_collection(string $nom, string $editeur = '', string $description = ''): int
{
    return insert('collections', ['nom' => $nom, 'slug' => slug_libre('collections', 'collection-' . slugify($nom)),
        'editeur' => mb_substr($editeur, 0, 190), 'description' => $description,
        'ordre' => (int) val('SELECT COALESCE(MAX(ordre), 0) + 1 FROM collections')]);
}

/** Analyse un fichier d'ouvrages : une ligne d'en-têtes puis un ouvrage par ligne. */
function analyser_ouvrages(array $lignes): array
{
    $entetes = array_map(fn($h) => IMPORT_COLONNES_OUVRAGES[normaliser_entete($h)] ?? null, array_shift($lignes));
    foreach (['titre', 'domaine', 'type'] as $obligatoire) {
        if (!in_array($obligatoire, $entetes, true)) {
            throw new RuntimeException("Les colonnes « titre », « domaine » et « type » sont obligatoires (première ligne du fichier) : « $obligatoire » manque. Téléchargez le modèle.");
        }
    }
    $domaines = [];
    foreach (rows('SELECT id, nom, slug FROM domaines') as $d) {
        $domaines[cle_comparaison($d['nom'])] = $d;
        $domaines[cle_comparaison($d['slug'])] = $d;
    }
    $types = [];
    foreach (rows('SELECT id, nom, nom_pluriel, slug FROM types') as $t) {
        foreach (['nom', 'nom_pluriel', 'slug'] as $k) {
            $types[cle_comparaison($t[$k])] = $t;
        }
        $types[cle_comparaison(preg_replace('/^type-/', '', $t['slug']))] = $t;
    }
    $collections = [];
    foreach (rows('SELECT id, nom FROM collections') as $c) {
        $collections[cle_comparaison($c['nom'])] = $c;
    }
    $existants = [];
    foreach (rows('SELECT o.titre, o.domaine_id, o.isbn FROM ouvrages o') as $o) {
        $existants['t:' . $o['domaine_id'] . ':' . cle_comparaison($o['titre'])] = true;
        if ($o['isbn'] !== '') {
            $existants['i:' . preg_replace('/[^0-9X]/i', '', $o['isbn'])] = true;
        }
    }

    $oui = fn($v) => in_array(mb_strtolower(trim($v)), ['1', 'oui', 'o', 'x', 'yes', 'vrai', 'true'], true);
    $resultat = [];
    $vus = [];
    foreach ($lignes as $i => $cellules) {
        $v = [];
        foreach ($entetes as $j => $champ) {
            if ($champ !== null && !isset($v[$champ])) {
                $v[$champ] = $cellules[$j] ?? '';
            }
        }
        $messages = [];
        $statut = 'nouveau';
        $titre = $v['titre'] ?? '';
        if ($titre === '') {
            $statut = 'erreur';
            $messages[] = 'Titre manquant.';
        }
        $nomDomaine = $v['domaine'] ?? '';
        $domaine = $domaines[cle_comparaison($nomDomaine)] ?? null;
        if ($nomDomaine === '') {
            $statut = 'erreur';
            $messages[] = 'Domaine manquant.';
        } elseif (!$domaine) {
            $messages[] = "Domaine « $nomDomaine » créé.";
        }
        // Le type de document est obligatoire (il classe l'ouvrage dans le catalogue et le menu du site)
        $nomType = $v['type'] ?? '';
        $type = $nomType !== '' ? ($types[cle_comparaison($nomType)] ?? null) : null;
        if ($nomType === '') {
            $statut = 'erreur';
            $messages[] = 'Type de document manquant.';
        } elseif (!$type) {
            $messages[] = "Type « $nomType » créé.";
        }
        $nomCollection = $v['collection'] ?? '';
        if ($nomCollection !== '' && !isset($collections[cle_comparaison($nomCollection)])) {
            $messages[] = "Collection « $nomCollection » créée.";
        }
        $indice = trim($v['indice_dewey'] ?? '');
        if ($indice !== '' && !preg_match('/^\d{1,3}(\.\d{1,6})?$/', $indice)) {
            $messages[] = "Indice Dewey « $indice » ignoré (chiffres seulement, ex. 005.1).";
            $indice = '';
        }
        $pages = $v['pages'] ?? '';
        if ($pages !== '' && !ctype_digit($pages)) {
            $messages[] = 'Pages ignorées (nombre attendu).';
            $pages = '';
        }
        $nbEx = ($v['exemplaires'] ?? '') === '' ? 1 : (int) $v['exemplaires'];
        $nbEx = max(0, min(50, $nbEx));
        $image = $v['image'] ?? '';
        if ($image !== '') {
            $image = 'images/ouvrages/' . basename(str_replace('\\', '/', $image));
            if (!is_file(ROOT_DIR . '/' . $image)) {
                $messages[] = 'Image introuvable dans images/ouvrages : couverture par défaut.';
                $image = '';
            }
        }
        $cleTitre = $domaine ? 't:' . $domaine['id'] . ':' . cle_comparaison($titre) : 'n:' . cle_comparaison($nomDomaine) . ':' . cle_comparaison($titre);
        $isbn = preg_replace('/[^0-9X]/i', '', $v['isbn'] ?? '');
        if ($statut !== 'erreur' && (isset($existants[$cleTitre]) || ($isbn !== '' && isset($existants['i:' . $isbn])))) {
            $statut = 'ignore';
            $messages = ['Déjà au catalogue (même titre dans ce domaine, ou même ISBN).'];
        } elseif ($statut !== 'erreur' && isset($vus[$cleTitre])) {
            $statut = 'ignore';
            $messages = ['En double dans le fichier.'];
        }
        $vus[$cleTitre] = true;

        $resultat[] = [
            'ligne' => $i + 2, 'statut' => $statut, 'message' => implode(' ', $messages),
            'titre' => $titre, 'sous_titre' => $v['sous_titre'] ?? '',
            'auteurs' => array_values(array_filter(array_map('trim', preg_split('/\s*[|\/]\s*/', $v['auteurs'] ?? '')))),
            'domaine' => $nomDomaine, 'type' => $nomType,
            'collection' => $nomCollection, 'numero_collection' => $v['numero_collection'] ?? '',
            'edition' => $v['edition'] ?? '', 'tome' => $v['tome'] ?? '', 'volume' => $v['volume'] ?? '',
            'editeur' => $v['editeur'] ?? '', 'annee' => $v['annee'] ?? '',
            'isbn' => $v['isbn'] ?? '', 'issn' => $v['issn'] ?? '', 'indice_dewey' => $indice,
            'cote' => $v['cote'] ?? '', 'emplacement' => ($v['emplacement'] ?? '') ?: reglage('emplacement_defaut'),
            'pages' => $pages, 'langue' => ($v['langue'] ?? '') ?: 'Français',
            'exemplaires' => $nbEx, 'resume' => $v['resume'] ?? '', 'image' => $image,
            'sur_place' => $oui($v['sur_place'] ?? '') ? 1 : 0, 'coup_de_coeur' => $oui($v['coup_de_coeur'] ?? '') ? 1 : 0,
            'publie' => ($v['publie'] ?? '') === '' ? 1 : ($oui($v['publie']) ? 1 : 0),
        ];
    }
    return $resultat;
}

function importer_ouvrages(array $analyse): array
{
    $bilan = ['ouvrages' => 0, 'exemplaires' => 0, 'domaines' => 0, 'types' => 0, 'collections' => 0];
    db()->beginTransaction();
    try {
        foreach ($analyse as $l) {
            if ($l['statut'] !== 'nouveau') {
                continue;
            }
            $domaineId = null;
            foreach (rows('SELECT id, nom, slug FROM domaines') as $d) {
                if (cle_comparaison($d['nom']) === cle_comparaison($l['domaine']) || $d['slug'] === cle_comparaison($l['domaine'])) {
                    $domaineId = (int) $d['id'];
                }
            }
            if (!$domaineId) {
                $domaineId = insert('domaines', ['nom' => $l['domaine'], 'slug' => slug_libre('domaines', slugify($l['domaine'])),
                    'ordre' => (int) val('SELECT COALESCE(MAX(ordre), 0) + 1 FROM domaines')]);
                $bilan['domaines']++;
            }
            $typeId = null;
            if ($l['type'] !== '') {
                foreach (rows('SELECT id, nom, nom_pluriel, slug FROM types') as $t) {
                    if (in_array(cle_comparaison($l['type']), [cle_comparaison($t['nom']), cle_comparaison($t['nom_pluriel']), $t['slug'], preg_replace('/^type-/', '', $t['slug'])], true)) {
                        $typeId = (int) $t['id'];
                    }
                }
                if (!$typeId) {
                    $typeId = insert('types', ['nom' => $l['type'], 'nom_pluriel' => $l['type'] . 's', 'slug' => slug_libre('types', 'type-' . slugify($l['type'])),
                        'ordre' => (int) val('SELECT COALESCE(MAX(ordre), 0) + 1 FROM types')]);
                    $bilan['types']++;
                }
            }
            $avant = (int) val('SELECT COUNT(*) FROM collections');
            $collectionId = collection_par_nom($l['collection']);
            $bilan['collections'] += (int) val('SELECT COUNT(*) FROM collections') - $avant;
            $slug = slugify($l['titre']);
            for ($base = $slug, $i = 2; val('SELECT 1 FROM ouvrages WHERE domaine_id = ? AND slug = ?', [$domaineId, $slug]); $i++) {
                $slug = "$base-$i";
            }
            $id = insert('ouvrages', [
                'titre' => $l['titre'], 'sous_titre' => $l['sous_titre'], 'slug' => $slug, 'domaine_id' => $domaineId, 'type_id' => $typeId,
                'collection_id' => $collectionId, 'numero_collection' => $l['numero_collection'],
                'resume' => texte_vers_html($l['resume']), 'description' => '', 'editeur' => $l['editeur'], 'annee' => $l['annee'],
                'edition' => $l['edition'], 'tome' => $l['tome'], 'volume' => $l['volume'],
                'isbn' => $l['isbn'], 'issn' => $l['issn'], 'indice_dewey' => $l['indice_dewey'],
                'cote' => $l['cote'], 'emplacement' => $l['emplacement'],
                'pages' => $l['pages'] !== '' ? (int) $l['pages'] : null, 'langue' => $l['langue'],
                'image' => $l['image'], 'consultation_sur_place' => $l['sur_place'], 'coup_de_coeur' => $l['coup_de_coeur'], 'publie' => $l['publie'],
            ]);
            lier_auteurs($id, $l['auteurs']);
            $prefixe = $l['cote'] !== '' ? $l['cote'] : sprintf('EX%04d', $id);
            for ($k = 1, $num = 1; $k <= $l['exemplaires']; $k++, $num++) {
                while (val('SELECT 1 FROM exemplaires WHERE code = ?', ["$prefixe-$num"])) {
                    $num++;
                }
                insert('exemplaires', ['ouvrage_id' => $id, 'code' => "$prefixe-$num"]);
                $bilan['exemplaires']++;
            }
            $bilan['ouvrages']++;
        }
        db()->commit();
    } catch (Throwable $t) {
        db()->rollBack();
        throw $t;
    }
    return $bilan;
}

/** Premier slug libre dans une table (ajoute -2, -3… si besoin). */
function slug_libre(string $table, string $slug): string
{
    for ($base = $slug, $i = 2; val("SELECT 1 FROM $table WHERE slug = ?", [$slug]); $i++) {
        $slug = "$base-$i";
    }
    return $slug;
}

/** Contenu des fichiers modèles proposés au téléchargement (CSV point-virgule, lisible par Excel). */
function modele_import(string $quoi): string
{
    if ($quoi === 'auteurs') {
        return "nom\nVictor Hugo\nMariama Bâ\nCheikh Anta Diop\n";
    }
    if ($quoi === 'collections') {
        return "nom;editeur;description\n"
            . "Que sais-je ?;Presses universitaires de France;Collection encyclopédique de poche.\n"
            . "Folio;Gallimard;Littérature au format poche.\n"
            . "Repères;La Découverte;Synthèses en sciences humaines et sociales.\n";
    }
    return "titre;sous_titre;auteurs;domaine;type;collection;numero_collection;edition;tome;volume;editeur;annee;isbn;issn;indice_dewey;cote;emplacement;pages;langue;exemplaires;resume;image;sur_place;coup_de_coeur\n"
        . "L’Aventure ambiguë;;Cheikh Hamidou Kane;Littérature & Langues;Roman;;;;;;Julliard;1961;;;840;LIT-020;Salle de lecture;192;Français;2;Samba Diallo, entre deux mondes.;;non;oui\n"
        . "Algorithmique avancée;structures de données et complexité;Awa Kpélim | Kossi Mensah;Informatique;Manuel;Que sais-je ?;1234;3e éd.;II;2;Presses de Kara;2024;;;005.1;INF-050;Rayon B3;350;Français;3;;;non;non\n";
}
