<?php /** @var array $l, $enCours, $reservations, $historique */ ?>
<div class="fil"><a href="<?= e(admin_url('lecteurs')) ?>">Lecteurs</a> ›</div>
<div class="entete">
	<h1><?= e(nom_complet($l)) ?></h1>
	<?= $l['actif'] ? '<span class="badge vert">Actif</span>' : '<span class="badge orange">' . ($l['en_attente'] ? 'À valider' : 'Désactivé') . '</span>' ?>
	<div class="actions">
		<a class="btn" href="<?= e(admin_url('guichet', ['lecteur' => $l['id']])) ?>"><i class="fa-solid fa-right-left"></i> Prêt / retour</a>
		<a class="btn secondaire" href="<?= e(admin_url('lecteurs/modifier/' . $l['id'])) ?>"><i class="fa-solid fa-pen"></i> Modifier</a>
		<?= $l['actif']
			? bouton_action(admin_url('lecteurs/desactiver/' . $l['id']), 'Désactiver', 'btn danger', 'Désactiver ce compte ? Le lecteur ne pourra plus emprunter ni se connecter.')
			: bouton_action(admin_url('lecteurs/activer/' . $l['id']), '<i class="fa-solid fa-circle-check"></i> Activer', 'btn vert') ?>
	</div>
</div>

<div class="grille grille-form">
	<div>
		<section class="carte">
			<h2>Prêts en cours (<?= count($enCours) ?> / <?= quota_prets($l) ?>)</h2>
<?php if (!$enCours): ?>
			<p class="doux">Aucun prêt en cours.</p>
<?php else: ?>
			<div class="tableau-wrap"><table class="tableau">
				<thead><tr><th>Ouvrage</th><th>Exemplaire</th><th>Emprunté le</th><th>Échéance</th><th></th></tr></thead>
				<tbody>
<?php foreach ($enCours as $p): ?>
					<tr class="<?= $p['jours_retard'] > 0 ? 'retard' : '' ?>">
						<td><?= e($p['titre']) ?></td><td><code><?= e($p['exemplaire_code']) ?></code></td><td><?= e(date_fr($p['date_pret'])) ?></td>
						<td><?= e(date_fr($p['date_retour_prevue'])) ?><br><?= badge_echeance($p) ?></td>
						<td class="actions">
							<?= bouton_action(admin_url('prets/prolonger/' . $p['id']), 'Prolonger', 'btn secondaire petit', '', ['retour' => $_SERVER['REQUEST_URI']]) ?>
							<?= bouton_action(admin_url('prets/rendre/' . $p['id']), 'Retour', 'btn vert petit', '', ['retour' => $_SERVER['REQUEST_URI']]) ?>
						</td>
					</tr>
<?php endforeach; ?>
				</tbody>
			</table></div>
<?php endif; ?>
		</section>

		<section class="carte">
			<h2>Réservations</h2>
<?php if (!$reservations): ?>
			<p class="doux">Aucune réservation en cours.</p>
<?php else: ?>
			<div class="tableau-wrap"><table class="tableau"><tbody>
<?php foreach ($reservations as $r): ?>
				<tr><td><?= e($r['titre']) ?></td>
					<td><?= $r['statut'] === 'disponible' ? '<span class="badge vert">À retirer avant le ' . e(date_fr($r['expire_le'])) . '</span>' : '<span class="badge">En attente — rang ' . (int) $r['rang'] . '</span>' ?></td>
					<td class="actions">
						<?php if ($r['statut'] === 'disponible'): ?><a class="btn petit" href="<?= e(admin_url('guichet', ['lecteur' => $l['id'], 'code' => $r['exemplaire_code']])) ?>">Remettre</a><?php endif; ?>
						<?= bouton_action(admin_url('reservations/annuler/' . $r['id']), 'Annuler', 'btn danger petit', 'Annuler cette réservation ?') ?>
					</td></tr>
<?php endforeach; ?>
			</tbody></table></div>
<?php endif; ?>
		</section>

		<section class="carte">
			<h2>Historique</h2>
<?php if (!$historique): ?>
			<p class="doux">Aucun prêt terminé.</p>
<?php else: ?>
			<div class="tableau-wrap"><table class="tableau">
				<thead><tr><th>Ouvrage</th><th>Emprunté le</th><th>Rendu le</th><th></th></tr></thead>
				<tbody>
<?php foreach ($historique as $p): $tard = $p['date_retour'] > $p['date_retour_prevue']; ?>
					<tr><td><?= e($p['titre']) ?></td><td><?= e(date_fr($p['date_pret'])) ?></td><td><?= e(date_fr($p['date_retour'])) ?></td>
						<td><?= $tard ? '<span class="badge orange">Rendu en retard</span>' : '' ?></td></tr>
<?php endforeach; ?>
				</tbody>
			</table></div>
<?php endif; ?>
		</section>
	</div>

	<aside class="carte">
		<h2>Coordonnées</h2>
		<p><strong>Catégorie :</strong> <?= e(LIBELLES_CATEGORIE[$l['categorie']] ?? '—') ?><br>
		<strong>Matricule :</strong> <?= e($l['matricule'] ?? '—') ?><br>
		<strong>E-mail :</strong> <a href="mailto:<?= e($l['email']) ?>"><?= e($l['email']) ?></a><br>
		<strong>Téléphone :</strong> <?= e($l['telephone'] ?: '—') ?></p>
		<p class="doux">Inscrit le <?= e(date_fr($l['cree_le'])) ?><br>
		Dernière connexion en ligne : <?= $l['derniere_connexion'] ? e(date_fr($l['derniere_connexion'], true)) : 'jamais' ?><br>
		Espace en ligne : <?= $l['mot_de_passe'] !== '' ? 'mot de passe défini' : 'pas de mot de passe' ?></p>
		<?php if (!$enCours && !$historique): ?>
			<?= bouton_action(admin_url('lecteurs/supprimer/' . $l['id']), '<i class="fa-solid fa-trash"></i> Supprimer ce lecteur', 'btn danger petit', 'Supprimer définitivement ce lecteur ?') ?>
		<?php endif; ?>
	</aside>
</div>
