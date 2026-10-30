<?php
/** Champs d'identité d'un lecteur (fiche admin et création rapide au guichet). @var array $l @var array $erreurs */
$facultes = valeurs_existantes('faculte');
$parcours = valeurs_existantes('parcours');
?>
<div class="ligne">
	<?= champ('prenom', 'Prénom', $l['prenom'] ?? '', $erreurs, ['required' => true]) ?>
	<?= champ('nom', 'Nom', $l['nom'] ?? '', $erreurs, ['required' => true]) ?>
</div>
<div class="ligne">
	<?= champ('categorie', 'Catégorie', $l['categorie'] ?? 'etudiant', $erreurs, ['type' => 'select', 'options' => LIBELLES_CATEGORIE,
		'attrs' => 'data-montre-etudiant', 'aide' => 'Détermine le nombre de prêts simultanés (Réglages).']) ?>
	<?= champ('matricule', 'Matricule / n° de suivi', $l['matricule'] ?? '', $erreurs, ['aide' => 'Étudiant : son n° de suivi. Obligatoire sauf pour un lecteur externe.']) ?>
</div>
<div class="ligne bloc-etudiant">
	<?= champ('faculte', 'Faculté', $l['faculte'] ?? '', $erreurs, ['attrs' => 'list="liste-facultes"', 'placeholder' => 'ex. Faculté des Sciences et Techniques']) ?>
	<?= champ('parcours', 'Parcours', $l['parcours'] ?? '', $erreurs, ['attrs' => 'list="liste-parcours"', 'placeholder' => 'ex. Génie logiciel']) ?>
</div>
<datalist id="liste-facultes"><?php foreach ($facultes as $f): ?><option value="<?= e($f) ?>"><?php endforeach; ?></datalist>
<datalist id="liste-parcours"><?php foreach ($parcours as $d): ?><option value="<?= e($d) ?>"><?php endforeach; ?></datalist>
<div class="ligne">
	<?= champ('telephone', 'Téléphone', $l['telephone'] ?? '', $erreurs, ['type' => 'tel', 'required' => true]) ?>
	<?= champ('email', 'E-mail', $l['email'] ?? '', $erreurs, ['type' => 'email', 'aide' => 'Pour les rappels d’échéance ; obligatoire avec l’accès en ligne.']) ?>
</div>
