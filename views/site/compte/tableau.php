<?php
/**
 * Tableau de bord du lecteur : prêts en cours, réservations, historique, mot de passe.
 * @var array $u, $prets, $reservations, $historique
 */
$quota = quota_prets($u);
$maxProl = reglage_int('pret_max_prolongations', 1);
?>
<div class="biblio-compte">
	<div class="biblio-compte-entete">
		<div>
			<h1>Bonjour <?= e($u['prenom'] ?: $u['nom']) ?></h1>
			<p class="biblio-aide"><?= e(LIBELLES_CATEGORIE[$u['categorie']] ?? 'Lecteur') ?><?= $u['matricule'] ? ' · Matricule ' . e($u['matricule']) : '' ?> · <?= count($prets) ?> prêt(s) en cours sur <?= $quota ?> autorisé(s)</p>
		</div>
		<form method="post" action="<?= e(url('compte/deconnexion')) ?>"><?= csrf_field() ?><button class="btn btn-default" type="submit"><i class="fa fa-sign-out" aria-hidden="true"></i> Se déconnecter</button></form>
	</div>

	<section class="biblio-compte-section">
		<h2><i class="fa fa-book" aria-hidden="true"></i> Mes prêts en cours</h2>
<?php if (!$prets): ?>
		<p class="biblio-vide">Aucun prêt en cours. <a href="<?= e(url('catalogue')) ?>">Parcourir le catalogue</a></p>
<?php else: ?>
		<div class="biblio-table-wrap"><table class="biblio-table">
			<thead><tr><th>Ouvrage</th><th>Emprunté le</th><th>À rendre le</th><th></th></tr></thead>
			<tbody>
<?php foreach ($prets as $p): $retard = (int) $p['jours_retard']; ?>
				<tr<?= $retard > 0 ? ' class="biblio-retard"' : '' ?>>
					<td><a href="<?= e(ouvrage_url($p)) ?>"><?= e($p['titre']) ?></a><br><small>Exemplaire <?= e($p['exemplaire_code']) ?></small></td>
					<td><?= e(date_fr($p['date_pret'])) ?></td>
					<td><strong><?= e(date_fr($p['date_retour_prevue'])) ?></strong>
						<?php if ($retard > 0): ?><br><span class="biblio-badge biblio-badge-rouge">En retard de <?= e(pluriel($retard, 'jour')) ?></span>
						<?php elseif ($retard >= -2): ?><br><span class="biblio-badge biblio-badge-orange">Bientôt</span><?php endif; ?>
					</td>
					<td class="biblio-actions">
						<?php if ($retard <= 0 && (int) $p['prolongations'] < $maxProl): ?>
						<form method="post" action="<?= e(url('compte/prolonger/' . $p['id'])) ?>"><?= csrf_field() ?><button class="btn btn-primary btn-sm" type="submit">Prolonger</button></form>
						<?php elseif ((int) $p['prolongations'] >= $maxProl): ?><small>Déjà prolongé</small><?php endif; ?>
					</td>
				</tr>
<?php endforeach; ?>
			</tbody>
		</table></div>
<?php endif; ?>
	</section>

	<section class="biblio-compte-section">
		<h2><i class="fa fa-bookmark" aria-hidden="true"></i> Mes réservations</h2>
<?php if (!$reservations): ?>
		<p class="biblio-vide">Aucune réservation en cours. Réservez un ouvrage depuis sa fiche.</p>
<?php else: ?>
		<div class="biblio-table-wrap"><table class="biblio-table">
			<thead><tr><th>Ouvrage</th><th>Demandée le</th><th>État</th><th></th></tr></thead>
			<tbody>
<?php foreach ($reservations as $r): ?>
				<tr>
					<td><a href="<?= e(ouvrage_url($r)) ?>"><?= e($r['titre']) ?></a></td>
					<td><?= e(date_fr($r['demandee_le'])) ?></td>
					<td>
						<?php if ($r['statut'] === 'disponible'): ?>
							<span class="biblio-badge biblio-badge-vert">À retirer avant le <?= e(date_fr($r['expire_le'])) ?></span>
						<?php else: ?>
							<span class="biblio-badge">En attente — rang <?= (int) $r['rang'] ?></span>
						<?php endif; ?>
					</td>
					<td class="biblio-actions"><form method="post" action="<?= e(url('compte/annuler/' . $r['id'])) ?>" onsubmit="return confirm('Annuler cette réservation ?');"><?= csrf_field() ?><button class="btn btn-default btn-sm" type="submit">Annuler</button></form></td>
				</tr>
<?php endforeach; ?>
			</tbody>
		</table></div>
<?php endif; ?>
	</section>

	<section class="biblio-compte-section">
		<h2><i class="fa fa-history" aria-hidden="true"></i> Historique</h2>
<?php if (!$historique): ?>
		<p class="biblio-vide">Aucun prêt terminé pour l’instant.</p>
<?php else: ?>
		<div class="biblio-table-wrap"><table class="biblio-table">
			<thead><tr><th>Ouvrage</th><th>Emprunté le</th><th>Rendu le</th></tr></thead>
			<tbody>
<?php foreach ($historique as $p): ?>
				<tr><td><a href="<?= e(ouvrage_url($p)) ?>"><?= e($p['titre']) ?></a></td><td><?= e(date_fr($p['date_pret'])) ?></td><td><?= e(date_fr($p['date_retour'])) ?></td></tr>
<?php endforeach; ?>
			</tbody>
		</table></div>
<?php endif; ?>
	</section>

	<section class="biblio-compte-section">
		<h2><i class="fa fa-lock" aria-hidden="true"></i> Changer mon mot de passe</h2>
		<form method="post" action="<?= e(url('compte/mot-de-passe')) ?>" class="biblio-form-ligne">
			<?= csrf_field() ?>
			<input type="password" name="actuel" placeholder="Mot de passe actuel" class="form-control" required autocomplete="current-password">
			<input type="password" name="nouveau" placeholder="Nouveau (8 caractères min.)" class="form-control" required minlength="8" autocomplete="new-password">
			<button type="submit" class="btn btn-primary">Enregistrer</button>
		</form>
	</section>
</div>
