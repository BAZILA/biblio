<?php /** @var string $filtre, $q @var array $liste, $pg, $totaux */
$onglets = ['tous' => 'Tous', 'sans_fiche' => 'Pas encore lecteurs', 'guichet' => 'Fiche guichet (sans compte)', 'compte' => 'Compte en ligne',
    'desactives' => 'Désactivés' . ($totaux['desactives'] ? ' (' . (int) $totaux['desactives'] . ')' : '')];
$cocher = peut('etudiants.activer');
$guichet = peut('guichet.voir') && peut('prets.ajouter');
?>
<div class="entete">
	<h1>Étudiants de l’université</h1>
	<div class="actions"><?php if (peut('etudiants.ajouter')): ?><a class="btn secondaire" href="<?= e(admin_url('etudiants/nouveau')) ?>"><i class="fa-solid fa-user-plus"></i> Ajouter un étudiant</a><?php endif; ?>
		<?php if (peut('import.etudiants')): ?><a class="btn" href="<?= e(admin_url('import', ['quoi' => 'etudiants'])) ?>"><i class="fa-solid fa-file-import"></i> Charger / mettre à jour la liste</a><?php endif; ?></div>
</div>

<p class="doux">Liste fournie par la scolarité (n° de suivi, nom, prénoms, date et lieu de naissance, faculté) : <?= e(pluriel((int) $totaux['total'], 'étudiant')) ?>
	<?= $totaux['maj'] ? ' — dernière mise à jour le ' . e(date_fr($totaux['maj'], true)) : '' ?>.
	Un étudiant actif de cette liste crée seul son compte en ligne (identité vérifiée) et peut emprunter au guichet même sans compte.
	Un rechargement de la liste ne retire jamais personne ; un étudiant désactivé ne peut plus créer de compte, mais un compte déjà créé reste valable.</p>

<nav class="onglets">
<?php foreach ($onglets as $cle => $lib): ?>
	<a href="<?= e(admin_url('etudiants', ['filtre' => $cle] + ($q !== '' ? ['q' => $q] : []))) ?>" class="<?= $filtre === $cle ? 'actif' : '' ?>"><?= e($lib) ?></a>
<?php endforeach; ?>
</nav>

<form class="filtres" method="get">
	<input type="hidden" name="filtre" value="<?= e($filtre) ?>">
	<input type="search" name="q" value="<?= e($q) ?>" placeholder="Nom, prénom, n° de suivi, faculté…">
	<button class="btn secondaire" type="submit"><i class="fa-solid fa-magnifying-glass"></i> Rechercher</button>
	<span class="doux"><?= e(pluriel($pg['total'], 'étudiant')) ?></span>
</form>

<?php if ($cocher && $filtre === 'desactives' && $totaux['desactives']): ?>
<form method="post" action="<?= e(admin_url('etudiants/activation')) ?>" class="barre-actions" style="margin-bottom:12px" data-confirmer="Réactiver les <?= (int) $totaux['desactives'] ?> étudiants désactivés ?">
	<?= csrf_field() ?><input type="hidden" name="valeur" value="1"><input type="hidden" name="tous_desactives" value="1">
	<button class="btn vert" type="submit"><i class="fa-solid fa-rotate-left"></i> Réactiver tous les étudiants désactivés (<?= (int) $totaux['desactives'] ?>)</button>
</form>
<?php endif; ?>

<form method="post" action="<?= e(admin_url('etudiants/activation')) ?>" id="form-etudiants">
	<?= csrf_field() ?>
	<input type="hidden" name="retour" value="<?= e($_SERVER['REQUEST_URI']) ?>">
<?php if ($cocher && $liste): ?>
	<div class="barre-actions" style="margin-bottom:12px">
		<span class="doux" data-compte-coches>Cochez des étudiants, puis :</span>
		<button class="btn danger petit" type="submit" name="valeur" value="0" data-exige-coche><i class="fa-solid fa-user-slash"></i> Désactiver la sélection</button>
		<button class="btn vert petit" type="submit" name="valeur" value="1" data-exige-coche><i class="fa-solid fa-user-check"></i> Réactiver la sélection</button>
	</div>
