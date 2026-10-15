<?php /** Constat au retour d'un prêt (guichet et fenêtre « Retour »). @var string $prefixe identifiant unique des champs */ ?>
<fieldset class="constat-retour" data-constat>
	<legend>État de l’exemplaire rendu</legend>
	<div class="choix-etat">
<?php foreach (ETATS_RETOUR as $cle => $libelle): ?>
		<label class="etat-<?= $cle ?>">
			<input type="radio" name="etat_retour" value="<?= $cle ?>"<?= $cle === 'conforme' ? ' checked' : '' ?>>
			<span><i class="fa-solid <?= ['conforme' => 'fa-circle-check', 'use' => 'fa-book', 'abime' => 'fa-triangle-exclamation', 'perdu' => 'fa-circle-question'][$cle] ?>"></i> <?= e($libelle) ?></span>
		</label>
<?php endforeach; ?>
	</div>
	<div class="champ" data-si-etat="abime perdu" hidden>
		<label for="<?= $prefixe ?>-note">Constat <span class="requis">*</span></label>
		<textarea id="<?= $prefixe ?>-note" name="note_retour" rows="2" maxlength="255" placeholder="Pages déchirées, couverture tachée… ou circonstances de la perte, remboursement convenu"></textarea>
	</div>
	<div class="champ" data-si-etat="conforme use" hidden>
		<label for="<?= $prefixe ?>-remarque">Remarque (facultatif)</label>
		<input type="text" id="<?= $prefixe ?>-remarque" name="note_retour" maxlength="255" placeholder="Ex. rendu avec un marque-page oublié">
	</div>
	<label class="case" data-si-etat="abime" hidden><input type="checkbox" name="retirer" value="1"> Retirer l’exemplaire de la circulation (réparation)</label>
	<p class="aide" data-si-etat="perdu" hidden>L’exemplaire sera retiré de la circulation et le prêt clos. Pensez à convenir du remplacement ou du remboursement avec le lecteur.</p>
</fieldset>
