<?php /** @var string $filtre @var array $liste, $pg @var int $auDiaporama */
$onglets = ['tous' => 'Tout', 'actualite' => 'Actualités', 'evenement' => 'Évènements', 'diaporama' => 'Au diaporama', 'brouillons' => 'Brouillons'];
$publier = peut('actualites.publier');
$max = reglage_int('diaporama_actualites_max', 6);
?>
<div class="entete">
	<h1>Actualités et évènements</h1>
	<div class="actions">
		<a class="btn secondaire" href="<?= e(url('actualites')) ?>" target="_blank" rel="noopener"><i class="fa-solid fa-arrow-up-right-from-square"></i> Voir la page du site</a>
<?php if (peut('actualites.ajouter')): ?>
		<a class="btn secondaire" href="<?= e(admin_url('actualites/nouveau', ['type' => 'evenement'])) ?>"><i class="fa-solid fa-calendar-plus"></i> Nouvel évènement</a>
		<a class="btn" href="<?= e(admin_url('actualites/nouveau')) ?>"><i class="fa-solid fa-plus"></i> Nouvelle actualité</a>
<?php endif; ?>
	</div>
</div>
<p class="doux">Le diaporama de l’accueil montre d’abord la ou les diapositives fixes (Bienvenue…, rubrique « Diaporama d’accueil »), puis les actualités et évènements
	cochés « au diaporama », les plus récents d’abord (<?= $max ?> au maximum, réglable dans Réglages). Actuellement : <strong><?= $auDiaporama ?></strong> au diaporama.</p>

<nav class="onglets">
<?php foreach ($onglets as $cle => $lib): ?>
	<a href="<?= e(admin_url('actualites', ['filtre' => $cle])) ?>" class="<?= $filtre === $cle ? 'actif' : '' ?>"><?= e($lib) ?></a>
<?php endforeach; ?>
</nav>

<div class="carte" style="padding:0">
<?php if (!$liste): ?>
	<div class="vide">Aucune actualité pour l’instant.</div>
<?php else: ?>
	<div class="tableau-wrap"><table class="tableau">
		<thead><tr><th style="width:96px"></th><th>Titre</th><th>Type</th><th>Date</th><th>Diaporama</th><th>Site</th><th></th></tr></thead>
		<tbody>
<?php foreach ($liste as $a): $avenir = strtotime($a['date_publication']) > time(); ?>
			<tr<?= $a['publie'] ? '' : ' class="inactif"' ?>>
				<td><?php if ($a['image']): ?><img src="<?= e(media($a['image'])) ?>" alt="" class="vignette-actu"><?php endif; ?></td>
				<td><a href="<?= e(admin_url('actualites/modifier/' . $a['id'])) ?>"><strong><?= e($a['titre']) ?></strong></a>
					<?= $a['video'] ? ' <span class="badge bleu" title="Vidéo de fond"><i class="fa-solid fa-film"></i> vidéo</span>' : '' ?>
					<br><span class="doux"><?= e(tronquer($a['chapo'], 110, '…')) ?></span></td>
				<td><span class="badge <?= $a['type'] === 'evenement' ? 'orange' : 'bleu' ?>"><?= e(TYPES_ACTUALITE[$a['type']]) ?></span></td>
				<td class="nowrap"><?= $a['type'] === 'evenement' && $a['debut'] ? e(date_evenement($a)) . ($a['lieu'] !== '' ? '<br><span class="doux">' . e($a['lieu']) . '</span>' : '') : e(date_courte($a['date_publication'])) ?>
					<?= $avenir ? '<br><span class="badge">Publication programmée le ' . e(date_fr($a['date_publication'], true)) . '</span>' : '' ?></td>
				<td><?php if ($publier): ?><?= bouton_action(admin_url('actualites/basculer/' . $a['id']), $a['au_diaporama'] ? '<i class="fa-solid fa-images"></i> Oui' : 'Non',
					'btn petit ' . ($a['au_diaporama'] ? 'vert' : 'secondaire'), '', ['champ' => 'au_diaporama', 'retour' => $_SERVER['REQUEST_URI']]) ?>
					<?php else: ?><?= $a['au_diaporama'] ? '<span class="badge vert">Oui</span>' : '<span class="doux">Non</span>' ?><?php endif; ?></td>
				<td><?php if ($publier): ?><?= bouton_action(admin_url('actualites/basculer/' . $a['id']), $a['publie'] ? 'Publiée' : 'Brouillon',
					'btn petit ' . ($a['publie'] ? 'vert' : 'secondaire'), '', ['champ' => 'publie', 'retour' => $_SERVER['REQUEST_URI']]) ?>
					<?php else: ?><?= $a['publie'] ? '<span class="badge vert">Publiée</span>' : '<span class="badge">Brouillon</span>' ?><?php endif; ?></td>
				<td class="actions">
					<?php if ($a['publie'] && !$avenir): ?><a class="btn secondaire petit" href="<?= e(actualite_url($a)) ?>" target="_blank" rel="noopener" title="Voir sur le site"><i class="fa-solid fa-eye"></i></a><?php endif; ?>
					<a class="btn secondaire petit" href="<?= e(admin_url('actualites/modifier/' . $a['id'])) ?>"><?= peut('actualites.modifier') ? '<i class="fa-solid fa-pen"></i> Modifier' : '<i class="fa-solid fa-eye"></i> Consulter' ?></a>
				</td>
			</tr>
<?php endforeach; ?>
		</tbody>
	</table></div>
<?php endif; ?>
</div>
<?= liens_pagination($pg) ?>
