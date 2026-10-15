<?php
/** @var array|null $lecteur @var array $pretsLecteur, $reservationsLecteur, $derniers @var string $codePropose */
$duree = reglage_int('pret_duree_jours', 14);
?>
<div class="entete"><h1>Guichet prêt / retour</h1></div>

<div class="guichet">
	<section class="carte">
		<h2><i class="fa-solid fa-book-open"></i> Nouveau prêt</h2>
		<form method="post" action="<?= e(admin_url('guichet/pret')) ?>">
			<?= csrf_field() ?>
			<div class="champ">
				<label for="lecteur-recherche">Lecteur</label>
				<div class="choix-lecteur" data-url="<?= e(admin_url('guichet/chercher')) ?>" data-focus-ensuite="#code-pret">
					<input type="search" id="lecteur-recherche" placeholder="Nom, matricule ou e-mail…" autocomplete="off" value="<?= $lecteur ? e(nom_complet($lecteur)) : '' ?>" <?= $lecteur ? '' : 'autofocus' ?>>
					<input type="hidden" name="lecteur_id" value="<?= $lecteur ? (int) $lecteur['id'] : '' ?>">
					<div class="suggestions"></div>
				</div>
				<div class="lecteur-choisi<?= $lecteur ? ' ouvert' : '' ?>">
<?php if ($lecteur): ?>
					<strong><?= e(nom_complet($lecteur)) ?></strong>
					<div class="doux"><?= e(implode(' · ', array_filter([LIBELLES_CATEGORIE[$lecteur['categorie']] ?? '', $lecteur['matricule'], $lecteur['email']]))) ?></div>
<?php endif; ?>
				</div>
<?php if ($lecteur && !$lecteur['actif']): ?>
				<div class="flash erreur" style="margin:8px 0 0">
					Compte <?= $lecteur['en_attente'] ? 'en attente de validation' : 'désactivé' ?> : prêt impossible.
					<a href="<?= e(admin_url('lecteurs/voir/' . $lecteur['id'])) ?>">Ouvrir la fiche du lecteur</a>
				</div>
<?php endif; ?>
			</div>
			<div class="champ">
				<label for="code-pret">Code de l’exemplaire</label>
				<input type="text" class="code" id="code-pret" name="code" value="<?= e($codePropose) ?>" placeholder="ex. INF-001-2" autocomplete="off" required <?= $lecteur ? 'autofocus' : '' ?>>
				<div class="aide">Scannez le code-barres ou saisissez la cote suivie du numéro d’exemplaire. Durée du prêt : <?= $duree ?> jours.</div>
			</div>
			<button class="btn" type="submit"<?= $lecteur && !$lecteur['actif'] ? ' disabled' : '' ?>><i class="fa-solid fa-circle-check"></i> Enregistrer le prêt</button>
		</form>
	</section>

	<section class="carte retour">
		<h2><i class="fa-solid fa-rotate-left"></i> Retour</h2>
		<form method="post" action="<?= e(admin_url('guichet/retour')) ?>">
			<?= csrf_field() ?>
			<?php if ($lecteur): ?><input type="hidden" name="lecteur" value="<?= (int) $lecteur['id'] ?>"><?php endif; ?>
			<div class="champ">
				<label for="code-retour">Code de l’exemplaire rendu</label>
				<input type="text" class="code" id="code-retour" name="code" placeholder="ex. INF-001-2" autocomplete="off" required>
				<div class="aide">Si un autre lecteur a réservé l’ouvrage, l’exemplaire lui est automatiquement mis de côté.</div>
			</div>
			<button class="btn vert" type="submit"><i class="fa-solid fa-rotate-left"></i> Enregistrer le retour</button>
		</form>
	</section>
</div>

<?php if ($lecteur): ?>
<section class="carte">
	<div class="carte-titre"><h2>Situation de <?= e(nom_complet($lecteur)) ?></h2>
		<a class="lien" href="<?= e(admin_url('lecteurs/voir/' . $lecteur['id'])) ?>">Fiche du lecteur</a></div>
	<p class="doux"><?= count($pretsLecteur) ?> prêt(s) en cours sur <?= quota_prets($lecteur) ?> autorisé(s)<?= nb_retards((int) $lecteur['id']) ? ' — <strong style="color:var(--rouge)">retard en cours</strong>' : '' ?></p>
<?php if ($pretsLecteur): ?>
	<div class="tableau-wrap"><table class="tableau">
		<thead><tr><th>Ouvrage</th><th>Exemplaire</th><th>Emprunté le</th><th>Échéance</th><th></th></tr></thead>
		<tbody>
<?php foreach ($pretsLecteur as $p): ?>
			<tr class="<?= $p['jours_retard'] > 0 ? 'retard' : '' ?>">
				<td><?= e($p['titre']) ?></td><td><code><?= e($p['exemplaire_code']) ?></code></td>
				<td><?= e(date_fr($p['date_pret'])) ?></td><td><?= e(date_fr($p['date_retour_prevue'])) ?> <?= badge_echeance($p) ?></td>
				<td class="actions">
					<?= bouton_action(admin_url('prets/prolonger/' . $p['id']), 'Prolonger', 'btn secondaire petit', '', ['retour' => admin_url('guichet', ['lecteur' => $lecteur['id']])]) ?>
					<?= bouton_action(admin_url('prets/rendre/' . $p['id']), 'Retour', 'btn vert petit', '', ['retour' => admin_url('guichet', ['lecteur' => $lecteur['id']])]) ?>
				</td>
			</tr>
<?php endforeach; ?>
		</tbody>
	</table></div>
<?php endif; ?>
<?php if ($reservationsLecteur): ?>
	<h3 style="margin-top:18px">Réservations</h3>
	<div class="tableau-wrap"><table class="tableau"><tbody>
<?php foreach ($reservationsLecteur as $r): ?>
		<tr><td><?= e($r['titre']) ?></td>
			<td><?= $r['statut'] === 'disponible' ? '<span class="badge vert">Exemplaire ' . e($r['exemplaire_code']) . ' prêt à retirer</span>' : '<span class="badge">En attente — rang ' . (int) $r['rang'] . '</span>' ?></td>
			<td class="actions"><?php if ($r['statut'] === 'disponible'): ?><a class="btn petit" href="<?= e(admin_url('guichet', ['lecteur' => $lecteur['id'], 'code' => $r['exemplaire_code']])) ?>">Remettre</a><?php endif; ?></td></tr>
<?php endforeach; ?>
	</tbody></table></div>
<?php endif; ?>
</section>
<?php endif; ?>

<section class="carte">
	<div class="carte-titre"><h2>Derniers mouvements</h2><a class="lien" href="<?= e(admin_url('prets')) ?>">Tous les prêts</a></div>
<?php if (!$derniers): ?>
	<p class="doux">Aucun prêt enregistré pour l’instant.</p>
<?php else: ?>
	<div class="tableau-wrap"><table class="tableau">
		<thead><tr><th>Date</th><th>Lecteur</th><th>Ouvrage</th><th>État</th></tr></thead>
		<tbody>
<?php foreach ($derniers as $p): ?>
			<tr><td><?= e(date_fr($p['date_pret'])) ?></td><td><?= e(trim($p['prenom'] . ' ' . $p['nom'])) ?></td>
				<td><?= e($p['titre']) ?> <small><code><?= e($p['exemplaire_code']) ?></code></small></td><td><?= badge_echeance($p) ?></td></tr>
<?php endforeach; ?>
		</tbody>
	</table></div>
<?php endif; ?>
</section>
