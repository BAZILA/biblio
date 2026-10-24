<?php
/**
 * Courbe(s) mois par mois en SVG, avec légende, étiquettes directes en bout de ligne, réticule et infobulle
 * au survol (assets/admin/admin.js, [data-courbe]) et tableau des valeurs.
 * Couleurs validées (contraste, daltonisme) : bleu #3a5bd0, vert UK #009c30.
 * @var string $titre @var array $mois  ['2026-01', …] @var array $series [['nom' => 'Prêts', 'couleur' => '#3a5bd0', 'valeurs' => [n, …]], …]
 */
$n = count($mois);
if ($n < 2) {
    echo '<p class="rien">Pas encore assez de mois d’activité pour tracer une courbe.</p>';
    return;
}
$L = 820; $H = 260; $g = 44; $d = 96; $h = 14; $b = 30; // largeur, hauteur, marges gauche / droite / haut / bas
$max = max(1, ...array_merge(...array_map(fn($s) => $s['valeurs'], $series)));
$pas = 5000;
foreach ([1, 2, 5, 10, 20, 25, 50, 100, 200, 250, 500, 1000, 2000, 5000] as $p) {
    if ($max / $p <= 4) { $pas = $p; break; }
}
$haut = (int) (ceil($max / $pas) * $pas);
$x = fn(int $i) => $g + ($L - $g - $d) * $i / ($n - 1);
$y = fn(float $v) => $h + ($H - $h - $b) * (1 - $v / $haut);
$donnees = ['mois' => array_map('mois_fr', $mois), 'series' => array_map(fn($s) => ['nom' => $s['nom'], 'couleur' => $s['couleur'], 'valeurs' => $s['valeurs'],
    'y' => array_map(fn($v) => round($y($v), 1), $s['valeurs'])], $series),
    'x' => array_map(fn($i) => round($x($i), 1), range(0, $n - 1)), 'largeur' => $L];
// Étiquettes de l'axe des mois : une sur deux au-delà de 12 mois
$saut = $n > 12 ? (int) ceil($n / 12) : 1;
// Étiquettes directes : écartées si les deux derniers points sont trop proches
$finY = array_map(fn($s) => $y(end($s['valeurs'])), $series);
if (count($finY) === 2 && abs($finY[0] - $finY[1]) < 16) {
    $milieu = ($finY[0] + $finY[1]) / 2;
    [$finY[0], $finY[1]] = $finY[0] <= $finY[1] ? [$milieu - 8, $milieu + 8] : [$milieu + 8, $milieu - 8];
}
?>
<figure class="courbe" data-courbe='<?= e(json_encode($donnees, JSON_UNESCAPED_UNICODE)) ?>'>
	<div class="courbe-legende" aria-hidden="true">
<?php foreach ($series as $s): ?>
		<span><i style="background:<?= e($s['couleur']) ?>"></i><?= e($s['nom']) ?></span>
<?php endforeach; ?>
	</div>
	<div class="courbe-zone">
		<svg viewBox="0 0 <?= $L ?> <?= $H ?>" role="img" aria-label="<?= e($titre) ?>">
<?php for ($v = 0; $v <= $haut; $v += $pas): ?>
			<line x1="<?= $g ?>" x2="<?= $L - $d + 6 ?>" y1="<?= round($y($v), 1) ?>" y2="<?= round($y($v), 1) ?>" class="courbe-grille<?= $v === 0 ? ' base' : '' ?>"/>
			<text x="<?= $g - 8 ?>" y="<?= round($y($v), 1) + 4 ?>" class="courbe-axe" text-anchor="end"><?= $v ?></text>
<?php endfor; ?>
<?php foreach ($mois as $i => $m): if ($i % $saut && $i !== $n - 1) continue; ?>
			<text x="<?= round($x($i), 1) ?>" y="<?= $H - 8 ?>" class="courbe-axe" text-anchor="middle"><?= e(mois_court($m)) ?></text>
<?php endforeach; ?>
<?php foreach ($series as $k => $s):
    $points = [];
    foreach ($s['valeurs'] as $i => $v) {
        $points[] = round($x($i), 1) . ',' . round($y($v), 1);
    }
    $aire = 'M' . round($x(0), 1) . ',' . round($y(0), 1) . ' L' . implode(' L', $points) . ' L' . round($x($n - 1), 1) . ',' . round($y(0), 1) . ' Z'; ?>
			<path d="<?= $aire ?>" fill="<?= e($s['couleur']) ?>" opacity="<?= $k === 0 ? '.08' : '.05' ?>"/>
			<polyline points="<?= implode(' ', $points) ?>" fill="none" stroke="<?= e($s['couleur']) ?>" stroke-width="2" stroke-linejoin="round" stroke-linecap="round"/>
			<circle cx="<?= round($x($n - 1), 1) ?>" cy="<?= round($y(end($s['valeurs'])), 1) ?>" r="4" fill="<?= e($s['couleur']) ?>" stroke="#fff" stroke-width="2"/>
			<text x="<?= $L - $d + 12 ?>" y="<?= round($finY[$k], 1) + 4 ?>" class="courbe-etiquette"><tspan class="valeur"><?= (int) end($s['valeurs']) ?></tspan> <?= e(mb_strtolower($s['nom'])) ?></text>
<?php endforeach; ?>
			<line class="courbe-reticule" x1="0" x2="0" y1="<?= $h ?>" y2="<?= $H - $b ?>" visibility="hidden"/>
<?php foreach ($series as $s): ?>
			<circle class="courbe-point" r="5" fill="<?= e($s['couleur']) ?>" stroke="#fff" stroke-width="2" visibility="hidden"/>
<?php endforeach; ?>
			<rect class="courbe-capture" x="<?= $g ?>" y="<?= $h ?>" width="<?= $L - $g - $d ?>" height="<?= $H - $h - $b ?>" fill="transparent" tabindex="0" aria-label="Parcourir les mois au clavier (flèches)"/>
		</svg>
		<div class="courbe-bulle" role="status" hidden></div>
	</div>
	<details class="courbe-tableau">
		<summary>Voir les valeurs</summary>
		<div class="tableau-wrap"><table class="tableau">
			<thead><tr><th>Mois</th><?php foreach ($series as $s): ?><th class="num"><?= e($s['nom']) ?></th><?php endforeach; ?></tr></thead>
			<tbody>
<?php foreach ($mois as $i => $m): ?>
				<tr><td><?= e(mois_fr($m)) ?></td><?php foreach ($series as $s): ?><td class="num"><?= (int) $s['valeurs'][$i] ?></td><?php endforeach; ?></tr>
<?php endforeach; ?>
			</tbody>
		</table></div>
	</details>
</figure>
