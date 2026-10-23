<?php
/**
 * Statistiques et rapports : calculs partagés par la page « Statistiques et rapports » et les exports PDF.
 * Les périodes sont des dates AAAA-MM-JJ incluses.
 */

/** Rapports PDF disponibles : [titre, description, permission(s) supplémentaire(s), paysage ?, dépend de la période ?] */
const RAPPORTS = [
    'statistiques' => ['Rapport statistique', 'Synthèse de la période : prêts, retours, retards, lecteurs, réservations, prolongations, répartition par domaine, type, catégorie et faculté, ouvrages les plus empruntés.', [], false, true],
    'prets-en-cours' => ['Prêts en cours et retards', 'Tous les prêts non rendus à ce jour, retards en tête, avec les coordonnées des lecteurs pour les relances.', ['prets.voir'], true, false],
    'historique' => ['Historique des prêts', 'Chaque prêt de la période : lecteur, ouvrage, exemplaire, dates, état au retour.', ['prets.voir'], true, true],
    'inventaire' => ['Inventaire du catalogue', 'Ouvrages avec cote, domaine, type et état de leurs exemplaires (disponibles, prêtés, réservés, retirés).', ['ouvrages.voir'], true, false],
    'lecteurs' => ['Liste des lecteurs', 'Lecteurs inscrits avec catégorie, matricule, faculté, contact, prêts en cours et total des emprunts.', ['lecteurs.voir'], true, false],
    'hors-circulation' => ['Exemplaires abîmés, perdus ou retirés', 'Exemplaires sortis de la circulation et constats « abîmé » ou « perdu » faits aux retours.', ['ouvrages.voir'], true, false],
];

/** Période demandée (paramètres du, au) ou par défaut du 1er janvier à aujourd'hui. */
function periode_demandee(): array
{
    $ok = fn(string $d) => DateTime::createFromFormat('!Y-m-d', $d) && DateTime::createFromFormat('!Y-m-d', $d)->format('Y-m-d') === $d;
    $du = get_str('du');
    $au = get_str('au');
    $du = $ok($du) ? $du : date('Y-01-01');
    $au = $ok($au) ? $au : date('Y-m-d');
    if ($du > $au) {
        [$du, $au] = [$au, $du];
    }
    return [$du, $au];
}

/** Raccourcis de période proposés à l'écran : [libellé => [du, au]] */
function periodes_rapides(): array
{
    $auj = date('Y-m-d');
    return [
        'Ce mois-ci' => [date('Y-m-01'), $auj],
        'Mois dernier' => [date('Y-m-01', strtotime('first day of last month')), date('Y-m-t', strtotime('last day of last month'))],
        '3 derniers mois' => [date('Y-m-d', strtotime('-3 months +1 day')), $auj],
        'Cette année' => [date('Y-01-01'), $auj],
        'Année dernière' => [(date('Y') - 1) . '-01-01', (date('Y') - 1) . '-12-31'],
        '12 derniers mois' => [date('Y-m-d', strtotime('-12 months +1 day')), $auj],
    ];
}

function libelle_periode(string $du, string $au): string
{
    return 'du ' . date_fr($du) . ' au ' . date_fr($au);
}

