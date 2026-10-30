<?php /** @var string $filtre, $q @var array $liste, $pg */
$onglets = ['tous' => 'Tous', 'en_cours' => 'En cours', 'retard' => 'En retard', 'rendus' => 'Rendus'];
?>
<div class="entete">
	<h1>Prêts</h1>
	<div class="actions"><?php if (peut('guichet.voir')): ?><a class="btn" href="<?= e(admin_url('guichet')) ?>"><i class="fa-solid fa-right-left"></i> Guichet</a><?php endif; ?></div>
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
		<thead><tr><th>Lecteur</th><th>Ouvrage</th><th>Exemplaire</th><th>Emprunté le</th><th>Échéance</th><th>Prol.</th><th>Retour / constat</th></tr></thead>
		<tbody>
<?php foreach ($liste as $p): $enCours = $p['date_retour'] === null; ?>
			<tr class="<?= $enCours && $p['jours_retard'] > 0 ? 'retard' : '' ?>">
				<td><a href="<?= e(admin_url('lecteurs/voir/' . $p['lecteur_id'])) ?>"><?= e(trim($p['prenom'] . ' ' . $p['nom'])) ?></a><?= $p['matricule'] ? '<br><small>' . e($p['matricule']) . '</small>' : '' ?></td>
				<td><a href="<?= e(admin_url('ouvrages/modifier/' . $p['ouvrage_id'])) ?>"><?= e($p['titre']) ?></a></td>
				<td><code><?= e($p['exemplaire_code']) ?></code></td>
				<td><?= e(date_fr($p['date_pret'])) ?><?= $p['agent_id'] ? '<br><small class="doux">par ' . e(nom_acteur((int) $p['agent_id'])) . '</small>' : '' ?></td>
				<td><?= e(date_fr($p['date_retour_prevue'])) ?><br><?= badge_echeance($p) ?></td>
				<td class="num"><?= (int) $p['prolongations'] ?></td>
				<td class="actions">
<?php if ($enCours): ?>
					<?= bouton_prolonger($p) ?>
					<?= bouton_retour($p) ?>
<?php else: ?>
					<?= badge_constat($p) ?>
<?php endif; ?>
				</td>
			</tr>
<?php endforeach; ?>
		</tbody>
	</table></div>
<?php endif; ?>
</div>
<?= liens_pagination($pg) ?>
