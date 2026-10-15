<?php /** @var array $liste */ ?>
<div class="entete">
	<h1>Comptes du personnel</h1>
	<div class="actions"><a class="btn" href="<?= e(admin_url('personnel/nouveau')) ?>"><i class="fa-solid fa-user-plus"></i> Nouveau compte</a></div>
</div>
<p class="doux">Les <strong>bibliothécaires</strong> gèrent le catalogue, les lecteurs, les prêts et les textes du site. Les <strong>administrateurs</strong> peuvent en plus gérer ces comptes.</p>
<div class="carte" style="padding:0">
	<div class="tableau-wrap"><table class="tableau">
		<thead><tr><th>Nom</th><th>E-mail</th><th>Rôle</th><th>Dernière connexion</th><th>État</th><th></th></tr></thead>
		<tbody>
<?php foreach ($liste as $p): ?>
			<tr>
				<td><strong><?= e(nom_complet($p)) ?></strong></td>
				<td><?= e($p['email']) ?></td>
				<td><span class="badge <?= $p['role'] === 'admin' ? 'bleu' : '' ?>"><?= e(LIBELLES_ROLE[$p['role']]) ?></span></td>
				<td><?= $p['derniere_connexion'] ? e(date_fr($p['derniere_connexion'], true)) : '—' ?></td>
				<td><?= $p['actif'] ? '<span class="badge vert">Actif</span>' : '<span class="badge">Désactivé</span>' ?></td>
				<td class="actions"><a class="btn secondaire petit" href="<?= e(admin_url('personnel/modifier/' . $p['id'])) ?>"><i class="fa-solid fa-pen"></i> Modifier</a></td>
			</tr>
<?php endforeach; ?>
		</tbody>
	</table></div>
</div>
