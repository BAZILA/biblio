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
    'mauvais-lecteurs' => ['Lecteurs à relancer', 'Lecteurs qui rendent en retard, abîment ou perdent des ouvrages : nombre de retards, jours cumulés, constats et coordonnées pour les relancer.', ['lecteurs.voir'], true, true],
    'hors-circulation' => ['Exemplaires abîmés, perdus ou retirés', 'Exemplaires sortis de la circulation et constats « abîmé » ou « perdu » faits aux retours.', ['ouvrages.voir'], true, false],
];

/**
 * Filtres choisis sur la page des rapports (centre documentaire, faculté du lecteur).
 * Appelée avec un tableau, elle les enregistre pour toute la requête ; sans argument, elle les relit.
 */
function filtres_rapport(?array $f = null): array
{
    static $courant = ['site' => 0, 'faculte' => ''];
    if ($f !== null) {
        $courant = ['site' => (int) ($f['site'] ?? 0), 'faculte' => (string) ($f['faculte'] ?? '')];
    }
    return $courant;
}

/** Conditions SQL de ces filtres pour une requête sur les prêts ($p = alias de la table prets). */
function sql_f_prets(string $p = 'p'): string
{
    $f = filtres_rapport();
    $sql = '';
    if ($f['site']) {
        $sql .= " AND EXISTS (SELECT 1 FROM exemplaires _fe JOIN ouvrages _fo ON _fo.id = _fe.ouvrage_id
                              WHERE _fe.id = $p.exemplaire_id AND _fo.site_id = " . $f['site'] . ')';
    }
    if ($f['faculte'] !== '') {
        $sql .= " AND EXISTS (SELECT 1 FROM utilisateurs _fu WHERE _fu.id = $p.lecteur_id AND _fu.faculte = " . db()->quote($f['faculte']) . ')';
    }
    return $sql;
}

/** Même filtre, pour une requête sur les ouvrages ($o = alias de la table ouvrages). */
function sql_f_ouvrages(string $o = 'o'): string
{
    $f = filtres_rapport();
    return $f['site'] ? " AND $o.site_id = " . $f['site'] : '';
}

/**
 * Lecteurs « à relancer » : ceux qui rendent en retard, abîment ou perdent des ouvrages.
 * Score : un perdu pèse 6, un abîmé 3, un prêt encore en retard 2, un retour tardif 1.
 */
