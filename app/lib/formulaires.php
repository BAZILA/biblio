<?php
/** Petits assistants de formulaires pour l'administration. */

/**
 * Champ complet (libellé, saisie, aide, erreur).
 * Options : type (text, email, password, number, date, tel, textarea, code, select, case),
 *           options (select : [valeur => libellé]), required, aide, placeholder, attrs (HTML brut), autocomplete.
 */
function champ(string $nom, string $libelle, $valeur, array $erreurs = [], array $o = []): string
{
    $type = $o['type'] ?? 'text';
    $id = 'f_' . $nom;
    $attrs = ($o['required'] ?? false) ? ' required' : '';
    $attrs .= isset($o['placeholder']) ? ' placeholder="' . e($o['placeholder']) . '"' : '';
    $attrs .= isset($o['autocomplete']) ? ' autocomplete="' . e($o['autocomplete']) . '"' : '';
    $attrs .= isset($o['attrs']) ? ' ' . $o['attrs'] : '';

    if ($type === 'case') {
        $html = '<div class="champ"><label class="case"><input type="checkbox" name="' . e($nom) . '" value="1"'
            . ($valeur ? ' checked' : '') . $attrs . '> ' . e($libelle) . '</label>';
    } else {
        $html = '<div class="champ' . (isset($erreurs[$nom]) ? ' en-erreur' : '') . '"><label for="' . $id . '">' . e($libelle) . '</label>';
        if ($type === 'textarea' || $type === 'code') {
            $html .= '<textarea id="' . $id . '" name="' . e($nom) . '"' . ($type === 'code' ? ' class="code"' : '') . $attrs . '>' . e($valeur) . '</textarea>';
        } elseif ($type === 'select') {
            $html .= '<select id="' . $id . '" name="' . e($nom) . '"' . $attrs . '>';
            foreach ($o['options'] ?? [] as $v => $lib) {
                $html .= '<option value="' . e($v) . '"' . ((string) $v === (string) $valeur ? ' selected' : '') . '>' . e($lib) . '</option>';
            }
            $html .= '</select>';
        } else {
            $html .= '<input type="' . e($type) . '" id="' . $id . '" name="' . e($nom) . '" value="' . e($type === 'password' ? '' : $valeur) . '"' . $attrs . '>';
        }
    }
    if (!empty($o['aide'])) {
        $html .= '<div class="aide">' . e($o['aide']) . '</div>';
    }
    if (isset($erreurs[$nom])) {
        $html .= '<div class="erreur">' . e($erreurs[$nom]) . '</div>';
    }
    return $html . '</div>';
}

/** Petit formulaire POST d'une seule action (bouton), avec jeton CSRF et confirmation optionnelle. */
function bouton_action(string $url, string $libelle, string $classe = 'btn secondaire petit', string $confirmer = '', array $champs = []): string
{
    $html = '<form method="post" action="' . e($url) . '"' . ($confirmer !== '' ? ' data-confirmer="' . e($confirmer) . '"' : '') . '>' . csrf_field();
    foreach ($champs as $k => $v) {
        $html .= '<input type="hidden" name="' . e($k) . '" value="' . e($v) . '">';
    }
    return $html . '<button type="submit" class="' . e($classe) . '">' . $libelle . '</button></form>';
}

/** Pagination de l'admin (?page=N), en conservant les autres paramètres. */
function pagination_admin(int $total, int $parPage = 25): array
{
    $parPage = max(1, $parPage);
    $pages = max(1, (int) ceil($total / $parPage));
    $page = min($pages, max(1, (int) ($_GET['page'] ?? 1)));
    return ['page' => $page, 'pages' => $pages, 'offset' => ($page - 1) * $parPage, 'parPage' => $parPage, 'total' => $total];
}

function liens_pagination(array $p): string
{
    if ($p['pages'] <= 1) {
        return '';
    }
    $html = '<nav class="pagination">';
    for ($i = 1; $i <= $p['pages']; $i++) {
        if ($i === $p['page']) {
            $html .= '<span class="courant">' . $i . '</span>';
        } else {
            $q = array_merge($_GET, ['page' => $i]);
            $html .= '<a href="?' . e(http_build_query($q)) . '">' . $i . '</a>';
        }
    }
    return $html . '</nav>';
}

/** Badge coloré du statut d'un exemplaire. */
function badge_exemplaire(string $statut): string
{
    $c = ['disponible' => ['vert', 'Disponible'], 'prete' => ['orange', 'Prêté'], 'reserve' => ['bleu', 'Réservé'], 'retire' => ['', 'Retiré']][$statut] ?? ['', $statut];
    return '<span class="badge ' . $c[0] . '">' . e($c[1]) . '</span>';
}

/** Libellé d'échéance d'un prêt en cours (badge). */
function badge_echeance(array $pret): string
{
    $j = (int) $pret['jours_retard'];
    if ($pret['date_retour'] !== null) {
        return '<span class="badge">Rendu le ' . e(date_fr($pret['date_retour'])) . '</span>';
    }
    if ($j > 0) {
        return '<span class="badge rouge">' . e(pluriel($j, 'jour')) . ' de retard</span>';
    }
    if ($j === 0) {
        return '<span class="badge orange">À rendre aujourd’hui</span>';
    }
    return '<span class="badge vert">Dans ' . e(pluriel(-$j, 'jour')) . '</span>';
}
