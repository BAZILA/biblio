<?php
/**
 * Grille des permissions d'un rôle : une ligne par rubrique, colonnes Voir / Ajouter / Modifier / Supprimer,
 * puis les actions particulières de la rubrique.
 * @var array $r, $perms, $erreurs, $mesDroits, $comptes, $visibles @var bool $lectureSeule
 */
$coche = array_flip($perms);
$vis = array_flip($visibles); // les permissions masquées par le Système n'apparaissent pas
$modulesVisibles = function (array $modules) use ($vis): array {
    $garde = [];
    foreach ($modules as $module => [$libelle, $actions, $aide]) {
        $actions = array_filter($actions, fn($a) => isset($vis["$module.$a"]), ARRAY_FILTER_USE_KEY);
        if ($actions) {
            $garde[$module] = [$libelle, $actions, $aide];
        }
    }
    return $garde;
};
$a = array_flip($mesDroits);
$case = function (string $perm, string $libelle, bool $standard) use ($coche, $a, $lectureSeule, $r): string {
    $horsPortee = !isset($a[$perm]);
    $attr = (isset($coche[$perm]) || $r['tous_droits'] ? ' checked' : '') . ($lectureSeule || $horsPortee || ($r['tous_droits'] && !$r['systeme']) ? ' disabled' : '');
    $titre = $horsPortee && !$lectureSeule ? ' title="Vous ne possédez pas ce droit : vous ne pouvez ni l’accorder ni le retirer."' : '';
    [$module, $action] = explode('.', $perm, 2);
    return '<label class="case-droit' . ($standard ? ' standard' : '') . '"' . $titre . '><input type="checkbox" name="permissions[]" value="' . e($perm) . '"'
        . ' data-module="' . e($module) . '" data-action="' . e($action) . '"' . $attr . '>'
        . '<span>' . e($libelle) . '</span></label>';
};
?>
<div class="fil"><a href="<?= e(admin_url('roles')) ?>">Rôles et permissions</a> ›</div>
<div class="entete">
	<h1><?= $r['id'] ? e($r['nom']) : 'Nouveau rôle' ?></h1>
	<?= $r['tous_droits'] ? '<span class="badge bleu">Tous les droits</span>' : ($lectureSeule ? '<span class="badge">Lecture seule</span>' : '') ?>
</div>

<?php if ($r['systeme']): ?>
<div class="flash info">Rôle <strong>Système</strong> : toutes les permissions, y compris celles masquées aux autres comptes. Invisible pour eux ; ses actions apparaissent comme faites par « Système ».</div>
<?php elseif ($r['tous_droits'] && !is_systeme()): ?>
<div class="flash info">Le rôle <strong>Administrateur</strong> possède toujours toutes les permissions, y compris celles des futures rubriques. Il ne se modifie pas : pour un rôle proche avec moins de droits, utilisez « Dupliquer » depuis la liste des rôles.</div>
<?php endif; ?>

<form method="post" id="form-role" autocomplete="off">
	<?= csrf_field() ?>
	<fieldset class="sans-cadre"<?= $lectureSeule ? ' disabled' : '' ?>>
	<section class="carte">
		<div class="ligne">
			<?= champ('nom', 'Nom du rôle', $r['nom'], $erreurs, ['required' => true, 'attrs' => 'maxlength="100" placeholder="Ex. Agent d’accueil"']) ?>
			<?= champ('description', 'Description', $r['description'], $erreurs, ['attrs' => 'maxlength="255" placeholder="À quoi sert ce rôle ?"']) ?>
		</div>
	</section>
<?php if (is_systeme() && !$r['systeme']): ?>
	<section class="carte carte-systeme">
		<h2><i class="fa-solid fa-microchip"></i> Réservé au compte Système</h2>
		<label class="case"><input type="checkbox" name="tous_droits" value="1" id="role-tous-droits"<?= $r['tous_droits'] ? ' checked' : '' ?>>
			<strong>Tous les droits</strong>, présents et futurs (comme l’Administrateur). Décochez pour choisir les permissions une à une dans la grille.</label>
		<label class="case" style="margin-top:10px"><input type="checkbox" name="masque" value="1" id="role-masque"<?= !empty($r['masque']) ? ' checked' : '' ?><?= $r['tous_droits'] ? ' disabled' : '' ?>>
			<strong>Masquer ce rôle aux autres comptes</strong> : il disparaît des Rôles et permissions, ne peut plus être attribué, et ses comptes n’apparaissent plus dans les Comptes du personnel.
			Seul le compte Système les voit. (Impossible pour un rôle « tous les droits ».)</label>
	</section>
<?php endif; ?>

	<section class="carte grille-droits-carte">
		<div class="carte-titre">
			<h2><i class="fa-solid fa-table-cells"></i> Permissions <span class="compteur" id="nb-droits"><?= count($perms) ?></span></h2>
