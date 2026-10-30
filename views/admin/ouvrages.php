<?php /** @var array $liste, $pg, $domaines, $types, $sites @var string $q, $etat @var int $domaine, $type, $site, $sansType */ ?>
<div class="entete">
	<h1>Ouvrages</h1>
	<div class="actions"><?php if (peut('import.ouvrages')): ?><a class="btn secondaire" href="<?= e(admin_url('import', ['quoi' => 'ouvrages'])) ?>"><i class="fa-solid fa-file-import"></i> Importer un fichier</a><?php endif; ?><?php if (peut('ouvrages.ajouter')): ?><a class="btn" href="<?= e(admin_url('ouvrages/nouveau')) ?>"><i class="fa-solid fa-plus"></i> Nouvel ouvrage</a><?php endif; ?></div>
</div>

<form class="filtres" method="get">
	<input type="search" name="q" value="<?= e($q) ?>" placeholder="Titre, auteur, collection, cote, ISBN, code…">
	<select name="domaine">
		<option value="">Tous les domaines</option>
		<?php foreach ($domaines as $d): ?><option value="<?= $d['id'] ?>"<?= $domaine === (int) $d['id'] ? ' selected' : '' ?>><?= e($d['nom']) ?></option><?php endforeach; ?>
	</select>
	<select name="type">
		<option value="">Tous les types</option>
		<?php foreach ($types as $t): ?><option value="<?= $t['id'] ?>"<?= $type === (int) $t['id'] ? ' selected' : '' ?>><?= e($t['nom']) ?></option><?php endforeach; ?>
	</select>
<?php if ($sites): ?>
	<select name="site">
		<option value="">Tous les centres</option>
		<?php foreach ($sites as $sid => $snom): ?><option value="<?= $sid ?>"<?= $site === (int) $sid ? ' selected' : '' ?>><?= e($snom) ?></option><?php endforeach; ?>
	</select>
<?php endif; ?>
	<select name="etat">
		<option value="">Tous</option>
		<option value="brouillon"<?= $etat === 'brouillon' ? ' selected' : '' ?>>Non publiés</option>
		<option value="coeur"<?= $etat === 'coeur' ? ' selected' : '' ?>>Coups de cœur</option>
		<option value="desactive"<?= $etat === 'desactive' ? ' selected' : '' ?>>Désactivés</option>
<?php if ($sansType): ?>
		<option value="sans-type"<?= $etat === 'sans-type' ? ' selected' : '' ?>>Sans type de document (<?= $sansType ?>)</option>
<?php endif; ?>
	</select>
	<button class="btn secondaire" type="submit"><i class="fa-solid fa-magnifying-glass"></i> Filtrer</button>
	<span class="doux"><?= e(pluriel($pg['total'], 'ouvrage')) ?></span>
</form>
<?php if ($sansType && $etat !== 'sans-type'): ?>
<p class="doux"><i class="fa-solid fa-circle-info"></i> Le type de document est maintenant obligatoire.
	<a href="<?= e(admin_url('ouvrages', ['etat' => 'sans-type'])) ?>"><?= e(pluriel($sansType, 'ouvrage n’a pas encore de type', 'ouvrages n’ont pas encore de type')) ?></a> : complétez-les à l’occasion.</p>
<?php endif; ?>

<div class="carte" style="padding:0">
<?php if (!$liste): ?>
	<div class="vide">Aucun ouvrage ne correspond.</div>
<?php else: ?>
	<div class="tableau-wrap"><table class="tableau">
		<thead><tr><th></th><th>Titre</th><th>Domaine</th><th>Type</th><?php if ($sites): ?><th>Centre</th><?php endif; ?><th>Cote</th><th class="num">Exemplaires</th><th></th></tr></thead>
		<tbody>
<?php foreach ($liste as $o): ?>
			<tr<?= $o['actif'] ? '' : ' class="inactif"' ?>>
				<td><img class="vignette" src="<?= e(couverture($o['image'])) ?>" alt=""></td>
				<td><a href="<?= e(admin_url('ouvrages/modifier/' . $o['id'])) ?>"><strong><?= e($o['titre']) ?></strong></a>
					<?= !$o['actif'] ? ' <span class="badge rouge">Désactivé</span>' : ($o['publie'] ? '' : ' <span class="badge orange">Non publié</span>') ?><?= $o['coup_de_coeur'] ? ' <span class="badge bleu">Coup de cœur</span>' : '' ?>
					<br><small><?= e($o['auteurs_txt']) ?></small></td>
				<td><?= e($o['domaine_nom']) ?></td>
				<td><?= e($o['type_nom'] ?? '—') ?></td>
<?php if ($sites): ?>
				<td><?= e($o['site_nom'] ?? '—') ?><?= $o['site_campus'] && $o['site_campus'] !== 'autre' ? '<br><small class="doux">' . e(CAMPUS[$o['site_campus']]) . '</small>' : '' ?></td>
<?php endif; ?>
				<td><code><?= e($o['cote']) ?></code></td>
				<td class="num"><?php if ($o['consultation_sur_place']): ?><span class="badge bleu">Sur place</span> <?php endif; ?>
					<span class="badge <?= $o['nb_dispo'] ? 'vert' : ($o['nb_ex'] ? 'orange' : '') ?>"><?= $o['nb_dispo'] ?> / <?= $o['nb_ex'] ?> dispo.</span></td>
				<td class="actions"><?php if ($o['actif'] && $o['nb_dispo'] && !$o['consultation_sur_place'] && peut('prets.ajouter')): ?><a class="btn petit" href="<?= e(admin_url('guichet', ['ouvrage' => $o['id']])) ?>" title="Prêter cet ouvrage"><i class="fa-solid fa-right-left"></i> Prêter</a> <?php endif; ?><a class="btn secondaire petit" href="<?= e($o['url']) ?>" target="_blank" rel="noopener" title="Voir sur le site"><i class="fa-solid fa-eye"></i></a></td>
			</tr>
<?php endforeach; ?>
		</tbody>
	</table></div>
<?php endif; ?>
</div>
<?= liens_pagination($pg) ?>
