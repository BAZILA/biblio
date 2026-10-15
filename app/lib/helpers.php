<?php
/** Fonctions utilitaires : URLs, échappement, vues, CSRF, messages flash. */

/** Chemin web de la racine du site, ex. « /biblio ». */
function base_url(): string
{
    static $base = null;
    if ($base !== null) {
        return $base;
    }
    if (!empty($GLOBALS['config']['base_url'])) {
        return $base = rtrim($GLOBALS['config']['base_url'], '/');
    }
    // SCRIPT_NAME (« /biblio/admin/index.php ») moins la partie du script sous ROOT_DIR (« /admin/index.php »)
    $file = str_replace('\\', '/', realpath($_SERVER['SCRIPT_FILENAME']) ?: '');
    $root = str_replace('\\', '/', ROOT_DIR);
    $rel  = substr($file, strlen($root));
    $name = $_SERVER['SCRIPT_NAME'] ?? '';
    $base = substr($name, 0, strlen($name) - strlen($rel));
    return $base = rtrim($base, '/');
}

/** URL absolue du site (schéma + hôte + base), ex. « http://localhost/biblio/ ». */
function site_url(): string
{
    $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    return ($https ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . base_url() . '/';
}

/** URL d'une page publique : url('catalogue') → /biblio/index.php/catalogue */
function url(string $path = '', array $query = []): string
{
    $u = base_url() . '/index.php' . ($path !== '' ? '/' . ltrim($path, '/') : '');
    return $query ? $u . '?' . http_build_query($query) : $u;
}

/** URL d'une page d'administration : admin_url('ouvrages/modifier/3') */
function admin_url(string $path = '', array $query = []): string
{
    $u = base_url() . '/admin/index.php' . ($path !== '' ? '/' . ltrim($path, '/') : '');
    return $query ? $u . '?' . http_build_query($query) : $u;
}

function asset(string $path): string
{
    return base_url() . '/assets/' . ltrim($path, '/');
}

/** URL d'un fichier média stocké sous la racine (images/…). */
function media(string $path): string
{
    if ($path === '' || preg_match('#^(https?:)?//#', $path)) {
        return $path;
    }
    return base_url() . '/' . ltrim($path, '/');
}

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Chemin courant après le script (PATH_INFO), sans slash de début/fin. */
function current_path(): string
{
    $p = $_SERVER['PATH_INFO'] ?? '';
    if ($p === '' && isset($_SERVER['ORIG_PATH_INFO'])) {
        $p = $_SERVER['ORIG_PATH_INFO'];
    }
    return trim(rawurldecode($p), '/');
}

function redirect(string $url, int $code = 303): never
{
    header('Location: ' . $url, true, $code);
    exit;
}

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

/**
 * Valeur texte d'un paramètre de requête : '' si absent ou envoyé sous forme de tableau (q[]=…),
 * convertie en UTF-8 si le navigateur a envoyé du Latin-1 (évite les erreurs des fonctions /u).
 */
function chaine_requete(array $source, string $cle, string $defaut, bool $rogner): string
{
    $v = $source[$cle] ?? $defaut;
    if (!is_string($v)) {
        return $defaut;
    }
    if (!mb_check_encoding($v, 'UTF-8')) {
        $v = mb_convert_encoding($v, 'UTF-8', 'Windows-1252');
    }
    return $rogner ? trim($v) : $v;
}

function get_str(string $cle, string $defaut = ''): string
{
    return chaine_requete($_GET, $cle, $defaut, true);
}

/** $rogner = false pour les mots de passe et les contenus HTML, conservés tels quels. */
function post_str(string $cle, string $defaut = '', bool $rogner = true): string
{
    return chaine_requete($_POST, $cle, $defaut, $rogner);
}

/* ---------- Réglages et blocs éditables ---------- */

function reglage(string $cle, string $defaut = ''): string
{
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        foreach (rows('SELECT cle, valeur FROM reglages') as $r) {
            $cache[$r['cle']] = $r['valeur'];
        }
    }
    return $cache[$cle] ?? $defaut;
}

function reglage_int(string $cle, int $defaut = 0): int
{
    $v = reglage($cle, (string) $defaut);
    return is_numeric($v) ? (int) $v : $defaut;
}

/**
 * Contenu HTML d'un bloc éditable depuis l'admin (non échappé : saisi par le personnel).
 * Balises remplacées : {base} → racine du site, {recherche} → formulaire de recherche.
 */
function bloc(string $cle): string
{
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        foreach (rows('SELECT cle, contenu FROM blocs') as $r) {
            $cache[$r['cle']] = $r['contenu'];
        }
    }
    return strtr($cache[$cle] ?? '', [
        '{base}'      => base_url(),
        '{recherche}' => formulaire_recherche(),
    ]);
}

