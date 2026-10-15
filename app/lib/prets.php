<?php
/**
 * Règles de gestion des prêts et réservations.
 *
 * Cycle d'un exemplaire : disponible → prete → (retour) → disponible,
 * ou → reserve si un lecteur attend l'ouvrage (file d'attente par ordre de demande).
 * Les fonctions lèvent une RuntimeException avec un message affichable en cas de refus.
 */

const LIBELLES_CATEGORIE = [
    'etudiant'   => 'Étudiant',
    'enseignant' => 'Enseignant',
    'personnel'  => 'Personnel',
    'externe'    => 'Lecteur externe',
];

const LIBELLES_RESERVATION = [
    'en_attente' => 'En attente',
    'disponible' => 'À retirer',
    'honoree'    => 'Honorée',
    'annulee'    => 'Annulée',
    'expiree'    => 'Expirée',
];

const ETATS_EXEMPLAIRE = ['neuf' => 'Neuf', 'bon' => 'Bon état', 'use' => 'Usé', 'abime' => 'Abîmé'];

/** Constat fait au retour d'un prêt. */
const ETATS_RETOUR = [
    'conforme' => 'Rendu tel quel',
    'use'      => 'Usé (usure normale)',
    'abime'    => 'Abîmé',
    'perdu'    => 'Perdu / non rendu',
];

const LIBELLES_DEMANDE = [
    'en_attente' => 'En attente',
    'acceptee'   => 'Acceptée',
    'refusee'    => 'Refusée',
    'annulee'    => 'Sans objet',
];

function aujourdhui(): string
{
    return date('Y-m-d');
}

function plus_jours(string $date, int $jours): string
{
    return date('Y-m-d', strtotime("$date +$jours days"));
}

/** Nombre maximal de prêts simultanés pour un lecteur, selon sa catégorie. */
function quota_prets(array $lecteur): int
{
    return reglage_int('pret_max_' . ($lecteur['categorie'] ?: 'etudiant'), 3);
}

function nb_prets_en_cours(int $lecteurId): int
{
    return (int) val('SELECT COUNT(*) FROM prets WHERE lecteur_id = ? AND date_retour IS NULL', [$lecteurId]);
}

function nb_retards(int $lecteurId): int
{
    return (int) val('SELECT COUNT(*) FROM prets WHERE lecteur_id = ? AND date_retour IS NULL AND date_retour_prevue < CURDATE()', [$lecteurId]);
}

/** Prêts avec ouvrage, exemplaire et lecteur ; $where porte sur l'alias « p ». */
function prets(string $where = '1', array $params = [], string $ordre = 'p.date_pret DESC', ?int $limite = null, int $offset = 0): array
{
    $sql = "SELECT p.*, e.code AS exemplaire_code, e.etat AS exemplaire_etat, e.statut AS exemplaire_statut, o.id AS ouvrage_id, o.titre, o.slug, o.image,
                   d.slug AS domaine_slug, u.nom, u.prenom, u.matricule, u.email, u.telephone,
                   DATEDIFF(CURDATE(), p.date_retour_prevue) AS jours_retard
            FROM prets p
            JOIN exemplaires e ON e.id = p.exemplaire_id
            JOIN ouvrages o ON o.id = e.ouvrage_id
            JOIN domaines d ON d.id = o.domaine_id
            JOIN utilisateurs u ON u.id = p.lecteur_id
            WHERE $where ORDER BY $ordre";
    if ($limite !== null) {
        $sql .= ' LIMIT ' . (int) $limite . ' OFFSET ' . (int) $offset;
    }
    return rows($sql, $params);
}

