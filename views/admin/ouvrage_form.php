<?php
/** Fiche d'édition d'un ouvrage. @var array $o, $erreurs, $exemplaires, $domaines, $types, $file @var bool $lectureSeule */
$exModifier = peut('exemplaires.modifier');
$optDomaines = ['' => '— Choisir —'] + array_column($domaines, 'nom', 'id');
$optTypes = ['' => '— Aucun —'] + array_column($types, 'nom', 'id');
$langues = ['Français', 'Anglais', 'Arabe', 'Espagnol', 'Allemand', 'Autre'];
$enCirculation = count(array_filter($exemplaires, fn($x) => $x['statut'] !== 'retire'));
$disponibles = count(array_filter($exemplaires, fn($x) => $x['statut'] === 'disponible'));
$dejaPrete = array_sum(array_column($exemplaires, 'nb_prets')) > 0;
?>
<div class="fil"><a href="<?= e(admin_url('ouvrages')) ?>">Ouvrages</a> ›</div>
<div class="entete">
	<div>
		<h1><?= $o['id'] ? e($o['titre']) : 'Nouvel ouvrage' ?></h1>
<?php if ($o['id']): ?>
		<div class="sous-titre">
			<?= !$o['actif'] ? '<span class="badge rouge">Désactivé</span>' : ($o['publie'] ? '<span class="badge vert">Publié</span>' : '<span class="badge orange">Non publié</span>') ?>
			<?= $o['coup_de_coeur'] ? '<span class="badge bleu">Coup de cœur</span>' : '' ?>
			<span class="badge"><?= $disponibles ?> / <?= $enCirculation ?> exemplaire(s) disponible(s)</span>
		</div>
<?php endif; ?>
	</div>
<?php if ($o['id']): ?>
	<div class="actions">
		<?php if ($o['actif'] && !$o['consultation_sur_place'] && $disponibles && peut('prets.ajouter')): ?><a class="btn" href="<?= e(admin_url('guichet', ['ouvrage' => $o['id']])) ?>"><i class="fa-solid fa-right-left"></i> Prêter</a><?php endif; ?>
		<?php if ($o['publie']): ?><a class="btn secondaire" href="<?= e(ouvrage_url($o)) ?>" target="_blank" rel="noopener"><i class="fa-solid fa-eye"></i> Voir sur le site</a><?php endif; ?>
	</div>
<?php endif; ?>
</div>

<form method="post" enctype="multipart/form-data" class="fiche-ouvrage" id="form-ouvrage">
	<?= csrf_field() ?>
	<fieldset class="contenu-transparent"<?= $lectureSeule ? ' disabled' : '' ?>>

	<aside class="fiche-ouvrage-cote">
		<section class="carte">
			<h2><i class="fa-solid fa-image"></i> Couverture</h2>
			<div class="zone-couverture<?= isset($erreurs['image']) ? ' en-erreur' : '' ?>">
				<img id="apercu-couverture" src="<?= e(couverture($o['image'])) ?>" alt="Couverture">
				<?php if (!$o['image']): ?><span class="etiquette-defaut">Couverture par défaut</span><?php endif; ?>
			</div>
			<label class="btn secondaire bouton-fichier" for="f_image"><i class="fa-solid fa-upload"></i> <?= $o['image'] ? 'Changer l’image…' : 'Choisir une image…' ?></label>
			<input type="file" id="f_image" name="image" accept="image/jpeg,image/png,image/webp,image/gif" data-apercu="apercu-couverture" class="fichier-cache">
			<div class="nom-fichier doux" data-pour="f_image"></div>
			<div class="aide">JPG, PNG ou WebP, 5 Mo max., format portrait. Sans image, la couverture par défaut est affichée (modifiable dans Réglages).</div>
			<?php if (isset($erreurs['image'])): ?><div class="erreur"><?= e($erreurs['image']) ?></div><?php endif; ?>
			<?php if ($o['image']): ?><?= champ('retirer_image', 'Revenir à la couverture par défaut', 0, [], ['type' => 'case']) ?><?php endif; ?>
		</section>

		<section class="carte">
			<h2><i class="fa-solid fa-sliders"></i> Options</h2>
			<label class="option-interrupteur"><input type="checkbox" name="publie" value="1"<?= $o['publie'] ? ' checked' : '' ?><?= $o['actif'] ? '' : ' disabled' ?>><span><strong>Visible sur le site</strong><small><?= $o['actif'] ? 'Décochez pour préparer la fiche sans la publier.' : 'Ouvrage désactivé : réactivez-le (en bas de page) pour le publier.' ?></small></span></label>
			<label class="option-interrupteur"><input type="checkbox" name="coup_de_coeur" value="1"<?= $o['coup_de_coeur'] ? ' checked' : '' ?>><span><strong>Coup de cœur</strong><small>Mis en avant sur la page d’accueil.</small></span></label>
			<label class="option-interrupteur"><input type="checkbox" name="consultation_sur_place" value="1"<?= $o['consultation_sur_place'] ? ' checked' : '' ?>><span><strong>Consultation sur place</strong><small>Exclu du prêt (dictionnaires, thèses…).</small></span></label>