function formulaire_recherche(): string
{
    return '<form class="biblio-search" action="' . e(url('recherche')) . '" method="get" role="search">' . "\n"
        . '<input type="search" name="q" placeholder="Titre, auteur, mot-clé…" aria-label="Rechercher un ouvrage" />' . "\n"
        . '<button type="submit" class="btn btn-primary"><i class="fa fa-search" aria-hidden="true"></i><span>Rechercher</span></button>' . "\n"
        . '</form>';
}

/* ---------- Vues ---------- */

/** Rend un fichier PHP de vue avec les variables données et renvoie le HTML. */
function render(string $__vue, array $__vars = []): string
{
    // Noms préfixés pour ne pas entrer en collision avec les variables de la vue
    extract($__vars, EXTR_SKIP);
    unset($__vars);
    ob_start();
    require $__vue;
    return ob_get_clean();
}

/* ---------- Sécurité : jeton CSRF ---------- */

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function csrf_verify(): void
{
    $sent = $_POST['_csrf'] ?? '';
    if (!is_string($sent) || !hash_equals(csrf_token(), $sent)) {
        http_response_code(403);
        exit('<!DOCTYPE html><html lang="fr"><head><meta charset="utf-8"><title>Session expirée</title>'
            . '<meta name="viewport" content="width=device-width, initial-scale=1"></head>'
            . '<body style="font-family:system-ui,sans-serif;max-width:560px;margin:15vh auto;padding:0 20px;color:#1c2433">'
            . '<h1 style="font-size:1.4rem">Session expirée</h1>'
            . '<p>Le formulaire a été ouvert il y a trop longtemps ou dans une autre session. Revenez à la page, rechargez-la puis recommencez.</p>'
            . '<p><a href="javascript:history.back()" style="color:#243c9c">← Revenir à la page précédente</a></p></body></html>');
    }
}

/* ---------- Messages flash ---------- */

function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function flashes(): array
{
    $f = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $f;
}

/* ---------- Texte ---------- */

function slugify(string $text): string
{
    $text = str_replace(['’', "'", 'œ', 'Œ', 'æ', 'Æ'], ['-', '-', 'oe', 'oe', 'ae', 'ae'], $text);
    if (function_exists('transliterator_transliterate')) {
        $text = transliterator_transliterate('Any-Latin; Latin-ASCII', $text);
    } else {
        // Sans l'extension intl (cas de XAMPP par défaut) : table des lettres accentuées courantes
        $text = strtr(mb_strtolower($text), [
            'à' => 'a', 'â' => 'a', 'ä' => 'a', 'á' => 'a', 'ã' => 'a', 'å' => 'a', 'ç' => 'c',
            'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e', 'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i',
            'ñ' => 'n', 'ó' => 'o', 'ò' => 'o', 'ô' => 'o', 'ö' => 'o', 'õ' => 'o', 'ø' => 'o',
            'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u', 'ý' => 'y', 'ÿ' => 'y', 'ɛ' => 'e', 'ɔ' => 'o', 'ŋ' => 'n',
        ]);
    }
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-') ?: 'element';
}

/** Balises de mise en forme autorisées dans les résumés d'ouvrages. */
const HTML_BALISES_AUTORISEES = ['p', 'br', 'strong', 'b', 'em', 'i', 'u', 'ul', 'ol', 'li', 'a', 'h3', 'h4', 'blockquote', 'span', 'small'];
/** Balises supprimées avec tout leur contenu. */
const HTML_BALISES_INTERDITES = ['script', 'style', 'iframe', 'object', 'embed', 'template', 'svg', 'math', 'form',
    'input', 'button', 'textarea', 'select', 'noscript', 'link', 'meta', 'base'];

/**
 * Nettoie un HTML simple saisi dans l'admin (résumés d'ouvrages) par liste blanche, via DOMDocument :
 * balises de mise en forme uniquement, aucun attribut sauf href (http, https, mailto ou lien relatif)
 * et title. Les autres balises sont retirées en gardant leur texte.
 */
function nettoyer_html(string $html): string
{
    $html = trim($html);
    if ($html === '') {
        return '';
    }
    $doc = new DOMDocument();
    $precedent = libxml_use_internal_errors(true);
    $doc->loadHTML('<!DOCTYPE html><html><head><meta charset="utf-8"></head><body><div id="racine-nettoyage">'
        . $html . '</div></body></html>');
    libxml_clear_errors();
    libxml_use_internal_errors($precedent);
    $racine = $doc->getElementById('racine-nettoyage');
    if (!$racine) {
        return e(strip_tags($html));
    }
    nettoyer_noeud($racine);
    $sortie = '';
    foreach ($racine->childNodes as $enfant) {
        $sortie .= $doc->saveHTML($enfant);
    }
    return trim($sortie);
}

