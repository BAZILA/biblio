<?php /** @var array $moi @var array $erreurs */ ?>
<div class="entete"><h1>Mon compte</h1></div>
<form method="post" class="carte" style="max-width:640px">
	<?= csrf_field() ?>
	<div class="ligne">
		<?= champ('prenom', 'Prénom', $moi['prenom'], $erreurs) ?>
		<?= champ('nom', 'Nom', $moi['nom'], $erreurs, ['required' => true]) ?>
	</div>
	<?= champ('email', 'E-mail de connexion', $moi['email'], $erreurs, ['type' => 'email', 'required' => true]) ?>
	<?= champ('nouveau', 'Nouveau mot de passe', '', $erreurs, ['type' => 'password', 'aide' => 'Laisser vide pour le conserver. 10 caractères minimum.', 'autocomplete' => 'new-password']) ?>
	<hr style="border:0;border-top:1px solid var(--bord);margin:6px 0 16px">
	<?= champ('actuel', 'Mot de passe actuel (pour confirmer)', '', $erreurs, ['type' => 'password', 'required' => true, 'autocomplete' => 'current-password']) ?>
	<div class="form-actions"><button class="btn" type="submit"><i class="fa-solid fa-circle-check"></i> Enregistrer</button></div>
</form>
