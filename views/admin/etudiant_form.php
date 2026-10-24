<?php /** Ajout ou correction d'un étudiant de la liste. @var array $e, $erreurs @var array|null $fiche */ ?>
<div class="fil"><a href="<?= e(admin_url('etudiants')) ?>">Étudiants de l’université</a> ›</div>
<div class="entete"><h1><?= $e['id'] ? 'Modifier ' . e($e['prenom'] . ' ' . $e['nom']) : 'Ajouter un étudiant à la liste' ?></h1></div>

<div class="grille grille-form">
	<form method="post" class="carte">
		<?= csrf_field() ?>
		<div class="ligne">
			<?= champ('numero_suivi', 'N° de suivi', $e['numero_suivi'], $erreurs, ['required' => true, 'attrs' => 'inputmode="numeric" pattern="[0-9 ]+"', 'aide' => 'Chiffres uniquement.']) ?>
			<?= champ('faculte', 'Faculté', $e['faculte'], $erreurs, ['required' => true, 'attrs' => 'list="liste-facultes-etudiants"']) ?>
		</div>
		<datalist id="liste-facultes-etudiants"><?php foreach (facultes_liste() as $fac): ?><option value="<?= e($fac) ?>"><?php endforeach; ?></datalist>
		<div class="ligne">
			<?= champ('nom', 'Nom', $e['nom'], $erreurs, ['required' => true]) ?>
			<?= champ('prenom', 'Prénoms', $e['prenom'], $erreurs, ['required' => true]) ?>
		</div>
		<div class="ligne">
			<?= champ('date_naissance', 'Date de naissance', $e['date_naissance'], $erreurs, ['type' => 'date', 'required' => true]) ?>
			<?= champ('lieu_naissance', 'Lieu de naissance', $e['lieu_naissance'], $erreurs, ['required' => true]) ?>
		</div>
		<div class="form-actions">
			<button class="btn" type="submit"><i class="fa-solid fa-circle-check"></i> Enregistrer</button>
			<a class="btn secondaire" href="<?= e(admin_url('etudiants')) ?>">Annuler</a>
		</div>
	</form>
	<aside class="carte">
		<h2><i class="fa-solid fa-circle-info"></i> À savoir</h2>
		<p>L’étudiant crée son compte en ligne en saisissant <strong>exactement</strong> ce n° de suivi, ces nom et prénoms, cette date et ce lieu de naissance
			(les accents, majuscules et l’ordre des prénoms ne comptent pas). Il complète ensuite son parcours, son e-mail et son mot de passe.</p>
<?php if ($fiche): ?>
		<p>Fiche lecteur liée : <a href="<?= e(admin_url('lecteurs/voir/' . $fiche['id'])) ?>"><?= e(nom_complet($fiche)) ?></a>
			<?= $fiche['mot_de_passe'] !== '' ? '(compte en ligne)' : '(fiche guichet, sans compte)' ?>. Le nom, les prénoms et la faculté y seront repris.</p>
<?php endif; ?>
		<p class="doux">Ce qui est saisi ici sera remplacé au prochain chargement du fichier de la scolarité si ce n° de suivi y figure avec d’autres informations.</p>
	</aside>
</div>
