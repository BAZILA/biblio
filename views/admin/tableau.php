<?php
/**
 * Tableau de bord personnalisable : les blocs affichés et leur ordre sont choisis par chaque compte (« Personnaliser »).
 * @var array $blocs  [clé => [libellé, icône, permissions, taille, description, visible]] @var array $affiches @var array|null $role
 */
$moi = user();
// Données des blocs : présentes seulement pour les blocs affichés
$stats ??= []; $aRetirer ??= []; $enAttente ??= 0; $retards ??= []; $nbRetards ??= 0; $demandes ??= []; $echeances ??= [];
$populaires ??= []; $inscriptions ??= []; $messages ??= []; $actus ??= []; $securite ??= []; $nbSecurite ??= 0; $courbe ??= null;
$jours = function (string $date): int {
    return (int) round((strtotime($date) - strtotime(aujourdhui())) / 86400);
};
$lecteur = fn(array $l): string => '<a href="' . e(admin_url('lecteurs/voir/' . $l['lecteur_id'])) . '"><strong>' . e(trim($l['prenom'] . ' ' . $l['nom'])) . '</strong></a>'
    . ($l['matricule'] ? '<br><small class="doux">' . e($l['matricule']) . '</small>' : '');
$ouvrage = fn(array $o, string $code = ''): string => '<div class="cellule-ouvrage"><img class="vignette" src="' . e(couverture($o['image'] ?? '')) . '" alt=""><span>' . e($o['titre'])
    . ($code !== '' ? '<br><small class="doux">Exemplaire <code>' . e($code) . '</code></small>' : '') . '</span></div>';
