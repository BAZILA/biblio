<?php /** @var array $liste, $pg, $domaines, $types @var string $q, $etat @var int $domaine, $type */ ?>
<div class="entete">
	<h1>Ouvrages</h1>
	<div class="actions"><a class="btn" href="<?= e(admin_url('ouvrages/nouveau')) ?>"><i class="fa-solid fa-plus"></i> Nouvel ouvrage</a></div>
</div>

<form class="filtres" method="get">
	<input type="search" name="q" value="<?= e($q) ?>" placeholder="Titre, auteur, cote, ISBN, code…">
	<select name="domaine">
		<option value="">Tous les domaines</option>
		<?php foreach ($domaines as $d): ?><option value="<?= $d['id'] ?>"<?= $domaine === (int) $d['id'] ? ' selected' : '' ?>><?= e($d['nom']) ?></option><?php endforeach; ?>
	</select>
	<select name="type">
		<option value="">Tous les types</option>
		<?php foreach ($types as $t): ?><option value="<?= $t['id'] ?>"<?= $type === (int) $t['id'] ? ' selected' : '' ?>><?= e($t['nom']) ?></option><?php endforeach; ?>
	</select>
	<select name="etat">
		<option value="">Tous</option>
		<option value="brouillon"<?= $etat === 'brouillon' ? ' selected' : '' ?>>Non publiés</option>
		<option value="coeur"<?= $etat === 'coeur' ? ' selected' : '' ?>>Coups de cœur</option>
	</select>
	<button class="btn secondaire" type="submit"><i class="fa-solid fa-magnifying-glass"></i> Filtrer</button>
	<span class="doux"><?= e(pluriel($pg['total'], 'ouvrage')) ?></span>
</form>

<div class="carte" style="padding:0">
<?php if (!$liste): ?>
	<div class="vide">Aucun ouvrage ne correspond.</div>
<?php else: ?>
	<div class="tableau-wrap"><table class="tableau">
		<thead><tr><th></th><th>Titre</th><th>Domaine</th><th>Type</th><th>Cote</th><th class="num">Exemplaires</th><th></th></tr></thead>
		<tbody>
<?php foreach ($liste as $o): ?>
			<tr>
				<td><img class="vignette" src="<?= e(couverture($o['image'])) ?>" alt=""></td>
				<td><a href="<?= e(admin_url('ouvrages/modifier/' . $o['id'])) ?>"><strong><?= e($o['titre']) ?></strong></a>
					<?= $o['publie'] ? '' : ' <span class="badge orange">Non publié</span>' ?><?= $o['coup_de_coeur'] ? ' <span class="badge bleu">Coup de cœur</span>' : '' ?>
					<br><small><?= e($o['auteurs_txt']) ?></small></td>
				<td><?= e($o['domaine_nom']) ?></td>
				<td><?= e($o['type_nom'] ?? '—') ?></td>
				<td><code><?= e($o['cote']) ?></code></td>
				<td class="num"><?php if ($o['consultation_sur_place']): ?><span class="badge bleu">Sur place</span> <?php endif; ?>
					<span class="badge <?= $o['nb_dispo'] ? 'vert' : ($o['nb_ex'] ? 'orange' : '') ?>"><?= $o['nb_dispo'] ?> / <?= $o['nb_ex'] ?> dispo.</span></td>
				<td class="actions"><?php if ($o['nb_dispo'] && !$o['consultation_sur_place']): ?><a class="btn petit" href="<?= e(admin_url('guichet', ['ouvrage' => $o['id']])) ?>" title="Prêter cet ouvrage"><i class="fa-solid fa-right-left"></i> Prêter</a> <?php endif; ?><a class="btn secondaire petit" href="<?= e($o['url']) ?>" target="_blank" rel="noopener" title="Voir sur le site"><i class="fa-solid fa-eye"></i></a></td>
			</tr>
<?php endforeach; ?>
		</tbody>
	</table></div>
<?php endif; ?>
</div>
<?= liens_pagination($pg) ?>
