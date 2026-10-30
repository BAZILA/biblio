<?php /** Page de message simple (introuvable…). @var string $message */ ?>
<div class="carte vide">
	<p><?= e($message) ?></p>
	<p><a class="btn secondaire" href="<?= e(admin_url()) ?>">Retour au tableau de bord</a></p>
</div>
