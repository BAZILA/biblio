<?php /** @var string $filtre @var array $liste */
$badges = ['en_attente' => '', 'disponible' => 'vert', 'honoree' => 'bleu', 'annulee' => '', 'expiree' => 'orange'];
?>
<div class="entete"><h1>Réservations</h1></div>

<nav class="onglets">
	<a href="<?= e(admin_url('reservations')) ?>" class="<?= $filtre !== 'historique' ? 'actif' : '' ?>">En cours</a>
	<a href="<?= e(admin_url('reservations', ['filtre' => 'historique'])) ?>" class="<?= $filtre === 'historique' ? 'actif' : '' ?>">Historique</a>
</nav>

<?php if ($filtre !== 'historique'): ?>
<p class="doux">Quand un exemplaire est libre, il est mis de côté pour le premier lecteur de la file (statut « À retirer »). Il est conservé <?= reglage_int('reservation_delai_jours', 3) ?> jours, puis la réservation expire et l’exemplaire passe au lecteur suivant. Pour remettre l’ouvrage, cliquez sur « Remettre » : le guichet s’ouvre pré-rempli.</p>
<?php endif; ?>

<div class="carte" style="padding:0">
<?php if (!$liste): ?>
	<div class="vide">Aucune réservation.</div>
<?php else: ?>
	<div class="tableau-wrap"><table class="tableau">
		<thead><tr><th>Lecteur</th><th>Ouvrage</th><th>Demandée le</th><th>État</th><th>Exemplaire</th><th></th></tr></thead>
		<tbody>
<?php foreach ($liste as $r): ?>
			<tr>
				<td><a href="<?= e(admin_url('lecteurs/voir/' . $r['lecteur_id'])) ?>"><?= e(trim($r['prenom'] . ' ' . $r['nom'])) ?></a><?= $r['matricule'] ? '<br><small>' . e($r['matricule']) . '</small>' : '' ?></td>
				<td><a href="<?= e(admin_url('ouvrages/modifier/' . $r['ouvrage_id'])) ?>"><?= e($r['titre']) ?></a></td>
				<td><?= e(date_fr($r['demandee_le'], true)) ?></td>
				<td><span class="badge <?= $badges[$r['statut']] ?? '' ?>"><?= e(LIBELLES_RESERVATION[$r['statut']] ?? $r['statut']) ?></span>
					<?php if ($r['statut'] === 'disponible'): ?><br><small>jusqu’au <?= e(date_fr($r['expire_le'])) ?></small><?php elseif ($r['statut'] === 'en_attente'): ?><br><small>rang <?= (int) $r['rang'] ?></small><?php endif; ?></td>
				<td><?= $r['exemplaire_code'] ? '<code>' . e($r['exemplaire_code']) . '</code>' : '—' ?></td>
				<td class="actions">
<?php if ($r['statut'] === 'disponible'): ?>
					<a class="btn petit" href="<?= e(admin_url('guichet', ['lecteur' => $r['lecteur_id'], 'code' => $r['exemplaire_code']])) ?>">Remettre</a>
<?php endif; ?>
<?php if (in_array($r['statut'], ['disponible', 'en_attente'], true)): ?>
					<?= bouton_action(admin_url('reservations/annuler/' . $r['id']), 'Annuler', 'btn danger petit', 'Annuler cette réservation ?') ?>
<?php endif; ?>
				</td>
			</tr>
<?php endforeach; ?>
		</tbody>
	</table></div>
<?php endif; ?>
</div>