<?php if (!$o['id'] && peut('exemplaires.ajouter')): ?>
			<?= champ('nb_exemplaires', 'Exemplaires à créer', 1, $erreurs, ['type' => 'number', 'attrs' => 'min="0" max="50"', 'aide' => 'Codes créés automatiquement : cote-1, cote-2…']) ?>
<?php endif; ?>
			<?= champ('slug', 'Adresse web de la fiche', $o['slug'], $erreurs, ['attrs' => 'data-slug-de="titre"', 'aide' => 'Remplie automatiquement à partir du titre.']) ?>
		</section>

<?php if ($file): ?>
		<section class="carte">
			<h2><i class="fa-solid fa-bookmark"></i> File de réservation</h2>
			<ol class="liste-file">
<?php foreach ($file as $r): ?>
				<li><?= e(trim($r['prenom'] . ' ' . $r['nom'])) ?> <?= $r['statut'] === 'disponible' ? '<span class="badge vert">' . e($r['exemplaire_code']) . ' à retirer</span>' : '<span class="badge">en attente</span>' ?></li>
<?php endforeach; ?>
			</ol>
		</section>
<?php endif; ?>
	</aside>

	<div class="fiche-ouvrage-principal">
		<section class="carte">
			<h2><i class="fa-solid fa-book"></i> Informations</h2>
			<?= champ('titre', 'Titre', $o['titre'], $erreurs, ['required' => true]) ?>
			<div class="champ">
				<label for="auteurs-saisie">Auteur(s)</label>
				<div class="champ-auteurs" data-url="<?= e(admin_url('auteurs/chercher')) ?>">
					<textarea name="auteurs_txt" id="f_auteurs_txt" rows="3"><?= e($o['auteurs_txt']) ?></textarea>
				</div>
				<div class="aide">Tapez un nom : choisissez un auteur existant dans la liste, ou ajoutez-le s’il n’existe pas encore. L’ordre est celui de l’affichage.</div>
			</div>
			<div class="ligne">
				<?= champ('domaine_id', 'Domaine', $o['domaine_id'], $erreurs, ['type' => 'select', 'options' => $optDomaines, 'required' => true]) ?>
				<?= champ('type_id', 'Type de document', $o['type_id'], $erreurs, ['type' => 'select', 'options' => $optTypes]) ?>
			</div>
		</section>

		<section class="carte">
			<h2><i class="fa-solid fa-align-left"></i> Présentation</h2>
			<?= champ('resume', 'Résumé', $o['resume'], $erreurs, ['type' => 'textarea', 'aide' => 'Affiché sur la fiche et dans les listes. Séparez les paragraphes par une ligne vide.']) ?>
			<?= champ('description', 'Description complémentaire (facultatif)', $o['description'], $erreurs, ['type' => 'textarea', 'aide' => 'Affichée sous le résumé, sur la fiche uniquement.']) ?>
		</section>

		<section class="carte">
			<h2><i class="fa-solid fa-barcode"></i> Notice bibliographique</h2>
			<div class="ligne">
				<?= champ('editeur', 'Éditeur / Origine', $o['editeur'], $erreurs) ?>
				<?= champ('annee', 'Année', $o['annee'], $erreurs) ?>
				<?= champ('langue', 'Langue', $o['langue'], $erreurs, ['type' => 'select', 'options' => array_combine($langues, $langues)]) ?>
			</div>
			<div class="ligne">
				<?= champ('cote', 'Cote', $o['cote'], $erreurs, ['aide' => 'Préfixe des codes d’exemplaires (ex. INF-001-1).']) ?>
				<?= champ('isbn', 'ISBN', $o['isbn'], $erreurs) ?>
				<?= champ('pages', 'Pages', $o['pages'], $erreurs, ['type' => 'number', 'attrs' => 'min="1"']) ?>
			</div>
		</section>

	</div>
	</fieldset>

