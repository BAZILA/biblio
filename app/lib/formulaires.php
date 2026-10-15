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
        $html = '<div class="champ' . (isset($erreurs[$nom]) ? ' en-erreur' : '') . '"><label for="' . $id . '">' . e($libelle)
            . (($o['required'] ?? false) ? ' <span class="requis" title="Obligatoire">*</span>' : '') . '</label>';
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

/** Bouton qui ouvre une fenêtre (views/admin/_modales.php) préremplie avec $valeurs. */
function bouton_modale(string $modale, string $libelle, string $classe, array $valeurs): string
{
    return '<button type="button" class="' . e($classe) . '" data-modale="#' . e($modale) . '" data-valeurs="'
        . e(json_encode($valeurs, JSON_UNESCAPED_UNICODE)) . '">' . $libelle . '</button>';
}

/** « Remettre » une réservation à retirer : ouvre la fenêtre de remise (état, date de retour, remarque). */
function bouton_remettre(array $r): string
{
    if (!peut('reservations.remettre', 'prets.ajouter')) {
        return '';
    }
    return bouton_modale('modale-remise', '<i class="fa-solid fa-hand-holding"></i> Remettre', 'btn petit', [
        'lecteur_id' => $r['lecteur_id'],
        'code'       => $r['exemplaire_code'],
        'lecteur'    => trim($r['prenom'] . ' ' . $r['nom']) . ($r['matricule'] ? ' · ' . $r['matricule'] : ''),
        'titre'      => $r['titre'],
        'exemplaire' => $r['exemplaire_code'],
        'etat'       => $r['exemplaire_etat'] ?: 'bon',
        'echeance'   => echeance_standard(),
        'notes'      => '',
    ]);
}

/** « Retour » d'un prêt : ouvre la fenêtre de constat (rendu tel quel, usé, abîmé, perdu). */
function bouton_retour(array $p, string $libelle = '<i class="fa-solid fa-rotate-left"></i> Retour', string $classe = 'btn vert petit'): string
{
    if (!peut('prets.retour')) {
        return '';
    }
    $retard = (int) $p['jours_retard'];
    return bouton_modale('modale-retour', $libelle, $classe, [
        'action'     => admin_url('prets/rendre/' . $p['id']),
        'lecteur'    => trim($p['prenom'] . ' ' . $p['nom']),
        'titre'      => $p['titre'],
        'exemplaire' => $p['exemplaire_code'],
        'sortie'     => ETATS_EXEMPLAIRE[$p['etat_sortie'] ?: $p['exemplaire_etat']] ?? '—',
        'echeance'   => date_fr($p['date_retour_prevue']) . ($retard > 0 ? ' — en retard de ' . pluriel($retard, 'jour') : ''),
        'notes'      => $p['notes'] !== '' ? 'Remarque à la remise : ' . $p['notes'] : '',
    ]);
}

/** « Prolonger » un prêt (personnel) : ouvre la fenêtre de prolongation avec la date proposée. */
function bouton_prolonger(array $p): string
{
    if (!peut('prets.prolonger')) {
        return '';
    }
    $obstacles = obstacles_prolongation($p);
    if ($obstacles && !peut('prets.forcer')) {
        return '<button type="button" class="btn secondaire petit" disabled title="' . e('Prolongation impossible : ' . implode(' ', $obstacles)) . '">Prolonger</button>';
    }
    return bouton_modale('modale-prolonger', 'Prolonger', 'btn secondaire petit', [
        'action'    => admin_url('prets/prolonger/' . $p['id']),
        'lecteur'   => trim($p['prenom'] . ' ' . $p['nom']),
        'titre'     => $p['titre'],
        'echeance'  => date_fr($p['date_retour_prevue']),
        'date'      => echeance_prolongee($p),
        'obstacles' => implode(' ', $obstacles),
    ]);
}

/** Ce que devient l'exemplaire après un retour (message affiché au guichet). */
const DEVENIR_EXEMPLAIRE = [
    'rayon'   => 'Exemplaire à replacer en rayon.',
    'reserve' => 'Exemplaire à mettre de côté : un lecteur l’a réservé.',
    'retire'  => 'Exemplaire retiré de la circulation (voir sa fiche).',
];

/** Constat de retour envoyé par le formulaire (views/admin/_champs_retour.php). */
function constat_retour_post(int $agentId): array
{
    return [
        'etat'    => post_str('etat_retour', 'conforme'),
        'note'    => post_str('note_retour', ''),
        'retirer' => isset($_POST['retirer']),
        'agent'   => $agentId,
    ];
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

/** Constat fait au retour d'un prêt (badge, avec la remarque en info-bulle). */
function badge_constat(array $pret): string
{
    if (!$pret['etat_retour']) {
        return '';
    }
    $classe = ['conforme' => 'vert', 'use' => '', 'abime' => 'orange', 'perdu' => 'rouge'][$pret['etat_retour']] ?? '';
    return '<span class="badge ' . $classe . '"' . ($pret['note_retour'] !== '' ? ' title="' . e($pret['note_retour']) . '"' : '') . '>'
        . e(ETATS_RETOUR[$pret['etat_retour']] ?? $pret['etat_retour']) . '</span>'
        . ($pret['note_retour'] !== '' ? '<br><small>' . e($pret['note_retour']) . '</small>' : '');
}
