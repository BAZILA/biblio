<?php /** Page d'erreur (404…). @var string $message */ ?>
<div class="biblio-erreur">
	<h1>Page introuvable</h1>
	<p><?= e($message ?? 'La page demandée n’existe pas ou a été déplacée.') ?></p>
	<?= formulaire_recherche() ?>
	<p><a class="btn btn-primary" href="<?= e(url()) ?>">Retour à l’accueil</a> &nbsp; <a href="<?= e(url('catalogue')) ?>">Parcourir le catalogue</a></p>
</div>
