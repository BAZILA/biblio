<?php
/**
 * Écrans génériques liste / formulaire pour les tables simples :
 * domaines, types, auteurs, diapositives de l'accueil, citations.
 */

$ressources = [
    'domaines' => [
        'table' => 'domaines', 'modifier' => 'Modifier le domaine', 'titre' => 'Domaines', 'nouveau' => 'Nouveau domaine', 'ordre' => 'ordre, nom',
        'liste' => ['nom' => 'Nom', 'description' => 'Description', 'nb' => 'Ouvrages'],
        'requete' => 'SELECT d.*, (SELECT COUNT(*) FROM ouvrages o WHERE o.domaine_id = d.id) AS nb FROM domaines d ORDER BY d.ordre, d.nom',
        'champs' => [
            'nom' => ['Nom', 'text', ['required' => true]],
            'slug' => ['Adresse (URL)', 'slug', ['aide' => 'ex. informatique → index.php/domaines/informatique']],
            'description' => ['Description courte', 'textarea', []],
            'icone' => ['Icône affichée sur la page d’accueil', 'icone', []],
            'ordre' => ['Ordre d’affichage', 'number', []],
        ],
        'bloquer' => fn($id) => val('SELECT COUNT(*) FROM ouvrages WHERE domaine_id = ?', [$id]) ? 'Ce domaine contient des ouvrages : déplacez-les avant de le supprimer.' : null,
        'voir' => fn($r) => url('domaines/' . $r['slug']),
    ],
    'types' => [
        'table' => 'types', 'modifier' => 'Modifier le type de document', 'titre' => 'Types de documents', 'nouveau' => 'Nouveau type', 'ordre' => 'ordre, nom',
        'liste' => ['nom' => 'Nom', 'nom_pluriel' => 'Menu', 'description' => 'Description', 'nb' => 'Ouvrages'],
        'requete' => 'SELECT t.*, (SELECT COUNT(*) FROM ouvrages o WHERE o.type_id = t.id) AS nb FROM types t ORDER BY t.ordre, t.nom',
        'champs' => [
            'nom' => ['Nom (singulier)', 'text', ['required' => true, 'aide' => 'ex. Roman']],
            'nom_pluriel' => ['Libellé du menu (pluriel)', 'text', ['required' => true, 'aide' => 'ex. Romans']],
            'slug' => ['Adresse (URL)', 'slug', ['prefixe' => 'type-', 'aide' => 'ex. type-roman']],
            'description' => ['Description courte', 'textarea', []],
            'ordre' => ['Ordre d’affichage', 'number', []],
        ],
        'bloquer' => null, // les ouvrages concernés passent simplement « sans type »
        'voir' => fn($r) => url('types/' . $r['slug']),
    ],
    'auteurs' => [
        'table' => 'auteurs', 'modifier' => "Modifier l’auteur", 'titre' => 'Auteurs', 'nouveau' => 'Nouvel auteur', 'importer' => 'auteurs', 'ordre' => 'nom',
        'liste' => ['nom' => 'Nom', 'nb' => 'Ouvrages'],
        'requete' => 'SELECT a.*, (SELECT COUNT(*) FROM ouvrage_auteur oa WHERE oa.auteur_id = a.id) AS nb FROM auteurs a ORDER BY a.nom',
        'champs' => [
            'nom' => ['Nom affiché', 'text', ['required' => true, 'aide' => 'Corriger ici l’orthographe met à jour tous les ouvrages de l’auteur.']],
            'slug' => ['Adresse (URL)', 'slug', ['prefixe' => 'auteur-']],
        ],
        'bloquer' => fn($id) => val('SELECT COUNT(*) FROM ouvrage_auteur WHERE auteur_id = ?', [$id]) ? 'Cet auteur est lié à des ouvrages : retirez-le de leurs fiches d’abord.' : null,
        'voir' => fn($r) => url('auteurs/' . $r['slug']),
        'intro' => 'Ajoutez un auteur ici, importez une liste depuis un fichier, ou créez-le directement depuis la fiche d’un ouvrage.',
    ],
    'slides' => [
        'table' => 'slides', 'modifier' => 'Modifier la diapositive', 'titre' => 'Diaporama d’accueil', 'nouveau' => 'Nouvelle diapositive', 'ordre' => 'ordre, id',
        'liste' => ['image' => 'Image', 'titre' => 'Titre', 'bouton' => 'Bouton', 'publie' => 'Publiée'],
        'intro' => 'Diapositives fixes de l’accueil (la première accueille les visiteurs). Les actualités et évènements cochés « au diaporama » défilent ensuite (rubrique Actualités et évènements).',
        'requete' => 'SELECT * FROM slides ORDER BY ordre, id',
        'champs' => [
            'titre' => ['Titre', 'textarea', ['required' => true, 'attrs' => 'rows="2" style="min-height:0"', 'aide' => 'Un retour à la ligne crée un saut de ligne dans le grand titre.']],
            'texte' => ['Texte', 'textarea', []],
            'bouton' => ['Libellé du bouton', 'text', ['aide' => 'Laisser vide pour ne pas afficher de bouton.']],
            'lien' => ['Lien du bouton', 'text', ['aide' => 'Page du site (ex. catalogue, domaines/informatique) ou adresse complète https://…']],
            'image' => ['Image de fond', 'image', ['dossier' => 'biblio', 'aide' => 'Format paysage, 1920×1080 px conseillé. Sert aussi d’affiche pendant le chargement de la vidéo.']],
            'video' => ['Vidéo de fond (facultatif)', 'video', ['aide' => 'MP4 ou WebM, quelques secondes en boucle, sans son : elle remplace l’image sur la diapositive.']],
            'animation' => ['Effet de la diapositive', 'select', ['options' => ANIMATIONS_DIAPO]],
            'ordre' => ['Ordre', 'number', []],
            'publie' => ['Publiée', 'case', []],
        ],
        'bloquer' => null,
    ],
    'citations' => [
        'table' => 'citations', 'modifier' => 'Modifier la citation', 'titre' => 'Citations (« Paroles d’auteurs »)', 'nouveau' => 'Nouvelle citation', 'ordre' => 'ordre, id',
        'liste' => ['image' => 'Portrait', 'auteur' => 'Auteur', 'texte' => 'Citation', 'publie' => 'Publiée'],
        'requete' => 'SELECT * FROM citations ORDER BY ordre, id',
        'champs' => [
            'texte' => ['Citation', 'textarea', ['required' => true]],
            'auteur' => ['Auteur', 'text', ['required' => true]],
            'source' => ['Source', 'text', ['aide' => 'Œuvre ou circonstance, ex. « Du contrat social ».']],
            'image' => ['Portrait / avatar', 'image', ['dossier' => 'biblio', 'aide' => 'Image carrée, 200×200 px conseillé.']],
            'ordre' => ['Ordre', 'number', []],
            'publie' => ['Publiée', 'case', []],
        ],
        'bloquer' => null,
    ],
];

