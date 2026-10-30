<?php
/**
 * Saisie du code reçu par e-mail : le compte (ou la demande) n'est créé qu'une fois l'adresse vérifiée.
 * @var array $attente @var string $erreur
 */
?>
<div class="biblio-compte biblio-compte-etroit">
	<h1>Vérifiez votre adresse e-mail</h1>
	<p>Un code à 6 chiffres vient d’être envoyé à <strong><?= e($attente['email']) ?></strong>.
		Recopiez-le ci-dessous pour terminer votre inscription. Pensez à regarder dans les courriers indésirables.</p>

	<div class="biblio-contact-form box-shadow biblio-compte-boite">
		<form method="post" autocomplete="off">
			<?= csrf_field() ?>
<?php if ($erreur !== ''): ?>
			<div class="biblio-alert biblio-alert-erreur"><?= e($erreur) ?></div>
<?php endif; ?>
			<div class="control-group">
				<div class="control-label"><label for="i_code">Code reçu par e-mail</label></div>
				<div class="controls">
					<input type="text" name="code" id="i_code" class="form-control biblio-code" inputmode="numeric" pattern="[0-9]*"
						maxlength="6" required autofocus autocomplete="one-time-code" placeholder="000000">
					<span class="biblio-aide">Valable <?= max(5, reglage_int('code_duree_minutes', 15)) ?> minutes.</span>
				</div>
			</div>
			<div class="control-group">
				<div class="controls">
					<button type="submit" class="btn btn-primary">Valider mon inscription</button>
					<button type="submit" name="renvoyer" value="1" class="btn btn-secondary">Renvoyer un code</button>
				</div>
			</div>
		</form>
	</div>

	<p class="biblio-aide">Adresse erronée ? <a href="<?= e(url('compte/inscription')) ?>">Reprendre l’inscription</a>.
<?php if (reglage('aide_inscription') !== ''): ?>
		<?= e(reglage('aide_inscription')) ?>
<?php endif; ?>
	</p>
</div>
