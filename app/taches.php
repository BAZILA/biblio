<?php
/**
 * Tâches quotidiennes de la bibliothèque, à planifier une fois par jour (ex. 7 h) :
 *   C:\xampp\php\php.exe C:\xampp\htdocs\biblio\app\taches.php
 * Expire les réservations non retirées, envoie les rappels d'échéance et les relances de retard.
 */

if (PHP_SAPI !== 'cli') {
    exit('À lancer en ligne de commande.');
}
require __DIR__ . '/bootstrap.php';

$b = taches_quotidiennes(true);
echo date('d/m/Y H:i') . " — réservations expirées : {$b['reservations_expirees']}, rappels : {$b['rappels']}, relances : {$b['relances']}\n";
