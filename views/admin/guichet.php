<?php
/**
 * Guichet prêt / retour, par listes de sélection.
 * @var array|null $lecteur, $rapide @var int $ouvrageChoisi
 * @var array $lecteurs, $ouvrages, $pretsEnCours, $pretsLecteur, $reservationsLecteur, $derniers @var bool $nouveauLecteur, $etudiantsCharges
 */
$duree = reglage_int('pret_duree_jours', 14);
$lecteurInactif = $lecteur && !$lecteur['actif'];
// Ouvrages mis de côté pour le lecteur choisi : prêtables même si aucun exemplaire n'est « disponible »
$misDeCote = $lecteur ? array_column(array_filter($reservationsLecteur, fn($r) => $r['statut'] === 'disponible'), 'exemplaire_code', 'ouvrage_id') : [];
?>
<div class="entete"><h1>Guichet prêt / retour</h1></div>

<div class="guichet">
<?php if (peut('prets.ajouter', 'reservations.remettre')): ?>
	<section class="carte">
		<h2><i class="fa-solid fa-book-open"></i> Nouveau prêt</h2>
		<form method="post" action="<?= e(admin_url('guichet/pret')) ?>" id="form-pret">
			<?= csrf_field() ?>

			<div class="champ">
				<label for="choix-lecteur"><span class="etape-num">1</span> Lecteur</label>
				<input type="search" class="filtre-liste" data-liste="#choix-lecteur" placeholder="<?= $etudiantsCharges ? 'Tapez un nom ou un n° de suivi (lecteurs et étudiants de l’université)…' : 'Filtrer la liste : nom ou matricule…' ?>" aria-label="Filtrer les lecteurs">
				<select name="lecteur_id" id="choix-lecteur" required data-recharger-exemplaires<?php if ($etudiantsCharges): ?> data-url-etudiants="<?= e(admin_url('guichet/etudiants')) ?>" data-url-etudiant="<?= e(admin_url('guichet/etudiant')) ?>"<?php endif; ?>>
					<option value="">— Choisir un lecteur (<?= count($lecteurs) ?>) —</option>
<?php foreach ($lecteurs as $l): ?>
					<option value="<?= (int) $l['id'] ?>"<?= $lecteur && (int) $lecteur['id'] === (int) $l['id'] ? ' selected' : '' ?>><?= e($l['nom'] . ' ' . $l['prenom']) ?> — <?= e(LIBELLES_CATEGORIE[$l['categorie']] ?? '') ?><?= $l['matricule'] ? ' · ' . e($l['matricule']) : '' ?><?= $l['actif'] ? '' : ' (inactif)' ?></option>
<?php endforeach; ?>
<?php if ($etudiantsCharges): ?>
					<optgroup label="Étudiants de l’université (pas encore lecteurs)" data-etudiants hidden></optgroup>
<?php endif; ?>
				</select>
<?php if ($etudiantsCharges): ?>
				<div class="aide">Les étudiants de la liste de l’université apparaissent aussi quand vous tapez leur nom, même sans compte : leur fiche lecteur est créée à la sélection.</div>
<?php endif; ?>
<?php if ($lecteurInactif): ?>
				<div class="flash erreur" style="margin:8px 0 0">Compte <?= $lecteur['en_attente'] ? 'en attente de validation' : 'désactivé' ?> : prêt impossible.
					<a href="<?= e(admin_url('lecteurs/voir/' . $lecteur['id'])) ?>">Ouvrir la fiche</a></div>
<?php endif; ?>
			</div>

			<div class="champ">
				<label for="choix-ouvrage"><span class="etape-num">2</span> Ouvrage</label>
				<input type="search" class="filtre-liste" data-liste="#choix-ouvrage" placeholder="Filtrer la liste : titre, auteur ou cote…" aria-label="Filtrer les ouvrages">
				<select name="ouvrage_id" id="choix-ouvrage" data-url-exemplaires="<?= e(admin_url('guichet/exemplaires')) ?>">
					<option value="">— Choisir un ouvrage (<?= count($ouvrages) ?>) —</option>
