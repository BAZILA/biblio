<?php /** @var string $filtre, $q @var array $liste, $pg */
$onglets = ['tous' => 'Tous', 'actifs' => 'Actifs', 'a_valider' => 'À valider', 'retard' => 'Avec retard', 'inactifs' => 'Désactivés'];
?>
<div class="entete">
	<h1>Lecteurs</h1>
	<div class="actions"><?php if (peut('lecteurs.ajouter')): ?><a class="btn" href="<?= e(admin_url('lecteurs/nouveau')) ?>"><i class="fa-solid fa-user-plus"></i> Nouveau lecteur</a><?php endif; ?></div>
</div>

<nav class="onglets">
<?php foreach ($onglets as $cle => $lib): ?>
	<a href="<?= e(admin_url('lecteurs', ['filtre' => $cle] + ($q !== '' ? ['q' => $q] : []))) ?>" class="<?= $filtre === $cle ? 'actif' : '' ?>"><?= e($lib) ?></a>
<?php endforeach; ?>
</nav>

<form class="filtres" method="get">
	<input type="hidden" name="filtre" value="<?= e($filtre) ?>">
	<input type="search" name="q" value="<?= e($q) ?>" placeholder="Nom, e-mail, matricule…">
	<button class="btn secondaire" type="submit"><i class="fa-solid fa-magnifying-glass"></i> Rechercher</button>
	<span class="doux"><?= e(pluriel($pg['total'], 'lecteur')) ?></span>
</form>

<div class="carte" style="padding:0">
<?php if (!$liste): ?>
	<div class="vide">Aucun lecteur.</div>
<?php else: ?>
	<div class="tableau-wrap"><table class="tableau">
		<thead><tr><th>Nom</th><th>Catégorie</th><th>Matricule / n° de suivi</th><th>E-mail</th><th class="num">Prêts</th><th>Compte</th><th></th></tr></thead>
		<tbody>
<?php foreach ($liste as $l): ?>
			<tr>
				<td><a href="<?= e(admin_url('lecteurs/voir/' . $l['id'])) ?>"><strong><?= e($l['nom']) ?></strong> <?= e($l['prenom']) ?></a></td>
				<td><?= e(LIBELLES_CATEGORIE[$l['categorie']] ?? '—') ?></td>
				<td><?= e($l['matricule'] ?? '') ?></td>
				<td><?= e($l['email']) ?></td>
				<td class="num"><?= (int) $l['nb_prets'] ?><?= $l['nb_retards'] ? ' <span class="badge rouge">' . (int) $l['nb_retards'] . ' retard</span>' : '' ?></td>
				<td><?= $l['actif'] ? '<span class="badge vert">Actif</span>' : ($l['en_attente'] ? '<span class="badge orange">À valider</span>' : '<span class="badge">Désactivé</span>') ?></td>
				<td class="actions">
					<?php if (!$l['actif'] && peut('lecteurs.valider')): ?><?= bouton_action(admin_url('lecteurs/activer/' . $l['id']), $l['en_attente'] ? 'Valider' : 'Réactiver', 'btn vert petit', '', ['retour' => $_SERVER['REQUEST_URI']]) ?><?php endif; ?>
					<?php if (peut('guichet.voir')): ?><a class="btn secondaire petit" href="<?= e(admin_url('guichet', ['lecteur' => $l['id']])) ?>">Guichet</a><?php endif; ?>
				</td>
			</tr>
<?php endforeach; ?>
		</tbody>
	</table></div>
<?php endif; ?>
</div>
<?= liens_pagination($pg) ?>
