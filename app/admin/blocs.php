<?php
/**
 * Textes éditables du site (blocs HTML de l'accueil, du pied de page et de la page contact).
 * Réservé aux administrateurs : le HTML y est enregistré tel quel.
 */

exiger_admin();

if ($action === 'modifier') {
    $cle = (string) ($segments[2] ?? '');
    $bloc = row('SELECT * FROM blocs WHERE cle = ?', [$cle]) ?? admin_introuvable('Bloc');
    if (is_post()) {
        csrf_verify();
        q('UPDATE blocs SET contenu = ? WHERE cle = ?', [post_str('contenu', '', false), $cle]);
        flash('succes', '« ' . $bloc['titre'] . ' » enregistré.');
        redirect(admin_url('blocs'));
    }
    admin_page('bloc_form', ['bloc' => $bloc], $bloc['titre'], 'blocs');
}

admin_page('blocs', ['blocs' => rows('SELECT * FROM blocs ORDER BY titre')], 'Textes du site', 'blocs');
