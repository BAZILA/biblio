<?php
/**
 * Guichet : prêt (lecteur + ouvrage choisis dans des listes) et retour (liste des prêts en cours).
 * guichet/exemplaires?ouvrage=&lecteur= (JSON) remplit la liste des exemplaires de l'ouvrage choisi.
 * Un code d'exemplaire saisi ou scanné reste possible.
 */

if ($action === 'exemplaires') {
    exiger('prets.ajouter', 'reservations.remettre');
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'exemplaires' => exemplaires_pretables((int) get_str('ouvrage'), (int) get_str('lecteur')),
        'attente' => attente_ouvrage((int) get_str('ouvrage'), (int) get_str('lecteur')),
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Étudiants de la liste de l'université sans fiche lecteur (JSON), proposés dans le choix du lecteur
if ($action === 'etudiants') {
    exiger('prets.ajouter');
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array_map(fn($et) => [
        'valeur' => 'etu-' . $et['id'],
        'texte' => $et['nom'] . ' ' . $et['prenom'] . ' — Étudiant · n° de suivi ' . $et['numero_suivi'] . ' (sans compte)',
    ], etudiants_sans_fiche(get_str('q'))), JSON_UNESCAPED_UNICODE);
    exit;
}

// Étudiant de la liste choisi au guichet : sa fiche lecteur est créée (sans accès en ligne) pour enregistrer le prêt
if ($action === 'etudiant') {
    exiger_post();
    exiger('prets.ajouter');
    try {
        $lecteurId = lecteur_pour_etudiant($id ?: (int) ($_POST['etudiant_id'] ?? 0));
    } catch (RuntimeException $ex) {
        flash('erreur', $ex->getMessage());
        redirect(admin_url('guichet'));
    }
    redirect(admin_url('guichet', ['lecteur' => $lecteurId] + ((int) post_str('ouvrage') ? ['ouvrage' => (int) post_str('ouvrage')] : [])));
}

