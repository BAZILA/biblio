<?php /** @var array $liste, $attribuables */ ?>
<div class="entete">
	<h1>Comptes du personnel</h1>
	<div class="actions">
		<?php if (peut('roles.voir')): ?><a class="btn secondaire" href="<?= e(admin_url('roles')) ?>"><i class="fa-solid fa-user-lock"></i> Rôles et permissions</a><?php endif; ?>
		<?php if (peut('personnel.ajouter')): ?><a class="btn" href="<?= e(admin_url('personnel/nouveau')) ?>"><i class="fa-solid fa-user-plus"></i> Nouveau compte</a><?php endif; ?>
	</div>
</div>
<p class="doux">Chaque compte reçoit un <strong>rôle</strong> qui détermine ce qu’il voit et ce qu’il peut faire dans l’administration. Les rôles et leurs permissions se règlent dans « Rôles et permissions ».</p>
<div class="carte" style="padding:0">
	<div class="tableau-wrap"><table class="tableau">
		<thead><tr><th>Nom</th><th>E-mail</th><th>Rôle</th><th>Dernière connexion</th><th>État</th><th></th></tr></thead>
		<tbody>
<?php foreach ($liste as $p): $modifiable = peut('personnel.modifier') && ((int) $p['id'] === (int) user()['id'] || !$p['role_id'] || isset($attribuables[$p['role_id']])); ?>
			<tr>
				<td><strong><?= e(nom_complet($p)) ?></strong></td>
				<td><?= e($p['email']) ?></td>
				<td><?php if ($p['role_nom']): ?><span class="badge <?= $p['tous_droits'] ? 'bleu' : '' ?>"><?= $p['systeme'] ? '<i class="fa-solid fa-microchip"></i> ' : ($p['tous_droits'] ? '<i class="fa-solid fa-crown"></i> ' : '') ?><?= e($p['role_nom']) ?></span><?php else: ?><span class="badge orange">Aucun rôle</span><?php endif; ?></td>
				<td><?= $p['derniere_connexion'] ? e(date_fr($p['derniere_connexion'], true)) : '—' ?></td>
				<td><?= $p['actif'] ? '<span class="badge vert">Actif</span>' : '<span class="badge">Désactivé</span>' ?></td>
				<td class="actions"><?php if ($modifiable): ?><a class="btn secondaire petit" href="<?= e(admin_url('personnel/modifier/' . $p['id'])) ?>"><i class="fa-solid fa-pen"></i> Modifier</a><?php endif; ?></td>
			</tr>
<?php endforeach; ?>
		</tbody>
	</table></div>
</div>
