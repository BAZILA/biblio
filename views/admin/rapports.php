<?php
/** @var string $du, $au, $faculte @var int $site @var array $s, $sites, $facultes */
$barres = function (array $lignes, ?callable $libelle = null): string {
    if (!$lignes) {
        return '<p class="rien">Aucune donnée sur la période.</p>';
    }
    $max = max(1, max(array_column($lignes, 'nb')));
    $html = '<ul class="barres-stat">';
    foreach ($lignes as $l) {
        $html .= '<li><span class="lib" title="' . e($libelle ? $libelle($l['cle']) : $l['cle']) . '">' . e($libelle ? $libelle($l['cle']) : $l['cle']) . '</span>'
            . '<span class="piste"><span style="width:' . round(100 * $l['nb'] / $max) . '%"></span></span><strong>' . (int) $l['nb'] . '</strong></li>';
    }
    return $html . '</ul>';
};
$pc = fn($v) => $v === null ? '—' : $v . ' %';
$params = ['du' => $du, 'au' => $au] + ($site ? ['site' => $site] : []) + ($faculte !== '' ? ['faculte' => $faculte] : []);
$filtres = ($site ? ['site' => $site] : []) + ($faculte !== '' ? ['faculte' => $faculte] : []);
?>
<div class="entete">
	<h1>Statistiques et rapports</h1>
</div>

<form class="carte filtre-periode" method="get">
	<div class="champ"><label for="du">Du</label><input type="date" id="du" name="du" value="<?= e($du) ?>" max="<?= date('Y-m-d') ?>"></div>
	<div class="champ"><label for="au">Au</label><input type="date" id="au" name="au" value="<?= e($au) ?>"></div>
<?php if ($sites): ?>
	<div class="champ"><label for="site">Centre</label>
		<select id="site" name="site">
			<option value="">Tous les centres</option>
<?php foreach ($sites as $sid => $snom): ?>
			<option value="<?= (int) $sid ?>"<?= $site === (int) $sid ? ' selected' : '' ?>><?= e($snom) ?></option>
<?php endforeach; ?>
		</select></div>
<?php endif; ?>
<?php if ($facultes): ?>
	<div class="champ"><label for="faculte">Faculté</label>
		<select id="faculte" name="faculte">
			<option value="">Toutes les facultés</option>
<?php foreach ($facultes as $fac): ?>
			<option value="<?= e($fac) ?>"<?= $faculte === $fac ? ' selected' : '' ?>><?= e($fac) ?></option>
<?php endforeach; ?>
		</select></div>
<?php endif; ?>
	<button class="btn" type="submit"><i class="fa-solid fa-rotate"></i> Actualiser</button>
	<div class="periodes-rapides">
<?php foreach (periodes_rapides() as $lib => [$d, $a]): ?>
		<a class="<?= $d === $du && $a === $au ? 'actif' : '' ?>" href="<?= e(admin_url('rapports', ['du' => $d, 'au' => $a] + $filtres)) ?>"><?= e($lib) ?></a>
<?php endforeach; ?>
	</div>
</form>

<h2 class="titre-zone"><i class="fa-solid fa-calendar"></i> Période <?= e(libelle_periode($du, $au)) ?><?php
	if ($site || $faculte !== ''): ?> <small class="doux">— <?= e(trim(($site ? nom_site($site, true) : '') . ($site && $faculte !== '' ? ' · ' : '') . $faculte)) ?></small><?php endif; ?></h2>
