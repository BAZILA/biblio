<?php
/** @var string $du, $au @var array $s */
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
$params = ['du' => $du, 'au' => $au];
?>
<div class="entete">
	<h1>Statistiques et rapports</h1>
</div>

<form class="carte filtre-periode" method="get">
	<div class="champ"><label for="du">Du</label><input type="date" id="du" name="du" value="<?= e($du) ?>" max="<?= date('Y-m-d') ?>"></div>
	<div class="champ"><label for="au">Au</label><input type="date" id="au" name="au" value="<?= e($au) ?>"></div>
	<button class="btn" type="submit"><i class="fa-solid fa-rotate"></i> Actualiser</button>
	<div class="periodes-rapides">
<?php foreach (periodes_rapides() as $lib => [$d, $a]): ?>
		<a class="<?= $d === $du && $a === $au ? 'actif' : '' ?>" href="<?= e(admin_url('rapports', ['du' => $d, 'au' => $a])) ?>"><?= e($lib) ?></a>
<?php endforeach; ?>
	</div>
</form>

<h2 class="titre-zone"><i class="fa-solid fa-calendar"></i> Période <?= e(libelle_periode($du, $au)) ?></h2>
<div class="stats">
	<div class="stat"><i class="fa-solid fa-book-open"></i><div class="valeur"><?= $s['prets'] ?></div><div class="libelle">prêts enregistrés</div></div>
	<div class="stat"><i class="fa-solid fa-rotate-left"></i><div class="valeur"><?= $s['retours'] ?></div><div class="libelle">retours · <?= $pc($s['taux_a_temps']) ?> à temps</div></div>
	<div class="stat"><i class="fa-solid fa-users"></i><div class="valeur"><?= $s['emprunteurs'] ?></div><div class="libelle">lecteurs ont emprunté · <?= $s['nouveaux_lecteurs'] ?> nouveaux</div></div>
	<div class="stat"><i class="fa-solid fa-hourglass-half"></i><div class="valeur"><?= $s['duree_moyenne'] === null ? '—' : str_replace('.', ',', $s['duree_moyenne']) . ' j' ?></div><div class="libelle">durée moyenne d’emprunt</div></div>
	<div class="stat"><i class="fa-solid fa-bookmark"></i><div class="valeur"><?= $s['reservations'] ?></div><div class="libelle">réservations · <?= $s['reservations_honorees'] ?> honorées</div></div>
	<div class="stat"><i class="fa-solid fa-calendar-plus"></i><div class="valeur"><?= $s['demandes'] ?></div><div class="libelle">prolongations demandées · <?= $s['demandes_acceptees'] ?> acceptées, <?= $s['demandes_refusees'] ?> refusées</div></div>
</div>

<h2 class="titre-zone"><i class="fa-solid fa-clock"></i> Situation à ce jour</h2>
<div class="stats">
	<div class="stat"><i class="fa-solid fa-book-open-reader"></i><div class="valeur"><?= $s['en_cours'] ?></div><div class="libelle">prêts en cours</div></div>
	<div class="stat<?= $s['en_retard'] ? ' alerte' : '' ?>"><i class="fa-solid fa-triangle-exclamation"></i><div class="valeur"><?= $s['en_retard'] ?></div><div class="libelle">en retard</div></div>
	<div class="stat"><i class="fa-solid fa-barcode"></i><div class="valeur"><?= $s['disponibles'] ?> / <?= $s['exemplaires'] ?></div><div class="libelle">exemplaires disponibles</div></div>
	<div class="stat"><i class="fa-solid fa-chart-pie"></i><div class="valeur"><?= $pc($s['taux_occupation']) ?></div><div class="libelle">taux d’occupation</div></div>
	<div class="stat"><i class="fa-solid fa-users"></i><div class="valeur"><?= $s['lecteurs_actifs'] ?></div><div class="libelle">lecteurs actifs</div></div>
</div>

<div class="grille grille-2" style="margin-top:20px">
	<section class="carte"><h2><i class="fa-solid fa-chart-column"></i> Prêts par mois</h2><?= $barres($s['par_mois'], 'mois_fr') ?></section>
	<section class="carte"><h2><i class="fa-solid fa-layer-group"></i> Prêts par domaine</h2><?= $barres($s['par_domaine']) ?></section>
	<section class="carte"><h2><i class="fa-solid fa-user-graduate"></i> Prêts par catégorie de lecteurs</h2><?= $barres($s['par_categorie']) ?></section>
	<section class="carte"><h2><i class="fa-solid fa-building-columns"></i> Prêts des étudiants par faculté</h2><?= $barres($s['par_faculte']) ?></section>
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
