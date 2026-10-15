<?php /** @var array $groupes  [titre => [clé => [libellé, type, minimum?]]] */ ?>
<div class="entete"><h1>Réglages</h1></div>
<form method="post" class="grille grille-2">
	<?= csrf_field() ?>
<?php foreach ($groupes as $titre => $champs): ?>
	<section class="carte">
		<h2><?= e($titre) ?></h2>
	<?php foreach ($champs as $cle => $def): ?>
		<?= champ($cle, $def[0], reglage($cle), [], ['type' => $def[1], 'attrs' => $def[1] === 'number' ? 'min="' . (int) ($def[2] ?? 0) . '"' : '']) ?>
	<?php endforeach; ?>
	</section>
<?php endforeach; ?>
	<div class="form-actions pleine-largeur"><button class="btn" type="submit"><i class="fa-solid fa-circle-check"></i> Enregistrer les réglages</button></div>
</form>
