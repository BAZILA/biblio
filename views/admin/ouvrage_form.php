<?php
/** @var array $o, $erreurs, $exemplaires, $domaines, $types, $file */
$optDomaines = ['' => '— Choisir —'] + array_column($domaines, 'nom', 'id');
$optTypes = ['' => '— Aucun —'] + array_column($types, 'nom', 'id');
$langues = ['Français', 'Anglais', 'Arabe', 'Espagnol', 'Allemand', 'Autre'];
?>
<div class="fil"><a href="<?= e(admin_url('ouvrages')) ?>">Ouvrages</a> ›</div>
<div class="entete">
	<h1><?= $o['id'] ? e($o['titre']) : 'Nouvel ouvrage' ?></h1>
<?php if ($o['id']): ?>
	<div class="actions">
		<?php if ($o['publie']): ?><a class="btn secondaire" href="<?= e(ouvrage_url($o)) ?>" target="_blank" rel="noopener"><i class="fa-solid fa-eye"></i> Voir sur le site</a><?php endif; ?>
	</div>
<?php endif; ?>
</div>

<form method="post" enctype="multipart/form-data" class="grille grille-form">
	<?= csrf_field() ?>
	<div>
		<section class="carte">
			<?= champ('titre', 'Titre', $o['titre'], $erreurs, ['required' => true]) ?>
			<?= champ('auteurs_txt', 'Auteur(s)', $o['auteurs_txt'], $erreurs, ['type' => 'textarea', 'attrs' => 'rows="3" style="min-height:0"',
				'aide' => 'Un auteur par ligne, dans l’ordre d’affichage. Les nouveaux noms sont ajoutés à l’index des auteurs.']) ?>
			<div class="ligne">
				<?= champ('domaine_id', 'Domaine', $o['domaine_id'], $erreurs, ['type' => 'select', 'options' => $optDomaines, 'required' => true]) ?>
				<?= champ('type_id', 'Type de document', $o['type_id'], $erreurs, ['type' => 'select', 'options' => $optTypes]) ?>
			</div>
			<?= champ('resume', 'Résumé', $o['resume'], $erreurs, ['type' => 'textarea',
				'aide' => 'Affiché sur la fiche et dans les listes. Séparez les paragraphes par une ligne vide. Mise en forme possible avec <strong>, <em>, <ul>/<li>, <a>.']) ?>
			<?= champ('description', 'Description complémentaire (facultatif)', $o['description'], $erreurs, ['type' => 'textarea', 'aide' => 'Affichée sous le résumé, sur la fiche uniquement.']) ?>
		</section>

		<section class="carte">
			<h2>Notice bibliographique</h2>
			<div class="ligne">
				<?= champ('editeur', 'Éditeur / Origine', $o['editeur'], $erreurs) ?>
				<?= champ('annee', 'Année de publication', $o['annee'], $erreurs) ?>
			</div>
			<div class="ligne">
				<?= champ('cote', 'Cote', $o['cote'], $erreurs, ['aide' => 'Sert aussi de préfixe aux codes des exemplaires (ex. INF-001-1).']) ?>
				<?= champ('isbn', 'ISBN', $o['isbn'], $erreurs) ?>
			</div>
			<div class="ligne">
				<?= champ('pages', 'Nombre de pages', $o['pages'], $erreurs, ['type' => 'number', 'attrs' => 'min="1"']) ?>
				<?= champ('langue', 'Langue', $o['langue'], $erreurs, ['type' => 'select', 'options' => array_combine($langues, $langues)]) ?>
			</div>
		</section>

<?php if ($o['id']): ?>
		<section class="carte" id="exemplaires">
			<div class="carte-titre"><h2>Exemplaires (<?= count(array_filter($exemplaires, fn($x) => $x['statut'] !== 'retire')) ?> en circulation)</h2></div>
<?php if (!$exemplaires): ?>
			<p class="doux">Aucun exemplaire : l’ouvrage apparaît comme indisponible sur le site.</p>
<?php else: ?>
			<div class="tableau-wrap"><table class="tableau">
				<thead><tr><th>Code</th><th>État</th><th>Statut</th><th>Notes</th><th></th></tr></thead>
				<tbody>
<?php foreach ($exemplaires as $x): $fid = 'ex' . $x['id']; ?>
					<tr>
						<td><input form="<?= $fid ?>" type="text" name="code" value="<?= e($x['code']) ?>" style="width:130px" class="code"></td>
						<td><select form="<?= $fid ?>" name="etat" style="width:auto"><?php foreach (ETATS_EXEMPLAIRE as $k => $lib): ?><option value="<?= $k ?>"<?= $x['etat'] === $k ? ' selected' : '' ?>><?= e($lib) ?></option><?php endforeach; ?></select></td>
						<td><?= badge_exemplaire($x['statut']) ?><?= $x['emprunteur'] ? '<br><small>' . e($x['emprunteur']) . '</small>' : '' ?><br><small><?= e(pluriel((int) $x['nb_prets'], 'prêt')) ?> au total</small></td>
						<td><input form="<?= $fid ?>" type="text" name="notes" value="<?= e($x['notes']) ?>" placeholder="Remarque…"></td>
						<td class="actions">
							<button form="<?= $fid ?>" class="btn secondaire petit" type="submit" title="Enregistrer"><i class="fa-solid fa-circle-check"></i></button>
							<?php if ($x['statut'] === 'retire'): ?>
								<button form="<?= $fid ?>-remettre" class="btn secondaire petit" type="submit">Remettre</button>
							<?php elseif ($x['statut'] !== 'prete'): ?>
								<button form="<?= $fid ?>-retirer" class="btn secondaire petit" type="submit">Retirer</button>
							<?php endif; ?>
							<?php if (!$x['nb_prets']): ?>
								<button form="<?= $fid ?>-supprimer" class="btn danger petit" type="submit" title="Supprimer"><i class="fa-solid fa-trash"></i></button>
							<?php endif; ?>
						</td>
					</tr>
