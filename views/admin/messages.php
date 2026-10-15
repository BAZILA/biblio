<?php /** @var array $liste, $pg */ ?>
<div class="entete"><h1>Messages</h1></div>
<p class="doux">Messages envoyés depuis la page « Contact » du site.</p>
<div class="carte" style="padding:0">
<?php if (!$liste): ?>
	<div class="vide">Aucun message reçu.</div>
<?php else: ?>
	<div class="tableau-wrap"><table class="tableau">
		<thead><tr><th>Sujet</th><th>De</th><th>Reçu le</th><th></th></tr></thead>
		<tbody>
<?php foreach ($liste as $m): ?>
			<tr>
				<td><a href="<?= e(admin_url('messages/voir/' . $m['id'])) ?>"><?= $m['lu'] ? e($m['sujet']) : '<strong>' . e($m['sujet']) . '</strong> <span class="badge bleu">Nouveau</span>' ?></a>
					<br><small><?= e(tronquer($m['message'], 100, '…')) ?></small></td>
				<td><?= e($m['nom']) ?><br><small><?= e($m['email']) ?></small></td>
				<td><?= e(date_fr($m['recu_le'], true)) ?></td>
				<td class="actions"><?= bouton_action(admin_url('messages/supprimer/' . $m['id']), '<i class="fa-solid fa-trash"></i>', 'btn danger petit', 'Supprimer ce message ?') ?></td>
			</tr>
<?php endforeach; ?>
		</tbody>
	</table></div>
<?php endif; ?>
</div>
<?= liens_pagination($pg) ?>
