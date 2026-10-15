<?php
/**
 * Recherche dans le catalogue.
 * @var string $q
 * @var array  $resultats  ['ouvrages' => [...], 'auteurs' => [...]]
 */
$nb = count($resultats['ouvrages']) + count($resultats['auteurs']);
?>
<div class="com-finder finder">
            <h1>
                            Rechercher un ouvrage                    </h1>
        <div id="search-form" class="com-finder__form">

<form action="<?= e(url('recherche')) ?>" method="get" class="js-finder-searchform biblio-finder">
        <fieldset class="com-finder__search word mb-3">
        <legend class="com-finder__search-legend visually-hidden">
            Formulaire de recherche        </legend>
        <label for="q">
            Termes de la recherche&#160;:        </label>
        <div class="biblio-search-row">
            <input type="text" name="q" id="q" class="js-finder-search-query form-control" value="<?= e($q) ?>">
            <button type="submit" class="btn btn-primary">
                <i class="fa fa-search" aria-hidden="true"></i>
                <span>Rechercher</span>
            </button>
                    </div>
    </fieldset>

    </form>
    </div>
<?php if ($q !== ''): ?>
                <div id="search-results" class="com-finder__results">
                <div id="search-query-explained" class="com-finder__explained">
                                        <p role="alert">
<?php if ($nb): ?>
                La recherche de <span class="query-required"><span class="term"><?= e($q) ?></span></span> donne <a href="#search-result-list"><?= $nb === 1 ? 'le résultat suivant' : "les $nb résultats suivants" ?></a>...
<?php else: ?>
                Aucun résultat pour <span class="term"><?= e($q) ?></span>. Essayez avec d’autres mots-clés.
<?php endif; ?>
            </p>
            </div>
<?php if ($nb): ?>
    <ul id="search-result-list" class="js-highlight com-finder__results-list">
<?php foreach ($resultats['ouvrages'] as $o): ?>
                                    <li class="result__item">
<?php if ($o['image']): ?>
            <figure class=" result__image">
                            <img src="<?= e(media($o['image'])) ?>" alt="Couverture : <?= e($o['titre']) ?>">                    </figure>
<?php endif; ?>
        <p class="result__title">
                    <a href="<?= e($o['url']) ?>" class="result__title-link"><span class="result__title-text"><?= surligner($o['titre'], $q) ?></span></a>            </p>
            <p class="result__description">
                         <?= surligner(tronquer($o['resume'], 300, '…'), $q) ?>         </p>
                    <ul class="result__taxonomy">
<?php if ($o['type_nom']): ?>
                        <li class="result__taxonomy-item result__taxonomy--Type">
                            <span>Type:</span>
                            <?= e($o['type_nom']) ?>                        </li>
<?php endif; ?>
<?php if ($o['auteurs_txt'] !== ''): ?>
                        <li class="result__taxonomy-item result__taxonomy--Author">
                            <span>Auteur:</span>
                            <?= e($o['auteurs_txt']) ?>                        </li>
<?php endif; ?>
                        <li class="result__taxonomy-item result__taxonomy--Category">
                            <span>Catégorie:</span>
                            <?= e($o['domaine_nom']) ?>                        </li>
                        <li class="result__taxonomy-item result__taxonomy--Dispo">
                            <span>Disponibilité:</span>
                            <?= e(disponibilite($o)) ?>                        </li>
                    </ul>
    </li>
<?php endforeach; ?>
<?php foreach ($resultats['auteurs'] as $a): ?>
                                    <li class="result__item">
        <p class="result__title">
                    <a href="<?= e(url('auteurs/' . $a['slug'])) ?>" class="result__title-link"><span class="result__title-text"><?= surligner($a['nom'], $q) ?></span></a>            </p>
            <p class="result__description">
                         <?= e(pluriel((int) $a['nb'], 'ouvrage')) ?> : <?= surligner($a['titres'], $q) ?>         </p>
                    <ul class="result__taxonomy">
                        <li class="result__taxonomy-item result__taxonomy--Type">
                            <span>Type:</span>
                            Auteur                        </li>
                    </ul>
    </li>
<?php endforeach; ?>
    </ul>
<div class="com-finder__navigation search-pagination">
                <div class="com-finder__counter search-pages-counter">
                        Résultats <strong>1</strong> - <strong><?= $nb ?></strong> de <strong><?= $nb ?></strong>        </div>
    </div>
<?php endif; ?>
        </div>
<?php endif; ?>
    </div>