<div class="stats">
	<div class="stat"><i class="fa-solid fa-book-open"></i><div class="valeur"><?= $s['prets'] ?></div><div class="libelle">prêts enregistrés</div></div>
	<div class="stat"><i class="fa-solid fa-rotate-left"></i><div class="valeur"><?= $s['retours'] ?></div><div class="libelle">retours · <?= $pc($s['taux_a_temps']) ?> à temps</div></div>
	<div class="stat"><i class="fa-solid fa-users"></i><div class="valeur"><?= $s['emprunteurs'] ?></div><div class="libelle">lecteurs ont emprunté · <?= $s['nouveaux_lecteurs'] ?> nouveaux</div></div>
	<div class="stat"><i class="fa-solid fa-hourglass-half"></i><div class="valeur"><?= $s['duree_moyenne'] === null ? '—' : str_replace('.', ',', $s['duree_moyenne']) . ' j' ?></div><div class="libelle">durée moyenne d’emprunt</div></div>
	<div class="stat"><i class="fa-solid fa-bookmark"></i><div class="valeur"><?= $s['reservations'] ?></div><div class="libelle">réservations · <?= $s['reservations_honorees'] ?> honorées</div></div>
	<div class="stat"><i class="fa-solid fa-calendar-plus"></i><div class="valeur"><?= $s['demandes'] ?></div><div class="libelle">prolongations demandées · <?= $s['demandes_acceptees'] ?> acceptées, <?= $s['demandes_refusees'] ?> refusées</div></div>
</div>

<?php $pal = $s['palmares']; if (array_filter($pal)): ?>
<h2 class="titre-zone"><i class="fa-solid fa-trophy"></i> Palmarès de la période</h2>
<div class="palmares">
<?php if ($pal['lecteur']): ?>
	<div class="palmares-tuile or"><i class="fa-solid fa-medal"></i><span class="intitule">Meilleur lecteur</span>
		<strong><?= e($pal['lecteur']['cle']) ?></strong><span class="detail"><?= (int) $pal['lecteur']['nb'] ?> prêts · <?= (int) $pal['lecteur']['titres'] ?> titres différents<?= $pal['lecteur']['faculte'] !== '' ? '<br>' . e($pal['lecteur']['faculte']) : '' ?></span></div>
<?php endif; ?>
<?php if ($pal['faculte']): ?>
	<div class="palmares-tuile bleu"><i class="fa-solid fa-building-columns"></i><span class="intitule">Faculté la plus active</span>
		<strong><?= e($pal['faculte']['cle']) ?></strong><span class="detail"><?= (int) $pal['faculte']['nb'] ?> prêts par <?= (int) $pal['faculte']['lecteurs'] ?> lecteurs</span></div>
<?php endif; ?>
<?php if ($pal['parcours']): ?>
	<div class="palmares-tuile vert"><i class="fa-solid fa-route"></i><span class="intitule">Parcours le plus actif</span>
		<strong><?= e($pal['parcours']['cle']) ?></strong><span class="detail"><?= (int) $pal['parcours']['nb'] ?> prêts</span></div>
<?php endif; ?>
<?php if ($pal['ouvrage']): ?>
	<div class="palmares-tuile or"><i class="fa-solid fa-book"></i><span class="intitule">Ouvrage le plus emprunté</span>
		<strong><?= e($pal['ouvrage']['cle']) ?></strong><span class="detail"><?= (int) $pal['ouvrage']['nb'] ?> prêts</span></div>
<?php endif; ?>
<?php if ($pal['domaine']): ?>
	<div class="palmares-tuile bleu"><i class="fa-solid fa-layer-group"></i><span class="intitule">Domaine le plus consulté</span>
		<strong><?= e($pal['domaine']['cle']) ?></strong><span class="detail"><?= (int) $pal['domaine']['nb'] ?> prêts</span></div>
<?php endif; ?>
</div>
<?php endif; ?>

<section class="carte" style="margin-top:20px">
	<h2><i class="fa-solid fa-chart-line"></i> Prêts et retours par mois</h2>
	<?= render(ROOT_DIR . '/views/admin/_courbe.php', ['titre' => 'Prêts et retours par mois ' . libelle_periode($du, $au), 'mois' => $courbe['mois'], 'series' => [
		['nom' => 'Prêts', 'couleur' => '#3a5bd0', 'valeurs' => $courbe['prets']],
		['nom' => 'Retours', 'couleur' => '#009c30', 'valeurs' => $courbe['retours']],
	]]) ?>
</section>

