<?php /** @var array $l, $enCours, $reservations, $historique @var string|null $mdpProvisoire */ ?>
<div class="fil"><a href="<?= e(admin_url('lecteurs')) ?>">Lecteurs</a> ›</div>
<div class="entete">
	<h1><?= e(nom_complet($l)) ?></h1>
	<?= $l['actif'] ? '<span class="badge vert">Actif</span>' : '<span class="badge orange">' . ($l['en_attente'] ? 'À valider' : 'Désactivé') . '</span>' ?>
	<div class="actions">
		<?php if (peut('guichet.voir')): ?><a class="btn" href="<?= e(admin_url('guichet', ['lecteur' => $l['id']])) ?>"><i class="fa-solid fa-right-left"></i> Prêt / retour</a><?php endif; ?>
		<?php if (peut('lecteurs.modifier')): ?><a class="btn secondaire" href="<?= e(admin_url('lecteurs/modifier/' . $l['id'])) ?>"><i class="fa-solid fa-pen"></i> Modifier</a><?php endif; ?>
		<?= !peut('lecteurs.valider') ? '' : ($l['actif']
			? bouton_action(admin_url('lecteurs/desactiver/' . $l['id']), 'Désactiver', 'btn danger', 'Désactiver ce compte ? Le lecteur ne pourra plus emprunter ni se connecter.')
			: bouton_action(admin_url('lecteurs/activer/' . $l['id']), '<i class="fa-solid fa-circle-check"></i> Activer', 'btn vert')) ?>
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
							<?= bouton_prolonger($p) ?>
							<?= bouton_retour($p) ?>
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
						<?php if ($r['statut'] === 'disponible'): ?><?= bouton_remettre($r) ?><?php endif; ?>
						<?= peut('reservations.annuler') ? bouton_action(admin_url('reservations/annuler/' . $r['id']), 'Annuler', 'btn danger petit', 'Annuler cette réservation ?') : '' ?>
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
						<td><?= badge_constat($p) ?><?= $tard ? ' <span class="badge orange">Rendu en retard</span>' : '' ?></td></tr>
<?php endforeach; ?>
				</tbody>
			</table></div>
<?php endif; ?>
		</section>
	</div>

	<aside class="carte">
		<h2>Coordonnées</h2>
		<p><strong>Catégorie :</strong> <?= e(LIBELLES_CATEGORIE[$l['categorie']] ?? '—') ?><br>
<?php if ($l['categorie'] === 'etudiant'): ?>
		<strong>Faculté :</strong> <?= e($l['faculte'] ?: '—') ?><br>
		<strong>Parcours :</strong> <?= e($l['parcours'] ?: '—') ?><br>
<?php endif; ?>
		<strong><?= $l['categorie'] === 'etudiant' ? 'N° de suivi' : 'Matricule' ?> :</strong> <?= e($l['matricule'] ?? '—') ?><br>
		<strong>E-mail :</strong> <?= $l['email'] ? '<a href="mailto:' . e($l['email']) . '">' . e($l['email']) . '</a>' : '—' ?><br>
		<strong>Téléphone :</strong> <?= e($l['telephone'] ?: '—') ?></p>
		<p class="doux">Inscrit le <?= e(date_fr($l['cree_le'])) ?><br>
		Dernière connexion en ligne : <?= $l['derniere_connexion'] ? e(date_fr($l['derniere_connexion'], true)) : 'jamais' ?></p>

		<h2>Accès en ligne</h2>
<?php if ($mdpProvisoire): ?>
		<div class="flash info">Mot de passe provisoire : <code style="font-size:1rem"><?= e($mdpProvisoire) ?></code><br>
			<small>Affiché une seule fois. Communiquez-le au lecteur<?= $l['email'] && reglage('mail_mode') === 'smtp' ? ' (il vient aussi de lui être envoyé par e-mail)' : '' ?> ; il pourra le changer dans son espace.</small></div>
<?php endif; ?>
<?php if ($l['mot_de_passe'] !== ''): ?>
		<p class="doux">L’espace lecteur est ouvert.</p>
		<?php if (peut('lecteurs.mot_de_passe')): ?><?= bouton_action(admin_url('lecteurs/mot-de-passe/' . $l['id']), '<i class="fa-solid fa-key"></i> Réinitialiser le mot de passe', 'btn secondaire petit',
			'Créer un nouveau mot de passe provisoire pour ce lecteur ? L’ancien ne fonctionnera plus.') ?><?php endif; ?>
<?php else: ?>
		<p class="doux">Pas d’accès en ligne : le lecteur emprunte au guichet uniquement. Pour l’ouvrir, cochez « Ouvrir l’espace lecteur » dans <a href="<?= e(admin_url('lecteurs/modifier/' . $l['id'])) ?>">Modifier</a>.</p>
<?php endif; ?>
		<?php if (!$enCours && !$historique && peut('lecteurs.supprimer')): ?>
			<hr style="border:0;border-top:1px solid var(--bord);margin:16px 0">
			<?= bouton_action(admin_url('lecteurs/supprimer/' . $l['id']), '<i class="fa-solid fa-trash"></i> Supprimer ce lecteur', 'btn danger petit', 'Supprimer définitivement ce lecteur ?') ?>
		<?php endif; ?>
	</aside>
</div>
