<?php /** @var array $blocs */ ?>
<div class="entete"><h1>Textes du site</h1></div>
<p class="doux">Ces blocs de texte apparaissent sur la page d’accueil, dans le pied de page et sur la page contact. Les listes (domaines, types, nouveautés, chiffres clés…) se mettent à jour toutes seules à partir du catalogue.</p>
<div class="carte" style="padding:0">
	<div class="tableau-wrap"><table class="tableau">
		<thead><tr><th>Bloc</th><th>Aperçu</th><th>Modifié</th><th></th></tr></thead>
		<tbody>
<?php foreach ($blocs as $b): ?>
			<tr>
				<td><a href="<?= e(admin_url('blocs/modifier/' . $b['cle'])) ?>"><strong><?= e($b['titre']) ?></strong></a></td>
				<td class="doux"><?= e(tronquer($b['contenu'], 120, '…')) ?></td>
				<td><?= $b['modifie_le'] ? e(date_fr($b['modifie_le'], true)) : '—' ?></td>
				<td class="actions"><a class="btn secondaire petit" href="<?= e(admin_url('blocs/modifier/' . $b['cle'])) ?>"><i class="fa-solid fa-pen"></i> Modifier</a></td>
			</tr>
<?php endforeach; ?>
		</tbody>
	</table></div>
</div>
