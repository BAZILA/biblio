<?php /** @var array $l @var array $erreurs */ ?>
<div class="fil"><a href="<?= e(admin_url('lecteurs')) ?>">Lecteurs</a> ›</div>
<div class="entete"><h1><?= $l['id'] ? e(nom_complet($l)) : 'Nouveau lecteur' ?></h1></div>

<form method="post" class="carte" style="max-width:760px" autocomplete="off">
	<?= csrf_field() ?>
	<div class="ligne">
		<?= champ('prenom', 'Prénom', $l['prenom'], $erreurs) ?>
		<?= champ('nom', 'Nom', $l['nom'], $erreurs, ['required' => true]) ?>
	</div>
	<div class="ligne">
		<?= champ('email', 'E-mail', $l['email'], $erreurs, ['type' => 'email', 'required' => true, 'aide' => 'Sert d’identifiant pour l’espace lecteur.']) ?>
		<?= champ('telephone', 'Téléphone', $l['telephone'], $erreurs, ['type' => 'tel']) ?>
	</div>
	<div class="ligne">
		<?= champ('categorie', 'Catégorie', $l['categorie'], $erreurs, ['type' => 'select', 'options' => LIBELLES_CATEGORIE,
			'aide' => 'Détermine le nombre de prêts simultanés (voir Réglages).']) ?>
		<?= champ('matricule', 'Matricule / n° de carte', $l['matricule'] ?? '', $erreurs) ?>
	</div>
	<?= champ('mot_de_passe', $l['id'] ? 'Nouveau mot de passe' : 'Mot de passe', '', $erreurs, ['type' => 'password', 'autocomplete' => 'new-password',
		'aide' => $l['id'] ? 'Laisser vide pour ne pas le changer. À communiquer au lecteur s’il l’a oublié.' : 'Facultatif : sans mot de passe, le lecteur peut emprunter au guichet mais pas se connecter en ligne.']) ?>
	<?= champ('actif', 'Compte actif (peut emprunter et se connecter)', $l['actif'], $erreurs, ['type' => 'case']) ?>
	<div class="form-actions">
		<button class="btn" type="submit"><i class="fa-solid fa-circle-check"></i> Enregistrer</button>
		<a class="btn secondaire" href="<?= e($l['id'] ? admin_url('lecteurs/voir/' . $l['id']) : admin_url('lecteurs')) ?>">Annuler</a>
	</div>
</form>
