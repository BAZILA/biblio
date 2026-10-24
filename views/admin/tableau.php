<?php
/** @var array $stats, $retards, $demandes, $aRetirer, $echeances, $inscriptions, $messages, $populaires @var int $enAttente */
$moi = user();
$jours = function (string $date): int {
    return (int) round((strtotime($date) - strtotime(aujourdhui())) / 86400);
};
$lecteur = fn(array $l): string => '<a href="' . e(admin_url('lecteurs/voir/' . $l['lecteur_id'])) . '"><strong>' . e(trim($l['prenom'] . ' ' . $l['nom'])) . '</strong></a>'
    . ($l['matricule'] ? '<br><small class="doux">' . e($l['matricule']) . '</small>' : '');
$ouvrage = fn(array $o, string $code = ''): string => '<div class="cellule-ouvrage"><img class="vignette" src="' . e(couverture($o['image'] ?? '')) . '" alt=""><span>' . e($o['titre'])
    . ($code !== '' ? '<br><small class="doux">Exemplaire <code>' . e($code) . '</code></small>' : '') . '</span></div>';
?>
<div class="entete">
	<h1>Bonjour <?= e($moi['prenom'] ?: $moi['nom']) ?></h1>
	<div class="actions">
<?php if (peut('guichet.voir')): ?>
		<a class="btn" href="<?= e(admin_url('guichet')) ?>"><i class="fa-solid fa-right-left"></i> Guichet prêt / retour</a>
<?php endif; ?>
<?php if (peut('ouvrages.ajouter')): ?>
		<a class="btn secondaire" href="<?= e(admin_url('ouvrages/nouveau')) ?>"><i class="fa-solid fa-plus"></i> Nouvel ouvrage</a>
<?php endif; ?>
	</div>
</div>

<?php if (is_systeme() && is_file(ROOT_DIR . '/install/SYSTEME.txt')): ?>
<div class="flash info">Le fichier <code>install/SYSTEME.txt</code> contient encore le mot de passe initial du compte Système : changez-le dans <a href="<?= e(admin_url('mon-compte')) ?>">Mon compte</a>, puis supprimez le fichier.</div>
<?php endif; ?>
<?php if (is_admin() && is_file(ROOT_DIR . '/install/IDENTIFIANTS.txt')): ?>
<div class="flash info">Le fichier <code>install/IDENTIFIANTS.txt</code> contient encore le mot de passe initial de l’administrateur.
	Changez ce mot de passe dans <a href="<?= e(admin_url('mon-compte')) ?>">Mon compte</a>, puis supprimez le fichier.</div>
<?php endif; ?>

<div class="stats">
<?php if (peut('ouvrages.voir')): ?>
	<a class="stat" href="<?= e(admin_url('ouvrages')) ?>"><i class="fa-solid fa-book"></i><div class="valeur"><?= $stats['ouvrages'] ?></div><div class="libelle">ouvrages au catalogue</div></a>
	<a class="stat" href="<?= e(admin_url('ouvrages')) ?>"><i class="fa-solid fa-barcode"></i><div class="valeur"><?= $stats['dispo'] ?> / <?= $stats['exemplaires'] ?></div><div class="libelle">exemplaires disponibles</div></a>
<?php endif; ?>
<?php if (peut('prets.voir')): ?>
	<a class="stat" href="<?= e(admin_url('prets')) ?>"><i class="fa-solid fa-book-open"></i><div class="valeur"><?= $stats['prets'] ?></div><div class="libelle"><?= $stats['prets'] > 1 ? 'prêts en cours' : 'prêt en cours' ?> (<?= $stats['prets_mois'] ?> ce&nbsp;mois‑ci)</div></a>
	<a class="stat<?= $stats['retards'] ? ' alerte' : '' ?>" href="<?= e(admin_url('prets', ['filtre' => 'retard'])) ?>"><i class="fa-solid fa-clock"></i><div class="valeur"><?= $stats['retards'] ?></div><div class="libelle">prêts en retard</div></a>
<?php endif; ?>
<?php if (peut('lecteurs.voir')): ?>
	<a class="stat" href="<?= e(admin_url('lecteurs')) ?>"><i class="fa-solid fa-users"></i><div class="valeur"><?= $stats['lecteurs'] ?></div><div class="libelle">lecteurs actifs<?= $stats['a_valider'] ? ' · ' . $stats['a_valider'] . ' à valider' : '' ?></div></a>
<?php endif; ?>
</div>

<?php if (peut('reservations.voir', 'prets.voir', 'demandes.voir')): ?>
<h2 class="titre-zone"><i class="fa-solid fa-list-check"></i> À traiter</h2>
<?php endif; ?>

<?php if (peut('reservations.voir')): ?>
<section class="carte bloc-large">
	<div class="carte-titre"><h2><i class="fa-solid fa-hand-holding"></i> Réservations à préparer <span class="compteur"><?= count($aRetirer) ?></span></h2>
		<a class="lien" href="<?= e(admin_url('reservations')) ?>">Toutes les réservations<?= $enAttente ? ' · ' . e(pluriel($enAttente, 'en attente', 'en attente')) : '' ?></a></div>
