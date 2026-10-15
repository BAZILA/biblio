<?php
// Copier ce fichier en app/config.php et adapter les valeurs.
return [
    'db' => [
        'host'     => 'localhost',
        'name'     => 'biblio',
        'user'     => 'root',
        'password' => '',
    ],
    // Fuseau horaire utilisé pour les dates de prêt
    'timezone' => 'Africa/Lome',
    // true en développement : affiche les erreurs PHP
    'debug' => false,
];
