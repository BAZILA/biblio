<?php
/**
 * Installation par le navigateur, pour les hébergements sans terminal : https://votre-site/installation.php
 *
 * Ne fonctionne que sur une base VIDE : crée les tables, les textes du site et le compte Système.
 * Protégée par le mot de passe de la base (celui écrit dans app/config.php), que seul le propriétaire connaît.
 * Le fichier s'efface de lui-même après l'installation ; s'il n'y parvient pas, supprimez-le.
 */
require __DIR__ . '/app/bootstrap.php';
require ROOT_DIR . '/app/lib/installation.php';
require ROOT_DIR . '/app/lib/formulaires.php';

header('X-Robots-Tag: noindex, nofollow');
header('Cache-Control: no-store');

$etat = 'formulaire';
$erreurs = [];
$message = '';
$schema = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
$saisie = ['email' => '', 'url' => $schema . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . base_url() . '/'];

try {
    if (tables_existantes() > 0) {
        $etat = 'deja';
    }
} catch (PDOException $ex) {
    $etat = 'connexion';
    $message = 'Connexion à la base impossible. Vérifiez dans app/config.php l’hôte (souvent « localhost »), le nom de la base, l’utilisateur et le mot de passe, tels que créés dans votre panneau d’hébergement.';
}

if ($etat === 'formulaire' && is_post()) {
    csrf_verify();
    $saisie['email'] = mb_strtolower(post_str('email', ''));
    $saisie['url'] = post_str('url', '');
    $mdp = post_str('mot_de_passe', '', false);
    // Preuve d'accès au serveur : le mot de passe de la base, lu dans app/config.php
    if (!hash_equals((string) $GLOBALS['config']['db']['password'], post_str('cle', '', false))) {
        sleep(2); // ralentit les essais au hasard
        $erreurs['cle'] = 'Ce n’est pas le mot de passe de la base indiqué dans app/config.php.';
    }
    if (!filter_var($saisie['email'], FILTER_VALIDATE_EMAIL)) {
        $erreurs['email'] = 'Adresse e-mail invalide.';
    }
    if (mb_strlen($mdp) < 12) {
        $erreurs['mot_de_passe'] = 'Au moins 12 caractères.';
    } elseif ($mdp !== post_str('mot_de_passe2', '', false)) {
        $erreurs['mot_de_passe2'] = 'Les deux mots de passe ne correspondent pas.';
    }
    if (!preg_match('#^https?://[^\s/]+(/.*)?$#i', $saisie['url'])) {
        $erreurs['url'] = 'Adresse du site invalide (ex. https://bibliotheque.univkara.tg/).';
    }
    if (!$erreurs && tables_existantes() === 0) {
        installer_base($saisie['url']);
        creer_compte_systeme($saisie['email'], $mdp);
        $etat = 'termine';
        $avertissements = dossiers_non_modifiables();
        $efface = @unlink(__FILE__);
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="robots" content="noindex, nofollow">
	<title>Installation — Bibliothèque</title>
	<link rel="stylesheet" href="<?= asset('admin/admin.css') ?>">
</head>
<body class="page-connexion">
<div class="connexion">
	<div class="boite" style="max-width:460px">
		<img src="<?= e(media('images/biblio/logo-uk-original.png')) ?>" alt="">
		<h1>Installation de la bibliothèque</h1>
<?php if ($etat === 'connexion'): ?>
		<div class="flash erreur"><?= e($message) ?></div>
<?php elseif ($etat === 'deja'): ?>
		<div class="flash info">Le site est déjà installé : cette page ne fait plus rien. <strong>Supprimez le fichier installation.php</strong> de votre hébergement.</div>
		<p style="text-align:center"><a href="<?= e(admin_url()) ?>">Aller à l’administration</a></p>
<?php elseif ($etat === 'termine'): ?>
		<div class="flash succes">Installation terminée : base créée, textes du site chargés, compte Système <strong><?= e($saisie['email']) ?></strong> prêt.</div>
		<?php if (!$efface): ?><div class="flash erreur">Le fichier installation.php n’a pas pu s’effacer : <strong>supprimez-le maintenant</strong> avec le gestionnaire de fichiers.</div><?php endif; ?>
		<?php foreach ($avertissements as $d): ?><div class="flash info">Le dossier <code><?= e($d) ?></code> n’est pas modifiable par PHP : donnez-lui les droits d’écriture (755 ou 775) pour pouvoir envoyer des images.</div><?php endforeach; ?>
		<p>Étapes suivantes : connectez-vous à l’administration avec ce compte, créez le compte <strong>Administrateur</strong> de l’établissement (Configuration → Comptes du personnel), puis réglez les e-mails (Réglages).</p>
		<a class="btn" href="<?= e(admin_url()) ?>" style="width:100%;justify-content:center">Se connecter à l’administration</a>
<?php else: ?>
		<p class="doux">Cette page crée la base vide et le compte <strong>Système</strong> (super administrateur). Elle ne sert qu’une fois.</p>
		<form method="post" autocomplete="off">
			<?= csrf_field() ?>
			<?= champ('cle', 'Mot de passe de la base de données', '', $erreurs, ['type' => 'password', 'required' => true, 'autocomplete' => 'off',
				'aide' => 'Celui écrit dans app/config.php : il prouve que vous êtes le propriétaire du site.']) ?>
			<?= champ('url', 'Adresse du site', $saisie['url'], $erreurs, ['type' => 'url', 'required' => true]) ?>
			<?= champ('email', 'E-mail du compte Système', $saisie['email'], $erreurs, ['type' => 'email', 'required' => true, 'autocomplete' => 'username']) ?>
			<?= champ('mot_de_passe', 'Mot de passe du compte Système', '', $erreurs, ['type' => 'password', 'required' => true, 'autocomplete' => 'new-password', 'aide' => '12 caractères minimum.']) ?>
			<?= champ('mot_de_passe2', 'Confirmation du mot de passe', '', $erreurs, ['type' => 'password', 'required' => true, 'autocomplete' => 'new-password']) ?>
			<button class="btn" type="submit">Installer</button>
		</form>
<?php endif; ?>
	</div>
</div>
</body>
</html>
