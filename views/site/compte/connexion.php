<?php /** Connexion à l'espace lecteur. @var string $email @var string $retour @var string $erreur */ ?>
<div class="biblio-compte biblio-compte-etroit">
	<h1>Espace lecteur</h1>
<?php if (reglage('aide_inscription') !== ''): ?>
	<p class="biblio-aide-guichet"><i class="fa fa-life-ring" aria-hidden="true"></i> <?= e(reglage('aide_inscription')) ?></p>
<?php endif; ?>
	<p>Connectez-vous pour suivre vos prêts, prolonger un emprunt et réserver des ouvrages.</p>
	<div class="biblio-contact-form box-shadow biblio-compte-boite">
		<?php if ($erreur): ?><div class="biblio-alert biblio-alert-erreur"><?= e($erreur) ?></div><?php endif; ?>
		<form method="post" action="<?= e(url('compte/connexion')) ?>" class="well">
			<?= csrf_field() ?>
			<input type="hidden" name="retour" value="<?= e($retour) ?>">
			<div class="control-group">
				<div class="control-label"><label for="email">E-mail</label></div>
				<div class="controls"><input type="email" name="email" id="email" value="<?= e($email) ?>" class="form-control" required autocomplete="username" autofocus></div>
			</div>
			<div class="control-group">
				<div class="control-label"><label for="mdp">Mot de passe</label></div>
				<div class="controls"><input type="password" name="mot_de_passe" id="mdp" class="form-control" required autocomplete="current-password"></div>
			</div>
			<div class="control-group">
				<div class="controls"><button type="submit" class="btn btn-primary">Se connecter</button></div>
			</div>
		</form>
		<p>Pas encore de compte ? <a href="<?= e(url('compte/inscription')) ?>">Créer un compte lecteur</a></p>
		<p class="biblio-aide">Mot de passe oublié ? Adressez-vous à l’accueil de la bibliothèque ou <a href="<?= e(url('contact')) ?>">contactez-nous</a>.</p>
	</div>
</div>
