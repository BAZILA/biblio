<?php
/**
 * Création d'un compte lecteur : étudiant de la liste de l'université (compte immédiat, identité vérifiée)
 * ou enseignant / personnel / visiteur (demande validée ensuite par la bibliothèque).
 * @var array $saisie @var array $erreurs @var string $profil @var bool $listeEtudiants
 */
$champ = function (string $nom, string $libelle, string $type = 'text', string $extra = '', string $aide = '') use ($saisie, $erreurs): string {
    return '<div class="control-group' . (isset($erreurs[$nom]) ? ' error' : '') . '">'
        . '<div class="control-label"><label for="i_' . $nom . '">' . e($libelle) . '</label></div>'
        . '<div class="controls"><input type="' . $type . '" name="' . $nom . '" id="i_' . $nom . '" value="' . ($type === 'password' ? '' : e($saisie[$nom] ?? '')) . '" class="form-control" ' . $extra . '>'
        . ($aide !== '' ? '<span class="biblio-aide">' . e($aide) . '</span>' : '')
        . (isset($erreurs[$nom]) ? '<span class="help-inline">' . e($erreurs[$nom]) . '</span>' : '')
        . '</div></div>';
};
$motsDePasse = '<div class="biblio-form-2col">'
    . $champ('mot_de_passe', 'Mot de passe (8 caractères min.)', 'password', 'required minlength="8" autocomplete="new-password"')
    . $champ('mot_de_passe2', 'Confirmer le mot de passe', 'password', 'required minlength="8" autocomplete="new-password"')
    . '</div>';
$piege = '<div class="biblio-hp" aria-hidden="true"><label>Ne pas remplir <input type="text" name="site_web" tabindex="-1" autocomplete="off"></label></div>';
?>
<div class="biblio-compte biblio-compte-etroit">
	<h1>Créer un compte lecteur</h1>
	<nav class="biblio-profils" aria-label="Vous êtes">
		<a href="<?= e(url('compte/inscription')) ?>" class="<?= $profil === 'etudiant' ? 'actif' : '' ?>"<?= $profil === 'etudiant' ? ' aria-current="page"' : '' ?>>
			<i class="fa fa-graduation-cap" aria-hidden="true"></i> Étudiant de l’université</a>
		<a href="<?= e(url('compte/inscription', ['profil' => 'autre'])) ?>" class="<?= $profil === 'autre' ? 'actif' : '' ?>"<?= $profil === 'autre' ? ' aria-current="page"' : '' ?>>
			<i class="fa fa-user" aria-hidden="true"></i> Enseignant, personnel ou visiteur</a>
	</nav>

<?php if ($profil === 'etudiant' && !$listeEtudiants): ?>
	<div class="biblio-contact-form box-shadow biblio-compte-boite">
		<p>La liste des étudiants n’est pas encore disponible en ligne. Présentez-vous à l’accueil de la bibliothèque avec votre n° de suivi :
			vous pourrez emprunter tout de suite, puis créer votre compte en ligne dès que la liste sera chargée.</p>
		<p>Déjà inscrit ? <a href="<?= e(url('compte/connexion')) ?>">Se connecter</a></p>
	</div>

