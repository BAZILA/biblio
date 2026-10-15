<?php
/** @var array $stats, $retards, $aRetirer, $echeances, $inscriptions, $messages, $populaires @var int $enAttente */
$moi = user();
?>
<div class="entete">
	<h1>Bonjour <?= e($moi['prenom'] ?: $moi['nom']) ?></h1>
	<div class="actions">
		<a class="btn" href="<?= e(admin_url('guichet')) ?>"><i class="fa-solid fa-right-left"></i> Guichet prêt / retour</a>
		<a class="btn secondaire" href="<?= e(admin_url('ouvrages/nouveau')) ?>"><i class="fa-solid fa-plus"></i> Nouvel ouvrage</a>
	</div>
</div>

<?php if (is_admin() && is_file(ROOT_DIR . '/install/IDENTIFIANTS.txt')): ?>
<div class="flash info">Le fichier <code>install/IDENTIFIANTS.txt</code> contient encore le mot de passe initial de l’administrateur.
	Changez ce mot de passe dans <a href="<?= e(admin_url('mon-compte')) ?>">Mon compte</a>, puis supprimez le fichier.</div>
<?php endif; ?>

<div class="stats">
	<a class="stat" href="<?= e(admin_url('ouvrages')) ?>"><i class="fa-solid fa-book"></i><div class="valeur"><?= $stats['ouvrages'] ?></div><div class="libelle">ouvrages au catalogue</div></a>
	<a class="stat" href="<?= e(admin_url('ouvrages')) ?>"><i class="fa-solid fa-barcode"></i><div class="valeur"><?= $stats['dispo'] ?> / <?= $stats['exemplaires'] ?></div><div class="libelle">exemplaires disponibles</div></a>
	<a class="stat" href="<?= e(admin_url('prets')) ?>"><i class="fa-solid fa-book-open"></i><div class="valeur"><?= $stats['prets'] ?></div><div class="libelle"><?= $stats['prets'] > 1 ? 'prêts en cours' : 'prêt en cours' ?> (<?= $stats['prets_mois'] ?> ce&nbsp;mois‑ci)</div></a>
	<a class="stat<?= $stats['retards'] ? ' alerte' : '' ?>" href="<?= e(admin_url('prets', ['filtre' => 'retard'])) ?>"><i class="fa-solid fa-clock"></i><div class="valeur"><?= $stats['retards'] ?></div><div class="libelle">prêts en retard</div></a>
	<a class="stat" href="<?= e(admin_url('lecteurs')) ?>"><i class="fa-solid fa-users"></i><div class="valeur"><?= $stats['lecteurs'] ?></div><div class="libelle">lecteurs actifs<?= $stats['a_valider'] ? ' · ' . $stats['a_valider'] . ' à valider' : '' ?></div></a>
</div>

<div class="grille grille-2">
	<section class="carte">
		<div class="carte-titre"><h2>Réservations à préparer</h2><a class="lien" href="<?= e(admin_url('reservations')) ?>">Toutes (<?= count($aRetirer) + $enAttente ?>)</a></div>
<?php if (!$aRetirer): ?>
		<p class="doux">Aucun exemplaire à mettre de côté. <?= $enAttente ? e(pluriel($enAttente, 'réservation')) . ' en file d’attente.' : '' ?></p>
<?php else: ?>
		<div class="tableau-wrap"><table class="tableau">
			<thead><tr><th>Lecteur</th><th>Ouvrage</th><th>Exemplaire</th><th>Retrait avant</th></tr></thead>
			<tbody>
<?php foreach ($aRetirer as $r): ?>
				<tr><td><a href="<?= e(admin_url('lecteurs/voir/' . $r['lecteur_id'])) ?>"><?= e(trim($r['prenom'] . ' ' . $r['nom'])) ?></a></td>
					<td><?= e($r['titre']) ?></td><td><code><?= e($r['exemplaire_code']) ?></code></td><td><?= e(date_fr($r['expire_le'])) ?></td></tr>
<?php endforeach; ?>
			</tbody>
		</table></div>
<?php endif; ?>
	</section>

	<section class="carte">
		<div class="carte-titre"><h2>Retards</h2><a class="lien" href="<?= e(admin_url('prets', ['filtre' => 'retard'])) ?>">Tous les retards</a></div>
