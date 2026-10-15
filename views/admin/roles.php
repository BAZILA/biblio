<?php /** @var array $roles @var int $total */ ?>
<div class="entete">
	<h1>Rôles et permissions</h1>
	<div class="actions">
		<?php if (peut('personnel.voir')): ?><a class="btn secondaire" href="<?= e(admin_url('personnel')) ?>"><i class="fa-solid fa-user-shield"></i> Comptes du personnel</a><?php endif; ?>
		<?php if (peut('roles.ajouter')): ?><a class="btn" href="<?= e(admin_url('roles/nouveau')) ?>"><i class="fa-solid fa-plus"></i> Nouveau rôle</a><?php endif; ?>
	</div>
</div>
<p class="doux">Un rôle regroupe des permissions : pour chaque rubrique de l’administration, qui peut la voir, ajouter, modifier ou supprimer, et les actions particulières (prêter, valider un lecteur…). Chaque compte du personnel reçoit un rôle.</p>

<div class="cartes-roles">
<?php foreach ($roles as $r): ?>
	<section class="carte carte-role<?= $r['systeme'] ? ' systeme' : ($r['tous_droits'] ? ' admin' : '') ?>">
		<div class="carte-titre">
			<h2><i class="fa-solid <?= $r['systeme'] ? 'fa-microchip' : ($r['tous_droits'] ? 'fa-crown' : 'fa-user-tag') ?>"></i> <?= e($r['nom']) ?></h2><?= $r['systeme'] ? '<span class="badge">Invisible des autres comptes</span>' : '' ?>
		</div>
		<p class="doux"><?= e($r['description'] ?: 'Pas de description.') ?></p>
		<div class="jauge" title="<?= $r['tous_droits'] ? $total : (int) $r['nb_permissions'] ?> / <?= $total ?>"><span style="width:<?= $r['tous_droits'] ? 100 : round(100 * $r['nb_permissions'] / max(1, $total)) ?>%"></span></div>
		<p class="chiffres-role">
			<span><strong><?= $r['tous_droits'] ? 'Tous' : (int) $r['nb_permissions'] ?></strong> <?= $r['tous_droits'] ? 'les droits' : 'permission(s) sur ' . $total ?></span>
			<span><strong><?= (int) $r['nb_comptes'] ?></strong> compte(s)</span>
		</p>
		<div class="form-actions">
			<a class="btn secondaire petit" href="<?= e(admin_url('roles/modifier/' . $r['id'])) ?>"><?= !$r['tous_droits'] && peut('roles.modifier') ? '<i class="fa-solid fa-sliders"></i> Modifier les droits' : '<i class="fa-solid fa-eye"></i> Voir les droits' ?></a>
			<?php if (peut('roles.ajouter')): ?><?= bouton_action(admin_url('roles/dupliquer/' . $r['id']), '<i class="fa-solid fa-copy"></i> Dupliquer', 'btn secondaire petit') ?><?php endif; ?>
		</div>
	</section>
<?php endforeach; ?>
</div>
