<?php
/**
 * Espace lecteur : profil, prêts en cours (avec demandes de prolongation), réservations, historique, mot de passe.
 * @var array $u, $prets, $reservations, $historique, $demandes  (dernière demande de prolongation par prêt)
 */
$quota = quota_prets($u);
$duree = reglage_int('pret_duree_jours', 14);
$maxProl = reglage_int('pret_max_prolongations', 1);
$retards = array_filter($prets, fn($p) => (int) $p['jours_retard'] > 0);
$bientot = array_filter($prets, fn($p) => (int) $p['jours_retard'] <= 0 && (int) $p['jours_retard'] >= -3);
$aRetirer = array_filter($reservations, fn($r) => $r['statut'] === 'disponible');
$initiales = mb_strtoupper(mb_substr($u['prenom'] ?: $u['nom'], 0, 1) . mb_substr($u['nom'], 0, 1));
?>
<div class="biblio-compte cpt">
	<header class="cpt-profil">
		<div class="cpt-avatar" aria-hidden="true"><?= e($initiales) ?></div>
		<div class="cpt-identite">
			<h1>Bonjour <?= e($u['prenom'] ?: $u['nom']) ?></h1>
			<p><?= e(LIBELLES_CATEGORIE[$u['categorie']] ?? 'Lecteur') ?><?= $u['matricule'] ? ($u['categorie'] === 'etudiant' ? ' · N° de suivi ' : ' · Matricule ') . e($u['matricule']) : '' ?><?= ($u['faculte'] ?? '') !== '' ? ' · ' . e($u['faculte']) : '' ?></p>
		</div>
		<form method="post" action="<?= e(url('compte/deconnexion')) ?>"><?= csrf_field() ?><button class="btn btn-default" type="submit"><i class="fa fa-sign-out" aria-hidden="true"></i> Se déconnecter</button></form>
	</header>

	<div class="cpt-chiffres">
		<div class="cpt-chiffre"><i class="fa fa-book" aria-hidden="true"></i><strong><?= count($prets) ?><small> / <?= $quota ?></small></strong><span>prêt(s) en cours</span></div>
		<div class="cpt-chiffre<?= $retards ? ' alerte' : ($bientot ? ' attention' : '') ?>"><i class="fa fa-clock-o" aria-hidden="true"></i><strong><?= $retards ? count($retards) : count($bientot) ?></strong><span><?= $retards ? 'en retard' : 'à rendre sous 3 jours' ?></span></div>
		<div class="cpt-chiffre<?= $aRetirer ? ' ok' : '' ?>"><i class="fa fa-bookmark" aria-hidden="true"></i><strong><?= count($reservations) ?></strong><span><?= $aRetirer ? count($aRetirer) . ' à retirer à l’accueil' : 'réservation(s)' ?></span></div>
		<div class="cpt-chiffre"><i class="fa fa-history" aria-hidden="true"></i><strong><?= count($historique) ?></strong><span>ouvrage(s) déjà rendu(s)</span></div>
	</div>

<?php if ($retards): ?>
	<div class="biblio-alert biblio-alert-erreur"><strong>Prêt en retard.</strong> Merci de rapporter rapidement l’ouvrage : tant qu’un prêt est en retard, vous ne pouvez ni emprunter ni réserver.</div>
<?php endif; ?>

	<div class="cpt-bas">
		<section class="cpt-carte">
			<h2><i class="fa fa-user" aria-hidden="true"></i> Mon profil</h2>
			<dl class="cpt-fiche">
				<dt>Nom</dt><dd><?= e(trim($u['prenom'] . ' ' . $u['nom'])) ?></dd>
				<dt>E-mail</dt><dd><?= e($u['email'] ?: '—') ?></dd>
				<dt>Téléphone</dt><dd><?= e($u['telephone'] ?: '—') ?></dd>
<?php if ($u['categorie'] === 'etudiant'): ?>
				<dt>Faculté</dt><dd><?= e($u['faculte'] ?: '—') ?></dd>
				<dt>Parcours</dt><dd><?= e($u['parcours'] ?: '—') ?></dd>
