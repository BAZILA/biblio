<?php /** @var array $p, $erreurs, $roles, $sites @var bool $estMoi @var array|null $roleActuel */ ?>
<div class="fil"><a href="<?= e(admin_url('personnel')) ?>">Comptes du personnel</a> ›</div>
<div class="entete"><h1><?= $p['id'] ? e(nom_complet($p)) : 'Nouveau compte' ?></h1></div>
<form method="post" class="carte" style="max-width:680px" autocomplete="off">
	<?= csrf_field() ?>
	<div class="ligne">
		<?= champ('prenom', 'Prénom', $p['prenom'], $erreurs) ?>
		<?= champ('nom', 'Nom', $p['nom'], $erreurs, ['required' => true]) ?>
	</div>
	<?= champ('email', 'E-mail de connexion', $p['email'], $erreurs, ['type' => 'email', 'required' => true]) ?>
<?php if ($estMoi): ?>
	<div class="champ">
		<label>Rôle</label>
		<p><span class="badge bleu"><?= e($roleActuel['nom'] ?? 'Aucun') ?></span> <small class="doux">Vous ne pouvez pas changer votre propre rôle ni désactiver votre compte.</small></p>
	</div>
<?php else: ?>
	<div class="champ<?= isset($erreurs['role_id']) ? ' en-erreur' : '' ?>">
		<label for="f_role_id">Rôle <span class="requis">*</span></label>
		<select id="f_role_id" name="role_id" required>
			<option value="">— Choisir un rôle —</option>
<?php foreach ($roles as $r): ?>
			<option value="<?= (int) $r['id'] ?>"<?= (int) $p['role_id'] === (int) $r['id'] ? ' selected' : '' ?>><?= e($r['nom']) ?><?= $r['description'] !== '' ? ' — ' . e(tronquer($r['description'], 70, '…')) : '' ?></option>
<?php endforeach; ?>
		</select>
		<div class="aide">Seuls les rôles dont vous possédez vous-même tous les droits sont proposés.<?php if (peut('roles.voir')): ?> <a href="<?= e(admin_url('roles')) ?>" target="_blank" rel="noopener">Voir le détail des rôles ↗</a><?php endif; ?></div>
		<?php if (isset($erreurs['role_id'])): ?><div class="erreur"><?= e($erreurs['role_id']) ?></div><?php endif; ?>
	</div>
<?php endif; ?>
<?php if (!$estMoi && $sites): ?>
	<?= champ('site_id', 'Centre documentaire', $p['site_id'] ?? '', $erreurs, ['type' => 'select', 'options' => ['' => '— Tous les centres —'] + $sites,
		'aide' => 'La personne ne voit que les ouvrages, prêts, réservations et rapports de ce centre. « Tous les centres » = bibliothèque centrale.']) ?>
<?php elseif (!$estMoi && ($p['site_id'] ?? 0)): ?>
	<div class="champ"><label>Centre documentaire</label><p><span class="badge bleu"><?= e(nom_site((int) $p['site_id'], true)) ?></span></p></div>
<?php endif; ?>
	<?= champ('mot_de_passe', $p['id'] ? 'Nouveau mot de passe' : 'Mot de passe initial', '', $erreurs, ['type' => 'password', 'autocomplete' => 'new-password',
		'aide' => ($p['id'] ? 'Laisser vide pour le conserver. ' : '') . '10 caractères minimum ; la personne pourra le changer dans « Mon compte ».']) ?>
<?php if (!$estMoi && peut('personnel.desactiver')): ?>
	<?= champ('actif', 'Compte actif', $p['actif'], $erreurs, ['type' => 'case']) ?>
<?php endif; ?>
	<div class="form-actions">
		<button class="btn" type="submit"><i class="fa-solid fa-circle-check"></i> Enregistrer</button>
		<a class="btn secondaire" href="<?= e(admin_url('personnel')) ?>">Annuler</a>
	</div>
</form>

<?php if ($p['id'] && !$estMoi && is_systeme()): ?>
<section class="carte zone-sensible" style="max-width:760px;margin-top:20px">
	<h2><i class="fa-solid fa-trash"></i> Supprimer ce compte</h2>
	<p class="doux">Réservé au compte Système. La personne ne pourra plus se connecter ; les prêts, retours, décisions et actualités qu’elle a enregistrés sont conservés (sans son nom),
		et le journal d’audit garde la trace de toutes ses actions. Pour une absence temporaire, préférez « Compte actif » décoché.</p>
	<?= bouton_action(admin_url('personnel/supprimer/' . $p['id']), '<i class="fa-solid fa-trash"></i> Supprimer définitivement', 'btn danger',
		'Supprimer définitivement le compte de ' . nom_complet($p) . ' (' . $p['email'] . ') ?') ?>
</section>
<?php endif; ?>
