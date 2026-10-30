<?php /** @var string $quoi */ ?>
<div class="entete"><h1>Importer depuis un fichier</h1></div>

<nav class="onglets">
<?php if (peut('import.ouvrages')): ?>	<a href="<?= e(admin_url('import', ['quoi' => 'ouvrages'])) ?>" class="<?= $quoi === 'ouvrages' ? 'actif' : '' ?>">Ouvrages</a><?php endif; ?>
<?php if (peut('import.auteurs')): ?>	<a href="<?= e(admin_url('import', ['quoi' => 'auteurs'])) ?>" class="<?= $quoi === 'auteurs' ? 'actif' : '' ?>">Auteurs</a><?php endif; ?>
<?php if (peut('import.collections')): ?>	<a href="<?= e(admin_url('import', ['quoi' => 'collections'])) ?>" class="<?= $quoi === 'collections' ? 'actif' : '' ?>">Collections</a><?php endif; ?>
<?php if (peut('import.etudiants')): ?>	<a href="<?= e(admin_url('import', ['quoi' => 'etudiants'])) ?>" class="<?= $quoi === 'etudiants' ? 'actif' : '' ?>">Étudiants de l’université</a><?php endif; ?>
</nav>

<div class="grille grille-form">
	<form class="carte" method="post" action="<?= e(admin_url('import/analyser')) ?>" enctype="multipart/form-data">
		<?= csrf_field() ?>
		<input type="hidden" name="quoi" value="<?= e($quoi) ?>">
		<h2><i class="fa-solid fa-file-import"></i> <?= ['auteurs' => 'Liste d’auteurs', 'collections' => 'Liste de collections', 'etudiants' => 'Liste des étudiants (n° de suivi)', 'ouvrages' => 'Catalogue d’ouvrages'][$quoi] ?></h2>
		<div class="champ">
			<label for="fichier">Fichier CSV ou texte</label>
			<input type="file" id="fichier" name="fichier" accept=".csv,.txt,text/csv,text/plain" required>
			<div class="aide">Rien n’est enregistré à cette étape : vous verrez d’abord un aperçu ligne par ligne.</div>
		</div>
		<div class="form-actions">
			<button class="btn" type="submit"><i class="fa-solid fa-magnifying-glass"></i> Analyser le fichier</button>
			<a class="btn secondaire" href="<?= e(admin_url('import/modele', ['quoi' => $quoi])) ?>"><i class="fa-solid fa-download"></i> Télécharger le modèle</a>
		</div>
	</form>

	<aside class="carte">
		<h2>Format attendu</h2>
<?php if ($quoi === 'etudiants'): ?>
		<p>La liste des étudiants fournie par la scolarité, nouveaux comme anciens : une ligne d’en-têtes puis un étudiant par ligne (CSV enregistré depuis Excel, jusqu’à 40 000 lignes). Six colonnes :</p>
		<p><code>numero_suivi</code> ; <code>nom</code> ; <code>prenom</code> ; <code>date_naissance</code> (JJ/MM/AAAA) ; <code>lieu_naissance</code> ; <code>faculte</code></p>
		<p class="doux">Le n° de suivi ne contient que des chiffres. Dans Excel, mettez cette colonne au format « Texte » avant d’enregistrer : sinon Excel peut supprimer les zéros en tête ou écrire les longs numéros en notation scientifique (2,02E+11), ce que l’aperçu signale.</p>
		<p>Rechargez la liste quand elle change : les nouveaux sont ajoutés, les fiches dont le nom, les prénoms, la naissance ou la faculté ont changé sont corrigées (repérées par le n° de suivi).
			<strong>Personne n’est jamais retiré</strong> : un étudiant absent du nouveau fichier garde sa place et ses droits.</p>
		<p class="doux">L’étudiant complète lui-même son parcours, son e-mail et son mot de passe en créant son compte ; son identité doit correspondre à cette liste. Au guichet, il peut emprunter même sans compte.</p>
<?php elseif ($quoi === 'auteurs'): ?>
		<p>Un nom d’auteur par ligne, dans la première colonne. Une ligne d’en-tête « nom » est acceptée.</p>
		<p class="doux">Les auteurs déjà présents dans l’index sont ignorés, les doublons du fichier aussi.</p>
<?php elseif ($quoi === 'collections'): ?>
		<p>Une collection par ligne. Trois colonnes, dans cet ordre : <code>nom</code> ; <code>editeur</code> ; <code>description</code>.</p>
		<p>Seul le <strong>nom</strong> est obligatoire. Une ligne d’en-têtes est acceptée ; sans en-tête, l’ordre ci-dessus est appliqué.</p>
		<p class="doux">Les collections déjà présentes sont ignorées, les doublons du fichier aussi. Vous pouvez aussi créer une collection
			en la tapant directement sur la fiche d’un ouvrage.</p>
<?php else: ?>
		<p>Une ligne d’en-têtes puis un ouvrage par ligne. Le plus simple : téléchargez le modèle, remplissez-le dans Excel, puis enregistrez-le en « CSV (séparateur : point-virgule) ».</p>
		<ul>
			<li><strong>titre</strong>, <strong>domaine</strong> et <strong>type</strong> : obligatoires.</li>
			<li><strong>auteurs</strong> : séparés par <code>|</code> (ex. <code>Awa Kpélim | Kossi Mensah</code>) ; les nouveaux noms sont ajoutés à l’index.</li>
			<li><strong>domaine</strong>, <strong>type</strong>, <strong>collection</strong> : noms existants ; sinon ils sont créés.</li>
			<li><strong>exemplaires</strong> : nombre d’exemplaires à créer (1 par défaut), codes « cote-1, cote-2… ».</li>
			<li><strong>image</strong> : nom d’un fichier déjà copié dans <code>images/ouvrages/</code> (facultatif).</li>
			<li><strong>sur_place</strong>, <strong>coup_de_coeur</strong>, <strong>publie</strong> : oui / non.</li>
			<li>Facultatifs : sous_titre, collection, numero_collection, edition, tome, volume, editeur, annee, isbn, issn, cote, pages, langue, resume.</li>
		</ul>
		<p class="doux">Les ouvrages déjà au catalogue (même titre dans le même domaine, ou même ISBN) sont ignorés.</p>
<?php endif; ?>
	</aside>
</div>