<?php endif; ?>
			</dl>
			<p class="cpt-aide">Une information à corriger ? Adressez-vous à l’accueil de la bibliothèque.</p>
		</section>

		<section class="cpt-carte">
			<h2><i class="fa fa-info-circle" aria-hidden="true"></i> Règles du prêt</h2>
			<ul class="cpt-regles">
				<li><strong><?= $quota ?></strong> prêt(s) simultané(s) au maximum</li>
				<li>Durée d’un prêt : <strong><?= $duree ?> jours</strong></li>
				<li>Prolongation sur demande, <strong><?= $maxProl ?></strong> fois au plus, si personne n’attend l’ouvrage</li>
				<li>Réservation conservée <strong><?= reglage_int('reservation_delai_jours', 3) ?> jours</strong> à l’accueil</li>
			</ul>
		</section>

		<section class="cpt-carte">
			<h2><i class="fa fa-lock" aria-hidden="true"></i> Mot de passe</h2>
			<form method="post" action="<?= e(url('compte/mot-de-passe')) ?>" class="cpt-form">
				<?= csrf_field() ?>
				<label for="mdp-actuel">Mot de passe actuel</label>
				<input type="password" id="mdp-actuel" name="actuel" class="form-control" required autocomplete="current-password">
				<label for="mdp-nouveau">Nouveau mot de passe</label>
				<input type="password" id="mdp-nouveau" name="nouveau" class="form-control" required minlength="8" autocomplete="new-password" placeholder="8 caractères minimum">
				<button type="submit" class="btn btn-primary">Changer le mot de passe</button>
			</form>
		</section>
	</div>

	<section class="cpt-section">
		<h2><i class="fa fa-book" aria-hidden="true"></i> Mes prêts en cours</h2>
<?php if (!$prets): ?>
		<div class="cpt-vide"><i class="fa fa-book" aria-hidden="true"></i><p>Aucun prêt en cours.</p><a class="btn btn-primary" href="<?= e(url('catalogue')) ?>">Parcourir le catalogue</a></div>
<?php else: ?>
		<div class="cpt-cartes">
<?php foreach ($prets as $p):
    $retard = (int) $p['jours_retard'];
    $total = max(1, (strtotime($p['date_retour_prevue']) - strtotime($p['date_pret'])) / 86400);
    $restant = -$retard;
    $progression = min(100, max(0, round(100 * ($total - max(0, $restant)) / $total)));
    $d = $demandes[$p['id']] ?? null;
    $obstacles = obstacles_prolongation($p);
    $etatClasse = $retard > 0 ? 'retard' : ($restant <= 3 ? 'bientot' : 'ok'); ?>
			<article class="cpt-pret <?= $etatClasse ?>">
				<div class="cpt-pret-tete">
					<a class="cpt-couv" href="<?= e(ouvrage_url($p)) ?>"><img src="<?= e(couverture($p['image'])) ?>" alt="" loading="lazy"></a>
					<div>
						<h3><a href="<?= e(ouvrage_url($p)) ?>"><?= e($p['titre']) ?></a></h3>
						<p class="cpt-meta">Exemplaire <?= e($p['exemplaire_code']) ?><br>Emprunté le <?= e(date_fr($p['date_pret'])) ?><?= (int) $p['prolongations'] ? '<br>Prolongé ' . (int) $p['prolongations'] . ' fois' : '' ?></p>
					</div>
				</div>
				<div class="cpt-echeance">
					<span>À rendre le <strong><?= e(date_fr($p['date_retour_prevue'])) ?></strong></span>
					<span class="cpt-etat"><?= $retard > 0 ? 'En retard de ' . e(pluriel($retard, 'jour')) : ($restant === 0 ? 'Aujourd’hui' : 'Encore ' . e(pluriel($restant, 'jour'))) ?></span>
				</div>
				<div class="cpt-barre" role="presentation"><span style="width:<?= $progression ?>%"></span></div>

				<div class="cpt-prolongation">
<?php if ($d && $d['statut'] === 'en_attente'): ?>
					<p class="cpt-info attente"><i class="fa fa-hourglass-half" aria-hidden="true"></i> Demande de prolongation envoyée le <?= e(date_fr($d['demandee_le'])) ?>, en attente de réponse.</p>
<?php else: ?>
<?php if ($d && $d['statut'] === 'acceptee' && $d['nouvelle_date'] === $p['date_retour_prevue']): ?>
					<p class="cpt-info ok"><i class="fa fa-check-circle" aria-hidden="true"></i> Prolongation accordée le <?= e(date_fr($d['traitee_le'])) ?>.</p>
<?php elseif ($d && $d['statut'] === 'refusee'): ?>
					<p class="cpt-info refus"><i class="fa fa-times-circle" aria-hidden="true"></i> Prolongation refusée le <?= e(date_fr($d['traitee_le'])) ?> : la date de retour est inchangée.</p>
<?php endif; ?>
<?php if ($d && in_array($d['statut'], ['acceptee', 'refusee'], true) && $d['reponse'] !== '' && ($d['statut'] === 'refusee' || $d['nouvelle_date'] === $p['date_retour_prevue'])): ?>
					<p class="cpt-mot"><span>Message de la bibliothèque</span><?= nl2br(e($d['reponse']), false) ?></p>
