<?php
/** Fiche d'édition d'un ouvrage. @var array $o, $erreurs, $exemplaires, $domaines, $types, $file @var bool $lectureSeule */
$exModifier = peut('exemplaires.modifier');
$optDomaines = ['' => '— Choisir —'] + array_column($domaines, 'nom', 'id');
$optTypes = ['' => '— Choisir —'] + array_column($types, 'nom', 'id');
$deweyActuel = $o['indice_dewey'] !== '' ? (string) val('SELECT libelle FROM dewey WHERE code = ?', [$o['indice_dewey']]) : '';
$langues = ['Français', 'Anglais', 'Arabe', 'Espagnol', 'Allemand', 'Autre'];
$enCirculation = count(array_filter($exemplaires, fn($x) => $x['statut'] !== 'retire'));
$disponibles = count(array_filter($exemplaires, fn($x) => $x['statut'] === 'disponible'));
$dejaPrete = array_sum(array_column($exemplaires, 'nb_prets')) > 0;
// Codes qui ne suivent plus la cote (cote modifiée après coup) : on propose alors la renumérotation
$prefixe = $o['id'] ? prefixe_exemplaire($o) : '';
$codesDecales = count(array_filter($exemplaires, fn($x) => !$x['remplace_par'] && !str_starts_with((string) $x['code'], $prefixe . '-')));
?>
<div class="fil"><a href="<?= e(admin_url('ouvrages')) ?>">Ouvrages</a> ›</div>
<div class="entete">
	<div>
		<h1><?= $o['id'] ? e($o['titre']) : 'Nouvel ouvrage' ?></h1>
<?php if ($o['id'] && $o['sous_titre'] !== ''): ?>
		<div class="sous-titre"><?= e($o['sous_titre']) ?></div>
<?php endif; ?>
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
			<?= champ('sous_titre', 'Sous-titre', $o['sous_titre'], $erreurs, ['aide' => 'Complément du titre, ex. « une introduction à la linguistique africaine ».']) ?>
			<div class="champ">
				<label for="auteurs-saisie">Auteur(s)</label>
				<div class="champ-auteurs" data-url="<?= e(admin_url('auteurs/chercher')) ?>">
					<textarea name="auteurs_txt" id="f_auteurs_txt" rows="3"><?= e($o['auteurs_txt']) ?></textarea>
				</div>
				<div class="aide">Tapez un nom : choisissez un auteur existant dans la liste, ou ajoutez-le s’il n’existe pas encore. L’ordre est celui de l’affichage.</div>
			</div>
			<div class="ligne">
				<?= champ('domaine_id', 'Domaine', $o['domaine_id'], $erreurs, ['type' => 'select', 'options' => $optDomaines, 'required' => true]) ?>
				<?= champ('type_id', 'Type de document', $o['type_id'], $erreurs, ['type' => 'select', 'options' => $optTypes, 'required' => true]) ?>
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
				<div class="champ<?= isset($erreurs['collection']) ? ' en-erreur' : '' ?>">
					<label for="collection-saisie">Collection</label>
					<div class="champ-choix" data-url="<?= e(admin_url('collections/chercher')) ?>"
						data-placeholder="Rechercher ou ajouter une collection…"
						<?= peut('collections.ajouter') ? 'data-nouveau="nouvelle collection"' : '' ?>>
						<input type="hidden" name="collection" value="<?= e($o['collection']) ?>">
					</div>
					<div class="aide"><?= peut('collections.ajouter')
						? 'Tapez un nom : choisissez-en un dans la liste, ou ajoutez le vôtre, il sera créé.'
						: 'Choisissez une collection de la liste.' ?></div>
					<?php if (isset($erreurs['collection'])): ?><div class="erreur"><?= e($erreurs['collection']) ?></div><?php endif; ?>
				</div>
				<?= champ('numero_collection', 'N° dans la collection', $o['numero_collection'], $erreurs, ['placeholder' => 'ex. 1234']) ?>
				<?= champ('edition', 'Mention d’édition', $o['edition'], $erreurs, ['placeholder' => 'ex. 3e éd. revue et corrigée']) ?>
			</div>
			<div class="ligne">
				<?= champ('tome', 'Tome', $o['tome'], $erreurs, ['placeholder' => 'ex. II']) ?>
				<?= champ('volume', 'N° de volume', $o['volume'], $erreurs, ['placeholder' => 'ex. 2']) ?>
				<?= champ('pages', 'Pages', $o['pages'], $erreurs, ['type' => 'number', 'attrs' => 'min="1"']) ?>
			</div>
			<div class="ligne">
				<?= champ('isbn', 'ISBN', $o['isbn'], $erreurs, ['aide' => 'Livres.']) ?>
				<?= champ('issn', 'ISSN', $o['issn'], $erreurs, ['aide' => 'Revues, périodiques et collections.', 'placeholder' => 'ex. 0317-8471']) ?>
			</div>
		</section>

		<section class="carte">
			<h2><i class="fa-solid fa-hashtag"></i> Classement et emplacement</h2>
			<div class="ligne">
				<div class="champ<?= isset($erreurs['indice_dewey']) ? ' en-erreur' : '' ?>">
					<label for="indice_dewey-saisie">Indice Dewey</label>
					<div class="champ-choix" data-url="<?= e(admin_url('dewey/chercher')) ?>"
						data-placeholder="Chercher un sujet ou taper un indice…" data-nouveau="indice hors index" data-min="1">
						<input type="hidden" name="indice_dewey" value="<?= e($o['indice_dewey']) ?>" data-dewey
							data-detail="<?= e($deweyActuel) ?>">
					</div>
					<div class="aide">Le sujet du document en chiffres : 500 sciences, 530 physique, 005.1 programmation.</div>
					<?php if (isset($erreurs['indice_dewey'])): ?><div class="erreur"><?= e($erreurs['indice_dewey']) ?></div><?php endif; ?>
				</div>
				<div class="champ<?= isset($erreurs['cote']) ? ' en-erreur' : '' ?>">
					<label for="f_cote">Cote</label>
					<div class="champ-avec-bouton">
						<input type="text" id="f_cote" name="cote" value="<?= e($o['cote']) ?>" data-cote>
						<button type="button" class="btn secondaire petit" data-cote-proposer>Proposer</button>
					</div>
					<div class="aide">Où le document est rangé, et préfixe des codes d’exemplaires (ex. 005.1 CAR → 005.1 CAR-1).</div>
					<?php if (isset($erreurs['cote'])): ?><div class="erreur"><?= e($erreurs['cote']) ?></div><?php endif; ?>
				</div>
				<?= champ('emplacement', 'Emplacement dans la bibliothèque', $o['emplacement'], $erreurs, ['placeholder' => 'ex. Salle de lecture, rayon B3',
					'aide' => 'Proposé par défaut dans Réglages ; indiquez la salle, le rayon ou l’étagère.']) ?>
			</div>
			<p class="doux"><i class="fa-solid fa-circle-info"></i> L’indice Dewey classe par <strong>sujet</strong> : les ouvrages qui parlent de la même chose portent le même indice et se retrouvent côte à côte sur l’étagère.
				La cote le complète par les trois premières lettres du nom de l’auteur, pour ranger les livres d’un même sujet dans l’ordre alphabétique.</p>
		</section>

	</div>
	</fieldset>