<?php if (!$retards): ?>
		<p class="doux">Aucun prêt en retard.</p>
<?php else: ?>
		<div class="tableau-wrap"><table class="tableau">
			<thead><tr><th>Lecteur</th><th>Ouvrage</th><th>Échéance</th></tr></thead>
			<tbody>
<?php foreach ($retards as $p): ?>
				<tr class="retard"><td><a href="<?= e(admin_url('lecteurs/voir/' . $p['lecteur_id'])) ?>"><?= e(trim($p['prenom'] . ' ' . $p['nom'])) ?></a></td>
					<td><?= e($p['titre']) ?> <small><code><?= e($p['exemplaire_code']) ?></code></small></td><td><?= badge_echeance($p) ?></td></tr>
<?php endforeach; ?>
			</tbody>
		</table></div>
<?php endif; ?>
	</section>

	<section class="carte">
		<div class="carte-titre"><h2>À rendre d’ici 2 jours</h2></div>
<?php if (!$echeances): ?>
		<p class="doux">Aucune échéance proche.</p>
<?php else: ?>
		<div class="tableau-wrap"><table class="tableau"><tbody>
<?php foreach ($echeances as $p): ?>
			<tr><td><?= e(trim($p['prenom'] . ' ' . $p['nom'])) ?></td><td><?= e($p['titre']) ?></td><td class="num"><?= badge_echeance($p) ?></td></tr>
<?php endforeach; ?>
		</tbody></table></div>
<?php endif; ?>
	</section>

	<section class="carte">
		<div class="carte-titre"><h2>Comptes lecteurs à valider</h2><a class="lien" href="<?= e(admin_url('lecteurs', ['filtre' => 'a_valider'])) ?>">Voir</a></div>
<?php if (!$inscriptions): ?>
		<p class="doux">Aucune demande en attente.</p>
<?php else: ?>
		<div class="tableau-wrap"><table class="tableau"><tbody>
<?php foreach ($inscriptions as $l): ?>
			<tr><td><a href="<?= e(admin_url('lecteurs/modifier/' . $l['id'])) ?>"><?= e(nom_complet($l)) ?></a><br><small><?= e($l['email']) ?></small></td>
				<td><?= e(LIBELLES_CATEGORIE[$l['categorie']] ?? '') ?><?= $l['matricule'] ? '<br><small>' . e($l['matricule']) . '</small>' : '' ?></td>
				<td class="actions"><?= bouton_action(admin_url('lecteurs/activer/' . $l['id']), '<i class="fa-solid fa-circle-check"></i> Valider', 'btn vert petit') ?></td></tr>
<?php endforeach; ?>
		</tbody></table></div>
<?php endif; ?>
	</section>

	<section class="carte">
		<div class="carte-titre"><h2>Messages non lus</h2><a class="lien" href="<?= e(admin_url('messages')) ?>">Boîte de réception</a></div>
<?php if (!$messages): ?>
		<p class="doux">Aucun nouveau message.</p>
<?php else: ?>
		<div class="tableau-wrap"><table class="tableau"><tbody>
<?php foreach ($messages as $m): ?>
			<tr><td><a href="<?= e(admin_url('messages/voir/' . $m['id'])) ?>"><strong><?= e($m['sujet']) ?></strong></a><br><small><?= e($m['nom']) ?> · <?= e(date_fr($m['recu_le'], true)) ?></small></td></tr>
<?php endforeach; ?>
		</tbody></table></div>
<?php endif; ?>
	</section>

	<section class="carte">
		<div class="carte-titre"><h2>Les plus empruntés (12 mois)</h2></div>
<?php if (!$populaires): ?>
		<p class="doux">Pas encore de prêts enregistrés.</p>
<?php else: ?>
		<div class="tableau-wrap"><table class="tableau"><tbody>
<?php foreach ($populaires as $o): ?>
			<tr><td><a href="<?= e(admin_url('ouvrages/modifier/' . $o['id'])) ?>"><?= e($o['titre']) ?></a></td><td class="num"><?= e(pluriel((int) $o['nb'], 'prêt')) ?></td></tr>
<?php endforeach; ?>
		</tbody></table></div>
<?php endif; ?>
	</section>
</div>