$joursSemaine = ['dimanche', 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'];
$moisLongs = ['', 'janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];
$aujourdhui = $joursSemaine[(int) date('w')] . ' ' . (int) date('j') . ' ' . $moisLongs[(int) date('n')] . ' ' . date('Y');
$titreBloc = fn(string $cle, string $suite = '', string $lien = '') => '<div class="carte-titre"><h2><i class="fa-solid ' . $blocs[$cle][1] . '"></i> ' . e($blocs[$cle][0]) . $suite . '</h2>' . $lien . '</div>';

/** Contenu de chaque bloc. */
$rendus = [];

$rendus['raccourcis'] = function () {
    $liens = [
        [['guichet.voir'], 'guichet', 'fa-right-left', 'Guichet prêt / retour'],
        [['lecteurs.ajouter'], 'lecteurs/nouveau', 'fa-user-plus', 'Nouveau lecteur'],
        [['ouvrages.ajouter'], 'ouvrages/nouveau', 'fa-book-medical', 'Nouvel ouvrage'],
        [['prets.voir'], 'prets', 'fa-book-open', 'Prêts en cours'],
        [['etudiants.voir'], 'etudiants', 'fa-id-card', 'Étudiants de l’université'],
        [['actualites.ajouter'], 'actualites/nouveau', 'fa-newspaper', 'Nouvelle actualité'],
        [['slides.voir'], 'slides', 'fa-images', 'Diaporama d’accueil'],
        [['import.ouvrages', 'import.auteurs', 'import.etudiants'], 'import', 'fa-file-import', 'Importer un fichier'],
        [['rapports.voir'], 'rapports', 'fa-chart-column', 'Statistiques et rapports'],
        [['audit.voir'], 'audit', 'fa-clipboard-list', 'Journal d’audit'],
        [['reglages.voir'], 'reglages', 'fa-sliders', 'Réglages'],
    ];
    $html = '';
    foreach ($liens as [$perms, $chemin, $icone, $libelle]) {
        if (peut(...$perms)) {
            $html .= '<a class="raccourci" href="' . e(admin_url($chemin)) . '"><i class="fa-solid ' . $icone . '"></i><span>' . e($libelle) . '</span></a>';
        }
    }
    return $html ? '<div class="raccourcis">' . $html . '</div>' : '';
};

$rendus['chiffres'] = function () use ($stats) {
    ob_start(); ?>
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
<?php if (peut('actualites.voir')): ?>
	<a class="stat" href="<?= e(admin_url('actualites')) ?>"><i class="fa-solid fa-newspaper"></i><div class="valeur"><?= $stats['actualites'] ?></div><div class="libelle">actualités en ligne</div></a>
<?php endif; ?>
</div>
<?php
    return ob_get_clean();
};

$rendus['reservations'] = function () use ($aRetirer, $enAttente, $titreBloc, $jours, $lecteur, $ouvrage) {
    ob_start(); ?>
<section class="carte bloc-large">
	<?= $titreBloc('reservations', ' <span class="compteur">' . count($aRetirer) . '</span>', '<a class="lien" href="' . e(admin_url('reservations')) . '">Toutes les réservations' . ($enAttente ? ' · ' . e(pluriel($enAttente, 'en attente', 'en attente')) : '') . '</a>') ?>
<?php if (!$aRetirer): ?>
	<p class="rien"><i class="fa-solid fa-circle-check"></i> Aucun exemplaire à mettre de côté.</p>
<?php else: ?>
	<div class="tableau-wrap"><table class="tableau tableau-large">
		<thead><tr><th>Lecteur</th><th>Ouvrage</th><th>Exemplaire</th><th>À retirer avant le</th><th></th></tr></thead>
		<tbody>
<?php foreach ($aRetirer as $r): $j = $jours($r['expire_le']); ?>
			<tr><td><?= $lecteur($r) ?></td><td><?= $ouvrage($r) ?></td><td><code><?= e($r['exemplaire_code']) ?></code></td>
				<td><?= e(date_fr($r['expire_le'])) ?><br><span class="badge <?= $j <= 0 ? 'rouge' : ($j === 1 ? 'orange' : '') ?>"><?= $j < 0 ? 'expirée' : ($j === 0 ? 'dernier jour' : 'encore ' . pluriel($j, 'jour')) ?></span></td>
				<td class="actions"><?= bouton_remettre($r) ?></td></tr>
<?php endforeach; ?>
		</tbody>
	</table></div>
<?php endif; ?>
</section>
<?php
    return ob_get_clean();
};

$rendus['retards'] = function () use ($retards, $nbRetards, $titreBloc, $lecteur, $ouvrage) {
    ob_start(); ?>
<section class="carte bloc-large">
	<?= $titreBloc('retards', ' <span class="compteur' . ($retards ? ' rouge' : '') . '">' . $nbRetards . '</span>', '<a class="lien" href="' . e(admin_url('prets', ['filtre' => 'retard'])) . '">Tous les retards</a>') ?>
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
<?php
    return ob_get_clean();
};

$rendus['demandes'] = function () use ($demandes, $titreBloc, $lecteur) {
    ob_start(); ?>
<section class="carte bloc-large">
	<?= $titreBloc('demandes', ' <span class="compteur' . ($demandes ? ' orange' : '') . '">' . count($demandes) . '</span>', '<a class="lien" href="' . e(admin_url('prets/demandes')) . '">Traiter les demandes</a>') ?>
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
<?php
    return ob_get_clean();
};

$rendus['echeances'] = function () use ($echeances, $titreBloc) {
    ob_start(); ?>
<section class="carte">
	<?= $titreBloc('echeances', ' <span class="compteur">' . count($echeances) . '</span>') ?>
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
<?php
    return ob_get_clean();
};

$rendus['populaires'] = function () use ($populaires, $titreBloc) {
    ob_start(); ?>
<section class="carte">
	<?= $titreBloc('populaires', ' <small class="doux">(12 mois)</small>') ?>
<?php if (!$populaires): ?>
	<p class="rien">Pas encore de prêts enregistrés.</p>
<?php else: ?>
	<ul class="liste-simple">
<?php foreach ($populaires as $o): ?>
		<li><?= peut('ouvrages.voir') ? '<a href="' . e(admin_url('ouvrages/modifier/' . $o['id'])) . '">' . e($o['titre']) . '</a>' : e($o['titre']) ?><span class="badge"><?= e(pluriel((int) $o['nb'], 'prêt')) ?></span></li>
<?php endforeach; ?>
	</ul>
<?php endif; ?>
</section>
<?php
    return ob_get_clean();
};

$rendus['inscriptions'] = function () use ($inscriptions, $titreBloc) {
    ob_start(); ?>
<section class="carte">
	<?= $titreBloc('inscriptions', ' <span class="compteur">' . count($inscriptions) . '</span>', '<a class="lien" href="' . e(admin_url('lecteurs', ['filtre' => 'a_valider'])) . '">Voir</a>') ?>
<?php if (!$inscriptions): ?>
	<p class="rien"><i class="fa-solid fa-circle-check"></i> Aucune demande en attente.</p>
<?php else: ?>
	<ul class="liste-simple">
<?php foreach ($inscriptions as $l): ?>
		<li><span><a href="<?= e(admin_url('lecteurs/voir/' . $l['id'])) ?>"><strong><?= e(nom_complet($l)) ?></strong></a><br><small class="doux"><?= e(LIBELLES_CATEGORIE[$l['categorie']] ?? '') ?><?= $l['matricule'] ? ' · ' . e($l['matricule']) : '' ?> · <?= e((string) $l['email']) ?></small></span>
			<?= peut('lecteurs.valider') ? bouton_action(admin_url('lecteurs/activer/' . $l['id']), '<i class="fa-solid fa-circle-check"></i> Valider', 'btn vert petit') : '' ?></li>
<?php endforeach; ?>
	</ul>
<?php endif; ?>
</section>
<?php
    return ob_get_clean();
};

$rendus['messages'] = function () use ($messages, $titreBloc) {
    ob_start(); ?>
<section class="carte">
	<?= $titreBloc('messages', ' <span class="compteur">' . count($messages) . '</span>', '<a class="lien" href="' . e(admin_url('messages')) . '">Boîte de réception</a>') ?>
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
<?php
    return ob_get_clean();
};

$rendus['actualites'] = function () use ($actus, $titreBloc) {
    ob_start(); ?>
<section class="carte">
	<?= $titreBloc('actualites', '', '<a class="lien" href="' . e(admin_url('actualites')) . '">Gérer</a>') ?>
	<p class="pastilles">
		<span class="badge <?= $actus['diaporama'] ? 'vert' : '' ?>"><i class="fa-solid fa-images"></i> <?= $actus['diaporama'] ?> au diaporama</span>
		<?php if ($actus['brouillons']): ?><a class="badge orange" href="<?= e(admin_url('actualites', ['filtre' => 'brouillons'])) ?>"><?= e(pluriel($actus['brouillons'], 'brouillon')) ?></a><?php endif; ?>
		<?php if ($actus['programmees']): ?><span class="badge bleu"><?= e(pluriel($actus['programmees'], 'publication programmée', 'publications programmées')) ?></span><?php endif; ?>
	</p>
<?php if ($actus['avenir']): ?>
	<ul class="liste-simple">
<?php foreach ($actus['avenir'] as $ev): ?>
		<li><span><a href="<?= e(admin_url('actualites/modifier/' . $ev['id'])) ?>"><strong><?= e($ev['titre']) ?></strong></a><br><small class="doux"><?= e(date_evenement($ev)) ?><?= $ev['lieu'] !== '' ? ' · ' . e($ev['lieu']) : '' ?></small></span><span class="badge orange">Évènement</span></li>
<?php endforeach; ?>
	</ul>
<?php elseif ($actus['dernieres']): ?>
	<ul class="liste-simple">
<?php foreach ($actus['dernieres'] as $a): ?>
		<li><span><a href="<?= e(admin_url('actualites/modifier/' . $a['id'])) ?>"><strong><?= e($a['titre']) ?></strong></a><br><small class="doux"><?= e(date_courte($a['date_publication'])) ?></small></span></li>
<?php endforeach; ?>
	</ul>
<?php else: ?>
	<p class="rien">Aucune actualité publiée pour l’instant.</p>
<?php endif; ?>
<?php if (peut('actualites.ajouter')): ?>
	<p style="margin:12px 0 0"><a class="btn secondaire petit" href="<?= e(admin_url('actualites/nouveau')) ?>"><i class="fa-solid fa-plus"></i> Nouvelle actualité</a></p>
<?php endif; ?>
</section>
<?php
    return ob_get_clean();
};

$rendus['securite'] = function () use ($securite, $nbSecurite, $titreBloc) {
    ob_start(); ?>
<section class="carte">
	<?= $titreBloc('securite', ' <span class="compteur' . ($nbSecurite ? ' rouge' : '') . '">' . $nbSecurite . '</span>', '<a class="lien" href="' . e(admin_url('audit', ['categorie' => 'securite'])) . '">Journal d’audit</a>') ?>
<?php if (!$securite): ?>
	<p class="rien"><i class="fa-solid fa-circle-check"></i> Aucune connexion refusée ni bloquée depuis 24 heures.</p>
<?php else: ?>
	<ul class="liste-simple">
<?php foreach ($securite as $s): ?>
		<li><span><strong><?= e($s['resultat'] ?: $s['action']) ?></strong><br><small class="doux"><?= e(date_fr($s['le'], true)) ?> · IP <?= e($s['ip']) ?></small></span></li>
<?php endforeach; ?>
	</ul>
<?php endif; ?>
</section>
<?php
    return ob_get_clean();
};

$rendus['courbe'] = function () use ($courbe, $titreBloc) {
    ob_start(); ?>
<section class="carte bloc-large">
	<?= $titreBloc('courbe', '', peut('rapports.voir') ? '<a class="lien" href="' . e(admin_url('rapports', ['du' => date('Y-m-d', strtotime('-12 months +1 day')), 'au' => date('Y-m-d')])) . '">Statistiques détaillées</a>' : '') ?>
	<?= render(ROOT_DIR . '/views/admin/_courbe.php', ['titre' => 'Prêts et retours par mois sur les 12 derniers mois', 'mois' => $courbe['mois'], 'series' => [
		['nom' => 'Prêts', 'couleur' => '#3a5bd0', 'valeurs' => $courbe['prets']],
		['nom' => 'Retours', 'couleur' => '#009c30', 'valeurs' => $courbe['retours']],
	]]) ?>
</section>
<?php
    return ob_get_clean();
};
?>
<div class="entete entete-tableau">
	<div>
		<h1>Bonjour <?= e($moi['prenom'] ?: $moi['nom']) ?></h1>
		<p class="doux sous-titre"><?= e(ucfirst($aujourdhui)) ?><?= $role ? ' · ' . e($role['systeme'] ? 'Système' : $role['nom']) : '' ?></p>
	</div>
	<div class="actions">
		<button type="button" class="btn secondaire" data-modale="#modale-tableau"><i class="fa-solid fa-table-columns"></i> Personnaliser</button>
	</div>
</div>

<?php if (is_systeme() && is_file(ROOT_DIR . '/install/SYSTEME.txt')): ?>
<div class="flash info">Le fichier <code>install/SYSTEME.txt</code> contient encore le mot de passe initial du compte Système : changez-le dans <a href="<?= e(admin_url('mon-compte')) ?>">Mon compte</a>, puis supprimez le fichier.</div>
<?php endif; ?>
<?php if (is_admin() && is_file(ROOT_DIR . '/install/IDENTIFIANTS.txt')): ?>
<div class="flash info">Le fichier <code>install/IDENTIFIANTS.txt</code> contient encore le mot de passe initial de l’administrateur.
	Changez ce mot de passe dans <a href="<?= e(admin_url('mon-compte')) ?>">Mon compte</a>, puis supprimez le fichier.</div>
<?php endif; ?>

<?php if (!$affiches): ?>
<div class="carte vide">Tous les blocs sont masqués. Cliquez sur « Personnaliser » pour en afficher.</div>
<?php endif; ?>
<?php
// Les blocs « demi » qui se suivent sont rangés deux par deux ; les blocs « large » occupent toute la largeur
$groupe = [];
$vider = function () use (&$groupe) {
    if ($groupe) {
        echo '<div class="grille grille-2 bloc-large">' . implode('', $groupe) . '</div>';
        $groupe = [];
    }
};
foreach ($affiches as $cle) {
    $html = isset($rendus[$cle]) ? $rendus[$cle]() : '';
    if ($html === '') {
        continue;
    }
    if ($blocs[$cle][3] === 'demi') {
        $groupe[] = $html;
    } else {
        $vider();
        echo $html;
    }
}
$vider();
?>

<dialog class="modale" id="modale-tableau" aria-labelledby="titre-tableau">
	<form method="post" action="<?= e(admin_url('tableau/personnaliser')) ?>">
		<?= csrf_field() ?>
		<header><h2 id="titre-tableau"><i class="fa-solid fa-table-columns"></i> Personnaliser mon tableau de bord</h2><button type="button" class="fermer" data-fermer aria-label="Fermer">&times;</button></header>
		<div class="modale-corps">
			<p class="doux">Cochez les blocs à afficher et rangez-les avec les flèches. Seuls les blocs permis par votre rôle sont proposés. Ce réglage ne concerne que votre compte.</p>
			<ul class="choix-blocs" data-ordonnable>
<?php foreach ($blocs as $cle => [$libelle, $icone, , $taille, $description, $visible]): ?>
				<li>
					<input type="hidden" name="ordre[]" value="<?= e($cle) ?>">
					<label class="case"><input type="checkbox" name="visibles[]" value="<?= e($cle) ?>"<?= $visible ? ' checked' : '' ?>>
						<i class="fa-solid <?= $icone ?>"></i> <span><strong><?= e($libelle) ?></strong><small class="doux"><?= e($description) ?><?= $taille === 'demi' ? ' · demi-largeur' : '' ?></small></span></label>
					<span class="deplacer">
						<button type="button" class="btn secondaire petit" data-monter aria-label="Monter « <?= e($libelle) ?> »"><i class="fa-solid fa-arrow-up"></i></button>
						<button type="button" class="btn secondaire petit" data-descendre aria-label="Descendre « <?= e($libelle) ?> »"><i class="fa-solid fa-arrow-down"></i></button>
					</span>
				</li>
<?php endforeach; ?>
			</ul>
		</div>
		<footer>
			<button type="submit" name="reinitialiser" value="1" class="btn secondaire" formnovalidate><i class="fa-solid fa-rotate-left"></i> Affichage par défaut</button>
			<button type="button" class="btn secondaire" data-fermer>Annuler</button>
			<button type="submit" class="btn"><i class="fa-solid fa-circle-check"></i> Enregistrer</button>
		</footer>
	</form>
</dialog>
