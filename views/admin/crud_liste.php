<?php /** Liste générique. @var array $res, $lignes @var string $section */
$colonneLien = array_key_first(array_diff_key($res['liste'], ['image' => 1])); // la première colonne texte ouvre la fiche
?>
<div class="entete">
	<h1><?= e($res['titre']) ?></h1>
	<div class="actions">
		<?php if (!empty($res['importer']) && peut('import.' . $res['importer'])): ?><a class="btn secondaire" href="<?= e(admin_url('import', ['quoi' => $res['importer']])) ?>"><i class="fa-solid fa-file-import"></i> Importer un fichier</a><?php endif; ?>
		<?php if ($res['nouveau'] && peut($section . '.ajouter')): ?><a class="btn" href="<?= e(admin_url($section . '/nouveau')) ?>"><i class="fa-solid fa-plus"></i> <?= e($res['nouveau']) ?></a><?php endif; ?>
	</div>
</div>
<?php if (!empty($res['intro'])): ?><p class="doux"><?= e($res['intro']) ?></p><?php endif; ?>

<div class="carte" style="padding:0">
<?php if (!$lignes): ?>
	<div class="vide">Rien pour l’instant.</div>
<?php else: ?>
	<div class="tableau-wrap"><table class="tableau">
		<thead><tr><?php foreach ($res['liste'] as $col => $lib): ?><th<?= $col === 'nb' ? ' class="num"' : '' ?>><?= e($lib) ?></th><?php endforeach; ?><th></th></tr></thead>
		<tbody>
<?php foreach ($lignes as $r): ?>
			<tr>
<?php foreach ($res['liste'] as $col => $lib): $v = $r[$col] ?? ''; ?>
				<td<?= $col === 'nb' ? ' class="num"' : '' ?>>
<?php if ($col === 'image'): ?>
					<?php if ($v): ?><img class="vignette" style="width:<?= $section === 'slides' ? '90px' : '46px' ?>;height:46px" src="<?= e(media($v)) ?>" alt=""><?php endif; ?>
<?php elseif ($col === 'publie'): ?>
					<?= $v ? '<span class="badge vert">Oui</span>' : '<span class="badge">Non</span>' ?>
<?php elseif ($col === $colonneLien): ?>
					<a href="<?= e(admin_url($section . '/modifier/' . $r['id'])) ?>"><strong><?= e(str_replace("\n", ' ', (string) $v)) ?></strong></a>
<?php else: ?>
					<?= e(tronquer((string) $v, 110, '…')) ?>
<?php endif; ?>
				</td>
<?php endforeach; ?>
				<td class="actions">
					<?php if (isset($res['voir'])): ?><a class="btn secondaire petit" href="<?= e(($res['voir'])($r)) ?>" target="_blank" rel="noopener" title="Voir sur le site"><i class="fa-solid fa-eye"></i></a><?php endif; ?>
					<a class="btn secondaire petit" href="<?= e(admin_url($section . '/modifier/' . $r['id'])) ?>"><?= peut($section . '.modifier') ? '<i class="fa-solid fa-pen"></i> Modifier' : '<i class="fa-solid fa-eye"></i> Consulter' ?></a>
				</td>
			</tr>
<?php endforeach; ?>
		</tbody>
	</table></div>
<?php endif; ?>
</div>
