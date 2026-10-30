<?php /** @var array $m */ ?>
<div class="fil"><a href="<?= e(admin_url('messages')) ?>">Messages</a> ›</div>
<div class="entete">
	<h1><?= e($m['sujet']) ?></h1>
	<div class="actions">
		<a class="btn" href="mailto:<?= e($m['email']) ?>?subject=<?= rawurlencode('Re : ' . $m['sujet']) ?>"><i class="fa-solid fa-envelope"></i> Répondre par e-mail</a>
		<?= bouton_action(admin_url('messages/non-lu/' . $m['id']), 'Marquer non lu', 'btn secondaire') ?>
		<?= peut('messages.supprimer') ? bouton_action(admin_url('messages/supprimer/' . $m['id']), '<i class="fa-solid fa-trash"></i> Supprimer', 'btn danger', 'Supprimer ce message ?') : '' ?>
	</div>
</div>
<div class="carte" style="max-width:820px">
	<p class="doux">De <strong><?= e($m['nom']) ?></strong> &lt;<?= e($m['email']) ?>&gt; — <?= e(date_fr($m['recu_le'], true)) ?></p>
	<div style="white-space:pre-wrap"><?= e($m['message']) ?></div>
</div>