<h2 class="titre-zone"><i class="fa-solid fa-clock"></i> Situation à ce jour</h2>
<div class="stats">
	<div class="stat"><i class="fa-solid fa-book-open-reader"></i><div class="valeur"><?= $s['en_cours'] ?></div><div class="libelle">prêts en cours</div></div>
	<div class="stat<?= $s['en_retard'] ? ' alerte' : '' ?>"><i class="fa-solid fa-triangle-exclamation"></i><div class="valeur"><?= $s['en_retard'] ?></div><div class="libelle">en retard</div></div>
	<div class="stat"><i class="fa-solid fa-barcode"></i><div class="valeur"><?= $s['disponibles'] ?> / <?= $s['exemplaires'] ?></div><div class="libelle">exemplaires disponibles</div></div>
	<div class="stat"><i class="fa-solid fa-chart-pie"></i><div class="valeur"><?= $pc($s['taux_occupation']) ?></div><div class="libelle">taux d’occupation</div></div>
	<div class="stat"><i class="fa-solid fa-users"></i><div class="valeur"><?= $s['lecteurs_actifs'] ?></div><div class="libelle">lecteurs actifs</div></div>
</div>

<h2 class="titre-zone"><i class="fa-solid fa-triangle-exclamation"></i> Lecteurs à relancer</h2>
<section class="carte">
<?php if (!$s['mauvais']): ?>
	<p class="rien">Aucun incident sur la période : tous les ouvrages ont été rendus à temps et en bon état.</p>
<?php else: ?>
	<p class="doux">Retards, ouvrages abîmés ou perdus sur la période. Le score pèse un ouvrage perdu 6, un ouvrage abîmé 3,
		un prêt encore en retard 2 et un retour tardif 1.</p>
	<div class="tableau-wrap"><table class="tableau">
		<thead><tr><th>Lecteur</th><th>Catégorie / faculté</th><th>Contact</th><th class="num">Prêts</th><th class="num">Retards</th>
			<th class="num">Jours</th><th class="num">Abîmés</th><th class="num">Perdus</th><th class="num">En retard</th><th class="num">Score</th></tr></thead>
		<tbody>
<?php foreach ($s['mauvais'] as $m): ?>
			<tr>
				<td><?php if (peut('lecteurs.voir')): ?><a href="<?= e(admin_url('lecteurs/voir/' . $m['id'])) ?>"><strong><?= e($m['cle']) ?></strong></a><?php else: ?><strong><?= e($m['cle']) ?></strong><?php endif; ?>
					<?= $m['matricule'] ? '<br><small class="doux">' . e($m['matricule']) . '</small>' : '' ?></td>
				<td><?= e(LIBELLES_CATEGORIE[$m['categorie']] ?? '—') ?><?= $m['faculte'] !== '' ? '<br><small class="doux">' . e($m['faculte']) . '</small>' : '' ?></td>
				<td><small><?= e($m['email'] ?: '—') ?><?= $m['telephone'] !== '' ? '<br>' . e($m['telephone']) : '' ?></small></td>
				<td class="num"><?= (int) $m['total'] ?></td>
				<td class="num"><?= (int) $m['retards'] ?></td>
				<td class="num"><?= (int) $m['jours'] ?></td>
				<td class="num"><?= (int) $m['abimes'] ? '<span class="badge orange">' . (int) $m['abimes'] . '</span>' : '0' ?></td>
				<td class="num"><?= (int) $m['perdus'] ? '<span class="badge rouge">' . (int) $m['perdus'] . '</span>' : '0' ?></td>
				<td class="num"><?= (int) $m['encours'] ? '<span class="badge orange">' . (int) $m['encours'] . '</span>' : '0' ?></td>
				<td class="num"><strong><?= (int) $m['score'] ?></strong></td>
			</tr>
<?php endforeach; ?>
		</tbody>
	</table></div>
	<p class="aide">Les huit premiers sont affichés ici ; le rapport PDF « Lecteurs à relancer » les reprend tous avec leurs coordonnées.</p>
