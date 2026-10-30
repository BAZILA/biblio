<?php /** Écran de connexion de l'administration. @var string $erreur, $email, $retour */ ?>
<!DOCTYPE html>
<html lang="fr">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="robots" content="noindex, nofollow">
	<title>Connexion — Administration</title>
	<link rel="icon" href="<?= e(media('images/biblio/favicon.png')) ?>">
	<link rel="stylesheet" href="<?= asset('vendor/fontawesome/css/fontawesome.min.css') ?>">
	<link rel="stylesheet" href="<?= asset('admin/admin.css') ?>">
</head>
<body class="page-connexion">
<a class="retour-maison" href="<?= e(url()) ?>" title="Retour au site" aria-label="Retour au site"><i class="fa-solid fa-house" aria-hidden="true"></i></a>
<div class="connexion">
	<form class="boite" method="post" action="<?= e(admin_url('connexion')) ?>">
		<img src="<?= e(media('images/biblio/logo-uk-original.png')) ?>" alt="<?= e(reglage('site_nom')) ?>">
		<h1>Administration de la bibliothèque</h1>
		<?php foreach (flashes() as $f): ?><div class="flash <?= e($f['type']) ?>"><?= e($f['message']) ?></div><?php endforeach; ?>
		<?php if ($erreur): ?><div class="flash erreur"><?= e($erreur) ?></div><?php endif; ?>
		<?= csrf_field() ?>
		<input type="hidden" name="retour" value="<?= e($retour) ?>">
		<div class="champ">
			<label for="email">E-mail</label>
			<input type="email" id="email" name="email" value="<?= e($email) ?>" required autofocus autocomplete="username">
		</div>
		<div class="champ">
			<label for="mdp">Mot de passe</label>
			<input type="password" id="mdp" name="mot_de_passe" required autocomplete="current-password">
		</div>
		<button class="btn" type="submit">Se connecter</button>
	</form>
</div>
</body>
</html>