function nettoyer_noeud(DOMNode $noeud): void
{
    foreach (iterator_to_array($noeud->childNodes) as $enfant) {
        if ($enfant instanceof DOMComment) {
            $noeud->removeChild($enfant);
            continue;
        }
        if (!$enfant instanceof DOMElement) {
            continue;
        }
        $balise = strtolower($enfant->tagName);
        if (in_array($balise, HTML_BALISES_INTERDITES, true)) {
            $noeud->removeChild($enfant);
            continue;
        }
        nettoyer_noeud($enfant);
        if (!in_array($balise, HTML_BALISES_AUTORISEES, true)) {
            while ($enfant->firstChild) {
                $noeud->insertBefore($enfant->firstChild, $enfant); // on garde le texte, pas la balise
            }
            $noeud->removeChild($enfant);
            continue;
        }
        foreach (iterator_to_array($enfant->attributes) as $attribut) {
            $nom = strtolower($attribut->name);
            $garder = $nom === 'title';
            if ($balise === 'a' && $nom === 'href') {
                // Les entités sont déjà décodées par le DOM ; on ignore espaces et caractères de contrôle
                $url = preg_replace('/[\x00-\x20\x7f]+/', '', $attribut->value);
                $garder = preg_match('#^(https?:|mailto:|/|\#|\?|[^:]*$)#i', $url) === 1;
            }
            if (!$garder) {
                $enfant->removeAttribute($attribut->name);
            }
        }
    }
}

/**
 * Texte saisi dans l'admin → HTML : un paragraphe par bloc séparé d'une ligne vide.
 * Si le texte contient déjà des balises, il est seulement nettoyé.
 */
function texte_vers_html(string $texte): string
{
    $texte = trim($texte);
    if ($texte === '' || preg_match('#<(p|ul|ol|h3|h4|blockquote)\b#i', $texte)) {
        return nettoyer_html($texte);
    }
    $paragraphes = preg_split('/\n\s*\n/', str_replace("\r", '', $texte));
    return implode('', array_map(fn($p) => '<p>' . nl2br(nettoyer_html(trim($p)), false) . '</p>', $paragraphes));
}

/** Inverse de texte_vers_html() quand le HTML ne contient que des paragraphes simples. */
function html_vers_texte(string $html): string
{
    if (preg_match('#<(?!/?(p|br)\b)[a-z/]#i', $html)) {
        return $html; // mise en forme riche : on laisse le HTML tel quel
    }
    $texte = preg_replace(['#<br\s*/?>#i', '#</p>\s*<p[^>]*>#i', '#</?p[^>]*>#i'], ["\n", "\n\n", ''], $html);
    return trim(html_entity_decode($texte, ENT_QUOTES, 'UTF-8'));
}

/** Coupe un texte brut à $max caractères (comme les modules Joomla d'origine). */
function tronquer(string $text, int $max, string $suite = '...'): string
{
    $text = trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($text), ENT_QUOTES, 'UTF-8')));
    return mb_strlen($text) > $max ? mb_substr($text, 0, $max) . $suite : $text;
}

/** Échappe un texte et entoure les mots recherchés d'un <mark> (comme le surligneur d'origine). */
function surligner(string $texte, string $q): string
{
    $html = e($texte);
    $mots = array_filter(preg_split('/\s+/u', trim($q)) ?: [], fn($m) => mb_strlen($m) >= 2);
    if (!$mots) {
        return $html;
    }
    $motif = implode('|', array_map(fn($m) => preg_quote(e($m), '/'), $mots));
    return preg_replace('/(' . $motif . ')/iu', '<mark>$1</mark>', $html);
}

function date_fr(?string $date, bool $avecHeure = false): string
{
    if (!$date) {
        return '';
    }
    $ts = strtotime($date);
    return date($avecHeure ? 'd/m/Y H:i' : 'd/m/Y', $ts);
}

/** Pluriel simple : pluriel(3, 'ouvrage') → « 3 ouvrages ». */
function pluriel(int $n, string $mot, ?string $motPluriel = null): string
{
    return $n . ' ' . ($n > 1 ? ($motPluriel ?? $mot . 's') : $mot);
}

/** Calcule les infos de pagination à partir de ?start= (convention conservée du site d'origine). */
function pagination(int $total, int $parPage): array
{
    $start = max(0, (int) ($_GET['start'] ?? 0));
    $parPage = max(1, $parPage);
    $pages = max(1, (int) ceil($total / $parPage));
    $page  = min($pages, intdiv($start, $parPage) + 1);
    return [
        'total'   => $total,
        'parPage' => $parPage,
        'page'    => $page,
        'pages'   => $pages,
        'offset'  => ($page - 1) * $parPage,
    ];
}
