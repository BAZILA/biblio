<?php
/** Aperçu d'un import avant confirmation. @var string $quoi, $fichier @var array $analyse */
$compte = array_count_values(array_column($analyse, 'statut'));
$badges = ['nouveau' => ['vert', 'À importer'], 'maj' => ['bleu', 'Correction'], 'ignore' => ['', 'Ignoré'], 'erreur' => ['rouge', 'Erreur']];
$etudiants = $quoi === 'etudiants';
$aEnregistrer = ($compte['nouveau'] ?? 0) + ($compte['maj'] ?? 0);
// Liste des étudiants : les erreurs d'abord, puis un échantillon (le fichier peut compter des milliers de lignes)
$affichees = $analyse;
$limite = 300;
if ($etudiants && count($analyse) > $limite) {
    $erreurs = array_filter($analyse, fn($l) => $l['statut'] === 'erreur');
    $affichees = array_merge($erreurs, array_slice(array_filter($analyse, fn($l) => $l['statut'] !== 'erreur'), 0, max(50, $limite - count($erreurs))));
}
$libelles = ['auteurs' => 'auteurs', 'ouvrages' => 'ouvrages', 'etudiants' => 'étudiants'];
?>
<div class="fil"><a href="<?= e(admin_url('import', ['quoi' => $quoi])) ?>">Importer</a> ›</div>
<div class="entete">
	<h1>Aperçu : <?= e($fichier) ?></h1>
</div>

<div class="stats">
	<div class="stat"><div class="valeur"><?= $compte['nouveau'] ?? 0 ?></div><div class="libelle"><?= $libelles[$quoi] ?> à <?= $etudiants ? 'ajouter' : 'importer' ?></div></div>
<?php if ($etudiants): ?>
	<div class="stat"><div class="valeur"><?= $compte['maj'] ?? 0 ?></div><div class="libelle">fiches à corriger</div></div>
<?php endif; ?>
	<div class="stat"><div class="valeur"><?= $compte['ignore'] ?? 0 ?></div><div class="libelle">ignorés (<?= $etudiants ? 'déjà à jour' : 'déjà présents' ?> ou en double)</div></div>
	<div class="stat<?= !empty($compte['erreur']) ? ' alerte' : '' ?>"><div class="valeur"><?= $compte['erreur'] ?? 0 ?></div><div class="libelle">lignes en erreur (non importées)</div></div>
</div>

<?php if (count($affichees) < count($analyse)): ?>
<p class="doux">Le fichier compte <?= count($analyse) ?> lignes : <?= !empty($compte['erreur']) ? 'toutes les lignes en erreur et ' : '' ?>un échantillon des autres sont affichés.</p>
<?php endif; ?>
<div class="carte" style="padding:0">
	<div class="tableau-wrap"><table class="tableau">
		<thead><tr><th>Ligne</th><th>État</th>
			<?php if ($quoi === 'auteurs'): ?><th>Nom</th>
			<?php elseif ($etudiants): ?><th>N° de suivi</th><th>Nom et prénoms</th><th>Naissance</th>
			<?php else: ?><th>Titre</th><th>Auteurs</th><th>Domaine</th><th>Type</th><th>Cote</th><th class="num">Ex.</th><?php endif; ?>
			<th>Remarque</th></tr></thead>
		<tbody>
<?php foreach ($affichees as $l): $b = $badges[$l['statut']]; ?>
			<tr>
				<td class="num"><?= (int) $l['ligne'] ?></td>
				<td><span class="badge <?= $b[0] ?>"><?= e($b[1]) ?></span></td>
<?php if ($quoi === 'auteurs'): ?>
				<td><?= e($l['nom']) ?></td>
<?php elseif ($etudiants): ?>
				<td><code><?= e($l['numero_suivi']) ?></code></td>
				<td><strong><?= e($l['nom']) ?></strong> <?= e($l['prenom']) ?></td>
				<td><?= $l['date_naissance'] !== '' ? e(date_fr($l['date_naissance'])) : '' ?><?= $l['lieu_naissance'] !== '' ? ' à ' . e($l['lieu_naissance']) : '' ?></td>
<?php else: ?>
				<td><strong><?= e($l['titre']) ?></strong></td>
				<td><?= e(implode(', ', $l['auteurs'])) ?></td>
				<td><?= e($l['domaine']) ?></td>
				<td><?= e($l['type']) ?></td>
				<td><code><?= e($l['cote']) ?></code></td>
				<td class="num"><?= (int) $l['exemplaires'] ?></td>
<?php endif; ?>
				<td class="doux"><?= e($l['message']) ?></td>
			</tr>
<?php endforeach; ?>
		</tbody>
	</table></div>
</div>

<form method="post" action="<?= e(admin_url('import/confirmer')) ?>" class="barre-actions" style="margin-top:16px;flex-wrap:wrap">
	<?= csrf_field() ?>
	<span class="doux">Seules les lignes « À importer »<?= $etudiants ? ' et « Correction » seront enregistrées. Les étudiants absents du fichier restent dans la liste.' : ' seront enregistrées.' ?></span>
	<a class="btn secondaire" href="<?= e(admin_url('import', ['quoi' => $quoi])) ?>">Annuler</a>
	<button class="btn" type="submit"<?= $aEnregistrer || $etudiants ? '' : ' disabled' ?>><i class="fa-solid fa-circle-check"></i> <?= $etudiants ? 'Enregistrer ' . $aEnregistrer . ' ligne(s)' : 'Importer ' . $aEnregistrer . ' ligne(s)' ?></button>
</form>