<?php if (!$lectureSeule): ?>
			<div class="raccourcis-droits">
				<button type="button" class="btn secondaire petit" data-droits="tout"><i class="fa-solid fa-check-double"></i> Tout cocher</button>
				<button type="button" class="btn secondaire petit" data-droits="lecture"><i class="fa-solid fa-eye"></i> Lecture seule partout</button>
				<button type="button" class="btn secondaire petit" data-droits="rien"><i class="fa-solid fa-xmark"></i> Tout décocher</button>
			</div>
<?php endif; ?>
		</div>
		<p class="aide">Cocher une action coche automatiquement « Voir » de la rubrique ; décocher « Voir » retire toute la ligne. Sans « Modifier », une fiche s’ouvre en lecture seule.</p>

		<div class="tableau-wrap"><table class="tableau grille-droits">
			<thead><tr><th>Rubrique</th><th class="col-tout">Tout</th><?php foreach (ACTIONS_STANDARD as $lib): ?><th class="col-std"><?= e($lib) ?></th><?php endforeach; ?><th>Actions particulières</th></tr></thead>
<?php foreach (PERMISSIONS as $groupe => $modules): $modules = $modulesVisibles($modules); if (!$modules) continue; ?>
			<tbody>
				<tr class="groupe-droits"><th colspan="7"><?= e($groupe) ?></th></tr>
<?php foreach ($modules as $module => [$libelle, $actions, $aide]): ?>
				<tr data-ligne="<?= e($module) ?>">
					<td><strong><?= e($libelle) ?></strong><?= $aide !== '' ? '<br><small class="doux">' . e($aide) . '</small>' : '' ?></td>
					<td class="col-tout"><label class="case-droit"><input type="checkbox" data-tout-ligne="<?= e($module) ?>" aria-label="Toutes les permissions : <?= e($libelle) ?>"<?= $lectureSeule ? ' disabled' : '' ?>></label></td>
<?php foreach (ACTIONS_STANDARD as $act => $lib): ?>
					<td class="col-std"><?= isset($actions[$act]) ? $case("$module.$act", $lib, true) : '<span class="sans-objet">—</span>' ?></td>
<?php endforeach; ?>
					<td><div class="particulieres">
<?php foreach (array_diff_key($actions, ACTIONS_STANDARD) as $act => $lib): ?>
						<?= $case("$module.$act", $lib, false) ?>
<?php endforeach; ?>
					</div></td>
				</tr>
<?php endforeach; ?>
			</tbody>
<?php endforeach; ?>
		</table></div>
	</section>
	</fieldset>

	<div class="barre-actions">
		<span class="doux"><?= $lectureSeule ? 'Consultation uniquement.' : 'Les changements s’appliquent dès l’enregistrement à tous les comptes ayant ce rôle.' ?></span>
		<a class="btn secondaire" href="<?= e(admin_url('roles')) ?>"><?= $lectureSeule ? 'Retour aux rôles' : 'Annuler' ?></a>
<?php if (!$lectureSeule): ?>
		<button class="btn" type="submit"><i class="fa-solid fa-circle-check"></i> Enregistrer le rôle</button>
<?php endif; ?>
	</div>
</form>

<?php if ($r['id']): ?>
<div class="grille grille-2" style="margin-top:20px">
	<section class="carte">
		<div class="carte-titre"><h2><i class="fa-solid fa-users"></i> Comptes ayant ce rôle <span class="compteur"><?= count($comptes) ?></span></h2>
			<?php if (peut('personnel.voir')): ?><a class="lien" href="<?= e(admin_url('personnel')) ?>">Comptes du personnel</a><?php endif; ?></div>
<?php if (!$comptes): ?>
		<p class="rien">Aucun compte n’a ce rôle.</p>
<?php else: ?>
		<ul class="liste-simple">
<?php foreach ($comptes as $c): ?>
			<li><span><strong><?= e(nom_complet($c)) ?></strong><br><small class="doux"><?= e($c['email']) ?></small></span><?= $c['actif'] ? '<span class="badge vert">Actif</span>' : '<span class="badge">Désactivé</span>' ?></li>
<?php endforeach; ?>
		</ul>
<?php endif; ?>
	</section>
<?php if (!$r['tous_droits'] && peut('roles.supprimer')): ?>
	<section class="carte zone-sensible">
		<h2><i class="fa-solid fa-trash"></i> Supprimer le rôle</h2>
		<p class="doux">Possible seulement si aucun compte n’a ce rôle.</p>
		<?= bouton_action(admin_url('roles/supprimer/' . $r['id']), '<i class="fa-solid fa-trash"></i> Supprimer', 'btn danger', 'Supprimer définitivement le rôle « ' . $r['nom'] . ' » ?') ?>
	</section>
<?php endif; ?>
</div>
<?php endif; ?>
