<?php
/**
 * Site public de la bibliothèque — contrôleur frontal.
 * Les adresses reprennent celles de l'ancien site (index.php/catalogue, index.php/domaines/…).
 */

require __DIR__ . '/app/bootstrap.php';

$vues = __DIR__ . '/views/site';
$segments = current_path() === '' ? [] : explode('/', current_path());

/** Affiche une page dans le gabarit commun et termine. */
function page(string $vue, array $vars, array $gabarit): never
{
    $gabarit['contenu'] = render(__DIR__ . '/views/site/' . $vue . '.php', $vars);
    echo render(__DIR__ . '/views/site/layout.php', $gabarit);
    exit;
}

function introuvable(string $message = ''): never
{
    http_response_code(404);
    page('erreur', ['message' => $message ?: null], ['titre' => 'Page introuvable']);
}

/** Lien d'un bouton de diaporama : chemin interne (« catalogue ») ou URL complète. */
function lien_slide(string $lien): string
{
    return preg_match('#^(https?:)?//|^/|^\#|^mailto:#', $lien) ? $lien : url($lien);
}

// Anciennes adresses Joomla encore présentes dans des favoris ou des moteurs de recherche
if (get_str('option') !== '') {
    redirect(get_str('option') === 'com_contact' ? url('contact') : url(), 301);
}
if (in_array(get_str('format'), ['feed', 'opensearch', 'raw', 'json'], true)) {
    $query = $_GET;
    unset($query['format'], $query['type']);
    redirect(url(current_path(), $query), 301);
}

$parPage = reglage_int('par_page', 12);