/** Toutes les statistiques d'une période. */
function statistiques(string $du, string $au): array
{
    $p = [$du, $au];
    $entre = 'BETWEEN ? AND ?';
    $s = [];
    $s['prets'] = (int) val("SELECT COUNT(*) FROM prets WHERE date_pret $entre", $p);
    $s['retours'] = (int) val("SELECT COUNT(*) FROM prets WHERE date_retour $entre", $p);
    $s['retours_en_retard'] = (int) val("SELECT COUNT(*) FROM prets WHERE date_retour $entre AND date_retour > date_retour_prevue", $p);
    $s['taux_a_temps'] = $s['retours'] ? round(100 * ($s['retours'] - $s['retours_en_retard']) / $s['retours']) : null;
    $s['duree_moyenne'] = val("SELECT ROUND(AVG(DATEDIFF(date_retour, date_pret)), 1) FROM prets WHERE date_retour $entre", $p);
    $s['emprunteurs'] = (int) val("SELECT COUNT(DISTINCT lecteur_id) FROM prets WHERE date_pret $entre", $p);
    $s['nouveaux_lecteurs'] = (int) val("SELECT COUNT(*) FROM utilisateurs WHERE role = 'lecteur' AND DATE(cree_le) $entre", $p);
    $s['reservations'] = (int) val("SELECT COUNT(*) FROM reservations WHERE DATE(demandee_le) $entre", $p);
    $s['reservations_honorees'] = (int) val("SELECT COUNT(*) FROM reservations WHERE DATE(demandee_le) $entre AND statut = 'honoree'", $p);
    $s['demandes'] = (int) val("SELECT COUNT(*) FROM demandes_prolongation WHERE DATE(demandee_le) $entre", $p);
    $s['demandes_acceptees'] = (int) val("SELECT COUNT(*) FROM demandes_prolongation WHERE DATE(demandee_le) $entre AND statut = 'acceptee'", $p);
    $s['demandes_refusees'] = (int) val("SELECT COUNT(*) FROM demandes_prolongation WHERE DATE(demandee_le) $entre AND statut = 'refusee'", $p);
    // Situation à ce jour
    $s['en_cours'] = (int) val('SELECT COUNT(*) FROM prets WHERE date_retour IS NULL');
    $s['en_retard'] = (int) val('SELECT COUNT(*) FROM prets WHERE date_retour IS NULL AND date_retour_prevue < CURDATE()');
    $s['lecteurs_actifs'] = (int) val("SELECT COUNT(*) FROM utilisateurs WHERE role = 'lecteur' AND actif = 1");
    $s['ouvrages'] = (int) val('SELECT COUNT(*) FROM ouvrages WHERE actif = 1');
    $s['exemplaires'] = (int) val("SELECT COUNT(*) FROM exemplaires e JOIN ouvrages o ON o.id = e.ouvrage_id WHERE o.actif = 1 AND e.statut <> 'retire'");
    $s['disponibles'] = (int) val("SELECT COUNT(*) FROM exemplaires e JOIN ouvrages o ON o.id = e.ouvrage_id WHERE o.actif = 1 AND e.statut = 'disponible'");
    $s['retires'] = (int) val("SELECT COUNT(*) FROM exemplaires WHERE statut = 'retire'");
    $s['taux_occupation'] = $s['exemplaires'] ? round(100 * $s['en_cours'] / $s['exemplaires']) : null;

    // Répartitions des prêts de la période
    $s['par_mois'] = rows("SELECT DATE_FORMAT(date_pret, '%Y-%m') AS cle, COUNT(*) AS nb FROM prets WHERE date_pret $entre GROUP BY cle ORDER BY cle", $p);
    $s['par_domaine'] = rows("SELECT d.nom AS cle, COUNT(*) AS nb FROM prets p JOIN exemplaires e ON e.id = p.exemplaire_id JOIN ouvrages o ON o.id = e.ouvrage_id
                              JOIN domaines d ON d.id = o.domaine_id WHERE p.date_pret $entre GROUP BY d.id ORDER BY nb DESC, d.nom", $p);
    $s['par_type'] = rows("SELECT COALESCE(t.nom, 'Sans type') AS cle, COUNT(*) AS nb FROM prets p JOIN exemplaires e ON e.id = p.exemplaire_id JOIN ouvrages o ON o.id = e.ouvrage_id
                            LEFT JOIN types t ON t.id = o.type_id WHERE p.date_pret $entre GROUP BY t.id ORDER BY nb DESC", $p);
    $s['par_categorie'] = array_map(fn($r) => ['cle' => LIBELLES_CATEGORIE[$r['cle']] ?? 'Non précisée', 'nb' => $r['nb']],
        rows("SELECT u.categorie AS cle, COUNT(*) AS nb FROM prets p JOIN utilisateurs u ON u.id = p.lecteur_id WHERE p.date_pret $entre GROUP BY u.categorie ORDER BY nb DESC", $p));
    $s['par_faculte'] = rows("SELECT COALESCE(NULLIF(u.faculte, ''), 'Non précisée') AS cle, COUNT(*) AS nb FROM prets p JOIN utilisateurs u ON u.id = p.lecteur_id
                              WHERE p.date_pret $entre AND u.categorie = 'etudiant' GROUP BY cle ORDER BY nb DESC LIMIT 12", $p);
    $s['constats'] = array_map(fn($r) => ['cle' => ETATS_RETOUR[$r['cle']] ?? 'Non renseigné', 'nb' => $r['nb']],
        rows("SELECT etat_retour AS cle, COUNT(*) AS nb FROM prets WHERE date_retour $entre GROUP BY etat_retour ORDER BY nb DESC", $p));
    $s['top_ouvrages'] = rows("SELECT o.titre AS cle, COUNT(*) AS nb FROM prets p JOIN exemplaires e ON e.id = p.exemplaire_id JOIN ouvrages o ON o.id = e.ouvrage_id
                               WHERE p.date_pret $entre GROUP BY o.id ORDER BY nb DESC, o.titre LIMIT 10", $p);
    $s['top_lecteurs'] = rows("SELECT CONCAT(u.nom, ' ', u.prenom) AS cle, COUNT(*) AS nb FROM prets p JOIN utilisateurs u ON u.id = p.lecteur_id
                               WHERE p.date_pret $entre GROUP BY u.id ORDER BY nb DESC, u.nom LIMIT 10", $p);
    return $s;
}

/** Libellé de mois « octobre 2026 » à partir de « 2026-10 ». */
function mois_fr(string $cle): string
{
    static $mois = ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];
    [$a, $m] = explode('-', $cle);
    return $mois[(int) $m - 1] . ' ' . $a;
}

/** Fabrique et envoie le PDF d'un rapport. */
function rapport_pdf(string $type, string $du, string $au): never
{
    [$titre, , , $paysage, $avecPeriode] = RAPPORTS[$type];
    $sous = ($avecPeriode ? 'Période ' . libelle_periode($du, $au) : 'Situation au ' . date_fr(date('Y-m-d'))) . ' — ' . reglage('site_nom', 'Bibliothèque');
    $pdf = new Pdf($titre, $sous, $paysage, ROOT_DIR . '/images/biblio/logo-uk-original.png');
    $paires = fn(array $l) => array_map(fn($r) => [$r['cle'], (int) $r['nb']], $l);

    switch ($type) {
        case 'statistiques':
            $s = statistiques($du, $au);
            $pdf->titreSection('Activité de la période');
            $pdf->chiffres([
                ['Prêts enregistrés', $s['prets']], ['Retours', $s['retours']], ['Lecteurs ayant emprunté', $s['emprunteurs']], ['Nouveaux lecteurs', $s['nouveaux_lecteurs']],
                ['Retours à temps', $s['taux_a_temps'] === null ? '—' : $s['taux_a_temps'] . ' %'], ['Durée moyenne d’emprunt', $s['duree_moyenne'] === null ? '—' : str_replace('.', ',', $s['duree_moyenne']) . ' j'],
                ['Réservations (honorées)', $s['reservations'] . ' (' . $s['reservations_honorees'] . ')'], ['Prolongations (acceptées)', $s['demandes'] . ' (' . $s['demandes_acceptees'] . ')'],
            ]);
            $pdf->titreSection('Situation à ce jour');
            $pdf->chiffres([
                ['Prêts en cours', $s['en_cours']], ['Dont en retard', $s['en_retard']], ['Lecteurs actifs', $s['lecteurs_actifs']], ['Ouvrages au catalogue', $s['ouvrages']],
                ['Exemplaires en circulation', $s['exemplaires']], ['Exemplaires disponibles', $s['disponibles']], ['Taux d’occupation', $s['taux_occupation'] === null ? '—' : $s['taux_occupation'] . ' %'], ['Exemplaires retirés', $s['retires']],
            ]);
            $pdf->titreSection('Prêts par mois');
            $pdf->barres(array_map(fn($r) => [mois_fr($r['cle']), (int) $r['nb']], $s['par_mois']));
            if (!$s['par_mois']) {
                $pdf->paragraphe('Aucun prêt sur la période.', 9, false, 0.45);
            }
            foreach (['par_domaine' => 'Prêts par domaine', 'par_type' => 'Prêts par type de document', 'par_categorie' => 'Prêts par catégorie de lecteurs',
                      'par_faculte' => 'Prêts des étudiants par faculté', 'constats' => 'État des exemplaires au retour'] as $cle => $t) {
                if ($s[$cle]) {
                    $pdf->titreSection($t);
                    $pdf->barres($paires($s[$cle]));
                }
            }
            $pdf->titreSection('Ouvrages les plus empruntés');
            $pdf->tableau([['Rang', 1, 'C'], ['Ouvrage', 8, 'G'], ['Prêts', 1.5, 'D']],
                array_map(fn($r, $i) => [$i + 1, $r['cle'], $r['nb']], $s['top_ouvrages'], array_keys($s['top_ouvrages'])));
            $pdf->titreSection('Lecteurs les plus actifs');
            $pdf->tableau([['Rang', 1, 'C'], ['Lecteur', 8, 'G'], ['Prêts', 1.5, 'D']],
                array_map(fn($r, $i) => [$i + 1, $r['cle'], $r['nb']], $s['top_lecteurs'], array_keys($s['top_lecteurs'])));
            break;

        case 'prets-en-cours':
            $l = prets('p.date_retour IS NULL', [], 'p.date_retour_prevue, u.nom');
            $retards = count(array_filter($l, fn($p) => $p['jours_retard'] > 0));
            $pdf->paragraphe(pluriel(count($l), 'prêt en cours', 'prêts en cours') . ', dont ' . pluriel($retards, 'en retard', 'en retard') . '.', 10, true);
            $pdf->tableau([['Lecteur', 4, 'G'], ['Matricule', 2, 'G'], ['Téléphone', 2.3, 'G'], ['E-mail', 3.6, 'G'], ['Ouvrage', 5, 'G'], ['Exemplaire', 2, 'G'],
                           ['Emprunté le', 2, 'C'], ['À rendre le', 2, 'C'], ['Retard', 1.6, 'D']],
                array_map(fn($p) => [trim($p['nom'] . ' ' . $p['prenom']), $p['matricule'], $p['telephone'], $p['email'], $p['titre'], $p['exemplaire_code'],
                    date_fr($p['date_pret']), date_fr($p['date_retour_prevue']), $p['jours_retard'] > 0 ? $p['jours_retard'] . ' j' : ''], $l));
            break;

        case 'historique':
            $l = prets('p.date_pret BETWEEN ? AND ?', [$du, $au], 'p.date_pret, p.id');
            $pdf->paragraphe(pluriel(count($l), 'prêt enregistré', 'prêts enregistrés') . ' sur la période.', 10, true);
            $pdf->tableau([['Emprunté le', 2, 'C'], ['Lecteur', 4, 'G'], ['Matricule', 2, 'G'], ['Ouvrage', 5.5, 'G'], ['Exemplaire', 2, 'G'],
                           ['Prévu le', 2, 'C'], ['Rendu le', 2, 'C'], ['État au retour', 3, 'G'], ['Par', 2.5, 'G']],
                array_map(fn($p) => [date_fr($p['date_pret']), trim($p['nom'] . ' ' . $p['prenom']), $p['matricule'], $p['titre'], $p['exemplaire_code'],
                    date_fr($p['date_retour_prevue']), $p['date_retour'] ? date_fr($p['date_retour']) : 'en cours',
                    $p['etat_retour'] ? (ETATS_RETOUR[$p['etat_retour']] ?? '') : '', nom_acteur((int) $p['agent_id'])], $l));
            break;

        case 'inventaire':
            $l = rows("SELECT o.titre, o.cote, o.annee, d.nom AS domaine, COALESCE(t.nom, '') AS type,
                              (SELECT GROUP_CONCAT(a.nom ORDER BY oa.ordre SEPARATOR ', ') FROM ouvrage_auteur oa JOIN auteurs a ON a.id = oa.auteur_id WHERE oa.ouvrage_id = o.id) AS auteurs,
                              SUM(e.statut = 'disponible') AS dispo, SUM(e.statut = 'prete') AS pretes, SUM(e.statut = 'reserve') AS reserves,
                              SUM(e.statut = 'retire') AS retires, COUNT(e.id) AS total
                       FROM ouvrages o JOIN domaines d ON d.id = o.domaine_id LEFT JOIN types t ON t.id = o.type_id
                       LEFT JOIN exemplaires e ON e.ouvrage_id = o.id WHERE o.actif = 1 GROUP BY o.id ORDER BY d.nom, o.titre");
            $pdf->paragraphe(pluriel(count($l), 'ouvrage', 'ouvrages') . ', ' . pluriel((int) array_sum(array_column($l, 'total')), 'exemplaire', 'exemplaires') . '.', 10, true);
            $lignes = array_map(fn($o) => [$o['cote'], $o['titre'], $o['auteurs'], $o['domaine'], $o['type'], $o['annee'],
                (int) $o['total'], (int) $o['dispo'], (int) $o['pretes'], (int) $o['reserves'], (int) $o['retires']], $l);
            if ($lignes) {
                $lignes[] = ['', 'Total', '', '', '', '', array_sum(array_column($l, 'total')), array_sum(array_column($l, 'dispo')),
                    array_sum(array_column($l, 'pretes')), array_sum(array_column($l, 'reserves')), array_sum(array_column($l, 'retires'))];
            }
            $pdf->tableau([['Cote', 1.8, 'G'], ['Titre', 5, 'G'], ['Auteurs', 3.6, 'G'], ['Domaine', 2.6, 'G'], ['Type', 1.8, 'G'], ['Année', 1.2, 'C'],
                           ['Ex.', 0.9, 'D'], ['Disp.', 1, 'D'], ['Prêtés', 1.1, 'D'], ['Rés.', 0.9, 'D'], ['Retirés', 1.2, 'D']], $lignes, (bool) $lignes);
            break;

        case 'lecteurs':
            $l = rows("SELECT u.*, (SELECT COUNT(*) FROM prets p WHERE p.lecteur_id = u.id AND p.date_retour IS NULL) AS en_cours,
                              (SELECT COUNT(*) FROM prets p WHERE p.lecteur_id = u.id) AS total
                       FROM utilisateurs u WHERE u.role = 'lecteur' ORDER BY u.nom, u.prenom");
            $pdf->paragraphe(pluriel(count($l), 'lecteur inscrit', 'lecteurs inscrits') . ', dont ' . count(array_filter($l, fn($u) => $u['actif'])) . ' actif(s).', 10, true);
            $pdf->tableau([['Nom', 4, 'G'], ['Catégorie', 2.2, 'G'], ['Matricule', 2, 'G'], ['Faculté / parcours', 4, 'G'], ['Téléphone', 2.2, 'G'],
                           ['E-mail', 4, 'G'], ['État', 1.6, 'G'], ['En cours', 1.4, 'D'], ['Total prêts', 1.6, 'D']],
                array_map(fn($u) => [trim($u['nom'] . ' ' . $u['prenom']), LIBELLES_CATEGORIE[$u['categorie']] ?? '', $u['matricule'],
                    trim($u['faculte'] . ($u['parcours'] ? ' / ' . $u['parcours'] : '')), $u['telephone'], $u['email'],
                    $u['actif'] ? 'Actif' : ($u['en_attente'] ? 'À valider' : 'Désactivé'), (int) $u['en_cours'], (int) $u['total']], $l));
            break;

        case 'hors-circulation':
            $retires = rows("SELECT e.code, e.etat, e.notes, o.titre, o.cote FROM exemplaires e JOIN ouvrages o ON o.id = e.ouvrage_id
                             WHERE e.statut = 'retire' ORDER BY o.titre, e.code");
            $pdf->titreSection('Exemplaires retirés de la circulation (' . count($retires) . ')');
            $pdf->tableau([['Exemplaire', 2, 'G'], ['Ouvrage', 6, 'G'], ['État', 1.6, 'G'], ['Motif / remarque', 8, 'G']],
                array_map(fn($e) => [$e['code'], $e['titre'], ETATS_EXEMPLAIRE[$e['etat']] ?? $e['etat'], $e['notes']], $retires));
            $constats = prets("p.etat_retour IN ('abime', 'perdu')", [], 'p.date_retour DESC');
            $pdf->titreSection('Constats « abîmé » ou « perdu » aux retours (' . count($constats) . ')');
            $pdf->tableau([['Rendu le', 1.8, 'C'], ['Exemplaire', 2, 'G'], ['Ouvrage', 5, 'G'], ['Lecteur', 3.5, 'G'], ['Constat', 1.8, 'G'], ['Description', 6, 'G']],
                array_map(fn($p) => [date_fr($p['date_retour']), $p['exemplaire_code'], $p['titre'], trim($p['nom'] . ' ' . $p['prenom']),
                    ETATS_RETOUR[$p['etat_retour']] ?? '', $p['note_retour']], $constats));
            break;
    }
    $pdf->sortie(slugify($titre) . ($avecPeriode ? "-$du-au-$au" : '-' . date('Y-m-d')) . '.pdf');
}
