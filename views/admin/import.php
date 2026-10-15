<?php /** @var string $quoi */ ?>
<div class="entete"><h1>Importer depuis un fichier</h1></div>

<nav class="onglets">
<?php if (peut('import.ouvrages')): ?>	<a href="<?= e(admin_url('import', ['quoi' => 'ouvrages'])) ?>" class="<?= $quoi === 'ouvrages' ? 'actif' : '' ?>">Ouvrages</a><?php endif; ?>
<?php if (peut('import.auteurs')): ?>	<a href="<?= e(admin_url('import', ['quoi' => 'auteurs'])) ?>" class="<?= $quoi === 'auteurs' ? 'actif' : '' ?>">Auteurs</a><?php endif; ?>
</nav>

<div class="grille grille-form">
	<form class="carte" method="post" action="<?= e(admin_url('import/analyser')) ?>" enctype="multipart/form-data">
		<?= csrf_field() ?>
		<input type="hidden" name="quoi" value="<?= e($quoi) ?>">
		<h2><i class="fa-solid fa-file-import"></i> <?= $quoi === 'auteurs' ? 'Liste d’auteurs' : 'Catalogue d’ouvrages' ?></h2>
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
<?php if ($quoi === 'auteurs'): ?>
		<p>Un nom d’auteur par ligne, dans la première colonne. Une ligne d’en-tête « nom » est acceptée.</p>
		<p class="doux">Les auteurs déjà présents dans l’index sont ignorés, les doublons du fichier aussi.</p>
<?php else: ?>
		<p>Une ligne d’en-têtes puis un ouvrage par ligne. Le plus simple : téléchargez le modèle, remplissez-le dans Excel, puis enregistrez-le en « CSV (séparateur : point-virgule) ».</p>
		<ul>
			<li><strong>titre</strong> et <strong>domaine</strong> : obligatoires.</li>
			<li><strong>auteurs</strong> : séparés par <code>|</code> (ex. <code>Awa Kpélim | Kossi Mensah</code>) ; les nouveaux noms sont ajoutés à l’index.</li>
			<li><strong>domaine</strong>, <strong>type</strong> : noms existants ; sinon ils sont créés.</li>
			<li><strong>exemplaires</strong> : nombre d’exemplaires à créer (1 par défaut), codes « cote-1, cote-2… ».</li>
			<li><strong>image</strong> : nom d’un fichier déjà copié dans <code>images/ouvrages/</code> (facultatif).</li>
			<li><strong>sur_place</strong>, <strong>coup_de_coeur</strong>, <strong>publie</strong> : oui / non.</li>
			<li>Facultatifs : type, editeur, annee, isbn, cote, pages, langue, resume.</li>
		</ul>
		<p class="doux">Les ouvrages déjà au catalogue (même titre dans le même domaine, ou même ISBN) sont ignorés.</p>
<?php endif; ?>
	</aside>
</div>
