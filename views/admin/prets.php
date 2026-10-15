<?php /** @var string $filtre, $q @var array $liste, $pg */
$onglets = ['en_cours' => 'En cours', 'retard' => 'En retard', 'rendus' => 'Rendus', 'tous' => 'Tous'];
?>
<div class="entete">
	<h1>Prêts</h1>
	<div class="actions"><a class="btn" href="<?= e(admin_url('guichet')) ?>"><i class="fa-solid fa-right-left"></i> Guichet</a></div>
</div>

<nav class="onglets">
<?php foreach ($onglets as $cle => $lib): ?>
	<a href="<?= e(admin_url('prets', ['filtre' => $cle] + ($q !== '' ? ['q' => $q] : []))) ?>" class="<?= $filtre === $cle ? 'actif' : '' ?>"><?= e($lib) ?></a>
<?php endforeach; ?>
</nav>

<form class="filtres" method="get">
	<input type="hidden" name="filtre" value="<?= e($filtre) ?>">
	<input type="search" name="q" value="<?= e($q) ?>" placeholder="Lecteur, matricule, titre, code…">
	<button class="btn secondaire" type="submit"><i class="fa-solid fa-magnifying-glass"></i> Filtrer</button>
	<span class="doux"><?= e(pluriel($pg['total'], 'prêt')) ?></span>
</form>

<div class="carte" style="padding:0">
<?php if (!$liste): ?>
	<div class="vide">Aucun prêt dans cette liste.</div>
<?php else: ?>
	<div class="tableau-wrap"><table class="tableau">
		<thead><tr><th>Lecteur</th><th>Ouvrage</th><th>Exemplaire</th><th>Emprunté le</th><th>Échéance</th><th>Prol.</th><th></th></tr></thead>
		<tbody>
<?php foreach ($liste as $p): $enCours = $p['date_retour'] === null; ?>
			<tr class="<?= $enCours && $p['jours_retard'] > 0 ? 'retard' : '' ?>">
				<td><a href="<?= e(admin_url('lecteurs/voir/' . $p['lecteur_id'])) ?>"><?= e(trim($p['prenom'] . ' ' . $p['nom'])) ?></a><?= $p['matricule'] ? '<br><small>' . e($p['matricule']) . '</small>' : '' ?></td>
				<td><a href="<?= e(admin_url('ouvrages/modifier/' . $p['ouvrage_id'])) ?>"><?= e($p['titre']) ?></a></td>
				<td><code><?= e($p['exemplaire_code']) ?></code></td>
				<td><?= e(date_fr($p['date_pret'])) ?></td>
				<td><?= e(date_fr($p['date_retour_prevue'])) ?><br><?= badge_echeance($p) ?></td>
				<td class="num"><?= (int) $p['prolongations'] ?></td>
				<td class="actions">
<?php if ($enCours): ?>
					<?= bouton_action(admin_url('prets/prolonger/' . $p['id']), 'Prolonger', 'btn secondaire petit', '', ['retour' => $_SERVER['REQUEST_URI']]) ?>
					<?= bouton_action(admin_url('prets/rendre/' . $p['id']), 'Retour', 'btn vert petit', 'Enregistrer le retour de « ' . $p['titre'] . ' » ?', ['retour' => $_SERVER['REQUEST_URI']]) ?>
<?php endif; ?>
				</td>
			</tr>
<?php endforeach; ?>
		</tbody>
	</table></div>
<?php endif; ?>
</div>
<?= liens_pagination($pg) ?>