<?php endif; ?>
</section>

<div class="grille grille-2" style="margin-top:20px">
	<section class="carte"><h2><i class="fa-solid fa-medal"></i> Meilleurs lecteurs</h2>
<?php if (!$s['top_lecteurs']): ?>
		<p class="rien">Aucun prêt sur la période.</p>
<?php else: ?>
		<ol class="classement">
<?php foreach ($s['top_lecteurs'] as $i => $l): ?>
			<li class="rang-<?= $i + 1 ?>"><span class="rang"><?= $i + 1 ?></span>
				<span class="qui"><?php if (peut('lecteurs.voir')): ?><a href="<?= e(admin_url('lecteurs/voir/' . $l['id'])) ?>"><strong><?= e($l['cle']) ?></strong></a><?php else: ?><strong><?= e($l['cle']) ?></strong><?php endif; ?>
					<small class="doux"><?= e(trim($l['faculte'] . ($l['parcours'] !== '' ? ' · ' . $l['parcours'] : '')) ?: (LIBELLES_CATEGORIE[$l['categorie']] ?? '')) ?></small></span>
				<span class="combien"><strong><?= (int) $l['nb'] ?></strong> prêts<br><small class="doux"><?= (int) $l['titres'] ?> titres</small></span></li>
<?php endforeach; ?>
		</ol>
<?php endif; ?>
	</section>
	<section class="carte"><h2><i class="fa-solid fa-building-columns"></i> Facultés qui empruntent le plus</h2><?= $barres($s['facultes']) ?>
		<p class="aide">Prêts des lecteurs rattachés à chaque faculté (étudiants et enseignants).</p></section>
	<section class="carte"><h2><i class="fa-solid fa-route"></i> Parcours qui empruntent le plus</h2><?= $barres($s['par_parcours']) ?></section>
	<section class="carte"><h2><i class="fa-solid fa-layer-group"></i> Prêts par domaine</h2><?= $barres($s['par_domaine']) ?></section>
	<section class="carte"><h2><i class="fa-solid fa-user-graduate"></i> Prêts par catégorie de lecteurs</h2><?= $barres($s['par_categorie']) ?></section>
	<section class="carte"><h2><i class="fa-solid fa-ranking-star"></i> Ouvrages les plus empruntés</h2><?= $barres($s['top_ouvrages']) ?></section>
	<section class="carte"><h2><i class="fa-solid fa-clipboard-check"></i> État des exemplaires au retour</h2><?= $barres($s['constats']) ?></section>
</div>

<h2 class="titre-zone"><i class="fa-solid fa-file-pdf"></i> Rapports à télécharger (PDF)</h2>
<?php if (!peut('rapports.exporter')): ?>
<p class="doux">Votre rôle ne permet pas de télécharger les rapports.</p>
<?php else: ?>
<p class="doux">Documents en noir et blanc, prêts à imprimer ; seul le logo de l’université est en couleur. Les rapports « période » utilisent les dates choisies ci-dessus.</p>
<div class="liste-rapports">
<?php foreach (RAPPORTS as $cle => [$titre, $description, $perms, $paysage, $avecPeriode]):
    if ($perms && !peut(...$perms)) continue; ?>
	<section class="carte rapport">
		<div>
			<h3><?= e($titre) ?></h3>
			<p class="doux"><?= e($description) ?></p>
			<small class="doux"><i class="fa-solid <?= $avecPeriode ? 'fa-calendar' : 'fa-clock' ?>"></i> <?= $avecPeriode ? 'Période ' . e(libelle_periode($du, $au)) : 'Situation à ce jour' ?> · <?= $paysage ? 'A4 paysage' : 'A4 portrait' ?></small>
		</div>
		<a class="btn" href="<?= e(admin_url('rapports/pdf', ['type' => $cle] + $params)) ?>"><i class="fa-solid fa-file-arrow-down"></i> PDF</a>
	</section>
<?php endforeach; ?>
</div>
<?php endif; ?>