<?php foreach ($ouvrages as $o):
    $pourLui = isset($misDeCote[$o['id']]);
    $pretable = !$o['consultation_sur_place'] && ($o['nb_dispo'] > 0 || $pourLui); ?>
					<option value="<?= (int) $o['id'] ?>"<?= $pretable ? '' : ' disabled' ?><?= $ouvrageChoisi === (int) $o['id'] ? ' selected' : '' ?> data-recherche="<?= e(mb_strtolower($o['titre'] . ' ' . $o['auteurs_txt'] . ' ' . $o['cote'])) ?>"><?= e($o['titre']) ?><?= $o['auteurs_txt'] !== '' ? ' — ' . e(tronquer($o['auteurs_txt'], 40, '…')) : '' ?> · <?= $o['consultation_sur_place'] ? 'sur place uniquement' : ($pourLui ? 'mis de côté pour ce lecteur' : $o['nb_dispo'] . '/' . $o['nb_ex'] . ' disponible(s)') ?></option>
<?php endforeach; ?>
				</select>
			</div>

			<div class="champ">
				<label for="choix-exemplaire"><span class="etape-num">3</span> Exemplaire</label>
				<select name="code" id="choix-exemplaire">
					<option value="">Automatique (meilleur exemplaire disponible)</option>
				</select>
				<div class="aide">Ou, avec une douchette : <input type="text" class="code code-scan" name="code_scan" placeholder="scanner le code-barres" autocomplete="off"></div>
			</div>

			<div class="champ">
				<label for="echeance-pret"><span class="etape-num">4</span> À rendre le</label>
				<input type="date" id="echeance-pret" name="echeance" value="<?= e(echeance_standard()) ?>" min="<?= e(plus_jours(aujourdhui(), 1)) ?>" max="<?= e(plus_jours(aujourdhui(), 365)) ?>" required style="max-width:220px">
				<div class="aide">Durée habituelle : <?= $duree ?> jours. L’état de l’exemplaire remis est enregistré comme référence pour le retour.</div>
			</div>

			<div class="form-actions">
				<button class="btn" type="submit"<?= $lecteurInactif ? ' disabled' : '' ?>><i class="fa-solid fa-circle-check"></i> Enregistrer le prêt</button>
			</div>
		</form>

<?php if (peut('lecteurs.ajouter')): ?>
		<details class="panneau-rapide"<?= $nouveauLecteur ? ' open' : '' ?>>
			<summary><i class="fa-solid fa-user-plus"></i> Le lecteur n’est pas dans la liste ? Créer un lecteur (sans compte en ligne)</summary>
			<form method="post" action="<?= e(admin_url('lecteurs/rapide')) ?>">
				<?= csrf_field() ?>
				<?php if ($ouvrageChoisi): ?><input type="hidden" name="ouvrage" value="<?= $ouvrageChoisi ?>"><?php endif; ?>
				<p class="doux">Il pourra emprunter tout de suite. L’e-mail est facultatif : s’il est renseigné, le lecteur reçoit les rappels.</p>
<?php if ($etudiantsCharges): ?>
				<p class="aide"><i class="fa-solid fa-circle-info"></i> Étudiant de l’université ? Tapez d’abord son nom ou son n° de suivi dans le choix du lecteur ci-dessus :
					s’il figure dans la liste, sa fiche est créée automatiquement. Ce formulaire sert aux enseignants, au personnel, aux visiteurs
					et aux étudiants pas encore dans la liste (n° de suivi comme matricule).</p>