function reservations(string $where = '1', array $params = [], string $ordre = 'r.demandee_le'): array
{
    return rows("SELECT r.*, o.titre, o.slug, o.image, d.slug AS domaine_slug, e.code AS exemplaire_code, e.etat AS exemplaire_etat,
                        u.nom, u.prenom, u.matricule, u.email,
                        (SELECT COUNT(*) FROM reservations r2 WHERE r2.ouvrage_id = r.ouvrage_id
                           AND r2.statut = 'en_attente' AND r2.demandee_le <= r.demandee_le) AS rang
                 FROM reservations r
                 JOIN ouvrages o ON o.id = r.ouvrage_id
                 JOIN domaines d ON d.id = o.domaine_id
                 JOIN utilisateurs u ON u.id = r.lecteur_id
                 LEFT JOIN exemplaires e ON e.id = r.exemplaire_id
                 WHERE $where ORDER BY $ordre", $params);
}

/** Date de retour proposée pour un prêt fait aujourd'hui. */
function echeance_standard(): string
{
    return plus_jours(aujourdhui(), reglage_int('pret_duree_jours', 14));
}

/** Contrôle une date d'échéance choisie à la main (AAAA-MM-JJ, entre demain et un an). */
function valider_echeance(string $date): string
{
    $d = DateTime::createFromFormat('!Y-m-d', $date);
    if (!$d || $d->format('Y-m-d') !== $date) {
        throw new RuntimeException('Date de retour invalide.');
    }
    if ($date <= aujourdhui() || $date > plus_jours(aujourdhui(), 365)) {
        throw new RuntimeException('La date de retour doit être comprise entre demain et dans un an.');
    }
    return $date;
}

/**
 * Enregistre un prêt (guichet). L'exemplaire doit être disponible,
 * ou réservé pour ce même lecteur.
 * $options : 'echeance' (date de retour choisie), 'etat' (état constaté à la remise), 'notes' (remarque).
 */
function enregistrer_pret(string $codeExemplaire, int $lecteurId, ?int $agentId, array $options = []): int
{
    $echeance = ($options['echeance'] ?? '') !== '' ? valider_echeance($options['echeance']) : echeance_standard();
    $etatSortie = $options['etat'] ?? '';
    if ($etatSortie !== '' && !isset(ETATS_EXEMPLAIRE[$etatSortie])) {
        throw new RuntimeException('État de l’exemplaire invalide.');
    }
    $ex = row('SELECT e.*, o.titre, o.consultation_sur_place, o.actif AS ouvrage_actif FROM exemplaires e JOIN ouvrages o ON o.id = e.ouvrage_id WHERE e.code = ?', [trim($codeExemplaire)]);
    if (!$ex) {
        throw new RuntimeException("Aucun exemplaire ne porte le code « $codeExemplaire ».");
    }
    $lecteur = row("SELECT * FROM utilisateurs WHERE id = ? AND role = 'lecteur'", [$lecteurId]);
    if (!$lecteur) {
        throw new RuntimeException('Lecteur introuvable.');
    }
    if (!$lecteur['actif']) {
        throw new RuntimeException('Le compte de ce lecteur est désactivé.');
    }
    if (!$ex['ouvrage_actif']) {
        throw new RuntimeException("« {$ex['titre']} » est désactivé : réactivez-le avant de le prêter.");
    }
    if ($ex['consultation_sur_place']) {
        throw new RuntimeException("« {$ex['titre']} » est en consultation sur place uniquement.");
    }

    $reservation = null;
    if ($ex['statut'] === 'reserve') {
        $reservation = row("SELECT * FROM reservations WHERE exemplaire_id = ? AND statut = 'disponible'", [$ex['id']]);
        if (!$reservation || (int) $reservation['lecteur_id'] !== $lecteurId) {
            throw new RuntimeException("Cet exemplaire est mis de côté pour un autre lecteur.");
        }
    } elseif ($ex['statut'] !== 'disponible') {
        throw new RuntimeException("Cet exemplaire n’est pas disponible (statut : {$ex['statut']}).");
    }
    // Garde-fou : jamais deux prêts ouverts sur le même exemplaire, quel que soit son statut affiché
    if (val('SELECT COUNT(*) FROM prets WHERE exemplaire_id = ? AND date_retour IS NULL', [$ex['id']])) {
        throw new RuntimeException('Cet exemplaire figure déjà dans un prêt en cours : enregistrez d’abord son retour.');
    }

    if (nb_retards($lecteurId) > 0) {
        throw new RuntimeException('Ce lecteur a des prêts en retard : enregistrez d’abord leur retour.');
    }
    $quota = quota_prets($lecteur);
    if (nb_prets_en_cours($lecteurId) >= $quota) {
        throw new RuntimeException("Quota atteint : $quota prêt(s) simultané(s) au maximum pour ce lecteur.");
    }

    db()->beginTransaction();
    try {
        $id = insert('prets', [
            'exemplaire_id'      => $ex['id'],
            'lecteur_id'         => $lecteurId,
            'agent_id'           => $agentId,
            'date_pret'          => aujourdhui(),
            'date_retour_prevue' => $echeance,
            'etat_sortie'        => $etatSortie ?: $ex['etat'],
            'notes'              => mb_substr(trim($options['notes'] ?? ''), 0, 255),
        ]);
        // L'état constaté à la remise devient l'état de référence de l'exemplaire
        q("UPDATE exemplaires SET statut = 'prete', etat = ? WHERE id = ?", [$etatSortie ?: $ex['etat'], $ex['id']]);
        if ($reservation) {
            q("UPDATE reservations SET statut = 'honoree' WHERE id = ?", [$reservation['id']]);
        } else {
            // Un lecteur qui emprunte un ouvrage qu'il attendait n'a plus besoin de sa réservation ;
            // si un autre exemplaire lui était mis de côté, celui-ci passe au lecteur suivant.
            foreach (rows("SELECT * FROM reservations WHERE ouvrage_id = ? AND lecteur_id = ? AND statut IN ('en_attente','disponible')",
                          [$ex['ouvrage_id'], $lecteurId]) as $r) {
                q("UPDATE reservations SET statut = 'honoree' WHERE id = ?", [$r['id']]);
                if ($r['statut'] === 'disponible' && $r['exemplaire_id']) {
                    liberer_exemplaire((int) $r['exemplaire_id']);
                }
            }
        }
        db()->commit();
    } catch (Throwable $t) {
        db()->rollBack();
        throw $t;
    }
    mail_pret_enregistre($id);
    return $id;
}

/**
 * Enregistre le retour d'un prêt avec le constat de l'exemplaire.
 * $constat : 'etat' (clé de ETATS_RETOUR, « conforme » par défaut), 'note', 'retirer' (abîmé : sortir l'exemplaire
 * de la circulation pour réparation), 'agent' (id du bibliothécaire).
 * Exemplaire perdu ou retiré : il ne revient pas en rayon ; sinon il passe au lecteur suivant de la file s'il y en a un.
 * Renvoie 'rayon', 'reserve' ou 'retire' selon ce que devient l'exemplaire.
 */
function enregistrer_retour(int $pretId, array $constat = []): string
{
    $pret = row('SELECT p.*, u.prenom, u.nom FROM prets p JOIN utilisateurs u ON u.id = p.lecteur_id WHERE p.id = ?', [$pretId]);
    if (!$pret) {
        throw new RuntimeException('Prêt introuvable.');
    }
    if ($pret['date_retour'] !== null) {
        throw new RuntimeException('Ce prêt est déjà rendu.');
    }
    $etat = $constat['etat'] ?? 'conforme';
    if (!isset(ETATS_RETOUR[$etat])) {
        throw new RuntimeException('Constat de retour invalide.');
    }
    $note = mb_substr(trim($constat['note'] ?? ''), 0, 255);
    if (in_array($etat, ['abime', 'perdu'], true) && $note === '') {
        throw new RuntimeException($etat === 'perdu'
            ? 'Exemplaire perdu : précisez les circonstances (et, le cas échéant, le remboursement convenu).'
            : 'Exemplaire abîmé : décrivez les dégâts constatés.');
    }
    $retirer = $etat === 'perdu' || ($etat === 'abime' && !empty($constat['retirer']));

    db()->beginTransaction();
    try {
        q('UPDATE prets SET date_retour = ?, etat_retour = ?, note_retour = ?, agent_retour_id = ? WHERE id = ?',
            [aujourdhui(), $etat, $note, $constat['agent'] ?? null, $pretId]);
        // Une demande de prolongation encore ouverte n'a plus d'objet
        q("UPDATE demandes_prolongation SET statut = 'annulee', traitee_le = NOW() WHERE pret_id = ? AND statut = 'en_attente'", [$pretId]);
        if (in_array($etat, ['use', 'abime'], true)) {
            q('UPDATE exemplaires SET etat = ? WHERE id = ?', [$etat, $pret['exemplaire_id']]);
        }
        if ($retirer) {
            $motif = ($etat === 'perdu' ? 'Perdu' : 'Retiré pour réparation') . ' le ' . date('d/m/Y') . ' ('
                . trim($pret['prenom'] . ' ' . $pret['nom']) . ') : ' . $note;
            q("UPDATE exemplaires SET statut = 'retire', notes = ? WHERE id = ?", [mb_substr($motif, 0, 255), $pret['exemplaire_id']]);
            $devenir = 'retire';
        } else {
            liberer_exemplaire((int) $pret['exemplaire_id']);
            $devenir = val('SELECT statut FROM exemplaires WHERE id = ?', [$pret['exemplaire_id']]) === 'reserve' ? 'reserve' : 'rayon';
        }
        db()->commit();
    } catch (Throwable $t) {
        db()->rollBack();
        throw $t;
    }
    return $devenir;
}

/** Retrouve le prêt en cours d'un exemplaire (douchette au guichet). */
function pret_par_code(string $code): int
{
    $id = val('SELECT p.id FROM prets p JOIN exemplaires e ON e.id = p.exemplaire_id WHERE e.code = ? AND p.date_retour IS NULL', [trim($code)]);
    if (!$id) {
        throw new RuntimeException("Aucun prêt en cours pour l’exemplaire « $code ».");
    }
    return (int) $id;
}

/**
 * Rend un exemplaire disponible, ou le met de côté pour la plus ancienne réservation en attente.
 * À appeler dans une transaction.
 */
function liberer_exemplaire(int $exemplaireId): void
{
    $ex = row('SELECT * FROM exemplaires WHERE id = ?', [$exemplaireId]);
    if (!$ex || $ex['statut'] === 'retire') {
        return;
    }
    $suivante = row("SELECT * FROM reservations WHERE ouvrage_id = ? AND statut = 'en_attente' ORDER BY demandee_le, id LIMIT 1", [$ex['ouvrage_id']]);
    if ($suivante) {
        q("UPDATE exemplaires SET statut = 'reserve' WHERE id = ?", [$exemplaireId]);
        q("UPDATE reservations SET statut = 'disponible', exemplaire_id = ?, disponible_le = NOW(), expire_le = ? WHERE id = ?",
            [$exemplaireId, plus_jours(aujourdhui(), reglage_int('reservation_delai_jours', 3)), $suivante['id']]);
        notifier_reservation_disponible((int) $suivante['id']);
    } else {
        q("UPDATE exemplaires SET statut = 'disponible' WHERE id = ?", [$exemplaireId]);
    }
}

/**
 * Attribue les exemplaires libres d'un ouvrage aux réservations en attente (par ordre de demande).
 * À appeler dans une transaction, par ex. après le retrait d'un exemplaire mis de côté.
 */
function servir_file_attente(int $ouvrageId): void
{
    foreach (rows("SELECT id FROM exemplaires WHERE ouvrage_id = ? AND statut = 'disponible' ORDER BY id", [$ouvrageId]) as $e) {
        if (!val("SELECT COUNT(*) FROM reservations WHERE ouvrage_id = ? AND statut = 'en_attente'", [$ouvrageId])) {
            return;
        }
        liberer_exemplaire((int) $e['id']);
    }
}

/** Nouvelle échéance proposée pour une prolongation : une durée de prêt complète après l'échéance actuelle. */
function echeance_prolongee(array $pret): string
{
    return plus_jours(max($pret['date_retour_prevue'], aujourdhui()), reglage_int('pret_duree_jours', 14));
}

/**
 * Ce qui s'oppose à la prolongation d'un prêt selon les règles (liste de messages, vide si rien).
 * Le lecteur ne peut pas demander une prolongation bloquée ; le personnel peut passer outre en connaissance de cause.
 */
function obstacles_prolongation(array $pret): array
{
    $obstacles = [];
    $max = reglage_int('pret_max_prolongations', 1);
    if ((int) $pret['prolongations'] >= $max) {
        $obstacles[] = "Déjà prolongé $max fois (maximum autorisé).";
    }
    if ($pret['date_retour_prevue'] < aujourdhui()) {
        $obstacles[] = 'Prêt en retard.';
    }
    $attente = (int) val("SELECT COUNT(*) FROM reservations r JOIN exemplaires e ON e.ouvrage_id = r.ouvrage_id
                          WHERE e.id = ? AND r.statut = 'en_attente'", [$pret['exemplaire_id']]);
    if ($attente) {
        $obstacles[] = pluriel($attente, 'autre lecteur', 'autres lecteurs') . ' attend' . ($attente > 1 ? 'ent' : '') . ' cet ouvrage.';
    }
    return $obstacles;
}

/**
 * Prolonge un prêt (décision du personnel). Par défaut d'une durée de prêt après l'échéance actuelle,
 * ou jusqu'à la date choisie. Sans $forcer, les règles (maximum, retard, file d'attente) s'appliquent.
 */
function prolonger_pret(int $pretId, ?string $date = null, bool $forcer = false): string
{
    $pret = row('SELECT * FROM prets WHERE id = ?', [$pretId]);
    if (!$pret) {
        throw new RuntimeException('Prêt introuvable.');
    }
    if ($pret['date_retour'] !== null) {
        throw new RuntimeException('Ce prêt est déjà rendu.');
    }
    if (!$forcer && ($obstacles = obstacles_prolongation($pret))) {
        throw new RuntimeException('Prolongation impossible : ' . implode(' ', $obstacles));
    }
    $nouvelle = $date !== null && $date !== '' ? valider_echeance($date) : echeance_prolongee($pret);
    if ($nouvelle <= $pret['date_retour_prevue']) {
        throw new RuntimeException('La nouvelle date doit être postérieure à l’échéance actuelle (' . date_fr($pret['date_retour_prevue']) . ').');
    }
    q('UPDATE prets SET date_retour_prevue = ?, prolongations = prolongations + 1, rappel_envoye_le = NULL WHERE id = ?', [$nouvelle, $pretId]);
    return $nouvelle;
}

/** Demandes de prolongation avec le prêt, l'ouvrage et le lecteur ; $where porte sur l'alias « dp ». */
function demandes_prolongation(string $where = '1', array $params = [], string $ordre = 'dp.demandee_le'): array
{
    return rows("SELECT dp.*, p.lecteur_id, p.exemplaire_id, p.date_pret, p.date_retour_prevue, p.date_retour, p.prolongations,
                        e.code AS exemplaire_code, o.id AS ouvrage_id, o.titre, o.slug, d.slug AS domaine_slug,
                        u.nom, u.prenom, u.matricule, u.email
                 FROM demandes_prolongation dp
                 JOIN prets p ON p.id = dp.pret_id
                 JOIN exemplaires e ON e.id = p.exemplaire_id
                 JOIN ouvrages o ON o.id = e.ouvrage_id
                 JOIN domaines d ON d.id = o.domaine_id
                 JOIN utilisateurs u ON u.id = p.lecteur_id
                 WHERE $where ORDER BY $ordre", $params);
}

/** Demande de prolongation faite par le lecteur depuis son espace ; c'est le personnel qui décide. */
function demander_prolongation(int $pretId, int $lecteurId, string $motif): void
{
    $pret = row('SELECT * FROM prets WHERE id = ? AND lecteur_id = ?', [$pretId, $lecteurId]);
    if (!$pret || $pret['date_retour'] !== null) {
        throw new RuntimeException('Prêt introuvable.');
    }
    if (val("SELECT COUNT(*) FROM demandes_prolongation WHERE pret_id = ? AND statut = 'en_attente'", [$pretId])) {
        throw new RuntimeException('Une demande est déjà en cours pour ce prêt : la bibliothèque va vous répondre.');
    }
    if ($obstacles = obstacles_prolongation($pret)) {
        throw new RuntimeException('Demande impossible : ' . implode(' ', $obstacles)
            . ($pret['date_retour_prevue'] < aujourdhui() ? ' Rapportez l’ouvrage à la bibliothèque.' : ''));
    }
    insert('demandes_prolongation', ['pret_id' => $pretId, 'motif' => mb_substr(trim($motif), 0, 255)]);
}

/**
 * Décision du personnel sur une demande. Accepter : prolongation jusqu'à $date (ou la date standard) ;
 * refuser : $reponse (motif communiqué au lecteur) est obligatoire. Le lecteur est prévenu par e-mail.
 */
function traiter_demande(int $demandeId, bool $accepter, int $agentId, string $reponse = '', ?string $date = null): string
{
    $dp = row('SELECT * FROM demandes_prolongation WHERE id = ?', [$demandeId]);
    if (!$dp) {
        throw new RuntimeException('Demande introuvable.');
    }
    if ($dp['statut'] !== 'en_attente') {
        throw new RuntimeException('Cette demande a déjà été traitée.');
    }
    $reponse = mb_substr(trim($reponse), 0, 255);
    $nouvelle = null;
    if ($accepter) {
        $nouvelle = prolonger_pret((int) $dp['pret_id'], $date, true); // le personnel décide en connaissance de cause
    } elseif ($reponse === '') {
        throw new RuntimeException('Indiquez au lecteur le motif du refus.');
    }
    q('UPDATE demandes_prolongation SET statut = ?, traitee_le = NOW(), agent_id = ?, reponse = ?, nouvelle_date = ? WHERE id = ?',
        [$accepter ? 'acceptee' : 'refusee', $agentId, $reponse, $nouvelle, $demandeId]);
    mail_decision_prolongation($demandeId);
    return $nouvelle ?? '';
}

/** Réservation d'un ouvrage par un lecteur depuis le site. */
function reserver(int $ouvrageId, int $lecteurId): array
{
    $o = row('SELECT * FROM ouvrages WHERE id = ? AND publie = 1 AND actif = 1', [$ouvrageId]);
    if (!$o) {
        throw new RuntimeException('Ouvrage introuvable.');
    }
    if ($o['consultation_sur_place']) {
        throw new RuntimeException('Cet ouvrage se consulte uniquement sur place.');
    }
    $lecteur = row("SELECT * FROM utilisateurs WHERE id = ? AND role = 'lecteur' AND actif = 1", [$lecteurId]);
    if (!$lecteur) {
        throw new RuntimeException('Seuls les comptes lecteurs actifs peuvent réserver.');
    }
    if (val("SELECT COUNT(*) FROM reservations WHERE ouvrage_id = ? AND lecteur_id = ? AND statut IN ('en_attente','disponible')", [$ouvrageId, $lecteurId])) {
        throw new RuntimeException('Vous avez déjà une réservation en cours pour cet ouvrage.');
    }
    if (val('SELECT COUNT(*) FROM prets p JOIN exemplaires e ON e.id = p.exemplaire_id
             WHERE e.ouvrage_id = ? AND p.lecteur_id = ? AND p.date_retour IS NULL', [$ouvrageId, $lecteurId])) {
        throw new RuntimeException('Vous avez déjà cet ouvrage en prêt.');
    }
    if (nb_retards($lecteurId) > 0) {
        throw new RuntimeException('Vous avez des prêts en retard : rapportez-les avant de réserver.');
    }
    $actives = (int) val("SELECT COUNT(*) FROM reservations WHERE lecteur_id = ? AND statut IN ('en_attente','disponible')", [$lecteurId]);
    if ($actives + nb_prets_en_cours($lecteurId) >= quota_prets($lecteur)) {
        throw new RuntimeException('Vous avez atteint le nombre maximal de prêts et réservations simultanés.');
    }
    if (!val("SELECT COUNT(*) FROM exemplaires WHERE ouvrage_id = ? AND statut <> 'retire'", [$ouvrageId])) {
        throw new RuntimeException('Aucun exemplaire de cet ouvrage n’est en circulation.');
    }

    db()->beginTransaction();
    try {
        $id = insert('reservations', ['ouvrage_id' => $ouvrageId, 'lecteur_id' => $lecteurId]);
        $libre = val("SELECT id FROM exemplaires WHERE ouvrage_id = ? AND statut = 'disponible' ORDER BY id LIMIT 1 FOR UPDATE", [$ouvrageId]);
        if ($libre) {
            liberer_exemplaire((int) $libre); // la réservation qu'on vient de créer est la première de la file
        }
        db()->commit();
    } catch (Throwable $t) {
        db()->rollBack();
        throw $t;
    }
    return row('SELECT * FROM reservations WHERE id = ?', [$id]);
}

function annuler_reservation(int $reservationId, ?int $lecteurId = null): void
{
    $r = row('SELECT * FROM reservations WHERE id = ?', [$reservationId]);
    if (!$r || ($lecteurId !== null && (int) $r['lecteur_id'] !== $lecteurId)) {
        throw new RuntimeException('Réservation introuvable.');
    }
    if (!in_array($r['statut'], ['en_attente', 'disponible'], true)) {
        throw new RuntimeException('Cette réservation n’est plus active.');
    }
    db()->beginTransaction();
    try {
        q("UPDATE reservations SET statut = 'annulee' WHERE id = ?", [$reservationId]);
        if ($r['statut'] === 'disponible' && $r['exemplaire_id']) {
            liberer_exemplaire((int) $r['exemplaire_id']);
        }
        db()->commit();
    } catch (Throwable $t) {
        db()->rollBack();
        throw $t;
    }
}

/** Expire les réservations non retirées dans le délai et libère leurs exemplaires. */
function expirer_reservations(): int
{
    $echues = rows("SELECT * FROM reservations WHERE statut = 'disponible' AND expire_le < CURDATE()");
    foreach ($echues as $r) {
        db()->beginTransaction();
        try {
            q("UPDATE reservations SET statut = 'expiree' WHERE id = ?", [$r['id']]);
            if ($r['exemplaire_id']) {
                liberer_exemplaire((int) $r['exemplaire_id']);
            }
            db()->commit();
        } catch (Throwable $t) {
            db()->rollBack();
            throw $t;
        }
    }
    return count($echues);
}

/**
 * Désactive un ouvrage au lieu de le supprimer : il disparaît du site, ne peut plus être prêté ni réservé,
 * mais son historique est conservé. Les réservations en cours sont annulées (lecteurs prévenus) ;
 * les prêts en cours se terminent normalement. Renvoie le nombre de réservations annulées.
 */
function desactiver_ouvrage(int $ouvrageId): int
{
    $annulees = rows("SELECT * FROM reservations WHERE ouvrage_id = ? AND statut IN ('en_attente','disponible')", [$ouvrageId]);
    db()->beginTransaction();
    try {
        q('UPDATE ouvrages SET actif = 0, publie = 0, coup_de_coeur = 0, desactive_le = NOW() WHERE id = ?', [$ouvrageId]);
        foreach ($annulees as $r) {
            q("UPDATE reservations SET statut = 'annulee' WHERE id = ?", [$r['id']]);
            if ($r['statut'] === 'disponible' && $r['exemplaire_id']) {
                q("UPDATE exemplaires SET statut = 'disponible' WHERE id = ? AND statut = 'reserve'", [$r['exemplaire_id']]);
            }
        }
        db()->commit();
    } catch (Throwable $t) {
        db()->rollBack();
        throw $t;
    }
    foreach ($annulees as $r) {
        mail_reservation_annulee((int) $r['id']);
    }
    return count($annulees);
}

/** Remet un ouvrage désactivé au catalogue (publié, prêtable, réservable). */
function reactiver_ouvrage(int $ouvrageId): void
{
    q('UPDATE ouvrages SET actif = 1, publie = 1, desactive_le = NULL WHERE id = ?', [$ouvrageId]);
}
