<?php
/**
 * Encadré « Où trouver ce document » : centre documentaire, campus, salle ou rayon, cote.
 * Mis en évidence sur la fiche, c'est ce que le lecteur cherche avant de se déplacer. @var array $o
 */
$centre = trim((string) ($o['site_nom'] ?? ''));
$campus = ($o['site_campus'] ?? 'autre') !== 'autre' ? CAMPUS[$o['site_campus']] : '';
$adresse = trim((string) ($o['site_adresse'] ?? ''));
$emplacement = trim((string) ($o['emplacement'] ?? ''));
$cote = trim((string) ($o['cote'] ?? ''));
if ($centre === '' && $emplacement === '' && $cote === '') {
    return;
}
?>
<aside class="biblio-ou">
	<h3><i class="fa fa-map-marker" aria-hidden="true"></i> Où le trouver</h3>
<?php if ($centre !== ''): ?>
	<p class="biblio-ou-centre"><?= e($centre) ?><?php if ($campus !== ''): ?> <span class="biblio-ou-campus"><?= e($campus) ?></span><?php endif; ?></p>
<?php endif; ?>
<?php if ($adresse !== ''): ?>
	<p class="biblio-ou-ligne"><i class="fa fa-building-o" aria-hidden="true"></i> <?= e($adresse) ?></p>
<?php endif; ?>
<?php if ($emplacement !== ''): ?>
	<p class="biblio-ou-ligne"><i class="fa fa-bookmark-o" aria-hidden="true"></i> <?= e($emplacement) ?></p>
<?php endif; ?>
<?php if ($cote !== ''): ?>
	<p class="biblio-ou-cote"><span>Cote</span> <strong><?= e($cote) ?></strong></p>
<?php endif; ?>
</aside>