<?php endif; ?>
				<?= render(ROOT_DIR . '/views/admin/_champs_lecteur.php', ['l' => $rapide['saisie'] ?? ['categorie' => $etudiantsCharges ? 'enseignant' : 'etudiant'], 'erreurs' => $rapide['erreurs'] ?? []]) ?>
				<button class="btn" type="submit"><i class="fa-solid fa-user-plus"></i> Créer et sélectionner</button>
			</form>
		</details>
<?php endif; ?>
<?php endif; ?>
	</section>

<?php if (peut('prets.retour')): ?>
	<section class="carte">
		<h2><i class="fa-solid fa-rotate-left"></i> Retour</h2>
		<form method="post" action="<?= e(admin_url('guichet/retour')) ?>">
			<?= csrf_field() ?>
			<?php if ($lecteur): ?><input type="hidden" name="lecteur" value="<?= (int) $lecteur['id'] ?>"><?php endif; ?>
			<div class="champ">
				<label for="choix-pret">Prêt en cours</label>
				<input type="search" class="filtre-liste" data-liste="#choix-pret" placeholder="Filtrer : lecteur, titre ou code…" aria-label="Filtrer les prêts">
				<select name="pret_id" id="choix-pret">
					<option value="">— Choisir le prêt à clore (<?= count($pretsEnCours) ?> en cours) —</option>
<?php foreach ($pretsEnCours as $p): ?>
					<option value="<?= (int) $p['id'] ?>"<?= $lecteur && (int) $p['lecteur_id'] === (int) $lecteur['id'] && count($pretsLecteur) === 1 ? ' selected' : '' ?>><?= e(trim($p['nom'] . ' ' . $p['prenom'])) ?> — <?= e(tronquer($p['titre'], 45, '…')) ?> (<?= e($p['exemplaire_code']) ?>) · <?= $p['jours_retard'] > 0 ? 'RETARD ' . (int) $p['jours_retard'] . ' j' : 'à rendre le ' . date_fr($p['date_retour_prevue']) ?></option>
<?php endforeach; ?>
				</select>
				<div class="aide">Ou, avec une douchette : <input type="text" class="code code-scan" name="code" placeholder="scanner le code-barres" autocomplete="off"></div>
			</div>
			<?= render(ROOT_DIR . '/views/admin/_champs_retour.php', ['prefixe' => 'guichet-retour']) ?>
			<button class="btn vert" type="submit"><i class="fa-solid fa-rotate-left"></i> Enregistrer le retour</button>
			<p class="aide" style="margin-top:10px">Si d’autres lecteurs ont réservé l’ouvrage, l’exemplaire rendu leur est automatiquement mis de côté.</p>
		</form>
	</section>
<?php endif; ?>
</div>

<?php if ($lecteur): ?>
<section class="carte">
	<div class="carte-titre"><h2>Situation de <?= e(nom_complet($lecteur)) ?></h2>
		<?php if (peut('lecteurs.voir')): ?><a class="lien" href="<?= e(admin_url('lecteurs/voir/' . $lecteur['id'])) ?>">Fiche du lecteur</a><?php endif; ?></div>
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
					<?= bouton_prolonger($p) ?>
					<?= bouton_retour($p) ?>
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
			<td class="actions"><?php if ($r['statut'] === 'disponible'): ?><?= bouton_remettre($r) ?><?php endif; ?></td></tr>
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
		<thead><tr><th>Date</th><th>Lecteur</th><th>Ouvrage</th><th>Enregistré par</th><th>État</th></tr></thead>
		<tbody>
<?php foreach ($derniers as $p): ?>
			<tr><td><?= e(date_fr($p['date_pret'])) ?></td><td><?= e(trim($p['prenom'] . ' ' . $p['nom'])) ?></td>
				<td><?= e($p['titre']) ?> <small><code><?= e($p['exemplaire_code']) ?></code></small></td><td><?= e(nom_acteur((int) $p['agent_id']) ?: '—') ?></td><td><?= badge_echeance($p) ?></td></tr>
<?php endforeach; ?>
		</tbody>
	</table></div>
<?php endif; ?>
</section>
