<?php
/** Aperçu d'un import avant confirmation. @var string $quoi, $fichier @var array $analyse */
$compte = array_count_values(array_column($analyse, 'statut'));
$badges = ['nouveau' => ['vert', 'À importer'], 'ignore' => ['', 'Ignoré'], 'erreur' => ['rouge', 'Erreur']];
?>
<div class="fil"><a href="<?= e(admin_url('import', ['quoi' => $quoi])) ?>">Importer</a> ›</div>
<div class="entete">
	<h1>Aperçu : <?= e($fichier) ?></h1>
</div>

<div class="stats">
	<div class="stat"><div class="valeur"><?= $compte['nouveau'] ?? 0 ?></div><div class="libelle"><?= $quoi === 'auteurs' ? 'auteurs' : 'ouvrages' ?> à importer</div></div>
	<div class="stat"><div class="valeur"><?= $compte['ignore'] ?? 0 ?></div><div class="libelle">ignorés (déjà présents ou en double)</div></div>
	<div class="stat<?= !empty($compte['erreur']) ? ' alerte' : '' ?>"><div class="valeur"><?= $compte['erreur'] ?? 0 ?></div><div class="libelle">lignes en erreur (non importées)</div></div>
</div>

<div class="carte" style="padding:0">
	<div class="tableau-wrap"><table class="tableau">
		<thead><tr><th>Ligne</th><th>État</th>
			<?php if ($quoi === 'auteurs'): ?><th>Nom</th><?php else: ?><th>Titre</th><th>Auteurs</th><th>Domaine</th><th>Type</th><th>Cote</th><th class="num">Ex.</th><?php endif; ?>
			<th>Remarque</th></tr></thead>
		<tbody>
<?php foreach ($analyse as $l): $b = $badges[$l['statut']]; ?>
			<tr>
				<td class="num"><?= (int) $l['ligne'] ?></td>
				<td><span class="badge <?= $b[0] ?>"><?= e($b[1]) ?></span></td>
<?php if ($quoi === 'auteurs'): ?>
				<td><?= e($l['nom']) ?></td>
<?php else: ?>
				<td><strong><?= e($l['titre']) ?></strong></td>
				<td><?= e(implode(', ', $l['auteurs'])) ?></td>
				<td><?= e($l['domaine']) ?></td>
				<td><?= e($l['type']) ?></td>
				<td><code><?= e($l['cote']) ?></code></td>
				<td class="num"><?= (int) $l['exemplaires'] ?></td>
<?php endif; ?>
				<td class="doux"><?= e($l['message']) ?></td>
			</tr>
<?php endforeach; ?>
		</tbody>
	</table></div>
</div>

<form method="post" action="<?= e(admin_url('import/confirmer')) ?>" class="barre-actions" style="margin-top:16px">
	<?= csrf_field() ?>
	<span class="doux">Seules les lignes « À importer » seront enregistrées.</span>
	<a class="btn secondaire" href="<?= e(admin_url('import', ['quoi' => $quoi])) ?>">Annuler</a>
	<button class="btn" type="submit"<?= empty($compte['nouveau']) ? ' disabled' : '' ?>><i class="fa-solid fa-circle-check"></i> Importer <?= (int) ($compte['nouveau'] ?? 0) ?> ligne(s)</button>
</form>