<?php if ($o['id']): ?>
		<section class="carte pleine" id="exemplaires">
			<div class="carte-titre"><h2><i class="fa-solid fa-layer-group"></i> Exemplaires <small class="doux">(<?= $enCirculation ?> en circulation)</small></h2></div>
<?php if (!$exemplaires): ?>
			<p class="doux">Aucun exemplaire : l’ouvrage apparaît comme indisponible sur le site.</p>
<?php else: ?>
			<div class="tableau-wrap"><table class="tableau tableau-exemplaires">
				<thead><tr><th>Code</th><th>État</th><th>Statut</th><th>Notes</th><th></th></tr></thead>
				<tbody>
<?php foreach ($exemplaires as $x): $fid = 'ex' . $x['id']; ?>
					<tr>
						<td><input form="<?= $fid ?>" type="text" name="code" value="<?= e($x['code']) ?>" class="code"<?= $exModifier ? '' : ' disabled' ?>></td>
						<td><select form="<?= $fid ?>" name="etat"<?= $exModifier ? '' : ' disabled' ?>><?php foreach (ETATS_EXEMPLAIRE as $k => $lib): ?><option value="<?= $k ?>"<?= $x['etat'] === $k ? ' selected' : '' ?>><?= e($lib) ?></option><?php endforeach; ?></select></td>
						<td><?= badge_exemplaire($x['statut']) ?><?= $x['emprunteur'] ? '<br><small>' . e($x['emprunteur']) . '</small>' : '' ?><br><small><?= e(pluriel((int) $x['nb_prets'], 'prêt')) ?> au total</small></td>
						<td><input form="<?= $fid ?>" type="text" name="notes" value="<?= e($x['notes']) ?>" placeholder="Remarque (emplacement, dégâts…)"<?= $exModifier ? '' : ' disabled' ?>></td>
						<td class="actions">
							<?php if ($exModifier): ?><button form="<?= $fid ?>" class="btn secondaire petit" type="submit" title="Enregistrer"><i class="fa-solid fa-circle-check"></i></button><?php endif; ?>
							<?php if (!peut('exemplaires.retirer')): ?>
							<?php elseif ($x['statut'] === 'retire'): ?>
								<button form="<?= $fid ?>-remettre" class="btn secondaire petit" type="submit">Remettre</button>
							<?php elseif ($x['statut'] !== 'prete'): ?>
								<button form="<?= $fid ?>-retirer" class="btn secondaire petit" type="submit">Retirer</button>
							<?php endif; ?>
							<?php if (!$x['nb_prets'] && peut('exemplaires.supprimer')): ?>
								<button form="<?= $fid ?>-supprimer" class="btn danger petit" type="submit" title="Supprimer"><i class="fa-solid fa-trash"></i></button>
							<?php endif; ?>
						</td>
					</tr>
