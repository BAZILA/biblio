<?php
/**
 * Crée en une fois les comptes du personnel listés dans un fichier Excel (.xlsx) ou CSV, avec un même rôle.
 *   php install/creer_comptes_personnel.php fichier.xlsx [--role="Formation"] [--meme-mot-de-passe | --mot-de-passe="…"] [--essai] [--envoyer]
 *
 *   --role     nom du rôle à donner (par défaut « Formation ») ; il doit déjà exister
 *   --meme-mot-de-passe   un seul mot de passe provisoire, tiré au hasard, pour tous les comptes
 *   --mot-de-passe="…"    un seul mot de passe provisoire choisi (10 caractères au moins) pour tous les comptes
 *                         (sans ces deux options : un mot de passe différent par compte)
 *   --essai   affiche ce qui serait fait, sans rien enregistrer
 *   --envoyer  envoie à chacun un e-mail avec son identifiant et son mot de passe provisoire (mode SMTP des Réglages)
 *
 * Le fichier doit avoir une ligne d'en-tête avec une colonne « Nom… » et une colonne « …mail… » (ex. « Nom et prénoms »,
 * « Email professionnel ») ; les lignes au-dessus (titre) sont ignorées. Le nom est découpé ainsi : civilité retirée
 * (M., Mme, Mlle, Dr), mots en MAJUSCULES = nom, le reste = prénoms. Lignes sans e-mail valide et adresses déjà
 * utilisées : ignorées (aucun compte existant n'est modifié).
 *
 * Les mots de passe provisoires sont écrits dans install/COMPTES_<date>.csv (dossier inaccessible depuis le web) :
 * transmettez-les, puis supprimez le fichier. Chacun change son mot de passe dans « Mon compte ».
 */
if (PHP_SAPI !== 'cli') {
    exit('À lancer en ligne de commande.');
}
require __DIR__ . '/../app/bootstrap.php';

$fichier = null;
$nomRole = 'Formation';
$essai = $envoyer = false;
$commun = null; // mot de passe provisoire commun à tous les comptes
foreach (array_slice($argv, 1) as $a) {
    if (str_starts_with($a, '--role=')) {
        $nomRole = trim(substr($a, 7), " \"'");
    } elseif ($a === '--meme-mot-de-passe') {
        $commun = rtrim(strtr(base64_encode(random_bytes(9)), '+/', 'Kx'), '=');
    } elseif (str_starts_with($a, '--mot-de-passe=')) {
        $commun = substr($a, 15);
        if (mb_strlen($commun) < 10) {
            exit("Mot de passe trop court : 10 caractères au moins.\n");
        }
    } elseif ($a === '--essai') {
        $essai = true;
    } elseif ($a === '--envoyer') {
        $envoyer = true;
    } elseif (!str_starts_with($a, '--')) {
        $fichier = $a;
    } else {
        exit("Option inconnue : $a\n");
    }
}
if (!$fichier || !is_file($fichier)) {
    exit(($fichier ? "Fichier introuvable : $fichier\n" : '')
        . "Usage : php install/creer_comptes_personnel.php fichier.xlsx [--role=\"Formation\"] [--meme-mot-de-passe | --mot-de-passe=\"…\"] [--essai] [--envoyer]\n");
}

$role = row('SELECT * FROM roles WHERE LOWER(TRIM(nom)) = LOWER(?) AND systeme = 0', [$nomRole]);
if (!$role) {
    echo "Rôle « $nomRole » introuvable. Rôles existants :\n";
    foreach (rows('SELECT nom FROM roles WHERE systeme = 0 ORDER BY nom') as $r) {
        echo "  - {$r['nom']}\n";
    }
    exit(1);
}

/** Lignes du fichier, chaque ligne étant un tableau de cellules (texte). */
function lire_tableau(string $fichier): array
{
    if (strtolower(pathinfo($fichier, PATHINFO_EXTENSION)) !== 'xlsx') {
        $texte = file_get_contents($fichier);
        $texte = preg_replace('/^\xEF\xBB\xBF/', '', $texte);
        if (!mb_check_encoding($texte, 'UTF-8')) {
            $texte = mb_convert_encoding($texte, 'UTF-8', 'Windows-1252');
        }
        $sep = substr_count($texte, ';') >= substr_count($texte, ',') ? ';' : ',';
        $lignes = [];
        foreach (preg_split('/\r\n|\r|\n/', $texte) as $l) {
            $lignes[] = str_getcsv($l, $sep, '"', '');
        }
        return $lignes;
    }
    // .xlsx : première feuille, textes partagés
    $zip = new ZipArchive();
    if ($zip->open($fichier) !== true) {
        exit("Impossible d'ouvrir le fichier Excel.\n");
    }
    $partages = [];
    if (($xml = $zip->getFromName('xl/sharedStrings.xml')) !== false) {
        $sst = simplexml_load_string($xml);
        foreach ($sst->si as $si) {
            $t = isset($si->t) ? (string) $si->t : '';
            foreach ($si->r ?? [] as $r) {
                $t .= (string) $r->t;
            }
            $partages[] = $t;
        }
    }
    $feuille = simplexml_load_string($zip->getFromName('xl/worksheets/sheet1.xml'));
    $zip->close();
    $lignes = [];
    foreach ($feuille->sheetData->row as $row) {
        $cellules = [];
        foreach ($row->c as $c) {
            preg_match('/^([A-Z]+)/', (string) $c['r'], $m);
            $col = 0;
            foreach (str_split($m[1]) as $lettre) {
                $col = $col * 26 + ord($lettre) - 64;
            }
            $type = (string) $c['t'];
            $v = $type === 's' ? ($partages[(int) $c->v] ?? '') : ($type === 'inlineStr' ? (string) $c->is->t : (string) $c->v);
            $cellules[$col - 1] = $v;
        }
        $lignes[] = $cellules ? array_replace(array_fill(0, max(array_keys($cellules)) + 1, ''), $cellules) : [];
    }
    return $lignes;
}