/** Exemplaires prêtables : ceux mis de côté pour ce lecteur d'abord, puis les disponibles (meilleur état d'abord). */
function exemplaires_pretables(int $ouvrageId, int $lecteurId): array
{
    return rows("SELECT e.code, e.etat, (r.id IS NOT NULL) AS pour_lui
                 FROM exemplaires e
                 LEFT JOIN reservations r ON r.exemplaire_id = e.id AND r.statut = 'disponible' AND r.lecteur_id = ?
                 WHERE e.ouvrage_id = ? AND (e.statut = 'disponible' OR r.id IS NOT NULL)
                 ORDER BY pour_lui DESC, FIELD(e.etat, 'neuf', 'bon', 'use', 'abime'), e.code", [$lecteurId, $ouvrageId]);
}

/**
 * Réservations en cours sur un ouvrage, pour prévenir l'agent avant d'enregistrer un prêt :
 * combien de lecteurs attendent (hors celui du guichet), qui est en tête, et ce que ce lecteur a lui-même réservé.
 */
function attente_ouvrage(int $ouvrageId, int $lecteurId): array
{
    $vide = ['nb' => 0, 'mis_de_cote' => 0, 'premier' => '', 'depuis' => '', 'sien' => ''];
    if (!$ouvrageId) {
        return $vide;
    }
    $autres = row("SELECT COUNT(*) AS nb, COALESCE(SUM(r.statut = 'disponible'), 0) AS mis_de_cote FROM reservations r
                   WHERE r.ouvrage_id = ? AND r.lecteur_id <> ? AND r.statut IN ('en_attente','disponible')", [$ouvrageId, $lecteurId]);
    $premier = row("SELECT u.prenom, u.nom, r.demandee_le FROM reservations r JOIN utilisateurs u ON u.id = r.lecteur_id
                    WHERE r.ouvrage_id = ? AND r.lecteur_id <> ? AND r.statut IN ('en_attente','disponible')
                    ORDER BY FIELD(r.statut, 'disponible', 'en_attente'), r.demandee_le, r.id LIMIT 1", [$ouvrageId, $lecteurId]);
    return [
        'nb' => (int) $autres['nb'],
        'mis_de_cote' => (int) $autres['mis_de_cote'],
        'premier' => $premier ? trim($premier['prenom'] . ' ' . $premier['nom']) : '',
        'depuis' => $premier ? date_fr($premier['demandee_le']) : '',
        'sien' => (string) val("SELECT statut FROM reservations WHERE ouvrage_id = ? AND lecteur_id = ? AND statut IN ('en_attente','disponible')
                                ORDER BY FIELD(statut, 'disponible', 'en_attente') LIMIT 1", [$ouvrageId, $lecteurId]),
    ];
}

if ($action === 'pret') {
    exiger_post();
    exiger('prets.ajouter', 'reservations.remettre');
    $lecteurId = (int) ($_POST['lecteur_id'] ?? 0);
    $ouvrageId = (int) ($_POST['ouvrage_id'] ?? 0);
    // Priorité : code scanné, puis exemplaire choisi, puis le meilleur exemplaire disponible de l'ouvrage
    $code = post_str('code_scan') ?: post_str('code');
    if ($code === '' && $ouvrageId) {
        $code = (string) (exemplaires_pretables($ouvrageId, $lecteurId)[0]['code'] ?? '');
        if ($code === '') {
            flash('erreur', 'Aucun exemplaire disponible pour cet ouvrage : le lecteur peut le réserver.');
            redirect(admin_url('guichet', array_filter(['lecteur' => $lecteurId, 'ouvrage' => $ouvrageId])));
        }
    }
    // Sans « Prêts : ajouter », seul un exemplaire mis de côté pour ce lecteur peut lui être remis
    if ($code !== '' && !peut('prets.ajouter') && !val("SELECT 1 FROM reservations r JOIN exemplaires e ON e.id = r.exemplaire_id
            WHERE e.code = ? AND r.lecteur_id = ? AND r.statut = 'disponible'", [$code, $lecteurId])) {
        exiger('prets.ajouter');
    }
    if (!$lecteurId || $code === '') {
        flash('erreur', 'Choisissez un lecteur et un ouvrage.');
    } else {
        try {
            $pretId = enregistrer_pret($code, $lecteurId, (int) $moi['id'], [
                'echeance' => post_str('echeance'),
                'etat'     => post_str('etat'),
                'notes'    => post_str('notes'),
            ]);
            $p = prets('p.id = ?', [$pretId], 'p.date_pret DESC', null, 0, true)[0];
            flash('succes', "Prêt enregistré : « {$p['titre']} » (exemplaire {$p['exemplaire_code']}) pour " . trim($p['prenom'] . ' ' . $p['nom'])
                . ', à rendre le ' . date_fr($p['date_retour_prevue']) . '.' . ($p['email'] ? '' : ' Lecteur sans e-mail : indiquez-lui la date de retour.'));
            // Rappel à l'agent : d'autres lecteurs attendent toujours cet ouvrage
            $reste = attente_ouvrage((int) val('SELECT ouvrage_id FROM exemplaires WHERE code = ?', [$code]), $lecteurId);
            if ($reste['nb']) {
                flash('info', pluriel($reste['nb'], 'lecteur attend', 'lecteurs attendent') . ' encore « ' . $p['titre'] . ' »'
                    . ($reste['premier'] !== '' ? ', à commencer par ' . $reste['premier'] : '') . '.');
            }
        } catch (RuntimeException $ex) {
            flash('erreur', $ex->getMessage());
            redirect(admin_url('guichet', array_filter(['lecteur' => $lecteurId, 'ouvrage' => $ouvrageId])));
        }
    }
    redirect(retour_sur(post_str('retour'), admin_url('guichet', $lecteurId ? ['lecteur' => $lecteurId] : [])));
}

if ($action === 'retour') {
    exiger_post();
    exiger('prets.retour');
    try {
        $codeScan = post_str('code');
        $pretId = $codeScan !== '' ? pret_par_code($codeScan) : (int) ($_POST['pret_id'] ?? 0);
        if (!$pretId) {
            throw new RuntimeException('Choisissez le prêt à clore dans la liste.');
        }
        $p = prets('p.id = ? AND p.date_retour IS NULL', [$pretId], 'p.date_pret DESC', null, 0, true)[0] ?? null;
        if (!$p) {
            throw new RuntimeException('Ce prêt est introuvable ou déjà rendu.');
        }
        $devenir = enregistrer_retour($pretId, constat_retour_post((int) $moi['id']));
        $message = "Retour enregistré : « {$p['titre']} » ({$p['exemplaire_code']}) rendu par " . trim($p['prenom'] . ' ' . $p['nom'])
            . ' — ' . mb_strtolower(ETATS_RETOUR[post_str('etat_retour', 'conforme')] ?? '') . '. ';
        if ($devenir === 'reserve') {
            $suite = row("SELECT u.prenom, u.nom FROM reservations r JOIN utilisateurs u ON u.id = r.lecteur_id
                          WHERE r.exemplaire_id = ? AND r.statut = 'disponible'", [$p['exemplaire_id']]);
            $message .= 'À mettre de côté pour ' . trim(($suite['prenom'] ?? '') . ' ' . ($suite['nom'] ?? '')) . ' (réservation).';
        } else {
            $message .= DEVENIR_EXEMPLAIRE[$devenir];
        }
        flash('succes', $message);
    } catch (RuntimeException $ex) {
        flash('erreur', $ex->getMessage());
    }
    redirect(retour_sur(post_str('retour'), admin_url('guichet', isset($_POST['lecteur']) ? ['lecteur' => (int) $_POST['lecteur']] : [])));
}

$lecteur = null;
$pretsLecteur = $reservationsLecteur = [];
if (get_str('lecteur') !== '') {
    $lecteur = row("SELECT * FROM utilisateurs WHERE id = ? AND role = 'lecteur'", [(int) get_str('lecteur')]);
    if ($lecteur) {
        $pretsLecteur = prets('p.lecteur_id = ? AND p.date_retour IS NULL', [$lecteur['id']], 'p.date_retour_prevue', null, 0, true);
        $reservationsLecteur = reservations("r.lecteur_id = ? AND r.statut IN ('en_attente','disponible')", [$lecteur['id']], 'r.demandee_le', true);
    }
}
$rapide = $_SESSION['lecteur_rapide'] ?? null;
unset($_SESSION['lecteur_rapide']);

admin_page('guichet', [
    'lecteur' => $lecteur,
    'ouvrageChoisi' => (int) get_str('ouvrage'),
    'lecteurs' => rows("SELECT id, nom, prenom, matricule, categorie, actif FROM utilisateurs WHERE role = 'lecteur' AND (actif = 1 OR id = ?)
                        ORDER BY nom, prenom", [(int) ($lecteur['id'] ?? 0)]),
    'ouvrages' => enrichir(rows(OUVRAGE_SELECT . ' WHERE o.actif = 1' . filtre_site('o.site_id') . ' ORDER BY o.titre')),
    'pretsEnCours' => prets('p.date_retour IS NULL', [], 'u.nom, u.prenom, o.titre'),
    'pretsLecteur' => $pretsLecteur,
    'reservationsLecteur' => $reservationsLecteur,
    'nouveauLecteur' => get_str('nouveau_lecteur') !== '',
    'etudiantsCharges' => peut('prets.ajouter') && (bool) val('SELECT 1 FROM etudiants WHERE actif = 1 LIMIT 1'),
    'rapide' => $rapide,
    'derniers' => prets('1', [], 'p.id DESC', 8),
], 'Guichet', 'guichet');
