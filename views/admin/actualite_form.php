<?php
/** Fiche d'une actualité ou d'un évènement. @var array $a, $erreurs @var bool $lectureSeule */
$dt = fn($v) => $v ? date('Y-m-d\TH:i', strtotime((string) $v)) : '';
$publier = peut('actualites.publier');
$evenement = $a['type'] === 'evenement';
$maxVideo = round(video_taille_max() / 1048576);
?>
<div class="fil"><a href="<?= e(admin_url('actualites')) ?>">Actualités et évènements</a> ›</div>
<div class="entete">
	<h1><?= $a['id'] ? e($a['titre']) : ($evenement ? 'Nouvel évènement' : 'Nouvelle actualité') ?></h1>
	<?= $lectureSeule ? '<span class="badge">Lecture seule</span>' : '' ?>
	<div class="actions"><?php if ($a['id'] && $a['publie']): ?><a class="btn secondaire" href="<?= e(actualite_url($a)) ?>" target="_blank" rel="noopener"><i class="fa-solid fa-eye"></i> Voir sur le site</a><?php endif; ?></div>
</div>

<form method="post" enctype="multipart/form-data" id="form-actualite">
	<?= csrf_field() ?>
	<fieldset class="sans-cadre"<?= $lectureSeule ? ' disabled' : '' ?>>
	<div class="grille grille-form">
		<div class="carte">
			<div class="champ">
				<label>Type</label>
				<div class="choix-type">
<?php foreach (TYPES_ACTUALITE as $cle => $lib): ?>
					<label class="case"><input type="radio" name="type" value="<?= $cle ?>"<?= $a['type'] === $cle ? ' checked' : '' ?>> <?= $cle === 'evenement' ? '<i class="fa-solid fa-calendar-day"></i>' : '<i class="fa-solid fa-newspaper"></i>' ?> <?= e($lib) ?></label>
<?php endforeach; ?>
				</div>
			</div>
			<?= champ('titre', 'Titre', $a['titre'], $erreurs, ['required' => true, 'attrs' => 'maxlength="190"']) ?>
			<?= champ('slug', 'Adresse de la page', $a['slug'], $erreurs, ['attrs' => 'data-slug-de="titre"', 'aide' => 'Laissée vide, elle est tirée du titre : index.php/actualites/…']) ?>
			<?= champ('chapo', 'Présentation courte', $a['chapo'], $erreurs, ['type' => 'textarea', 'required' => true, 'attrs' => 'rows="3" maxlength="400" style="min-height:0"',
				'aide' => 'Deux ou trois phrases : affichées sur la diapositive de l’accueil, les cartes et en tête de la page.']) ?>
			<div class="bloc-evenement" data-si-type="evenement"<?= $evenement ? '' : ' hidden' ?>>
				<div class="ligne">
					<?= champ('debut', 'Début', $dt($a['debut']), $erreurs, ['type' => 'datetime-local']) ?>
					<?= champ('fin', 'Fin (facultatif)', $dt($a['fin']), $erreurs, ['type' => 'datetime-local']) ?>
				</div>
				<?= champ('lieu', 'Lieu', $a['lieu'], $erreurs, ['attrs' => 'maxlength="190"', 'placeholder' => 'ex. Salle de lecture, bâtiment central']) ?>
			</div>
			<?= champ('contenu', 'Texte de la page', $a['contenu'], $erreurs, ['type' => 'textarea', 'attrs' => 'rows="12"',
				'aide' => 'Séparez les paragraphes par une ligne vide.']) ?>
		</div>

		<aside>
			<div class="carte">
				<h2><i class="fa-solid fa-image"></i> Image et vidéo</h2>
				<div class="champ<?= isset($erreurs['image']) ? ' en-erreur' : '' ?>">
					<label>Image</label>
					<img id="apercu-image" class="apercu-image apercu-large" src="<?= $a['image'] ? e(media($a['image'])) : '' ?>" alt="" style="<?= $a['image'] ? '' : 'display:none' ?>">
					<input type="file" name="image" accept="image/jpeg,image/png,image/webp" data-apercu="apercu-image">
					<div class="aide">Paysage, 1920×1080 px conseillé. Fond de la diapositive, image des cartes et de la page.</div>
					<?php if (isset($erreurs['image'])): ?><div class="erreur"><?= e($erreurs['image']) ?></div><?php endif; ?>
				</div>
				<div class="champ<?= isset($erreurs['video']) ? ' en-erreur' : '' ?>">
					<label>Vidéo de fond (facultatif)</label>
