<?php /** @var array $liste, $pg */
$motifs = ['pret' => 'Prêt', 'rappel' => 'Rappel', 'retard' => 'Retard', 'reservation' => 'Réservation', 'compte' => 'Compte', 'test' => 'Test'];
$statuts = ['envoye' => ['vert', 'Envoyé'], 'echec' => ['rouge', 'Échec'], 'journal' => ['', 'Non envoyé (journal)']];
$actif = reglage('mail_mode') === 'smtp';
?>
<div class="entete">
	<h1>E-mails envoyés</h1>
	<div class="actions">
		<?= bouton_action(admin_url('mails/taches'), '<i class="fa-solid fa-paper-plane"></i> Envoyer les rappels maintenant', 'btn secondaire',
			'Envoyer maintenant les rappels d’échéance et les relances de retard dus ?') ?>
		<a class="btn secondaire" href="<?= e(admin_url('reglages')) ?>"><i class="fa-solid fa-sliders"></i> Réglages des e-mails</a>
	</div>
</div>

<div class="grille grille-form">
	<div class="carte" style="padding:0">
<?php if (!$liste): ?>
		<div class="vide">Aucun e-mail pour l’instant.</div>
<?php else: ?>
		<div class="tableau-wrap"><table class="tableau">
			<thead><tr><th>Date</th><th>Destinataire</th><th>Objet</th><th>Type</th><th>État</th></tr></thead>
			<tbody>
<?php foreach ($liste as $m): $s = $statuts[$m['statut']]; ?>
				<tr>
					<td><?= e(date_fr($m['cree_le'], true)) ?></td>
					<td><?= e($m['destinataire']) ?></td>
					<td><a href="<?= e(admin_url('mails/voir/' . $m['id'])) ?>"><?= e($m['sujet']) ?></a></td>
					<td><?= e($motifs[$m['motif']] ?? $m['motif']) ?></td>
					<td><span class="badge <?= $s[0] ?>" title="<?= e($m['erreur']) ?>"><?= e($s[1]) ?></span></td>
				</tr>
<?php endforeach; ?>
			</tbody>
		</table></div>
<?php endif; ?>
	</div>
	<aside>
		<section class="carte">
			<h2>État de l’envoi</h2>
			<p><?= $actif ? '<span class="badge vert">Activé</span> par ' . e(reglage('smtp_hote')) : '<span class="badge orange">Désactivé</span> : les messages sont seulement consignés ici.' ?></p>
			<p class="doux">Les rappels et relances partent chaque jour, au premier passage dans l’administration. Pour un envoi à heure fixe, même sans connexion, planifiez la commande <code>php app/taches.php</code> (voir le README).</p>
		</section>
		<form class="carte" method="post" action="<?= e(admin_url('mails/test')) ?>">
			<?= csrf_field() ?>
			<h2>Envoyer un test</h2>
			<?= champ('destinataire', 'Adresse', user()['email'] ?? '', [], ['type' => 'email', 'required' => true]) ?>
			<button class="btn secondaire" type="submit"><i class="fa-solid fa-paper-plane"></i> Envoyer</button>
		</form>
	</aside>
</div>
<?= liens_pagination($pg) ?>
