<?php
/** Tableau de bord : chiffres du jour et tâches à traiter. */

admin_page('tableau', [
    'stats' => [
        'ouvrages'    => (int) val('SELECT COUNT(*) FROM ouvrages'),
        'exemplaires' => (int) val("SELECT COUNT(*) FROM exemplaires WHERE statut <> 'retire'"),
        'dispo'       => (int) val("SELECT COUNT(*) FROM exemplaires WHERE statut = 'disponible'"),
        'prets'       => (int) val('SELECT COUNT(*) FROM prets WHERE date_retour IS NULL'),
        'retards'     => (int) val('SELECT COUNT(*) FROM prets WHERE date_retour IS NULL AND date_retour_prevue < CURDATE()'),
        'lecteurs'    => (int) val("SELECT COUNT(*) FROM utilisateurs WHERE role = 'lecteur' AND actif = 1"),
        'a_valider'   => (int) val("SELECT COUNT(*) FROM utilisateurs WHERE role = 'lecteur' AND en_attente = 1"),
        'prets_mois'  => (int) val("SELECT COUNT(*) FROM prets WHERE date_pret >= DATE_FORMAT(CURDATE(), '%Y-%m-01')"),
    ],
    'retards'   => prets('p.date_retour IS NULL AND p.date_retour_prevue < CURDATE()', [], 'p.date_retour_prevue', 8),
    'aRetirer'  => reservations("r.statut = 'disponible'", [], 'r.expire_le'),
    'enAttente' => (int) val("SELECT COUNT(*) FROM reservations WHERE statut = 'en_attente'"),
    'echeances' => prets('p.date_retour IS NULL AND p.date_retour_prevue BETWEEN CURDATE() AND CURDATE() + INTERVAL 2 DAY', [], 'p.date_retour_prevue', 8),
    'inscriptions' => rows("SELECT * FROM utilisateurs WHERE role = 'lecteur' AND en_attente = 1 ORDER BY cree_le DESC LIMIT 5"),
    'messages'  => rows('SELECT * FROM messages WHERE lu = 0 ORDER BY recu_le DESC LIMIT 5'),
    'populaires' => rows('SELECT o.id, o.titre, COUNT(*) AS nb FROM prets p JOIN exemplaires e ON e.id = p.exemplaire_id
                          JOIN ouvrages o ON o.id = e.ouvrage_id WHERE p.date_pret >= CURDATE() - INTERVAL 1 YEAR
                          GROUP BY o.id ORDER BY nb DESC, o.titre LIMIT 5'),
], 'Tableau de bord', 'tableau');