switch ($segments[0] ?? '') {

    case '':
        $slides = rows('SELECT * FROM slides WHERE publie = 1 ORDER BY ordre, id');
        page('accueil', [
            'slides'       => $slides,
            'chiffres'     => chiffres_cles(),
            'types'        => rows('SELECT * FROM types ORDER BY ordre, nom'),
            'domaines'     => domaines(),
            'nouveautes'   => ouvrages('1', [], 'o.cree_le DESC, o.id DESC', 12),
            'citations'    => rows('SELECT * FROM citations WHERE publie = 1 ORDER BY ordre, id'),
            'coupsDeCoeur' => ouvrages('o.coup_de_coeur = 1', [], 'o.titre', 12),
        ], [
            'titre' => 'Accueil', 'actif' => 'accueil', 'accueil' => true,
            'classeHtml' => 'view-featured',
            'tete' => render($vues . '/accueil_tete.php', ['nbSlides' => count($slides)]),
        ]);

    case 'catalogue':
        $pg = pagination(compter_ouvrages(), $parPage);
        page('liste', [
            'domaine' => null,
            'ouvrages' => ouvrages('1', [], 'o.titre', $pg['parPage'], $pg['offset']),
            'pagination' => $pg, 'chemin' => 'catalogue',
        ], ['titre' => 'Catalogue des ouvrages', 'actif' => 'catalogue', 'classeHtml' => 'view-category layout-blog']);

    case 'domaines':
        if (!isset($segments[1])) {
            page('domaines', ['domaines' => domaines()], ['titre' => 'Ouvrages par domaine', 'actif' => 'domaines', 'classeHtml' => 'view-categories']);
        }
        $domaine = domaine_par_slug($segments[1]) ?? introuvable('Ce domaine n’existe pas.');

        if (isset($segments[2])) {
            $o = ouvrage_par_slugs($domaine['slug'], $segments[2]) ?? introuvable('Cet ouvrage n’existe pas ou n’est plus au catalogue.');
            [$precedent, $suivant] = ouvrages_voisins($o);
            $u = user();
            $maReservation = null;
            $monPret = false;
            if ($u) {
                $maReservation = reservations("r.ouvrage_id = ? AND r.lecteur_id = ? AND r.statut IN ('en_attente','disponible')", [$o['id'], $u['id']])[0] ?? null;
                $monPret = (bool) val('SELECT COUNT(*) FROM prets p JOIN exemplaires e ON e.id = p.exemplaire_id
                                       WHERE e.ouvrage_id = ? AND p.lecteur_id = ? AND p.date_retour IS NULL', [$o['id'], $u['id']]);
            }
            page('fiche', [
                'o' => $o, 'precedent' => $precedent, 'suivant' => $suivant,
                'maReservation' => $maReservation, 'monPret' => $monPret,
                'fileAttente' => (int) val("SELECT COUNT(*) FROM reservations WHERE ouvrage_id = ? AND statut = 'en_attente'", [$o['id']]),
            ], [
                'titre' => $o['titre'], 'actif' => 'domaines', 'sousActif' => $domaine['slug'],
                'classeHtml' => 'view-article', 'description' => tronquer($o['resume'], 160),
            ]);
        }

        $pg = pagination(compter_ouvrages('o.domaine_id = ?', [$domaine['id']]), $parPage);
        page('liste', [
            'domaine' => $domaine,
            'ouvrages' => ouvrages('o.domaine_id = ?', [$domaine['id']], 'o.titre', $pg['parPage'], $pg['offset']),
            'pagination' => $pg, 'chemin' => 'domaines/' . $domaine['slug'],
        ], ['titre' => $domaine['nom'], 'actif' => 'domaines', 'sousActif' => $domaine['slug'], 'classeHtml' => 'view-category layout-blog']);

    case 'types':
        if (!isset($segments[1])) {
            $elements = array_map(fn($t) => [
                'url' => url('types/' . $t['slug']),
                'nom' => $t['nom'],
                'texte' => $t['description'] ? '<p>' . e($t['description']) . '</p>' : '',
            ], rows('SELECT * FROM types ORDER BY nom'));
            page('index_tags', [
                'titre' => 'Ouvrages par type de document',
                'intro' => 'Livres, romans, manuels, revues, thèses, mémoires, dictionnaires : choisissez un type de document.',
                'elements' => $elements, 'filtre' => null, 'parLigne' => 3,
            ], ['titre' => 'Ouvrages par type de document', 'actif' => 'types', 'classeHtml' => 'view-tags']);
        }
        $type = type_par_slug($segments[1]) ?? introuvable('Ce type de document n’existe pas.');
        page('cartes', [
            'titre' => $type['nom'], 'description' => (string) $type['description'],
            'ouvrages' => ouvrages('o.type_id = ?', [$type['id']]),
        ], ['titre' => $type['nom'], 'actif' => 'types', 'sousActif' => $type['slug'], 'classeHtml' => 'view-tag']);

    case 'auteurs':
        if (!isset($segments[1])) {
            $filtre = get_str('filtre', '');
            $elements = array_map(fn($a) => [
                'url' => url('auteurs/' . $a['slug']),
                'nom' => $a['nom'],
                'texte' => '<p><strong>' . e(pluriel((int) $a['nb'], 'ouvrage')) . '</strong> : ' . e($a['titres']) . '</p>',
            ], auteurs($filtre));
            page('index_tags', [
                'titre' => 'Les auteurs du catalogue',
                'intro' => 'Cliquez sur un auteur pour voir tous ses ouvrages disponibles à la bibliothèque.',
                'elements' => $elements, 'filtre' => $filtre, 'parLigne' => 4,
            ], ['titre' => 'Les auteurs du catalogue', 'actif' => 'auteurs', 'classeHtml' => 'view-tags']);
        }
        $auteur = auteur_par_slug($segments[1]) ?? introuvable('Cet auteur n’existe pas.');
        $ouvragesAuteur = ouvrages('EXISTS (SELECT 1 FROM ouvrage_auteur oa WHERE oa.ouvrage_id = o.id AND oa.auteur_id = ?)', [$auteur['id']]);
        page('cartes', [
            'titre' => $auteur['nom'],
            'description' => '',
            'ouvrages' => $ouvragesAuteur,
        ], ['titre' => $auteur['nom'], 'actif' => 'auteurs', 'classeHtml' => 'view-tag']);

    case 'recherche':
        $q = get_str('q', '');
        page('recherche', ['q' => $q, 'resultats' => rechercher($q)], [
            'titre' => $q !== '' ? "Recherche : $q" : 'Rechercher un ouvrage', 'actif' => 'recherche', 'classeHtml' => 'view-search',
            'cssAvant' => [asset('vendor/finder/finder.min.css')],
        ]);

    case 'contact':
        $saisie = ['nom' => '', 'email' => '', 'sujet' => '', 'message' => ''];
        $erreurs = [];
        if (is_post()) {
            csrf_verify();
            foreach ($saisie as $k => $_) {
                $saisie[$k] = post_str($k, '');
                if ($saisie[$k] === '') {
                    $erreurs[$k] = 'Champ requis.';
                }
            }
            if ($saisie['email'] !== '' && !filter_var($saisie['email'], FILTER_VALIDATE_EMAIL)) {
                $erreurs['email'] = 'Adresse e-mail invalide.';
            }
            if (!$erreurs && ($_POST['site_web'] ?? '') === '') { // champ piège anti-robots
                insert('messages', [
                    'nom' => mb_substr($saisie['nom'], 0, 190), 'email' => mb_substr($saisie['email'], 0, 190),
                    'sujet' => mb_substr($saisie['sujet'], 0, 255), 'message' => $saisie['message'],
                ]);
            }
            if (!$erreurs) {
                flash('succes', 'Merci, votre message a bien été envoyé. La bibliothèque vous répondra rapidement.');
                redirect(url('contact'));
            }
        }
        page('contact', ['saisie' => $saisie, 'erreurs' => $erreurs], ['titre' => 'Contacter la bibliothèque', 'actif' => 'contact', 'classeHtml' => 'view-contact']);

    case 'compte':
        require __DIR__ . '/app/site/compte.php';
        exit;

    default:
        introuvable();
}