/** « Mme KASSAN Balaïbaou » → ['KASSAN', 'Balaïbaou'] ; « GNAKOU TCHA Hodalo » → ['GNAKOU TCHA', 'Hodalo']. */
function decouper_nom(string $complet): array
{
    $complet = trim(preg_replace('/\s+/u', ' ', $complet));
    $complet = preg_replace('/^(M\.|Mme\.?|Mlle\.?|Mr\.?|Dr\.?|Pr\.?)\s*/iu', '', $complet);
    $nom = $prenoms = [];
    foreach (explode(' ', $complet) as $mot) {
        $majuscules = preg_match('/\p{L}/u', $mot) && mb_strtoupper($mot) === $mot;
        if ($majuscules && !$prenoms) {
            $nom[] = $mot;
        } else {
            $prenoms[] = $mot;
        }
    }
    if (!$nom && $prenoms) { // aucun mot en majuscules : le premier mot sert de nom
        $nom[] = mb_strtoupper(array_shift($prenoms));
    }
    return [implode(' ', $nom), implode(' ', $prenoms)];
}

$lignes = lire_tableau($fichier);
$colNom = $colMail = null;
$debut = 0;
foreach ($lignes as $i => $l) {
    foreach ($l as $j => $v) {
        $v = mb_strtolower(trim((string) $v));
        if ($colMail === null && str_contains($v, 'mail')) {
            $colMail = $j;
        } elseif ($colNom === null && str_starts_with($v, 'nom')) {
            $colNom = $j;
        }
    }
    if ($colNom !== null && $colMail !== null) {
        $debut = $i + 1;
        break;
    }
    $colNom = $colMail = null;
}
if ($colNom === null) {
    exit("En-tête introuvable : il faut une colonne « Nom… » et une colonne « …mail… ».\n");
}

echo ($essai ? '[ESSAI — rien n’est enregistré] ' : '') . "Rôle donné : « {$role['nom']} »\n\n";
$crees = $ignores = [];
$vus = [];
foreach (array_slice($lignes, $debut) as $l) {
    $complet = trim((string) ($l[$colNom] ?? ''));
    $email = mb_strtolower(trim((string) ($l[$colMail] ?? '')));
    if ($complet === '' && $email === '') {
        continue;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $ignores[] = "$complet : pas d’adresse e-mail valide";
        continue;
    }
    if (isset($vus[$email]) || val('SELECT 1 FROM utilisateurs WHERE email = ?', [$email])) {
        $ignores[] = "$complet : $email déjà utilisée par un compte";
        continue;
    }
    $vus[$email] = true;
    [$nom, $prenom] = decouper_nom($complet);
    $motDePasse = $commun ?? rtrim(strtr(base64_encode(random_bytes(9)), '+/', 'Kx'), '='); // 12 caractères
    $u = ['role' => 'personnel', 'role_id' => (int) $role['id'], 'nom' => $nom, 'prenom' => $prenom, 'email' => $email,
        'mot_de_passe' => password_hash($motDePasse, PASSWORD_DEFAULT), 'actif' => 1];
    if (!$essai) {
        $u['id'] = insert('utilisateurs', $u);
        if ($envoyer) {
            envoyer_mail($email, 'Votre compte sur l’application de la bibliothèque',
                bonjour($u) . "\n\nUn compte vous a été ouvert sur l’application de gestion de la bibliothèque.\n\n"
                . "Adresse : " . site_url_absolue() . "admin/\n"
                . "Identifiant : $email (votre adresse e-mail)\n"
                . "Mot de passe provisoire : $motDePasse\n\n"
                . "À la première connexion, changez ce mot de passe dans « Mon compte ».", 'compte');
        }
    }
    $crees[] = [$nom, $prenom, $email, $motDePasse];
    $libelle = trim("$nom $prenom");
    echo '  + ' . $libelle . str_repeat(' ', max(1, 36 - mb_strlen($libelle))) . $email . "\n";
}

echo "\n" . count($crees) . ' compte(s) ' . ($essai ? 'à créer' : 'créé(s)') . ', ' . count($ignores) . " ligne(s) ignorée(s).\n";
foreach ($ignores as $m) {
    echo "  - $m\n";
}

if ($crees && !$essai) {
    $sortie = __DIR__ . '/COMPTES_' . date('Y-m-d_His') . '.csv';
    $f = fopen($sortie, 'w');
    fwrite($f, "\xEF\xBB\xBF");
    fputcsv($f, ['Nom', 'Prénoms', 'E-mail (identifiant)', 'Mot de passe provisoire', 'Rôle'], ';', '"', '');
    foreach ($crees as $c) {
        fputcsv($f, [...$c, $role['nom']], ';', '"', '');
    }
    fclose($f);
    chmod($sortie, 0600);
    audit('personnel', 'Création en lot de comptes du personnel', count($crees) . " compte(s) avec le rôle « {$role['nom']} »", 'succes',
        ['fichier' => basename($fichier), 'emails' => implode(', ', array_column($crees, 2))],
        ['id' => null, 'role' => 'personnel', 'role_id' => null, 'prenom' => '', 'nom' => 'Script en ligne de commande'], 'admin');
    echo ($commun !== null ? "\nMot de passe provisoire commun à tous les comptes : $commun" : '')
        . "\nMots de passe provisoires : install/" . basename($sortie) . "\n"
        . ($envoyer ? "E-mails envoyés (voir « E-mails envoyés » dans l’administration).\n" : '')
        . "Transmettez-les, puis supprimez ce fichier.\n";
}
