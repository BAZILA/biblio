<?php /** Liste des caractéristiques d'un ouvrage (même balisage que les champs Joomla d'origine). @var array $o */ ?>
<ul class="fields-container">
<?php
$champs = [
    'auteurs'       => ['Auteur(s)', $o['auteurs_txt']],
    'type-document' => ['Type de document', $o['type_nom'] ?? ''],
    'editeur'       => ['Éditeur / Origine', $o['editeur']],
    'annee'         => ['Année de publication', $o['annee']],
    'isbn'          => ['ISBN', $o['isbn']],
    'cote'          => ['Cote', $o['cote']],
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