// Auto-complétion des auteurs (fiche ouvrage)
if ($section === 'auteurs' && $action === 'chercher') {
    $q = '%' . get_str('q') . '%';
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(rows('SELECT a.nom, (SELECT COUNT(*) FROM ouvrage_auteur oa WHERE oa.auteur_id = a.id) AS nb
                           FROM auteurs a WHERE a.nom LIKE ? ORDER BY a.nom LIMIT 10', [$q]), JSON_UNESCAPED_UNICODE);
    exit;
}

$res = $ressources[$section];
$table = $res['table'];

if ($action === 'supprimer') {
    exiger_post();
    exiger("$section.supprimer");
    $ligne = row("SELECT * FROM $table WHERE id = ?", [$id]) ?? admin_introuvable();
    $motif = $res['bloquer'] ? ($res['bloquer'])($id) : null;
    if ($motif) {
        flash('erreur', $motif);
        redirect(admin_url("$section/modifier/$id"));
    }
    q("DELETE FROM $table WHERE id = ?", [$id]);
    flash('succes', 'Élément supprimé.');
    redirect(admin_url($section));
}

if ($action === 'nouveau' || $action === 'modifier') {
    if ($action === 'nouveau' && !$res['nouveau']) {
        admin_introuvable('Page');
    }
    // Créer exige « ajouter » ; une fiche existante s'ouvre en lecture seule sans « modifier »
    exiger($action === 'nouveau' ? "$section.ajouter" : "$section.voir");
    $lectureSeule = $action === 'modifier' && !peut("$section.modifier");
    $ligne = $action === 'modifier'
        ? (row("SELECT * FROM $table WHERE id = ?", [$id]) ?? admin_introuvable())
        : array_fill_keys(array_keys($res['champs']), '') + ['id' => 0, 'publie' => 1]
          + (isset($res['champs']['ordre']) ? ['ordre' => (int) val("SELECT COALESCE(MAX(ordre), 0) + 1 FROM $table")] : []);
    $erreurs = [];

    if (is_post()) {
        csrf_verify();
        exiger($action === 'nouveau' ? "$section.ajouter" : "$section.modifier");
        $data = [];
        foreach ($res['champs'] as $nom => [$libelle, $type, $opts]) {
            if ($type === 'image' || $type === 'video') {
                continue;
            }
            $v = $type === 'case' ? (isset($_POST[$nom]) ? 1 : 0) : post_str($nom, '');
            if ($type === 'number') {
                $v = (int) $v;
            }
            if ($type === 'slug') {
                $source = $ligne['nom'] ?? '';
                $source = post_str('nom', $source);
                $prefixe = $opts['prefixe'] ?? '';
                $v = $v !== '' ? slugify($v) : $prefixe . slugify($source);
                if (val("SELECT 1 FROM $table WHERE slug = ? AND id <> ?", [$v, (int) $ligne['id']])) {
                    $erreurs[$nom] = 'Adresse déjà utilisée.';
                }
            }
            if (($opts['required'] ?? false) && $v === '') {
                $erreurs[$nom] = 'Champ requis.';
            }
            $data[$nom] = $v;
            $ligne[$nom] = $v;
        }
        foreach ($res['champs'] as $nom => [$libelle, $type, $opts]) {
            if (($type !== 'image' && $type !== 'video') || $erreurs) {
                continue;
            }
            try {
                if ($type === 'video' && isset($_POST['retirer_' . $nom])) {
                    $data[$nom] = $ligne[$nom] = '';
                }
                $chemin = $type === 'video'
                    ? televerser_video($nom, $ligne['titre'] ?? $section)
                    : televerser_image($nom, $opts['dossier'], $ligne['titre'] ?? $ligne['auteur'] ?? $section);
                if ($chemin) {
                    $data[$nom] = $chemin;
                    $ligne[$nom] = $chemin;
                }
            } catch (RuntimeException $ex) {
                $erreurs[$nom] = $ex->getMessage();
            }
        }
        if ($section === 'slides' && !$ligne['id'] && empty($data['image']) && !$erreurs) {
            $erreurs['image'] = 'Choisissez une image de fond.';
        }
        if (!$erreurs) {
            if ($ligne['id']) {
                update($table, (int) $ligne['id'], $data);
            } else {
                $ligne['id'] = insert($table, $data);
            }
            flash('succes', 'Enregistré.');
            redirect(admin_url($section));
        }
    }
    admin_page('crud_form', ['res' => $res, 'section' => $section, 'ligne' => $ligne, 'erreurs' => $erreurs, 'lectureSeule' => $lectureSeule],
        $ligne['id'] ? $res['modifier'] : $res['nouveau'], $section);
}

admin_page('crud_liste', ['res' => $res, 'section' => $section, 'lignes' => rows($res['requete'])], $res['titre'], $section);