<?php if ($a['video']): ?>
					<video src="<?= e(media($a['video'])) ?>" class="apercu-image apercu-large" muted loop playsinline controls preload="metadata"></video>
					<label class="case"><input type="checkbox" name="retirer_video" value="1"> Retirer la vidéo</label>
<?php endif; ?>
					<input type="file" name="video" accept="video/mp4,video/webm">
					<div class="aide">MP4 ou WebM, <?= $maxVideo ?> Mo au maximum sur ce serveur, quelques secondes en boucle, sans son. Elle remplace l’image sur la diapositive (l’image reste affichée pendant le chargement).</div>
					<?php if (isset($erreurs['video'])): ?><div class="erreur"><?= e($erreurs['video']) ?></div><?php endif; ?>
				</div>
			</div>

			<div class="carte">
				<h2><i class="fa-solid fa-images"></i> Diaporama de l’accueil</h2>
<?php if ($publier): ?>
				<label class="case"><input type="checkbox" name="au_diaporama" value="1"<?= $a['au_diaporama'] ? ' checked' : '' ?>> Faire défiler dans le diaporama de l’accueil</label>
<?php else: ?>
				<p class="doux"><?= $a['au_diaporama'] ? 'Au diaporama.' : 'Pas au diaporama.' ?> (droit « publier » nécessaire pour changer.)</p>
<?php endif; ?>
				<?= champ('animation', 'Effet de la diapositive', $a['animation'], $erreurs, ['type' => 'select', 'options' => ANIMATIONS_DIAPO, 'attrs' => 'data-apercu-animation="#apercu-animation"']) ?>
				<div class="apercu-diapo" id="apercu-animation" data-animation="<?= e($a['animation']) ?>">
					<div class="fond" style="<?= $a['image'] ? 'background-image:url(' . e(media($a['image'])) . ')' : '' ?>"></div>
					<div class="texte"><span class="etiquette"><?= e(TYPES_ACTUALITE[$a['type']]) ?></span><strong><?= e($a['titre'] ?: 'Titre de l’actualité') ?></strong></div>
				</div>
				<div class="aide">Aperçu de l’effet (il se rejoue quand vous changez de choix).</div>
			</div>

			<div class="carte">
				<h2><i class="fa-solid fa-globe"></i> Publication</h2>
				<?= champ('date_publication', 'Date de publication', $dt($a['date_publication']), $erreurs, ['type' => 'datetime-local',
					'aide' => 'Une date future programme la publication : l’actualité n’apparaît qu’à partir de ce moment.']) ?>
<?php if ($publier): ?>
				<label class="case"><input type="checkbox" name="publie" value="1"<?= $a['publie'] ? ' checked' : '' ?>> Publiée sur le site</label>
<?php else: ?>
				<p class="doux"><?= $a['publie'] ? 'Publiée.' : 'Brouillon : une personne ayant le droit « publier » la mettra en ligne.' ?></p>
<?php endif; ?>
			</div>
		</aside>
	</div>
	</fieldset>
	<div class="form-actions">
<?php if (!$lectureSeule): ?>
		<button class="btn" type="submit"><i class="fa-solid fa-circle-check"></i> Enregistrer</button>
<?php endif; ?>
		<a class="btn secondaire" href="<?= e(admin_url('actualites')) ?>"><?= $lectureSeule ? 'Retour à la liste' : 'Annuler' ?></a>
	</div>
</form>

<?php if ($a['id'] && peut('actualites.supprimer')): ?>
<form method="post" action="<?= e(admin_url('actualites/supprimer/' . $a['id'])) ?>" data-confirmer="Supprimer définitivement « <?= e($a['titre']) ?> » ?" style="margin-top:12px">
	<?= csrf_field() ?>
	<button class="btn danger" type="submit"><i class="fa-solid fa-trash"></i> Supprimer</button>
</form>
<?php endif; ?>
