<?php
/** Demande de création d'un compte lecteur (validée ensuite par la bibliothèque). @var array $saisie @var array $erreurs */
$champ = function (string $nom, string $libelle, string $type = 'text', string $extra = '') use ($saisie, $erreurs): string {
    return '<div class="control-group' . (isset($erreurs[$nom]) ? ' error' : '') . '">'
        . '<div class="control-label"><label for="i_' . $nom . '">' . e($libelle) . '</label></div>'
        . '<div class="controls"><input type="' . $type . '" name="' . $nom . '" id="i_' . $nom . '" value="' . ($type === 'password' ? '' : e($saisie[$nom] ?? '')) . '" class="form-control" ' . $extra . '>'
        . (isset($erreurs[$nom]) ? '<span class="help-inline">' . e($erreurs[$nom]) . '</span>' : '')
        . '</div></div>';
};
?>
<div class="biblio-compte biblio-compte-etroit">
	<h1>Créer un compte lecteur</h1>
	<p>Votre demande sera validée par la bibliothèque (présentez votre carte d’étudiant ou de personnel à l’accueil). Vous pourrez ensuite réserver et suivre vos prêts en ligne.</p>
	<div class="biblio-contact-form box-shadow biblio-compte-boite">
		<form method="post" action="<?= e(url('compte/inscription')) ?>" class="well">
			<?= csrf_field() ?>
			<div class="biblio-form-2col">
				<?= $champ('prenom', 'Prénom', 'text', 'required autocomplete="given-name"') ?>
				<?= $champ('nom', 'Nom', 'text', 'required autocomplete="family-name"') ?>
			</div>
			<?= $champ('email', 'E-mail', 'email', 'required autocomplete="email"') ?>
			<div class="biblio-form-2col">
				<div class="control-group<?= isset($erreurs['categorie']) ? ' error' : '' ?>">
					<div class="control-label"><label for="i_categorie">Vous êtes</label></div>
					<div class="controls"><select name="categorie" id="i_categorie" class="form-control">
						<?php foreach (LIBELLES_CATEGORIE as $cle => $lib): ?>
						<option value="<?= $cle ?>"<?= ($saisie['categorie'] ?? 'etudiant') === $cle ? ' selected' : '' ?>><?= e($lib) ?></option>
						<?php endforeach; ?>
					</select></div>
				</div>
				<?= $champ('matricule', 'Matricule / n° de carte', 'text', '') ?>
			</div>
			<?= $champ('telephone', 'Téléphone (facultatif)', 'tel', 'autocomplete="tel"') ?>
			<div class="biblio-form-2col">
				<?= $champ('mot_de_passe', 'Mot de passe (8 caractères min.)', 'password', 'required minlength="8" autocomplete="new-password"') ?>
				<?= $champ('mot_de_passe2', 'Confirmer le mot de passe', 'password', 'required minlength="8" autocomplete="new-password"') ?>
			</div>
			<div class="biblio-hp" aria-hidden="true"><label>Ne pas remplir <input type="text" name="site_web" tabindex="-1" autocomplete="off"></label></div>
			<div class="control-group">
				<div class="controls"><button type="submit" class="btn btn-primary">Envoyer ma demande</button></div>
			</div>
		</form>
		<p>Déjà inscrit ? <a href="<?= e(url('compte/connexion')) ?>">Se connecter</a></p>
	</div>
</div>
