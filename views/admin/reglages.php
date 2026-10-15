<?php /** @var array $groupes  [titre => [clé => [libellé, type, minimum|options]]] */ ?>
<div class="entete"><h1>Réglages</h1></div>
<form method="post" class="grille grille-2" enctype="multipart/form-data" autocomplete="off">
	<?= csrf_field() ?>
<?php foreach ($groupes as $titre => $champs): ?>
	<section class="carte">
		<h2><?= e($titre) ?></h2>
<?php if ($titre === 'E-mails aux lecteurs'): ?>
		<p class="doux">Les lecteurs qui ont une adresse e-mail reçoivent : la confirmation de chaque prêt, un rappel avant l’échéance, des relances en cas de retard, l’avis « réservation disponible » et la validation de leur compte. Tant que l’envoi est désactivé, ces messages sont seulement consignés dans <a href="<?= e(admin_url('mails')) ?>">E-mails envoyés</a>.</p>
<?php endif; ?>
	<?php foreach ($champs as $cle => $def):
		[$libelle, $type] = $def;
		if ($type === 'image'):
			$valeur = reglage($cle);
			$apercu = $cle === 'couverture_defaut' ? couverture(null) : media($valeur ?: IMAGES_PAR_DEFAUT[$cle]);
			$aide = $cle === 'couverture_defaut'
				? 'Affichée pour tout ouvrage sans couverture. Format portrait 2:3.'
				: 'Photo de la section « La bibliothèque universitaire », sous le diaporama. Format portrait (environ 480 × 615 px).'; ?>
		<div class="champ">
			<label for="f_<?= $cle ?>"><?= e($libelle) ?></label>
			<div style="display:flex;gap:14px;align-items:flex-start">
				<img id="apercu-<?= $cle ?>" src="<?= e($apercu) ?>" alt="" style="width:80px;aspect-ratio:3/4;object-fit:cover;border-radius:4px;border:1px solid var(--bord)">
				<div>
					<input type="file" id="f_<?= $cle ?>" name="<?= $cle ?>" accept="image/jpeg,image/png,image/webp,image/gif" data-apercu="apercu-<?= $cle ?>">
					<div class="aide"><?= e($aide) ?></div>
					<?php if ($valeur !== (IMAGES_PAR_DEFAUT[$cle] ?? '')): ?><label class="case" style="margin-top:6px"><input type="checkbox" name="<?= $cle ?>_retirer" value="1"> Revenir à l’image d’origine</label><?php endif; ?>
				</div>
			</div>
		</div>
		<?php elseif ($type === 'select'): ?>
		<?= champ($cle, $libelle, reglage($cle), [], ['type' => 'select', 'options' => $def[2]]) ?>
		<?php elseif ($type === 'password'): ?>
		<?= champ($cle, $libelle, '', [], ['type' => 'password', 'autocomplete' => 'new-password', 'aide' => reglage($cle) !== '' ? 'Enregistré. Laisser vide pour le conserver.' : 'Non renseigné.']) ?>
		<?php else: ?>
		<?= champ($cle, $libelle, reglage($cle), [], ['type' => $type, 'attrs' => $type === 'number' ? 'min="' . (int) ($def[2] ?? 0) . '"' : '']) ?>
		<?php endif; ?>
	<?php endforeach; ?>
	</section>
<?php endforeach; ?>
	<div class="form-actions pleine-largeur"><button class="btn" type="submit"><i class="fa-solid fa-circle-check"></i> Enregistrer les réglages</button></div>
</form>
