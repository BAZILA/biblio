<?php
/**
 * Actualités et évènements : liste, fiche (texte, image, vidéo de fond, dates), mise au diaporama de l'accueil.
 * Publier, masquer et mettre au diaporama relèvent du droit « publier » : sans lui, une nouvelle fiche reste en brouillon.
 */

$actualiteParId = fn(int $id) => row('SELECT * FROM actualites WHERE id = ?', [$id]) ?? admin_introuvable('Actualité');

if ($action === 'supprimer') {
    exiger_post();
    exiger('actualites.supprimer');
    $a = $actualiteParId($id);
    q('DELETE FROM actualites WHERE id = ?', [$id]);
    flash('succes', '« ' . $a['titre'] . ' » supprimé.');
    redirect(admin_url('actualites'));
}

// Bascules rapides depuis la liste : publiée / au diaporama
if ($action === 'basculer') {
    exiger_post();
    exiger('actualites.publier');
    $a = $actualiteParId($id);
    $champ = post_str('champ') === 'au_diaporama' ? 'au_diaporama' : 'publie';
    q("UPDATE actualites SET $champ = 1 - $champ WHERE id = ?", [$id]);
    flash('succes', '« ' . $a['titre'] . ' » ' . ($champ === 'publie' ? ($a['publie'] ? 'masqué' : 'publié') : ($a['au_diaporama'] ? 'retiré du diaporama' : 'ajouté au diaporama de l’accueil')) . '.');
    redirect(retour_sur(post_str('retour'), admin_url('actualites')));
}

if ($action === 'nouveau' || $action === 'modifier') {
    exiger($action === 'nouveau' ? 'actualites.ajouter' : 'actualites.voir');
    $lectureSeule = $action === 'modifier' && !peut('actualites.modifier');
    $a = $action === 'modifier' ? $actualiteParId($id) : [
        'id' => 0, 'type' => get_str('type') === 'evenement' ? 'evenement' : 'actualite', 'titre' => '', 'slug' => '', 'chapo' => '', 'contenu' => '',
        'image' => '', 'video' => '', 'date_publication' => date('Y-m-d H:i:s'), 'debut' => null, 'fin' => null, 'lieu' => '',
        'au_diaporama' => 0, 'animation' => 'zoom', 'publie' => peut('actualites.publier') ? 1 : 0,
    ];
    $a['contenu'] = html_vers_texte((string) $a['contenu']);
    $erreurs = [];
    if (is_post()) {
        csrf_verify();
        exiger($action === 'nouveau' ? 'actualites.ajouter' : 'actualites.modifier');
        $a['type'] = post_str('type') === 'evenement' ? 'evenement' : 'actualite';
        foreach (['titre' => 190, 'chapo' => 400, 'lieu' => 190] as $k => $max) {
            $a[$k] = mb_substr(post_str($k, ''), 0, $max);
        }
        $a['contenu'] = post_str('contenu', '', false);
        $a['slug'] = slugify(post_str('slug') !== '' ? post_str('slug') : $a['titre']);
        $a['animation'] = isset(ANIMATIONS_DIAPO[post_str('animation')]) ? post_str('animation') : 'zoom';
        $dt = fn(string $k) => ($v = post_str($k)) !== '' && strtotime($v) ? date('Y-m-d H:i:s', strtotime($v)) : null;
        $a['date_publication'] = $dt('date_publication') ?? date('Y-m-d H:i:s');
        $a['debut'] = $a['type'] === 'evenement' ? $dt('debut') : null;
        $a['fin'] = $a['type'] === 'evenement' ? $dt('fin') : null;
        if ($a['type'] !== 'evenement') {
            $a['lieu'] = '';
        }
        if (peut('actualites.publier')) {
            $a['publie'] = isset($_POST['publie']) ? 1 : 0;
            $a['au_diaporama'] = isset($_POST['au_diaporama']) ? 1 : 0;
        }
        if ($a['titre'] === '') {
            $erreurs['titre'] = 'Champ requis.';
        }
        if ($a['chapo'] === '') {
            $erreurs['chapo'] = 'Quelques mots de présentation : ils s’affichent sur la diapositive et les cartes.';
        }
        if (val('SELECT 1 FROM actualites WHERE slug = ? AND id <> ?', [$a['slug'], (int) $a['id']])) {
            $erreurs['slug'] = 'Adresse déjà utilisée par une autre actualité.';
        }
        if ($a['type'] === 'evenement' && !$a['debut']) {
            $erreurs['debut'] = 'Indiquez la date de l’évènement.';
        }
        if ($a['debut'] && $a['fin'] && $a['fin'] < $a['debut']) {
            $erreurs['fin'] = 'La fin est avant le début.';
        }
        if (!$erreurs) {
            try {
                $image = televerser_image('image', 'actualites', $a['titre']);
                $a['image'] = $image ?? $a['image'];
                if (isset($_POST['retirer_video'])) {
                    $a['video'] = '';
                }
                $video = televerser_video('video', $a['titre']);
                $a['video'] = $video ?? $a['video'];
            } catch (RuntimeException $ex) {
                $erreurs[str_contains($ex->getMessage(), 'idéo') ? 'video' : 'image'] = $ex->getMessage();
            }
        }
        if (!$erreurs && $a['au_diaporama'] && $a['image'] === '') {
            $erreurs['image'] = 'Une image est nécessaire pour paraître dans le diaporama (elle sert aussi d’affiche à la vidéo).';
        }
        if (!$erreurs) {
            $donnees = array_intersect_key($a, array_flip(['type', 'titre', 'slug', 'chapo', 'image', 'video', 'date_publication', 'debut', 'fin', 'lieu',
                'au_diaporama', 'animation', 'publie']));
            $donnees['contenu'] = texte_vers_html($a['contenu']);
            if ($a['id']) {
                update('actualites', (int) $a['id'], $donnees);
            } else {
                $a['id'] = insert('actualites', $donnees + ['auteur_id' => $moi['id']]);
            }
            flash('succes', TYPES_ACTUALITE[$a['type']] . ' « ' . $a['titre'] . ' » enregistré' . ($a['type'] === 'actualite' ? 'e' : '') . '.'
                . ($a['publie'] ? '' : ' (brouillon : pas encore visible sur le site)'));
            redirect(admin_url('actualites/modifier/' . $a['id']));
        }
    }
    admin_page('actualite_form', ['a' => $a, 'erreurs' => $erreurs, 'lectureSeule' => $lectureSeule],
        $a['id'] ? 'Modifier : ' . $a['titre'] : 'Nouvelle ' . ($a['type'] === 'evenement' ? 'fiche d’évènement' : 'actualité'), 'actualites');
}

// Liste
$filtre = get_str('filtre', 'tous');
$where = ['tous' => '1', 'actualite' => "a.type = 'actualite'", 'evenement' => "a.type = 'evenement'", 'diaporama' => 'a.au_diaporama = 1',
    'brouillons' => 'a.publie = 0'][$filtre] ?? '1';
$pg = pagination_admin((int) val("SELECT COUNT(*) FROM actualites a WHERE $where"), 30);
admin_page('actualites', [
    'filtre' => $filtre, 'pg' => $pg,
    'liste' => rows("SELECT a.* FROM actualites a WHERE $where ORDER BY a.date_publication DESC, a.id DESC LIMIT {$pg['parPage']} OFFSET {$pg['offset']}"),
    'auDiaporama' => (int) val('SELECT COUNT(*) FROM actualites a WHERE a.au_diaporama = 1 AND ' . ACTUALITE_VISIBLE),
], 'Actualités et évènements', 'actualites');
