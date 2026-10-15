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
    $sql = "SELECT p.*, e.code AS exemplaire_code, o.id AS ouvrage_id, o.titre, o.slug, o.image,
                   d.slug AS domaine_slug, u.nom, u.prenom, u.matricule, u.email,
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
    return rows("SELECT r.*, o.titre, o.slug, o.image, d.slug AS domaine_slug, e.code AS exemplaire_code,
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

/**
 * Enregistre un prêt (guichet). L'exemplaire doit être disponible,
 * ou réservé pour ce même lecteur.
 */
function enregistrer_pret(string $codeExemplaire, int $lecteurId, ?int $agentId): int
{
    $ex = row('SELECT e.*, o.titre, o.consultation_sur_place FROM exemplaires e JOIN ouvrages o ON o.id = e.ouvrage_id WHERE e.code = ?', [trim($codeExemplaire)]);
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
            'date_retour_prevue' => plus_jours(aujourdhui(), reglage_int('pret_duree_jours', 14)),
        ]);
        q("UPDATE exemplaires SET statut = 'prete' WHERE id = ?", [$ex['id']]);
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
    return $id;
}

/** Enregistre le retour d'un prêt et passe l'exemplaire au lecteur suivant s'il y en a un. */
function enregistrer_retour(int $pretId): void
{
    $pret = row('SELECT * FROM prets WHERE id = ?', [$pretId]);
    if (!$pret) {
        throw new RuntimeException('Prêt introuvable.');
    }
    if ($pret['date_retour'] !== null) {
        throw new RuntimeException('Ce prêt est déjà rendu.');
    }
    db()->beginTransaction();
    try {
        q('UPDATE prets SET date_retour = ? WHERE id = ?', [aujourdhui(), $pretId]);
        liberer_exemplaire((int) $pret['exemplaire_id']);
        db()->commit();
    } catch (Throwable $t) {
        db()->rollBack();
        throw $t;
    }
}

/** Retour par code d'exemplaire (douchette au guichet). */
function retour_par_code(string $code): array
{
    $pret = row('SELECT p.id, o.titre, u.prenom, u.nom FROM prets p
                 JOIN exemplaires e ON e.id = p.exemplaire_id JOIN ouvrages o ON o.id = e.ouvrage_id
                 JOIN utilisateurs u ON u.id = p.lecteur_id
                 WHERE e.code = ? AND p.date_retour IS NULL', [trim($code)]);
    if (!$pret) {
        throw new RuntimeException("Aucun prêt en cours pour l’exemplaire « $code ».");
    }
    enregistrer_retour((int) $pret['id']);
    return $pret;
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

/**
 * Prolonge un prêt d'une durée de prêt complète à partir de la date prévue.
 * Refusé si le maximum est atteint, si le prêt est en retard ou si d'autres lecteurs attendent l'ouvrage.
 */
function prolonger_pret(int $pretId, ?int $lecteurId = null): string
{
    $pret = row('SELECT p.*, e.ouvrage_id FROM prets p JOIN exemplaires e ON e.id = p.exemplaire_id WHERE p.id = ?', [$pretId]);
    if (!$pret || ($lecteurId !== null && (int) $pret['lecteur_id'] !== $lecteurId)) {
        throw new RuntimeException('Prêt introuvable.');
    }
    if ($pret['date_retour'] !== null) {
        throw new RuntimeException('Ce prêt est déjà rendu.');
    }
    $max = reglage_int('pret_max_prolongations', 1);
    if ((int) $pret['prolongations'] >= $max) {
        throw new RuntimeException("Ce prêt a déjà été prolongé $max fois (maximum autorisé).");
    }
    if ($pret['date_retour_prevue'] < aujourdhui()) {
        throw new RuntimeException('Un prêt en retard ne peut pas être prolongé : rapportez l’ouvrage à la bibliothèque.');
    }
    if (val("SELECT COUNT(*) FROM reservations WHERE ouvrage_id = ? AND statut = 'en_attente'", [$pret['ouvrage_id']]) > 0) {
        throw new RuntimeException('D’autres lecteurs attendent cet ouvrage : la prolongation est impossible.');
    }
    $nouvelle = plus_jours($pret['date_retour_prevue'], reglage_int('pret_duree_jours', 14));
    q('UPDATE prets SET date_retour_prevue = ?, prolongations = prolongations + 1 WHERE id = ?', [$nouvelle, $pretId]);
    return $nouvelle;
}

/** Réservation d'un ouvrage par un lecteur depuis le site. */
function reserver(int $ouvrageId, int $lecteurId): array
{
    $o = row('SELECT * FROM ouvrages WHERE id = ? AND publie = 1', [$ouvrageId]);
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