<?php endif; ?>
<div class="carte" style="padding:0">
<?php if (!$liste): ?>
	<div class="vide"><?= $totaux['total'] ? 'Aucun étudiant ne correspond.' : 'La liste est vide : chargez le fichier des étudiants fourni par la scolarité.' ?></div>
<?php else: ?>
	<div class="tableau-wrap"><table class="tableau">
		<thead><tr>
			<?php if ($cocher): ?><th style="width:34px"><input type="checkbox" data-tout-cocher="ids[]" aria-label="Tout cocher sur cette page" title="Tout cocher sur cette page"></th><?php endif; ?>
			<th>N° de suivi</th><th>Nom et prénoms</th><th>Naissance</th><th>Lecteur</th><th>Faculté / parcours</th><th>Liste</th><th></th></tr></thead>
		<tbody>
<?php foreach ($liste as $et): ?>
			<tr<?= $et['actif'] ? '' : ' class="inactif"' ?>>
				<?php if ($cocher): ?><td><input type="checkbox" name="ids[]" value="<?= (int) $et['id'] ?>" aria-label="Cocher <?= e($et['nom'] . ' ' . $et['prenom']) ?>"></td><?php endif; ?>
				<td><code><?= e($et['numero_suivi']) ?></code></td>
				<td><strong><?= e($et['nom']) ?></strong> <?= e($et['prenom']) ?></td>
				<td><?= e(date_fr($et['date_naissance'])) ?> à <?= e($et['lieu_naissance']) ?></td>
				<td><?php if (!$et['lecteur_id']): ?><span class="doux">—</span>
					<?php elseif ($et['compte_en_ligne']): ?><span class="badge <?= $et['lecteur_actif'] ? 'vert' : 'orange' ?>">Compte en ligne<?= $et['lecteur_actif'] ? '' : ' (inactif)' ?></span>
					<?php else: ?><span class="badge bleu">Fiche guichet</span><?php endif; ?></td>
				<td><?= e((string) $et['faculte']) ?><?= (string) $et['parcours'] !== '' ? '<br><span class="doux">Parcours : ' . e($et['parcours']) . '</span>' : '' ?></td>
				<td><?= $et['actif'] ? '<span class="badge vert">Actif</span>' : '<span class="badge">Désactivé</span>' ?></td>
				<td class="actions">
					<?php if (peut('etudiants.modifier')): ?><a class="btn secondaire petit" href="<?= e(admin_url('etudiants/modifier/' . $et['id'])) ?>" title="Modifier"><i class="fa-solid fa-pen"></i></a><?php endif; ?>
					<?php if ($et['lecteur_id'] && peut('lecteurs.voir')): ?><a class="btn secondaire petit" href="<?= e(admin_url('lecteurs/voir/' . $et['lecteur_id'])) ?>">Fiche</a><?php endif; ?>
					<?php if ($guichet && $et['lecteur_id']): ?><a class="btn secondaire petit" href="<?= e(admin_url('guichet', ['lecteur' => $et['lecteur_id']])) ?>">Guichet</a>
					<?php elseif ($guichet && $et['actif']): ?><button class="btn secondaire petit" type="submit" form="guichet-<?= (int) $et['id'] ?>">Guichet</button><?php endif; ?>
				</td>
			</tr>
<?php endforeach; ?>
		</tbody>
	</table></div>
<?php endif; ?>
</div>
</form>
<?php if ($guichet): foreach ($liste as $et): if (!$et['lecteur_id'] && $et['actif']): ?>
<form id="guichet-<?= (int) $et['id'] ?>" method="post" action="<?= e(admin_url('guichet/etudiant/' . $et['id'])) ?>"><?= csrf_field() ?></form>
<?php endif; endforeach; endif; ?>
<?= liens_pagination($pg) ?>
