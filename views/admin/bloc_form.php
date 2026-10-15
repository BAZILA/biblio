<?php /** @var array $bloc */ ?>
<div class="fil"><a href="<?= e(admin_url('blocs')) ?>">Textes du site</a> ›</div>
<div class="entete"><h1><?= e($bloc['titre']) ?></h1></div>

<div class="grille grille-form">
	<form method="post" class="carte">
		<?= csrf_field() ?>
		<?= champ('contenu', 'Contenu (HTML)', $bloc['contenu'], [], ['type' => 'code', 'attrs' => 'rows="22" spellcheck="false"']) ?>
		<div class="form-actions">
			<button class="btn" type="submit"><i class="fa-solid fa-circle-check"></i> Enregistrer</button>
			<a class="btn secondaire" href="<?= e(admin_url('blocs')) ?>">Annuler</a>
			<a class="btn lien" href="<?= e(url()) ?>" target="_blank" rel="noopener">Voir le site ↗</a>
		</div>
	</form>
	<aside class="carte">
		<h2>Aide</h2>
		<p>Le contenu est du HTML : conservez les balises et les classes existantes pour garder la mise en forme du thème, et ne modifiez que les textes.</p>
		<p>Balises spéciales :</p>
		<ul>
			<li><code>{base}</code> : racine du site, pour les liens et images (ex. <code>{base}/index.php/catalogue</code>, <code>{base}/images/biblio/photo1.jpg</code>).</li>
			<li><code>{recherche}</code> : le formulaire de recherche du catalogue.</li>
		</ul>
		<p class="doux">Pages internes : <code>catalogue</code>, <code>domaines</code>, <code>types</code>, <code>auteurs</code>, <code>recherche</code>, <code>contact</code>, <code>compte</code>.</p>
	</aside>
</div>