function lecteurs_a_relancer(string $du, string $au, int $limite = 10): array
{
    $l = rows("SELECT u.id, CONCAT(u.nom, ' ', u.prenom) AS cle, u.categorie, u.faculte, u.parcours, u.email, u.telephone, u.matricule,
                      COUNT(*) AS total,
                      COALESCE(SUM(p.date_retour IS NOT NULL AND p.date_retour > p.date_retour_prevue), 0) AS retards,
                      COALESCE(SUM(GREATEST(DATEDIFF(COALESCE(p.date_retour, CURDATE()), p.date_retour_prevue), 0)), 0) AS jours,
                      COALESCE(SUM(p.etat_retour = 'abime'), 0) AS abimes,
                      COALESCE(SUM(p.etat_retour = 'perdu'), 0) AS perdus,
                      COALESCE(SUM(p.date_retour IS NULL AND p.date_retour_prevue < CURDATE()), 0) AS encours
               FROM prets p JOIN utilisateurs u ON u.id = p.lecteur_id
               WHERE p.date_pret BETWEEN ? AND ?" . filtre_site_prets() . sql_f_prets() . "
               GROUP BY u.id
               HAVING retards > 0 OR abimes > 0 OR perdus > 0 OR encours > 0
               ORDER BY (COALESCE(SUM(p.etat_retour = 'perdu'), 0) * 6 + COALESCE(SUM(p.etat_retour = 'abime'), 0) * 3
                        + COALESCE(SUM(p.date_retour IS NULL AND p.date_retour_prevue < CURDATE()), 0) * 2
                        + COALESCE(SUM(p.date_retour IS NOT NULL AND p.date_retour > p.date_retour_prevue), 0)) DESC,
                       COALESCE(SUM(GREATEST(DATEDIFF(COALESCE(p.date_retour, CURDATE()), p.date_retour_prevue), 0)), 0) DESC, u.nom
               LIMIT " . max(1, $limite), [$du, $au]);
    foreach ($l as &$r) {
        $r['score'] = 6 * (int) $r['perdus'] + 3 * (int) $r['abimes'] + 2 * (int) $r['encours'] + (int) $r['retards'];
    }
    return $l;
}

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
    $s['prets'] = (int) val("SELECT COUNT(*) FROM prets p WHERE p.date_pret $entre" . filtre_site_prets() . sql_f_prets(), $p);
    $s['retours'] = (int) val("SELECT COUNT(*) FROM prets p WHERE p.date_retour $entre" . filtre_site_prets() . sql_f_prets(), $p);
    $s['retours_en_retard'] = (int) val("SELECT COUNT(*) FROM prets p WHERE p.date_retour $entre AND p.date_retour > p.date_retour_prevue" . filtre_site_prets() . sql_f_prets(), $p);
    $s['taux_a_temps'] = $s['retours'] ? round(100 * ($s['retours'] - $s['retours_en_retard']) / $s['retours']) : null;
    $s['duree_moyenne'] = val("SELECT ROUND(AVG(DATEDIFF(p.date_retour, p.date_pret)), 1) FROM prets p WHERE p.date_retour $entre" . filtre_site_prets() . sql_f_prets(), $p);
    $s['emprunteurs'] = (int) val("SELECT COUNT(DISTINCT p.lecteur_id) FROM prets p WHERE p.date_pret $entre" . filtre_site_prets() . sql_f_prets(), $p);
    $s['nouveaux_lecteurs'] = (int) val("SELECT COUNT(*) FROM utilisateurs WHERE role = 'lecteur' AND DATE(cree_le) $entre", $p);
    $s['reservations'] = (int) val("SELECT COUNT(*) FROM reservations r JOIN ouvrages o ON o.id = r.ouvrage_id WHERE DATE(r.demandee_le) $entre" . filtre_site('o.site_id') . sql_f_ouvrages('o'), $p);
    $s['reservations_honorees'] = (int) val("SELECT COUNT(*) FROM reservations r JOIN ouvrages o ON o.id = r.ouvrage_id WHERE DATE(r.demandee_le) $entre AND r.statut = 'honoree'" . filtre_site('o.site_id') . sql_f_ouvrages('o'), $p);
    $s['demandes'] = (int) val("SELECT COUNT(*) FROM demandes_prolongation dp JOIN prets p ON p.id = dp.pret_id WHERE DATE(dp.demandee_le) $entre" . filtre_site_prets() . sql_f_prets(), $p);
    $s['demandes_acceptees'] = (int) val("SELECT COUNT(*) FROM demandes_prolongation dp JOIN prets p ON p.id = dp.pret_id WHERE DATE(dp.demandee_le) $entre AND dp.statut = 'acceptee'" . filtre_site_prets() . sql_f_prets(), $p);
    $s['demandes_refusees'] = (int) val("SELECT COUNT(*) FROM demandes_prolongation dp JOIN prets p ON p.id = dp.pret_id WHERE DATE(dp.demandee_le) $entre AND dp.statut = 'refusee'" . filtre_site_prets() . sql_f_prets(), $p);
    // Situation à ce jour
    $s['en_cours'] = (int) val('SELECT COUNT(*) FROM prets p WHERE p.date_retour IS NULL' . filtre_site_prets() . sql_f_prets());
    $s['en_retard'] = (int) val('SELECT COUNT(*) FROM prets p WHERE p.date_retour IS NULL AND p.date_retour_prevue < CURDATE()' . filtre_site_prets() . sql_f_prets());
    $s['lecteurs_actifs'] = (int) val("SELECT COUNT(*) FROM utilisateurs WHERE role = 'lecteur' AND actif = 1");
    $s['ouvrages'] = (int) val('SELECT COUNT(*) FROM ouvrages WHERE actif = 1' . filtre_site('site_id') . sql_f_ouvrages('ouvrages'));
    $s['exemplaires'] = (int) val("SELECT COUNT(*) FROM exemplaires e JOIN ouvrages o ON o.id = e.ouvrage_id WHERE o.actif = 1 AND e.statut <> 'retire'" . filtre_site('o.site_id') . sql_f_ouvrages('o'));
    $s['disponibles'] = (int) val("SELECT COUNT(*) FROM exemplaires e JOIN ouvrages o ON o.id = e.ouvrage_id WHERE o.actif = 1 AND e.statut = 'disponible'" . filtre_site('o.site_id') . sql_f_ouvrages('o'));
    $s['retires'] = (int) val("SELECT COUNT(*) FROM exemplaires e WHERE e.statut = 'retire'" . filtre_site_exemplaires() . sql_f_ouvrages('_o'));
    $s['taux_occupation'] = $s['exemplaires'] ? round(100 * $s['en_cours'] / $s['exemplaires']) : null;

    // Répartitions des prêts de la période
    $s['par_mois'] = rows("SELECT DATE_FORMAT(p.date_pret, '%Y-%m') AS cle, COUNT(*) AS nb FROM prets p WHERE p.date_pret $entre" . filtre_site_prets() . sql_f_prets() . " GROUP BY cle ORDER BY cle", $p);
    $s['par_domaine'] = rows("SELECT d.nom AS cle, COUNT(*) AS nb FROM prets p JOIN exemplaires e ON e.id = p.exemplaire_id JOIN ouvrages o ON o.id = e.ouvrage_id
                              JOIN domaines d ON d.id = o.domaine_id WHERE p.date_pret $entre" . filtre_site('o.site_id') . sql_f_ouvrages('o') . sql_f_prets('p') . " GROUP BY d.id ORDER BY nb DESC, d.nom", $p);
    $s['par_type'] = rows("SELECT COALESCE(t.nom, 'Sans type') AS cle, COUNT(*) AS nb FROM prets p JOIN exemplaires e ON e.id = p.exemplaire_id JOIN ouvrages o ON o.id = e.ouvrage_id
                            LEFT JOIN types t ON t.id = o.type_id WHERE p.date_pret $entre" . filtre_site('o.site_id') . sql_f_ouvrages('o') . sql_f_prets('p') . " GROUP BY t.id ORDER BY nb DESC", $p);
    $s['par_categorie'] = array_map(fn($r) => ['cle' => LIBELLES_CATEGORIE[$r['cle']] ?? 'Non précisée', 'nb' => $r['nb']],
        rows("SELECT u.categorie AS cle, COUNT(*) AS nb FROM prets p JOIN utilisateurs u ON u.id = p.lecteur_id WHERE p.date_pret $entre" . filtre_site_prets() . sql_f_prets() . " GROUP BY u.categorie ORDER BY nb DESC", $p));
    $s['par_faculte'] = rows("SELECT COALESCE(NULLIF(u.faculte, ''), 'Non précisée') AS cle, COUNT(*) AS nb FROM prets p JOIN utilisateurs u ON u.id = p.lecteur_id
                              WHERE p.date_pret $entre AND u.categorie = 'etudiant'" . filtre_site_prets() . sql_f_prets() . " GROUP BY cle ORDER BY nb DESC LIMIT 12", $p);
    $s['constats'] = array_map(fn($r) => ['cle' => ETATS_RETOUR[$r['cle']] ?? 'Non renseigné', 'nb' => $r['nb']],
        rows("SELECT p.etat_retour AS cle, COUNT(*) AS nb FROM prets p WHERE p.date_retour $entre" . filtre_site_prets() . sql_f_prets() . " GROUP BY p.etat_retour ORDER BY nb DESC", $p));
    $s['top_ouvrages'] = rows("SELECT o.titre AS cle, COUNT(*) AS nb FROM prets p JOIN exemplaires e ON e.id = p.exemplaire_id JOIN ouvrages o ON o.id = e.ouvrage_id
                               WHERE p.date_pret $entre" . filtre_site('o.site_id') . sql_f_ouvrages('o') . sql_f_prets('p') . " GROUP BY o.id ORDER BY nb DESC, o.titre LIMIT 10", $p);
    $s['mauvais'] = lecteurs_a_relancer($du, $au, 8);
    $s['top_lecteurs'] = rows("SELECT CONCAT(u.nom, ' ', u.prenom) AS cle, COUNT(*) AS nb, u.id, u.categorie, u.faculte, u.parcours,
                                      COUNT(DISTINCT e.ouvrage_id) AS titres
                               FROM prets p JOIN utilisateurs u ON u.id = p.lecteur_id JOIN exemplaires e ON e.id = p.exemplaire_id
                               WHERE p.date_pret $entre" . filtre_site_prets() . sql_f_prets() . " GROUP BY u.id ORDER BY nb DESC, titres DESC, u.nom LIMIT 10", $p);
    // Facultés : tous les lecteurs rattachés (étudiants et enseignants), prêts et nombre de lecteurs différents
    $s['facultes'] = rows("SELECT u.faculte AS cle, COUNT(*) AS nb, COUNT(DISTINCT u.id) AS lecteurs FROM prets p JOIN utilisateurs u ON u.id = p.lecteur_id
                            WHERE p.date_pret $entre AND u.faculte <> ''" . filtre_site_prets() . sql_f_prets() . " GROUP BY u.faculte ORDER BY nb DESC LIMIT 12", $p);
    $s['par_parcours'] = rows("SELECT u.parcours AS cle, COUNT(*) AS nb FROM prets p JOIN utilisateurs u ON u.id = p.lecteur_id
                                WHERE p.date_pret $entre AND u.parcours <> ''" . filtre_site_prets() . sql_f_prets() . " GROUP BY u.parcours ORDER BY nb DESC LIMIT 10", $p);
    // Palmarès de la période
    $s['palmares'] = [
        'lecteur' => $s['top_lecteurs'][0] ?? null,
        'faculte' => $s['facultes'][0] ?? null,
        'parcours' => $s['par_parcours'][0] ?? null,
        'ouvrage' => $s['top_ouvrages'][0] ?? null,
        'domaine' => $s['par_domaine'][0] ?? null,
    ];
    return $s;
}

/**
 * Prêts et retours mois par mois entre deux dates (mois sans activité à zéro), pour les courbes.
 * Renvoie ['mois' => ['2026-01', …], 'prets' => [n, …], 'retours' => [n, …]].
 */
function series_mensuelles(string $du, string $au): array
{
    $mois = [];
    for ($t = strtotime(date('Y-m-01', strtotime($du))); $t <= strtotime($au); $t = strtotime('+1 month', $t)) {
        $mois[date('Y-m', $t)] = 0;
    }
    $compter = function (string $colonne) use ($mois, $du, $au): array {
        $valeurs = $mois;
        foreach (rows("SELECT DATE_FORMAT($colonne, '%Y-%m') AS m, COUNT(*) AS nb FROM prets WHERE $colonne BETWEEN ? AND ? GROUP BY m", [$du, $au]) as $r) {
            if (isset($valeurs[$r['m']])) {
                $valeurs[$r['m']] = (int) $r['nb'];
            }
        }
        return array_values($valeurs);
    };
    return ['mois' => array_keys($mois), 'prets' => $compter('date_pret'), 'retours' => $compter('date_retour')];
}

/** « oct. 26 » à partir de « 2026-10 » (axe des courbes). */
function mois_court(string $cle): string
{
    [$a, $m] = explode('-', $cle);
    return MOIS_COURTS[(int) $m] . ' ' . substr($a, 2);
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
    $f = filtres_rapport();
    $sous = 'Bibliothèque universitaire — ' . reglage('site_nom', 'Université de Kara')
        . ' · ' . ($avecPeriode ? 'période ' . libelle_periode($du, $au) : 'situation au ' . date_fr(date('Y-m-d')))
        . ($f['site'] ? ' · ' . nom_site($f['site'], true) : '')
        . ($f['faculte'] !== '' ? ' · ' . $f['faculte'] : '');
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
            $pdf->titreSection('Meilleurs lecteurs de la période');
            $pdf->tableau([['Rang', 1, 'C'], ['Lecteur', 4.5, 'G'], ['Faculté / parcours', 5, 'G'], ['Titres', 1.3, 'D'], ['Prêts', 1.3, 'D']],
                array_map(fn($r, $i) => [$i + 1, $r['cle'], trim($r['faculte'] . ($r['parcours'] !== '' ? ' / ' . $r['parcours'] : '')) ?: (LIBELLES_CATEGORIE[$r['categorie']] ?? ''),
                    $r['titres'], $r['nb']], $s['top_lecteurs'], array_keys($s['top_lecteurs'])));
            if ($s['facultes']) {
                $pdf->titreSection('Facultés qui empruntent le plus');
                $pdf->tableau([['Rang', 1, 'C'], ['Faculté', 7, 'G'], ['Lecteurs', 1.6, 'D'], ['Prêts', 1.4, 'D']],
                    array_map(fn($r, $i) => [$i + 1, $r['cle'], $r['lecteurs'], $r['nb']], $s['facultes'], array_keys($s['facultes'])));
            }
            if ($s['par_parcours']) {
                $pdf->titreSection('Parcours qui empruntent le plus');
                $pdf->barres($paires($s['par_parcours']));
            }
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
                       LEFT JOIN exemplaires e ON e.ouvrage_id = o.id WHERE o.actif = 1" . filtre_site('o.site_id') . sql_f_ouvrages('o') . " GROUP BY o.id ORDER BY d.nom, o.titre");
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

        case 'mauvais-lecteurs':
            $l = lecteurs_a_relancer($du, $au, 200);
            $pdf->paragraphe('Lecteurs ayant rendu en retard, abîmé ou perdu un ouvrage sur la période, du plus problématique au moins. '
                . 'Un ouvrage perdu pèse 6 points, un ouvrage abîmé 3, un prêt encore en retard 2, un retour tardif 1.', 10, true);
            $pdf->tableau([['Lecteur', 5, 'G'], ['Catégorie / faculté', 5, 'G'], ['Contact', 5, 'G'], ['Prêts', 1.4, 'D'],
                ['Retards', 1.6, 'D'], ['Jours', 1.4, 'D'], ['Abîmés', 1.6, 'D'], ['Perdus', 1.5, 'D'], ['En cours', 1.7, 'D'], ['Score', 1.4, 'D']],
                array_map(fn($r) => [$r['cle'], trim((LIBELLES_CATEGORIE[$r['categorie']] ?? '') . ($r['faculte'] !== '' ? ' · ' . $r['faculte'] : '')),
                    trim($r['email'] . ($r['telephone'] !== '' ? ' · ' . $r['telephone'] : ''), ' ·'),
                    (int) $r['total'], (int) $r['retards'], (int) $r['jours'], (int) $r['abimes'], (int) $r['perdus'], (int) $r['encours'], $r['score']], $l));
            if (!$l) {
                $pdf->paragraphe('Aucun incident sur la période : tous les ouvrages ont été rendus à temps et en bon état.', 10);
            }
            break;

        case 'hors-circulation':
            $retires = rows("SELECT e.code, e.etat, e.notes, o.titre, o.cote FROM exemplaires e JOIN ouvrages o ON o.id = e.ouvrage_id
                             WHERE e.statut = 'retire'" . filtre_site('o.site_id') . sql_f_ouvrages('o') . " ORDER BY o.titre, e.code");
            $pdf->titreSection('Exemplaires retirés de la circulation (' . count($retires) . ')');
            $pdf->tableau([['Exemplaire', 2, 'G'], ['Ouvrage', 6, 'G'], ['État', 1.6, 'G'], ['Motif / remarque', 8, 'G']],
                array_map(fn($e) => [$e['code'], $e['titre'], ETATS_EXEMPLAIRE_TOUS[$e['etat']] ?? $e['etat'], $e['notes']], $retires));
            $constats = prets("p.etat_retour IN ('abime', 'perdu')", [], 'p.date_retour DESC');
            $pdf->titreSection('Constats « abîmé » ou « perdu » aux retours (' . count($constats) . ')');
            $pdf->tableau([['Rendu le', 1.8, 'C'], ['Exemplaire', 2, 'G'], ['Ouvrage', 5, 'G'], ['Lecteur', 3.5, 'G'], ['Constat', 1.8, 'G'], ['Description', 6, 'G']],
                array_map(fn($p) => [date_fr($p['date_retour']), $p['exemplaire_code'], $p['titre'], trim($p['nom'] . ' ' . $p['prenom']),
                    ETATS_RETOUR[$p['etat_retour']] ?? '', $p['note_retour']], $constats));
            break;
    }
    $pdf->sortie(slugify($titre) . ($avecPeriode ? "-$du-au-$au" : '-' . date('Y-m-d')) . '.pdf');
}
