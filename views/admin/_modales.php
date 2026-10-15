<?php
/**
 * Fenêtres de gestion des prêts, ouvertes par bouton_remettre(), bouton_retour() et bouton_prolonger().
 * Les éléments [data-remplir="clé"] reçoivent les valeurs du bouton (attribut data-valeurs).
 */
$retourPage = $_SERVER['REQUEST_URI'] ?? '';
?>
<dialog class="modale" id="modale-remise" aria-labelledby="titre-remise">
	<form method="post" action="<?= e(admin_url('guichet/pret')) ?>">
		<?= csrf_field() ?>
		<input type="hidden" name="retour" value="<?= e($retourPage) ?>">
		<input type="hidden" name="lecteur_id" data-remplir="lecteur_id">
		<input type="hidden" name="code" data-remplir="code">
		<header><h2 id="titre-remise"><i class="fa-solid fa-hand-holding"></i> Remise d’un ouvrage réservé</h2><button type="button" class="fermer" data-fermer aria-label="Fermer">&times;</button></header>
		<div class="modale-corps">
			<dl class="recap">
				<dt>Lecteur</dt><dd data-remplir="lecteur"></dd>
				<dt>Ouvrage</dt><dd data-remplir="titre"></dd>
				<dt>Exemplaire</dt><dd><code data-remplir="exemplaire"></code></dd>
			</dl>
			<div class="grille-2-champs">
				<div class="champ">
					<label for="remise-etat">État de l’exemplaire remis</label>
					<select id="remise-etat" name="etat" data-remplir="etat">
<?php foreach (ETATS_EXEMPLAIRE as $k => $lib): ?>
						<option value="<?= $k ?>"><?= e($lib) ?></option>
<?php endforeach; ?>
					</select>
					<div class="aide">Vérifiez l’ouvrage avec le lecteur : cet état servira de référence au retour.</div>
				</div>
				<div class="champ">
					<label for="remise-echeance">À rendre le</label>
					<input type="date" id="remise-echeance" name="echeance" data-remplir="echeance" required min="<?= e(plus_jours(aujourdhui(), 1)) ?>" max="<?= e(plus_jours(aujourdhui(), 365)) ?>">
					<div class="aide">Durée habituelle : <?= reglage_int('pret_duree_jours', 14) ?> jours.</div>
				</div>
			</div>
			<div class="champ">
				<label for="remise-notes">Remarque (facultatif)</label>
				<input type="text" id="remise-notes" name="notes" maxlength="255" data-remplir="notes" placeholder="Ex. coin de couverture plié, CD d’accompagnement fourni…">
			</div>
		</div>
		<footer><button type="button" class="btn secondaire" data-fermer>Annuler</button><button class="btn" type="submit"><i class="fa-solid fa-circle-check"></i> Enregistrer le prêt</button></footer>
	</form>
</dialog>

<dialog class="modale" id="modale-retour" aria-labelledby="titre-retour">
	<form method="post" data-remplir-action>
		<?= csrf_field() ?>
		<input type="hidden" name="retour" value="<?= e($retourPage) ?>">
		<header><h2 id="titre-retour"><i class="fa-solid fa-rotate-left"></i> Retour d’un prêt</h2><button type="button" class="fermer" data-fermer aria-label="Fermer">&times;</button></header>
		<div class="modale-corps">
			<dl class="recap">
				<dt>Lecteur</dt><dd data-remplir="lecteur"></dd>
				<dt>Ouvrage</dt><dd data-remplir="titre"></dd>
				<dt>Exemplaire</dt><dd><code data-remplir="exemplaire"></code> · remis en état « <span data-remplir="sortie"></span> »</dd>
				<dt>Échéance</dt><dd data-remplir="echeance"></dd>
			</dl>
			<p class="aide" data-remplir="notes"></p>
			<?= render(ROOT_DIR . '/views/admin/_champs_retour.php', ['prefixe' => 'modale-retour']) ?>
		</div>
		<footer><button type="button" class="btn secondaire" data-fermer>Annuler</button><button class="btn vert" type="submit"><i class="fa-solid fa-rotate-left"></i> Enregistrer le retour</button></footer>
	</form>
</dialog>

<dialog class="modale" id="modale-prolonger" aria-labelledby="titre-prolonger">
	<form method="post" data-remplir-action>
		<?= csrf_field() ?>
		<input type="hidden" name="retour" value="<?= e($retourPage) ?>">
		<header><h2 id="titre-prolonger"><i class="fa-solid fa-calendar-plus"></i> Prolonger un prêt</h2><button type="button" class="fermer" data-fermer aria-label="Fermer">&times;</button></header>
		<div class="modale-corps">
			<dl class="recap">
				<dt>Lecteur</dt><dd data-remplir="lecteur"></dd>
				<dt>Ouvrage</dt><dd data-remplir="titre"></dd>
				<dt>Échéance actuelle</dt><dd data-remplir="echeance"></dd>
			</dl>
			<div class="flash erreur" data-remplir="obstacles" data-masquer-si-vide></div>
			<div class="champ">
				<label for="prolonger-date">Nouvelle date de retour</label>
				<input type="date" id="prolonger-date" name="date" data-remplir="date" required min="<?= e(plus_jours(aujourdhui(), 1)) ?>" max="<?= e(plus_jours(aujourdhui(), 365)) ?>">
			</div>
			<label class="case" data-masquer-si-vide="obstacles"><input type="checkbox" name="forcer" value="1" required> Prolonger quand même, malgré la règle ci-dessus</label>
		</div>
		<footer><button type="button" class="btn secondaire" data-fermer>Annuler</button><button class="btn" type="submit"><i class="fa-solid fa-calendar-plus"></i> Prolonger</button></footer>
	</form>
</dialog>
