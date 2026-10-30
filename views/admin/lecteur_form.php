<?php /** @var array $l @var array $erreurs @var bool $accesEnLigne */ ?>
<div class="fil"><a href="<?= e(admin_url('lecteurs')) ?>">Lecteurs</a> ›</div>
<div class="entete"><h1><?= $l['id'] ? e(nom_complet($l)) : 'Nouveau lecteur' ?></h1></div>

<form method="post" class="carte" style="max-width:780px" autocomplete="off">
	<?= csrf_field() ?>
	<?= render(ROOT_DIR . '/views/admin/_champs_lecteur.php', ['l' => $l, 'erreurs' => $erreurs]) ?>

	<h2 style="margin-top:8px">Accès en ligne</h2>
	<?= champ('acces_en_ligne', 'Ouvrir l’espace lecteur (réservations et suivi des prêts en ligne)', $accesEnLigne, $erreurs, ['type' => 'case']) ?>
	<p class="aide" style="margin-top:-8px">
		<?php if ($l['id'] && $l['mot_de_passe'] !== ''): ?>
			Le lecteur a déjà un mot de passe. Pour lui en donner un nouveau, utilisez « Réinitialiser le mot de passe » sur sa fiche.
		<?php else: ?>
			Un mot de passe provisoire sera créé, affiché sur la fiche du lecteur et envoyé à son adresse e-mail. Sans accès en ligne, le lecteur emprunte normalement au guichet.
		<?php endif; ?>
	</p>
	<?= champ('actif', 'Compte actif (peut emprunter)', $l['actif'], $erreurs, ['type' => 'case']) ?>
	<div class="form-actions">
		<button class="btn" type="submit"><i class="fa-solid fa-circle-check"></i> Enregistrer</button>
		<a class="btn secondaire" href="<?= e($l['id'] ? admin_url('lecteurs/voir/' . $l['id']) : admin_url('lecteurs')) ?>">Annuler</a>
	</div>
</form>