<?php endforeach; ?>
				</tbody>
			</table></div>
<?php endif; ?>
<?php if (peut('exemplaires.ajouter')): ?>
			<div class="form-actions" style="margin-top:14px">
				<input form="ajout-ex" type="number" name="nombre" value="1" min="1" max="50" style="width:80px">
				<button form="ajout-ex" class="btn secondaire" type="submit"><i class="fa-solid fa-plus"></i> Ajouter des exemplaires</button>
			</div>
<?php endif; ?>
		</section>

<?php if (peut('ouvrages.desactiver') || (peut('ouvrages.supprimer') && !$dejaPrete)): ?>
		<section class="carte zone-sensible pleine">
<?php if ($o['actif']): ?>
			<h2><i class="fa-solid fa-box-archive"></i> Désactiver l’ouvrage</h2>
			<p class="doux">L’ouvrage disparaît du site et ne peut plus être prêté ni réservé ; son historique et ses exemplaires sont conservés. Les réservations en cours sont annulées (les lecteurs sont prévenus), les prêts en cours se terminent normalement. Vous pourrez le réactiver à tout moment.</p>
			<div class="form-actions">
<?php if (peut('ouvrages.desactiver')): ?>
				<button form="desactiver-ouvrage" class="btn danger" type="submit"><i class="fa-solid fa-box-archive"></i> Désactiver</button>
<?php endif; ?>
<?php if (!$dejaPrete && peut('ouvrages.supprimer')): ?>
				<button form="suppr-ouvrage" class="btn lien-danger" type="submit">ou supprimer définitivement (fiche créée par erreur, jamais prêtée)</button>
<?php endif; ?>
			</div>
<?php else: ?>
			<h2><i class="fa-solid fa-box-archive"></i> Ouvrage désactivé<?= $o['desactive_le'] ? ' le ' . e(date_fr($o['desactive_le'])) : '' ?></h2>
			<p class="doux">Il n’apparaît plus sur le site et ne peut être ni prêté ni réservé. Réactivez-le pour le remettre au catalogue.</p>
<?php if (peut('ouvrages.desactiver')): ?>
			<button form="reactiver-ouvrage" class="btn vert" type="submit"><i class="fa-solid fa-rotate-left"></i> Réactiver l’ouvrage</button>
<?php endif; ?>
<?php endif; ?>
		</section>
<?php endif; ?>
<?php endif; ?>

	<div class="barre-actions">
		<span class="doux"><?= $lectureSeule ? 'Lecture seule : votre rôle ne permet pas de modifier les ouvrages.' : ($o['id'] ? 'Modifications non enregistrées tant que vous ne cliquez pas sur Enregistrer.' : 'Nouvel ouvrage') ?></span>
		<a class="btn secondaire" href="<?= e(admin_url('ouvrages')) ?>"><?= $lectureSeule ? 'Retour à la liste' : 'Annuler' ?></a>
<?php if (!$lectureSeule): ?>
		<button class="btn" type="submit"><i class="fa-solid fa-circle-check"></i> Enregistrer</button>
<?php endif; ?>
	</div>
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
<form id="suppr-ouvrage" method="post" action="<?= e(admin_url('ouvrages/supprimer/' . $o['id'])) ?>" data-confirmer="Supprimer définitivement « <?= e($o['titre']) ?> » et ses exemplaires ? Cette action est irréversible."><?= csrf_field() ?></form>
<form id="desactiver-ouvrage" method="post" action="<?= e(admin_url('ouvrages/desactiver/' . $o['id'])) ?>" data-confirmer="Désactiver « <?= e($o['titre']) ?> » ? Il sera retiré du site, du prêt et des réservations."><?= csrf_field() ?></form>
<form id="reactiver-ouvrage" method="post" action="<?= e(admin_url('ouvrages/reactiver/' . $o['id'])) ?>"><?= csrf_field() ?></form>
<?php endif; ?>
