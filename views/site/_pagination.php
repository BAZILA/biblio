<?php
/** Pagination au format du site d'origine (?start=N). @var array $pagination @var string $chemin */
$p = $pagination;
if ($p['pages'] <= 1) {
    return;
}
$query = $_GET;
$lien = function (int $page) use ($p, $chemin, $query): string {
    $query['start'] = ($page - 1) * $p['parPage'];
    if ($query['start'] === 0) {
        unset($query['start']);
    }
    return url($chemin, $query);
};
$item = function (string $libelle, ?int $page, bool $actif = false) use ($lien): string {
    if ($actif) {
        return '<li class="active"><a>' . e($libelle) . '</a></li>';
    }
    if ($page === null) {
        return '<li class="disabled"><a>' . e($libelle) . '</a></li>';
    }
    return '<li><a title="' . e($libelle) . '" href="' . e($lien($page)) . '">' . e($libelle) . '</a></li>';
};
?>
		<div class="pagination-wrap">
				<div class="counter"> Page <?= $p['page'] ?> sur <?= $p['pages'] ?></div>
				<ul class="pagination"><?php
    echo $item('Démarrer', $p['page'] > 1 ? 1 : null);
    echo $item('Précédent', $p['page'] > 1 ? $p['page'] - 1 : null);
    for ($i = 1; $i <= $p['pages']; $i++) {
        echo $item((string) $i, $i, $i === $p['page']);
    }
    echo $item('Suivant', $p['page'] < $p['pages'] ? $p['page'] + 1 : null);
    echo $item('Fin', $p['page'] < $p['pages'] ? $p['pages'] : null);
?></ul> </div>