<?php endforeach; ?>
				</tbody>
			</table></div>
<?php endif; ?>
			<div class="form-actions" style="margin-top:14px">
				<input form="ajout-ex" type="number" name="nombre" value="1" min="1" max="50" style="width:80px">
				<button form="ajout-ex" class="btn secondaire" type="submit"><i class="fa-solid fa-plus"></i> Ajouter des exemplaires</button>
			</div>
		</section>
<?php endif; ?>
	</div>

	<aside>
		<section class="carte">
			<h2>Publication</h2>
			<?= champ('publie', 'Publié sur le site', $o['publie'], $erreurs, ['type' => 'case']) ?>
			<?= champ('coup_de_coeur', 'Coup de cœur des bibliothécaires (accueil)', $o['coup_de_coeur'], $erreurs, ['type' => 'case']) ?>
			<?= champ('consultation_sur_place', 'Consultation sur place uniquement (exclu du prêt)', $o['consultation_sur_place'], $erreurs, ['type' => 'case']) ?>
			<?= champ('slug', 'Adresse de la fiche', $o['slug'], $erreurs, ['attrs' => 'data-slug-de="titre"', 'aide' => 'Générée à partir du titre si vide.']) ?>
<?php if (!$o['id']): ?>
			<?= champ('nb_exemplaires', 'Exemplaires à créer', 1, $erreurs, ['type' => 'number', 'attrs' => 'min="0" max="50"']) ?>
<?php endif; ?>
			<div class="form-actions">
				<button class="btn" type="submit"><i class="fa-solid fa-circle-check"></i> Enregistrer</button>
				<a class="btn secondaire" href="<?= e(admin_url('ouvrages')) ?>">Annuler</a>
			</div>
		</section>

		<section class="carte">
			<h2>Couverture</h2>
			<img id="apercu-couverture" class="apercu-image" src="<?= $o['image'] ? e(media($o['image'])) : '' ?>" alt="" style="<?= $o['image'] ? '' : 'display:none' ?>">
			<div class="champ<?= isset($erreurs['image']) ? ' en-erreur' : '' ?>">
				<input type="file" name="image" accept="image/jpeg,image/png,image/webp,image/gif" data-apercu="apercu-couverture">
				<div class="aide">JPG, PNG ou WebP, 5 Mo max. Format portrait conseillé (2:3).</div>
				<?php if (isset($erreurs['image'])): ?><div class="erreur"><?= e($erreurs['image']) ?></div><?php endif; ?>
			</div>
			<?php if ($o['image']): ?><?= champ('retirer_image', 'Retirer la couverture', 0, [], ['type' => 'case']) ?><?php endif; ?>
		</section>

<?php if ($file): ?>
		<section class="carte">
			<h2>File de réservation</h2>
			<ol style="padding-left:18px;margin:0">
<?php foreach ($file as $r): ?>
				<li><?= e(trim($r['prenom'] . ' ' . $r['nom'])) ?> — <?= $r['statut'] === 'disponible' ? '<span class="badge vert">' . e($r['exemplaire_code']) . ' à retirer</span>' : '<span class="badge">en attente</span>' ?></li>
<?php endforeach; ?>
			</ol>
		</section>
<?php endif; ?>

<?php if ($o['id']): ?>
		<section class="carte">
			<h2>Zone sensible</h2>
			<p class="doux">La suppression est impossible si l’ouvrage a déjà été prêté : dépubliez-le à la place.</p>
			<button form="suppr-ouvrage" class="btn danger" type="submit"><i class="fa-solid fa-trash"></i> Supprimer l’ouvrage</button>
		</section>
<?php endif; ?>
	</aside>
</form>

<?php if ($o['id']): ?>
<?php /* Formulaires des actions sur les exemplaires (hors du formulaire principal : pas de formulaires imbriqués) */ ?>
<?php foreach ($exemplaires as $x): $fid = 'ex' . $x['id']; ?>
<form id="<?= $fid ?>" method="post" action="<?= e(admin_url('exemplaires/modifier/' . $x['id'])) ?>"><?= csrf_field() ?></form>
<form id="<?= $fid ?>-retirer" method="post" action="<?= e(admin_url('exemplaires/retirer/' . $x['id'])) ?>" data-confirmer="Retirer l’exemplaire <?= e($x['code']) ?> de la circulation (perdu, abîmé, désherbé) ?"><?= csrf_field() ?></form>
<form id="<?= $fid ?>-remettre" method="post" action="<?= e(admin_url('exemplaires/remettre/' . $x['id'])) ?>"><?= csrf_field() ?></form>
<form id="<?= $fid ?>-supprimer" method="post" action="<?= e(admin_url('exemplaires/supprimer/' . $x['id'])) ?>" data-confirmer="Supprimer définitivement l’exemplaire <?= e($x['code']) ?> ?"><?= csrf_field() ?></form>
<?php endforeach; ?>
<form id="ajout-ex" method="post" action="<?= e(admin_url('exemplaires/ajouter/' . $o['id'])) ?>"><?= csrf_field() ?></form>
<form id="suppr-ouvrage" method="post" action="<?= e(admin_url('ouvrages/supprimer/' . $o['id'])) ?>" data-confirmer="Supprimer définitivement « <?= e($o['titre']) ?> » et ses exemplaires ?"><?= csrf_field() ?></form>
<?php endif; ?>