<?php if (!$aRetirer): ?>
	<p class="rien"><i class="fa-solid fa-circle-check"></i> Aucun exemplaire à mettre de côté.</p>
<?php else: ?>
	<div class="tableau-wrap"><table class="tableau tableau-large">
		<thead><tr><th>Lecteur</th><th>Ouvrage</th><th>Exemplaire</th><th>À retirer avant le</th><th></th></tr></thead>
		<tbody>
<?php foreach ($aRetirer as $r): $j = $jours($r['expire_le']); ?>
			<tr><td><?= $lecteur($r) ?></td>
				<td><?= $ouvrage($r) ?></td>
				<td><code><?= e($r['exemplaire_code']) ?></code></td>
				<td><?= e(date_fr($r['expire_le'])) ?><br><span class="badge <?= $j <= 0 ? 'rouge' : ($j === 1 ? 'orange' : '') ?>"><?= $j < 0 ? 'expirée' : ($j === 0 ? 'dernier jour' : 'encore ' . pluriel($j, 'jour')) ?></span></td>
				<td class="actions"><?= bouton_remettre($r) ?></td></tr>
<?php endforeach; ?>
		</tbody>
	</table></div>
<?php endif; ?>
</section>
<?php endif; ?>

<?php if (peut('prets.voir')): ?>
<section class="carte bloc-large">
	<div class="carte-titre"><h2><i class="fa-solid fa-clock"></i> Prêts en retard <span class="compteur<?= $retards ? ' rouge' : '' ?>"><?= $stats['retards'] ?></span></h2>
		<a class="lien" href="<?= e(admin_url('prets', ['filtre' => 'retard'])) ?>">Tous les retards</a></div>
<?php if (!$retards): ?>
	<p class="rien"><i class="fa-solid fa-circle-check"></i> Aucun prêt en retard.</p>
<?php else: ?>
	<div class="tableau-wrap"><table class="tableau tableau-large">
		<thead><tr><th>Lecteur</th><th>Contact</th><th>Ouvrage</th><th>Devait rendre le</th><th></th></tr></thead>
		<tbody>
<?php foreach ($retards as $p): ?>
			<tr class="retard"><td><?= $lecteur($p) ?></td>
				<td><small><?= $p['telephone'] ? e($p['telephone']) : '' ?><?= $p['telephone'] && $p['email'] ? '<br>' : '' ?><?= $p['email'] ? e($p['email']) : '' ?><?= !$p['telephone'] && !$p['email'] ? '—' : '' ?></small></td>
				<td><?= $ouvrage($p, $p['exemplaire_code']) ?></td>
				<td><?= e(date_fr($p['date_retour_prevue'])) ?><br><?= badge_echeance($p) ?></td>
				<td class="actions"><?= bouton_retour($p) ?></td></tr>
<?php endforeach; ?>
		</tbody>
	</table></div>
<?php endif; ?>
</section>
<?php endif; ?>

<?php if (peut('demandes.voir')): ?>
<section class="carte bloc-large">
	<div class="carte-titre"><h2><i class="fa-solid fa-calendar-plus"></i> Demandes de prolongation <span class="compteur<?= $demandes ? ' orange' : '' ?>"><?= count($demandes) ?></span></h2>
		<a class="lien" href="<?= e(admin_url('prets/demandes')) ?>">Traiter les demandes</a></div>
<?php if (!$demandes): ?>
	<p class="rien"><i class="fa-solid fa-circle-check"></i> Aucune demande en attente.</p>
<?php else: ?>
	<div class="tableau-wrap"><table class="tableau tableau-large">
		<thead><tr><th>Lecteur</th><th>Ouvrage</th><th>Échéance actuelle</th><th>Motif</th><th></th></tr></thead>
		<tbody>
<?php foreach ($demandes as $d): ?>
			<tr><td><?= $lecteur($d) ?></td><td><?= e($d['titre']) ?></td><td><?= e(date_fr($d['date_retour_prevue'])) ?></td>
				<td><?= $d['motif'] !== '' ? '<em>« ' . e($d['motif']) . ' »</em>' : '<span class="doux">—</span>' ?></td>
				<td class="actions"><?php if (peut('demandes.traiter')): ?><a class="btn petit" href="<?= e(admin_url('prets/demandes')) ?>"><i class="fa-solid fa-gavel"></i> Traiter</a><?php endif; ?></td></tr>
<?php endforeach; ?>
		</tbody>
	</table></div>
<?php endif; ?>
</section>
<?php endif; ?>

<h2 class="titre-zone"><i class="fa-solid fa-chart-simple"></i> Suivi</h2>