<?php elseif ($profil === 'etudiant'): ?>
	<p>Votre compte est créé immédiatement. Renseignez votre <strong>n° de suivi</strong> et votre identité <strong>telle qu’enregistrée par la scolarité</strong> :
		la bibliothèque la vérifie automatiquement. Complétez ensuite votre faculté et votre parcours. Votre adresse e-mail sera votre identifiant de connexion.</p>
	<div class="biblio-contact-form box-shadow biblio-compte-boite">
		<?php if (isset($erreurs['identite'])): ?><div class="biblio-alert biblio-alert-erreur"><?= e($erreurs['identite']) ?></div><?php endif; ?>
		<form method="post" action="<?= e(url('compte/inscription')) ?>" class="well">
			<?= csrf_field() ?>
			<input type="hidden" name="profil" value="etudiant">
			<?= $champ('numero_suivi', 'N° de suivi', 'text', 'required autocomplete="off" inputmode="numeric" pattern="[0-9 ]+" title="Chiffres uniquement"', 'Chiffres uniquement.') ?>
			<div class="biblio-form-2col">
				<?= $champ('nom', 'Nom', 'text', 'required autocomplete="family-name"') ?>
				<?= $champ('prenom', 'Prénom(s)', 'text', 'required autocomplete="given-name"') ?>
			</div>
			<div class="biblio-form-2col">
				<?= $champ('date_naissance', 'Date de naissance', 'date', 'required max="' . date('Y-m-d') . '" autocomplete="bday"') ?>
				<?= $champ('lieu_naissance', 'Lieu de naissance', 'text', 'required') ?>
			</div>
			<div class="biblio-form-2col">
				<?= $champ('faculte', 'Faculté', 'text', 'required list="i_facultes"') ?>
				<?= $champ('parcours', 'Parcours', 'text', 'required list="i_parcours"') ?>
			</div>
			<datalist id="i_facultes"><?php foreach (valeurs_existantes('faculte') as $f): ?><option value="<?= e($f) ?>"><?php endforeach; ?></datalist>
			<datalist id="i_parcours"><?php foreach (valeurs_existantes('parcours') as $p): ?><option value="<?= e($p) ?>"><?php endforeach; ?></datalist>
			<div class="biblio-form-2col">
				<?= $champ('email', 'E-mail (votre identifiant de connexion)', 'email', 'required autocomplete="email"') ?>
				<?= $champ('telephone', 'Téléphone (facultatif)', 'tel', 'autocomplete="tel"') ?>
			</div>
			<?= $motsDePasse ?>
			<?= $piege ?>
			<div class="control-group">
				<div class="controls"><button type="submit" class="btn btn-primary">Créer mon compte</button></div>
			</div>
		</form>
		<p>Déjà inscrit ? <a href="<?= e(url('compte/connexion')) ?>">Se connecter</a></p>
		<p class="biblio-aide">Vos informations ne sont pas reconnues ? La liste des étudiants est mise à jour régulièrement :
			présentez-vous à l’accueil de la bibliothèque avec votre n° de suivi.</p>
	</div>

<?php else: ?>
	<p>Pour les enseignants, le personnel de l’université et les visiteurs. Votre demande sera validée par la bibliothèque (présentez votre carte
		à l’accueil). Vous recevrez alors un e-mail : votre adresse e-mail sera votre identifiant de connexion, avec le mot de passe choisi ci-dessous.</p>
	<div class="biblio-contact-form box-shadow biblio-compte-boite">
		<form method="post" action="<?= e(url('compte/inscription')) ?>" class="well">
			<?= csrf_field() ?>
			<input type="hidden" name="profil" value="autre">
			<div class="biblio-form-2col">
				<?= $champ('prenom', 'Prénom', 'text', 'required autocomplete="given-name"') ?>
				<?= $champ('nom', 'Nom', 'text', 'required autocomplete="family-name"') ?>
			</div>
			<?= $champ('email', 'E-mail (votre identifiant de connexion)', 'email', 'required autocomplete="email"') ?>
			<div class="biblio-form-2col">
				<div class="control-group<?= isset($erreurs['categorie']) ? ' error' : '' ?>">
					<div class="control-label"><label for="i_categorie">Vous êtes</label></div>
					<div class="controls"><select name="categorie" id="i_categorie" class="form-control">
						<?php foreach (CATEGORIES_HORS_ETUDIANT as $cle => $lib): ?>
						<option value="<?= $cle ?>"<?= ($saisie['categorie'] ?? '') === $cle ? ' selected' : '' ?>><?= e($lib) ?></option>
						<?php endforeach; ?>
					</select>
					<?= isset($erreurs['categorie']) ? '<span class="help-inline">' . e($erreurs['categorie']) . '</span>' : '' ?></div>
				</div>
				<?= $champ('matricule', 'Matricule', 'text', 'required', 'Facultatif pour un visiteur (lecteur externe).') ?>
			</div>
			<?= $champ('telephone', 'Téléphone', 'tel', 'required autocomplete="tel"') ?>
			<script>
			(function () {
				// Matricule facultatif pour un lecteur externe
				var cat = document.getElementById('i_categorie'), mat = document.getElementById('i_matricule');
				function maj() { mat.required = cat.value !== 'externe'; }
				cat.addEventListener('change', maj);
				maj();
			})();
			</script>
			<?= $motsDePasse ?>
			<?= $piege ?>
			<div class="control-group">
				<div class="controls"><button type="submit" class="btn btn-primary">Envoyer ma demande</button></div>
			</div>
		</form>
		<p>Déjà inscrit ? <a href="<?= e(url('compte/connexion')) ?>">Se connecter</a></p>
	</div>
<?php endif; ?>
</div>
