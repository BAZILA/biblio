<?php
/**
 * Compte Système : permissions masquées aux autres comptes.
 * @var array $masquees, $parPermission, $comptes @var int $nbAdmins
 */
$m = array_flip($masquees);
$case = function (string $perm, string $libelle, bool $standard) use ($m, $parPermission): string {
    [$module, $action] = explode('.', $perm, 2);
    $nb = $parPermission[$perm] ?? 0;
    return '<label class="case-droit case-masque' . ($standard ? ' standard' : '') . '" title="' . e(libelle_permission($perm) . ($nb ? " — accordée à $nb rôle(s)" : '')) . '">'
        . '<input type="checkbox" name="masquees[]" value="' . e($perm) . '" data-module="' . e($module) . '" data-action="' . e($action) . '"' . (isset($m[$perm]) ? ' checked' : '') . '>'
        . '<span>' . e($libelle) . '</span></label>';
};
?>
<div class="entete">
	<h1><i class="fa-solid fa-microchip"></i> Système</h1>
	<span class="badge">Visible uniquement par le compte Système</span>
</div>

<div class="grille grille-2">
	<section class="carte">
		<h2><i class="fa-solid fa-user-secret"></i> Compte Système</h2>
		<p class="doux">Ce compte a tous les droits, y compris ceux masqués ci-dessous. Il n’apparaît ni dans les comptes du personnel, ni dans les rôles, ni dans les compteurs des autres administrateurs ; ses actions (prêts, décisions…) sont affichées comme faites par « Système ».</p>
		<ul class="liste-simple">
<?php foreach ($comptes as $c): ?>
			<li><span><strong><?= e($c['email']) ?></strong><br><small class="doux">Dernière connexion : <?= $c['derniere_connexion'] ? e(date_fr($c['derniere_connexion'], true)) : 'jamais' ?></small></span><?= $c['actif'] ? '<span class="badge vert">Actif</span>' : '<span class="badge">Désactivé</span>' ?></li>
<?php endforeach; ?>
		</ul>
		<p class="aide">Mot de passe et e-mail : dans « Mon compte ». Recréer ou réinitialiser le compte : <code>php install/compte_systeme.php</code>.</p>
	</section>
	<section class="carte">
		<h2><i class="fa-solid fa-eye-slash"></i> Masquer des permissions</h2>
		<p>Une permission masquée <strong>n’existe plus</strong> pour les autres comptes : elle disparaît des menus, des boutons et de la grille des rôles, et aucun rôle ne peut l’accorder — pas même l’Administrateur (<?= $nbAdmins ?> compte(s) actif(s)).</p>
		<p class="doux">Les rôles qui l’avaient la conservent en réserve : elle leur revient si vous la démasquez. Masquer « Voir » masque toute la rubrique.</p>
	</section>
</div>

<form method="post" id="form-masquage" style="margin-top:20px">
	<?= csrf_field() ?>
	<section class="carte grille-droits-carte">
		<div class="carte-titre">
			<h2><i class="fa-solid fa-table-cells"></i> Permissions masquées <span class="compteur" id="nb-masquees"><?= count($masquees) ?></span></h2>
		</div>
		<p class="aide">Cochez ce que les autres comptes ne doivent pas voir. Le nombre entre parenthèses indique combien de rôles possèdent aujourd’hui la permission.</p>
		<div class="tableau-wrap"><table class="tableau grille-droits grille-masquage">
			<thead><tr><th>Rubrique</th><th class="col-tout">Toute la rubrique</th><?php foreach (ACTIONS_STANDARD as $lib): ?><th class="col-std"><?= e($lib) ?></th><?php endforeach; ?><th>Actions particulières</th></tr></thead>
<?php foreach (PERMISSIONS as $groupe => $modules): ?>
			<tbody>
				<tr class="groupe-droits"><th colspan="7"><?= e($groupe) ?></th></tr>
<?php foreach ($modules as $module => [$libelle, $actions]): ?>
				<tr>
					<td><strong><?= e($libelle) ?></strong></td>
					<td class="col-tout"><label class="case-droit case-masque"><input type="checkbox" data-tout-ligne="<?= e($module) ?>" aria-label="Masquer toute la rubrique <?= e($libelle) ?>"></label></td>
<?php foreach (ACTIONS_STANDARD as $act => $lib): ?>
					<td class="col-std"><?= isset($actions[$act]) ? $case("$module.$act", $lib, true) . (($parPermission["$module.$act"] ?? 0) ? '<small class="nb-roles">(' . $parPermission["$module.$act"] . ')</small>' : '') : '<span class="sans-objet">—</span>' ?></td>
<?php endforeach; ?>
					<td><div class="particulieres">
<?php foreach (array_diff_key($actions, ACTIONS_STANDARD) as $act => $lib): ?>
						<?= $case("$module.$act", $lib . (($parPermission["$module.$act"] ?? 0) ? ' (' . $parPermission["$module.$act"] . ')' : ''), false) ?>
<?php endforeach; ?>
					</div></td>
				</tr>
<?php endforeach; ?>
			</tbody>
<?php endforeach; ?>
		</table></div>
	</section>
	<div class="barre-actions">
		<span class="doux">S’applique immédiatement à tous les comptes, sauf au compte Système.</span>
		<button class="btn" type="submit"><i class="fa-solid fa-circle-check"></i> Enregistrer</button>
	</div>
</form>