<div class="grille grille-2">
<?php if (peut('prets.voir')): ?>
	<section class="carte">
		<div class="carte-titre"><h2><i class="fa-solid fa-hourglass-half"></i> À rendre d’ici 2 jours <span class="compteur"><?= count($echeances) ?></span></h2></div>
<?php if (!$echeances): ?>
		<p class="rien"><i class="fa-solid fa-circle-check"></i> Aucune échéance proche.</p>
<?php else: ?>
		<ul class="liste-simple">
<?php foreach ($echeances as $p): ?>
			<li><span><strong><?= e(trim($p['prenom'] . ' ' . $p['nom'])) ?></strong><br><small class="doux"><?= e($p['titre']) ?></small></span><?= badge_echeance($p) ?></li>
<?php endforeach; ?>
		</ul>
<?php endif; ?>
	</section>
<?php endif; ?>

<?php if (peut('prets.voir', 'ouvrages.voir')): ?>
	<section class="carte">
		<div class="carte-titre"><h2><i class="fa-solid fa-ranking-star"></i> Les plus empruntés (12 mois)</h2></div>
<?php if (!$populaires): ?>
		<p class="rien">Pas encore de prêts enregistrés.</p>
<?php else: ?>
		<ul class="liste-simple">
<?php foreach ($populaires as $o): ?>
			<li><a href="<?= e(admin_url('ouvrages/modifier/' . $o['id'])) ?>"><?= e($o['titre']) ?></a><span class="badge"><?= e(pluriel((int) $o['nb'], 'prêt')) ?></span></li>
<?php endforeach; ?>
		</ul>
<?php endif; ?>
	</section>
<?php endif; ?>

<?php if (peut('lecteurs.voir')): ?>
	<section class="carte">
		<div class="carte-titre"><h2><i class="fa-solid fa-user-check"></i> Comptes lecteurs à valider <span class="compteur"><?= count($inscriptions) ?></span></h2><a class="lien" href="<?= e(admin_url('lecteurs', ['filtre' => 'a_valider'])) ?>">Voir</a></div>
<?php if (!$inscriptions): ?>
		<p class="rien"><i class="fa-solid fa-circle-check"></i> Aucune demande en attente.</p>
<?php else: ?>
		<ul class="liste-simple">
<?php foreach ($inscriptions as $l): ?>
			<li><span><a href="<?= e(admin_url('lecteurs/modifier/' . $l['id'])) ?>"><strong><?= e(nom_complet($l)) ?></strong></a><br><small class="doux"><?= e(LIBELLES_CATEGORIE[$l['categorie']] ?? '') ?><?= $l['matricule'] ? ' · ' . e($l['matricule']) : '' ?> · <?= e($l['email']) ?></small></span>
				<?= peut('lecteurs.valider') ? bouton_action(admin_url('lecteurs/activer/' . $l['id']), '<i class="fa-solid fa-circle-check"></i> Valider', 'btn vert petit') : '' ?></li>
<?php endforeach; ?>
		</ul>
<?php endif; ?>
	</section>
<?php endif; ?>

<?php if (peut('messages.voir')): ?>
	<section class="carte">
		<div class="carte-titre"><h2><i class="fa-solid fa-envelope"></i> Messages non lus <span class="compteur"><?= count($messages) ?></span></h2><a class="lien" href="<?= e(admin_url('messages')) ?>">Boîte de réception</a></div>
<?php if (!$messages): ?>
		<p class="rien"><i class="fa-solid fa-circle-check"></i> Aucun nouveau message.</p>
<?php else: ?>
		<ul class="liste-simple">
<?php foreach ($messages as $m): ?>
			<li><span><a href="<?= e(admin_url('messages/voir/' . $m['id'])) ?>"><strong><?= e($m['sujet']) ?></strong></a><br><small class="doux"><?= e($m['nom']) ?> · <?= e(date_fr($m['recu_le'], true)) ?></small></span></li>
<?php endforeach; ?>
		</ul>
<?php endif; ?>
	</section>
<?php endif; ?>
</div>

<?php if ($courbe): ?>
<section class="carte" style="margin-top:20px">
	<div class="carte-titre"><h2><i class="fa-solid fa-chart-line"></i> Activité des 12 derniers mois</h2>
		<?php if (peut('rapports.voir')): ?><a class="lien" href="<?= e(admin_url('rapports', ['du' => date('Y-m-d', strtotime('-12 months +1 day')), 'au' => date('Y-m-d')])) ?>">Statistiques détaillées</a><?php endif; ?></div>
	<?= render(ROOT_DIR . '/views/admin/_courbe.php', ['titre' => 'Prêts et retours par mois sur les 12 derniers mois', 'mois' => $courbe['mois'], 'series' => [
		['nom' => 'Prêts', 'couleur' => '#3a5bd0', 'valeurs' => $courbe['prets']],
		['nom' => 'Retours', 'couleur' => '#009c30', 'valeurs' => $courbe['retours']],
	]]) ?>
</section>
<?php endif; ?>
