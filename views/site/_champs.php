<?php /** Caractéristiques d'un ouvrage (la localisation est présentée à part, voir _localisation.php). @var array $o */ ?>
<ul class="fields-container">
<?php
$collection = trim(($o['collection_nom'] ?? '') . (($o['numero_collection'] ?? '') !== '' ? ', n° ' . $o['numero_collection'] : ''), ', ');
$champs = [
    'auteurs'       => ['Auteur(s)', $o['auteurs_txt']],
    'type-document' => ['Type de document', $o['type_nom'] ?? ''],
    'editeur'       => ['Éditeur / Origine', $o['editeur']],
    'collection'    => ['Collection', $collection],
    'edition'       => ['Édition', $o['edition'] ?? ''],
    'annee'         => ['Année de publication', $o['annee']],
    'tome'          => ['Tome', $o['tome'] ?? ''],
    'volume'        => ['Volume', $o['volume'] ?? ''],
    'isbn'          => ['ISBN', $o['isbn']],
    'issn'          => ['ISSN', $o['issn'] ?? ''],
    'indice-dewey'  => ['Indice Dewey', $o['indice_dewey'] ?? ''],
    'pages'         => ['Nombre de pages', $o['pages']],
    'langue'        => ['Langue', $o['langue']],
    'exemplaires'   => ['Exemplaires', $o['nb_ex']],
    'disponibilite' => ['Disponibilité', disponibilite($o)],
];
foreach ($champs as $cle => [$libelle, $valeur]):
    if ($valeur === null || $valeur === '') continue; ?>
    <li class="field-entry <?= $cle ?> f-<?= $cle ?>">    <span class="field-label "><?= e($libelle) ?>: </span>
<span class="field-value <?= $cle === 'disponibilite' ? 'biblio-dispo biblio-dispo-' . slugify(disponibilite($o)) : '' ?>"><?= e($valeur) ?></span>
</li>
<?php endforeach; ?></ul>
