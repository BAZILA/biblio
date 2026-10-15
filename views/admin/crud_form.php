<?php /** Formulaire générique. @var array $res, $ligne, $erreurs @var string $section */ ?>
<?php if (in_array('icone', array_column($res['champs'], 1), true)): ?>
<link rel="stylesheet" href="<?= asset('theme/fonts/font-awesome/css/font-awesome.min.css') ?>"><?php /* icônes FA4 utilisées par le site public, pour l'aperçu */ ?>
<?php endif; ?>
<div class="fil"><a href="<?= e(admin_url($section)) ?>"><?= e($res['titre']) ?></a> ›</div>
<div class="entete"><h1><?= e($ligne['id'] ? $res['modifier'] : $res['nouveau']) ?></h1></div>

<form method="post" enctype="multipart/form-data" class="carte" style="max-width:760px">
	<?= csrf_field() ?>
<?php foreach ($res['champs'] as $nom => [$libelle, $type, $opts]):
    $valeur = $ligne[$nom] ?? '';
    if ($type === 'image'): ?>
	<div class="champ<?= isset($erreurs[$nom]) ? ' en-erreur' : '' ?>">
		<label><?= e($libelle) ?></label>
		<img id="apercu-<?= $nom ?>" class="apercu-image" src="<?= $valeur ? e(media($valeur)) : '' ?>" alt="" style="<?= $valeur ? '' : 'display:none' ?>">
		<input type="file" name="<?= $nom ?>" accept="image/jpeg,image/png,image/webp,image/gif" data-apercu="apercu-<?= $nom ?>">
		<?php if (!empty($opts['aide'])): ?><div class="aide"><?= e($opts['aide']) ?></div><?php endif; ?>
		<?php if (isset($erreurs[$nom])): ?><div class="erreur"><?= e($erreurs[$nom]) ?></div><?php endif; ?>
	</div>
<?php elseif ($type === 'slug'): ?>
	<?= champ($nom, $libelle, $valeur, $erreurs, $opts + ['attrs' => 'data-slug-de="nom"' . (isset($opts['prefixe']) ? ' data-prefixe="' . e($opts['prefixe']) . '"' : '')]) ?>
<?php elseif ($type === 'icone'): ?>
	<div class="champ">
		<label for="f_<?= $nom ?>"><?= e($libelle) ?></label>
		<div class="ligne">
			<input type="text" id="f_<?= $nom ?>" name="<?= $nom ?>" value="<?= e($valeur) ?>" placeholder="fa-book">
			<span class="fixe" style="font-size:1.6rem;color:var(--bleu)"><i class="fa <?= e($valeur ?: 'fa-book') ?>" aria-hidden="true"></i></span>
		</div>
		<?php if (!empty($opts['aide'])): ?><div class="aide"><?= e($opts['aide']) ?></div><?php endif; ?>
	</div>
<?php else: ?>
	<?= champ($nom, $libelle, $valeur, $erreurs, $opts + ['type' => $type]) ?>
<?php endif; ?>
<?php endforeach; ?>
	<div class="form-actions">
		<button class="btn" type="submit"><i class="fa-solid fa-circle-check"></i> Enregistrer</button>
		<a class="btn secondaire" href="<?= e(admin_url($section)) ?>">Annuler</a>
	</div>
</form>

<?php if ($ligne['id']): ?>
<form method="post" action="<?= e(admin_url($section . '/supprimer/' . $ligne['id'])) ?>" data-confirmer="Supprimer définitivement cet élément ?" style="max-width:760px">
	<?= csrf_field() ?>
	<button class="btn danger" type="submit"><i class="fa-solid fa-trash"></i> Supprimer</button>
</form>
<?php endif; ?>