<?php if ($o['id']): ?>
		<section class="carte pleine" id="exemplaires">
			<div class="carte-titre"><h2><i class="fa-solid fa-layer-group"></i> Exemplaires <small class="doux">(<?= $enCirculation ?> en circulation)</small></h2></div>
<?php if (!$exemplaires): ?>
			<p class="doux">Aucun exemplaire : l’ouvrage apparaît comme indisponible sur le site.</p>
<?php else: ?>
			<p class="doux">L’état se met à jour tout seul : <strong>neuf</strong> à la création, puis <strong>bon état</strong>, <strong>usé</strong>, <strong>abîmé</strong> ou <strong>perdu</strong> selon le constat fait à chaque retour.
				Corrigez-le ici seulement en cas d’erreur. Les modifications de cette liste sont <strong>enregistrées automatiquement</strong>.</p>
			<div class="tableau-wrap"><table class="tableau tableau-exemplaires">
				<thead><tr><th>Code</th><th>État</th><th>Statut</th><th>Notes</th><th></th></tr></thead>
				<tbody>
<?php foreach ($exemplaires as $x): $fid = 'ex' . $x['id']; ?>
					<tr data-auto-enregistrer="<?= $fid ?>">
						<td><input form="<?= $fid ?>" type="text" name="code" value="<?= e($x['code']) ?>" class="code"<?= $exModifier ? '' : ' disabled' ?>></td>
						<td><select form="<?= $fid ?>" name="etat"<?= $exModifier ? '' : ' disabled' ?>><?php foreach (ETATS_EXEMPLAIRE_TOUS as $k => $lib): ?><option value="<?= $k ?>"<?= $x['etat'] === $k ? ' selected' : '' ?>><?= e($lib) ?></option><?php endforeach; ?></select></td>
						<td><?= badge_exemplaire($x['statut']) ?><?= $x['emprunteur'] ? '<br><small>' . e($x['emprunteur']) . '</small>' : '' ?><br><small><?= e(pluriel((int) $x['nb_prets'], 'prêt')) ?> au total</small><?php if ($x['code_remplacant']): ?><br><small class="doux">remplacé par <?= e($x['code_remplacant']) ?></small><?php endif; ?></td>
						<td><input form="<?= $fid ?>" type="text" name="notes" value="<?= e($x['notes']) ?>" placeholder="Remarque (emplacement, dégâts…)"<?= $exModifier ? '' : ' disabled' ?>></td>
						<td class="actions">
							<?php if ($exModifier): ?><span class="etat-enregistrement" data-pour="<?= $fid ?>" aria-live="polite"></span>
							<button form="<?= $fid ?>" class="btn secondaire petit sans-auto" type="submit" title="Enregistrer"><i class="fa-solid fa-circle-check"></i></button><?php endif; ?>
							<?php if ($x['code_remplacant']): ?>
							<?php elseif (!peut('exemplaires.retirer')): ?>
							<?php elseif ($x['statut'] === 'retire'): ?>
								<button form="<?= $fid ?>-remettre" class="btn secondaire petit" type="submit">Remettre</button>
								<?php if (peut('exemplaires.ajouter')): ?>
								<button form="<?= $fid ?>-remplacer" class="btn secondaire petit" type="submit" title="Un exemplaire neuf reprend ce code">Remplacer</button>
								<?php endif; ?>
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
<?php if (peut('exemplaires.ajouter') || ($exemplaires && $exModifier)): ?>
			<div class="form-actions" style="margin-top:14px">
