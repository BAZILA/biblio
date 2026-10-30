<?php /** @var array $f, $lignes, $pg, $acteurs, $resume */
$badgesStatut = ['succes' => ['vert', 'Réussi'], 'erreur' => ['rouge', 'Refusé / erreur'], 'info' => ['', '—']];
$icones = ['action' => 'fa-pen-to-square', 'consultation' => 'fa-eye', 'connexion' => 'fa-right-to-bracket', 'securite' => 'fa-shield-halved', 'export' => 'fa-download'];
?>
<div class="entete">
	<h1>Journal d’audit</h1>
	<div class="actions"><?php if (peut('audit.exporter')): ?><a class="btn secondaire" href="<?= e(admin_url('audit/exporter', array_filter($f, fn($v) => $v !== ''))) ?>"><i class="fa-solid fa-file-csv"></i> Exporter (CSV)</a><?php endif; ?></div>
</div>
<p class="doux">Toutes les opérations faites sur la plateforme : pages consultées et actions de l’administration, connexions (réussies, refusées, bloquées),
	créations de compte, réservations, demandes et messages envoyés depuis le site. Chaque ligne indique qui, quand, depuis quelle adresse IP, le résultat
	et les informations envoyées (jamais les mots de passe). Le journal ne peut être ni modifié ni effacé.</p>

<div class="stats">
	<div class="stat"><div class="valeur"><?= (int) $resume['total'] ?></div><div class="libelle">opérations sur la période</div></div>
	<div class="stat"><div class="valeur"><?= (int) $resume['actions'] ?></div><div class="libelle">actions (modifications)</div></div>
	<div class="stat<?= $resume['securite'] ? ' alerte' : '' ?>"><div class="valeur"><?= (int) $resume['securite'] ?></div><div class="libelle">événements de sécurité</div></div>
	<div class="stat"><div class="valeur"><?= (int) $resume['personnes'] ?></div><div class="libelle">personnes connectées concernées</div></div>
</div>

<form class="filtres filtres-audit" method="get">
	<label>Du <input type="date" name="du" value="<?= e($f['du']) ?>"></label>
	<label>au <input type="date" name="au" value="<?= e($f['au']) ?>"></label>
	<select name="categorie" aria-label="Catégorie"><option value="">Toutes les catégories</option>
		<?php foreach (CATEGORIES_AUDIT as $k => $lib): ?><option value="<?= $k ?>"<?= $f['categorie'] === $k ? ' selected' : '' ?>><?= e($lib) ?></option><?php endforeach; ?></select>
	<select name="espace" aria-label="Espace"><option value="">Admin et site</option>
		<option value="admin"<?= $f['espace'] === 'admin' ? ' selected' : '' ?>>Administration</option><option value="site"<?= $f['espace'] === 'site' ? ' selected' : '' ?>>Site public</option></select>
	<select name="statut" aria-label="Résultat"><option value="">Tous les résultats</option>
		<option value="succes"<?= $f['statut'] === 'succes' ? ' selected' : '' ?>>Réussis</option><option value="erreur"<?= $f['statut'] === 'erreur' ? ' selected' : '' ?>>Refusés / erreurs</option></select>
	<select name="acteur" aria-label="Personne"><option value="">Tout le monde</option>
		<?php foreach ($acteurs as $a): ?><option value="<?= e($a) ?>"<?= $f['acteur'] === $a ? ' selected' : '' ?>><?= e($a) ?></option><?php endforeach; ?></select>
	<input type="search" name="q" value="<?= e($f['q']) ?>" placeholder="Rechercher : titre, nom, IP…">
	<button class="btn secondaire" type="submit"><i class="fa-solid fa-filter"></i> Filtrer</button>
</form>

<div class="carte" style="padding:0">
<?php if (!$lignes): ?>
	<div class="vide">Aucune opération sur cette période avec ces filtres.</div>
<?php else: ?>
	<div class="tableau-wrap"><table class="tableau tableau-audit">
		<thead><tr><th>Date</th><th>Qui</th><th>Opération</th><th>Résultat</th><th>Adresse IP</th></tr></thead>
		<tbody>
<?php foreach ($lignes as $l): $b = $badgesStatut[$l['statut']] ?? ['', '']; $details = $l['details'] ? json_decode($l['details'], true) : []; ?>
			<tr>
				<td class="nowrap"><?= e(date('d/m/Y', strtotime($l['le']))) ?><br><span class="doux"><?= e(date('H:i:s', strtotime($l['le']))) ?></span></td>
				<td><strong><?= e($l['acteur']) ?></strong><br><span class="doux"><?= $l['espace'] === 'admin' ? 'Administration' : 'Site public' ?></span></td>
				<td><i class="fa-solid <?= $icones[$l['categorie']] ?? 'fa-circle' ?> doux" title="<?= e(CATEGORIES_AUDIT[$l['categorie']] ?? '') ?>"></i> <?= e($l['action']) ?>
<?php if ($details): ?>
					<details class="audit-details"><summary>Informations envoyées</summary>
						<dl><?php foreach ($details as $k => $v): ?><dt><?= e($k) ?></dt><dd><?= e(is_array($v) ? implode(', ', $v) : (string) $v) ?></dd><?php endforeach; ?></dl>
					</details>
<?php endif; ?>
				</td>
				<td><?php if ($b[1] !== '—'): ?><span class="badge <?= $b[0] ?>"><?= $b[1] ?></span><br><?php endif; ?><span class="doux"><?= e($l['resultat']) ?></span></td>
				<td class="nowrap"><code><?= e($l['ip']) ?></code><br><span class="doux" title="<?= e($l['navigateur']) ?>"><?= e(navigateur_court($l['navigateur'])) ?></span></td>
			</tr>
<?php endforeach; ?>
		</tbody>
	</table></div>
<?php endif; ?>
</div>
<?= liens_pagination($pg) ?>
