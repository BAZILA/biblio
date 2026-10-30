<?php /** @var array $m */ ?>
<div class="fil"><a href="<?= e(admin_url('mails')) ?>">E-mails envoyés</a> ›</div>
<div class="entete"><h1><?= e($m['sujet']) ?></h1></div>
<div class="carte" style="max-width:760px">
	<p class="doux">À <strong><?= e($m['destinataire']) ?></strong> — <?= e(date_fr($m['cree_le'], true)) ?>
		<?= $m['erreur'] !== '' ? '<br><span style="color:var(--rouge)">Erreur : ' . e($m['erreur']) . '</span>' : '' ?></p>
	<div style="white-space:pre-wrap"><?= e($m['corps']) ?></div>
</div>