<?php endif; ?>
<?php if (!$obstacles): ?>
					<details class="cpt-demande">
						<summary><i class="fa fa-calendar-plus-o" aria-hidden="true"></i> Demander une prolongation</summary>
						<form method="post" action="<?= e(url('compte/prolongation/' . $p['id'])) ?>">
							<?= csrf_field() ?>
							<label for="motif-<?= (int) $p['id'] ?>">Pourquoi avez-vous besoin de plus de temps ? <small>(facultatif)</small></label>
							<textarea id="motif-<?= (int) $p['id'] ?>" name="motif" rows="2" maxlength="255" class="form-control" placeholder="Ex. mémoire à rendre le mois prochain"></textarea>
							<p class="cpt-aide">La bibliothèque étudie votre demande (environ <?= $duree ?> jours de plus) et vous répond ici<?= $u['email'] ? ' et par e-mail' : '' ?>.</p>
							<button type="submit" class="btn btn-primary btn-sm">Envoyer la demande</button>
						</form>
					</details>
<?php else: ?>
					<p class="cpt-aide"><i class="fa fa-info-circle" aria-hidden="true"></i> Prolongation impossible : <?= e(mb_strtolower(implode(' ', $obstacles), 'UTF-8')) ?></p>
<?php endif; ?>
<?php endif; ?>
				</div>
			</article>
<?php endforeach; ?>
		</div>
<?php endif; ?>
	</section>

	<section class="cpt-section">
		<h2><i class="fa fa-bookmark" aria-hidden="true"></i> Mes réservations</h2>
<?php if (!$reservations): ?>
		<p class="cpt-aide">Aucune réservation en cours. Un ouvrage est déjà emprunté ? Réservez-le depuis sa fiche : il vous sera mis de côté dès son retour.</p>
<?php else: ?>
		<div class="cpt-cartes">
<?php foreach ($reservations as $r): ?>
			<article class="cpt-pret">
				<div class="cpt-pret-tete">
					<a class="cpt-couv" href="<?= e(ouvrage_url($r)) ?>"><img src="<?= e(couverture($r['image'])) ?>" alt="" loading="lazy"></a>
					<div>
						<h3><a href="<?= e(ouvrage_url($r)) ?>"><?= e($r['titre']) ?></a></h3>
						<p class="cpt-meta">Réservé le <?= e(date_fr($r['demandee_le'])) ?></p>
					</div>
				</div>
<?php if ($r['statut'] === 'disponible'): ?>
				<p class="cpt-info ok"><i class="fa fa-check-circle" aria-hidden="true"></i> Un exemplaire vous attend à l’accueil jusqu’au <strong><?= e(date_fr($r['expire_le'])) ?></strong>.</p>
<?php else: ?>
				<p class="cpt-info attente"><i class="fa fa-users" aria-hidden="true"></i> En file d’attente : vous êtes <strong><?= (int) $r['rang'] === 1 ? '1er' : (int) $r['rang'] . 'e' ?></strong>.</p>
<?php endif; ?>
				<form method="post" action="<?= e(url('compte/annuler/' . $r['id'])) ?>" class="cpt-pied" onsubmit="return confirm('Annuler cette réservation ?');"><?= csrf_field() ?><button class="btn btn-default btn-sm" type="submit">Annuler la réservation</button></form>
			</article>
<?php endforeach; ?>
		</div>
<?php endif; ?>
	</section>

	<section class="cpt-section">
		<h2><i class="fa fa-history" aria-hidden="true"></i> Historique</h2>
<?php if (!$historique): ?>
		<p class="cpt-aide">Aucun prêt terminé pour l’instant.</p>
<?php else: ?>
		<div class="biblio-table-wrap"><table class="biblio-table cpt-table">
			<thead><tr><th>Ouvrage</th><th>Emprunté le</th><th>Rendu le</th></tr></thead>
			<tbody>
<?php foreach ($historique as $p): ?>
				<tr><td><a href="<?= e(ouvrage_url($p)) ?>"><?= e($p['titre']) ?></a></td><td><?= e(date_fr($p['date_pret'])) ?></td>
					<td><?= e(date_fr($p['date_retour'])) ?><?= $p['date_retour'] > $p['date_retour_prevue'] ? ' <span class="cpt-pastille">en retard</span>' : '' ?></td></tr>
<?php endforeach; ?>
			</tbody>
		</table></div>
<?php endif; ?>
	</section>

</div>