<?php if (peut('exemplaires.ajouter')): ?>
				<input form="ajout-ex" type="number" name="nombre" value="1" min="1" max="50" style="width:80px">
				<button form="ajout-ex" class="btn secondaire" type="submit"><i class="fa-solid fa-plus"></i> Ajouter des exemplaires</button>
<?php endif; ?>
<?php if ($exemplaires && $exModifier): ?>
				<button form="renum-ex" class="btn secondaire" type="submit"><i class="fa-solid fa-arrow-down-1-9"></i> Renuméroter d’après la cote</button>
<?php endif; ?>
			</div>
<?php if ($exemplaires && $exModifier): ?>
			<p class="doux">Les exemplaires ajoutés prennent la cote du moment : <strong><?= e($prefixe) ?>-1</strong>, <strong><?= e($prefixe) ?>-2</strong>…
				« Renuméroter » recale dessus les codes déjà attribués<?= $codesDecales ? ' — ' . e(pluriel($codesDecales, 'code ne la suit plus', 'codes ne la suivent plus')) : '' ?>, étiquettes à refaire en conséquence.
				Ces deux boutons prennent la cote telle qu’elle est affichée plus haut et l’enregistrent, même sans passer par « Enregistrer ».</p>
<?php endif; ?>
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
<form id="<?= $fid ?>-remplacer" method="post" action="<?= e(admin_url('exemplaires/remplacer/' . $x['id'])) ?>" data-confirmer="Un exemplaire neuf va reprendre le code <?= e($x['code']) ?>, avec zéro prêt à son compteur. L’ancien sera archivé sous « <?= e($x['code']) ?> (ancien) » et gardera son historique. Continuer ?"><?= csrf_field() ?></form>
<form id="<?= $fid ?>-supprimer" method="post" action="<?= e(admin_url('exemplaires/supprimer/' . $x['id'])) ?>" data-confirmer="Supprimer définitivement l’exemplaire <?= e($x['code']) ?> ?"><?= csrf_field() ?></form>
<?php endforeach; ?>
<?php /* data-cote-jointe : ces deux boutons emportent la cote affichée (bouton « Proposer ») et l'enregistrent au passage */ ?>
<form id="ajout-ex" method="post" action="<?= e(admin_url('exemplaires/ajouter/' . $o['id'])) ?>" data-cote-jointe><?= csrf_field() ?><input type="hidden" name="cote" value="<?= e($o['cote']) ?>"></form>
<form id="renum-ex" method="post" action="<?= e(admin_url('exemplaires/renumeroter/' . $o['id'])) ?>" data-cote-jointe
	data-confirmer-modele="Renuméroter les exemplaires en {cote}-1, {cote}-2… ? La cote sera enregistrée et les codes écrits sur les livres devront être refaits."
	data-confirmer="Renuméroter les exemplaires en <?= e($prefixe) ?>-1, <?= e($prefixe) ?>-2… ? Les codes écrits sur les livres devront être refaits."><?= csrf_field() ?><input type="hidden" name="cote" value="<?= e($o['cote']) ?>"></form>
<form id="suppr-ouvrage" method="post" action="<?= e(admin_url('ouvrages/supprimer/' . $o['id'])) ?>" data-confirmer="Supprimer définitivement « <?= e($o['titre']) ?> » et ses exemplaires ? Cette action est irréversible."><?= csrf_field() ?></form>
<form id="desactiver-ouvrage" method="post" action="<?= e(admin_url('ouvrages/desactiver/' . $o['id'])) ?>" data-confirmer="Désactiver « <?= e($o['titre']) ?> » ? Il sera retiré du site, du prêt et des réservations."><?= csrf_field() ?></form>
<form id="reactiver-ouvrage" method="post" action="<?= e(admin_url('ouvrages/reactiver/' . $o['id'])) ?>"><?= csrf_field() ?></form>
<?php endif; ?>
