<?php /** @var array $p, $erreurs */ ?>
<div class="fil"><a href="<?= e(admin_url('personnel')) ?>">Comptes du personnel</a> ›</div>
<div class="entete"><h1><?= $p['id'] ? e(nom_complet($p)) : 'Nouveau compte' ?></h1></div>
<form method="post" class="carte" style="max-width:640px" autocomplete="off">
	<?= csrf_field() ?>
	<div class="ligne">
		<?= champ('prenom', 'Prénom', $p['prenom'], $erreurs) ?>
		<?= champ('nom', 'Nom', $p['nom'], $erreurs, ['required' => true]) ?>
	</div>
	<?= champ('email', 'E-mail de connexion', $p['email'], $erreurs, ['type' => 'email', 'required' => true]) ?>
	<?= champ('role', 'Rôle', $p['role'], $erreurs, ['type' => 'select', 'options' => LIBELLES_ROLE]) ?>
	<?= champ('mot_de_passe', $p['id'] ? 'Nouveau mot de passe' : 'Mot de passe initial', '', $erreurs, ['type' => 'password', 'autocomplete' => 'new-password',
		'aide' => ($p['id'] ? 'Laisser vide pour le conserver. ' : '') . '10 caractères minimum ; la personne pourra le changer dans « Mon compte ».']) ?>
	<?= champ('actif', 'Compte actif', $p['actif'], $erreurs, ['type' => 'case']) ?>
	<div class="form-actions">
		<button class="btn" type="submit"><i class="fa-solid fa-circle-check"></i> Enregistrer</button>
		<a class="btn secondaire" href="<?= e(admin_url('personnel')) ?>">Annuler</a>
	</div>
</form>
