<?php
/**
 * E-mails aux lecteurs : prêt enregistré, rappel avant échéance, retard, réservation à retirer,
 * compte validé, mot de passe réinitialisé.
 *
 * Deux modes (Réglages) : « journal » (rien n'est envoyé, tout est consigné dans la table mails)
 * ou « smtp » (envoi par le serveur SMTP configuré ; chaque envoi est aussi consigné).
 */

/** Envoie (ou consigne) un e-mail texte. Ne lève jamais d'exception : un échec est journalisé. */
function envoyer_mail(?string $destinataire, string $sujet, string $texte, string $motif = ''): bool
{
    $destinataire = trim((string) $destinataire);
    if ($destinataire === '' || !filter_var($destinataire, FILTER_VALIDATE_EMAIL)) {
        return false; // lecteur sans adresse : rien à envoyer
    }
    $texte .= "\n\n-- \n" . reglage('contact_nom', 'Bibliothèque universitaire') . "\n" . site_url_absolue();
    $statut = 'journal';
    $erreur = '';
    if (reglage('mail_mode') === 'smtp') {
        try {
            smtp_envoyer($destinataire, $sujet, $texte);
            $statut = 'envoye';
        } catch (Throwable $t) {
            $statut = 'echec';
            $erreur = mb_substr($t->getMessage(), 0, 255);
        }
    }
    try {
        insert('mails', ['destinataire' => $destinataire, 'sujet' => mb_substr($sujet, 0, 255), 'corps' => $texte,
            'motif' => $motif, 'statut' => $statut, 'erreur' => $erreur]);
    } catch (Throwable) {
        // le journal ne doit jamais bloquer un prêt
    }
    return $statut === 'envoye';
}

/** Adresse du site utilisable dans un e-mail (aussi en ligne de commande). */
function site_url_absolue(): string
{
    if (PHP_SAPI === 'cli') {
        return reglage('site_adresse', 'http://localhost/biblio/');
    }
    return site_url();
}

/* ---------- Client SMTP minimal (STARTTLS / SSL, AUTH LOGIN) ---------- */

function smtp_envoyer(string $a, string $sujet, string $texte): void
{
    $hote = reglage('smtp_hote');
    $port = reglage_int('smtp_port', 587);
    $securite = reglage('smtp_securite', 'tls');
    $de = reglage('mail_expediteur') ?: reglage('contact_email');
    if ($hote === '' || !filter_var($de, FILTER_VALIDATE_EMAIL)) {
        throw new RuntimeException('SMTP non configuré (serveur ou adresse d’expédition manquants).');
    }
    $contexte = stream_context_create(['ssl' => ['verify_peer' => true, 'verify_peer_name' => true]]);
    $flux = @stream_socket_client(($securite === 'ssl' ? 'ssl://' : 'tcp://') . "$hote:$port", $errno, $errstr, 15, STREAM_CLIENT_CONNECT, $contexte);
    if (!$flux) {
        throw new RuntimeException("Connexion SMTP impossible ($errstr).");
    }
    stream_set_timeout($flux, 20);
    $lire = function (array $codesAttendus) use ($flux): string {
        $reponse = '';
        while (($ligne = fgets($flux, 1024)) !== false) {
            $reponse .= $ligne;
            if (strlen($ligne) < 4 || $ligne[3] !== '-') {
                break;
            }
        }
        if (!in_array((int) substr($reponse, 0, 3), $codesAttendus, true)) {
            throw new RuntimeException('Réponse SMTP inattendue : ' . trim($reponse));
        }
        return $reponse;
    };
    $ecrire = function (string $commande, array $codes) use ($flux, $lire): string {
        fwrite($flux, $commande . "\r\n");
        return $lire($codes);
    };
    try {
        $lire([220]);
        $nomLocal = gethostname() ?: 'localhost';
        $ecrire("EHLO $nomLocal", [250]);
        if ($securite === 'tls') {
            $ecrire('STARTTLS', [220]);
            if (!stream_socket_enable_crypto($flux, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                throw new RuntimeException('Échec de la négociation TLS.');
            }
            $ecrire("EHLO $nomLocal", [250]);
        }
        $utilisateur = reglage('smtp_utilisateur');
        if ($utilisateur !== '') {
            $ecrire('AUTH LOGIN', [334]);
            $ecrire(base64_encode($utilisateur), [334]);
            $ecrire(base64_encode(reglage('smtp_mot_de_passe')), [235]);
        }
        $ecrire("MAIL FROM:<$de>", [250]);
        $ecrire("RCPT TO:<$a>", [250, 251]);
        $ecrire('DATA', [354]);
        $nomExp = reglage('mail_expediteur_nom', 'Bibliothèque');
        $entetes = [
            'Date: ' . date('r'),
            'From: =?UTF-8?B?' . base64_encode($nomExp) . "?= <$de>",
            "To: <$a>",
            'Subject: =?UTF-8?B?' . base64_encode($sujet) . '?=',
            'Message-ID: <' . bin2hex(random_bytes(12)) . '@' . (explode('@', $de)[1] ?? 'localhost') . '>',
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=UTF-8',
            'Content-Transfer-Encoding: base64',
        ];
        $corps = chunk_split(base64_encode($texte), 76, "\r\n");
        fwrite($flux, implode("\r\n", $entetes) . "\r\n\r\n" . $corps . "\r\n.\r\n");
        $lire([250]);
        $ecrire('QUIT', [221]);
    } finally {
        fclose($flux);
    }
}

/* ---------- Messages ---------- */

function bonjour(array $u): string
{
    return 'Bonjour ' . (trim($u['prenom'] ?? '') ?: trim($u['nom'] ?? '')) . ',';
}

function mail_pret_enregistre(int $pretId): void
{
    $p = prets('p.id = ?', [$pretId])[0] ?? null;
    if (!$p) {
        return;
    }
    envoyer_mail($p['email'], 'Prêt enregistré : ' . $p['titre'],
        bonjour($p) . "\n\nVous avez emprunté « {$p['titre']} » (exemplaire {$p['exemplaire_code']}) le " . date_fr($p['date_pret']) . ".\n"
        . 'À rendre au plus tard le ' . date_fr($p['date_retour_prevue']) . ".\n\n"
        . "Vous pouvez suivre vos prêts et demander une prolongation depuis votre espace lecteur :\n" . site_url_absolue() . 'index.php/compte', 'pret');
}

function mail_rappel(array $p): void
{
    envoyer_mail($p['email'], 'Rappel : « ' . $p['titre'] . ' » à rendre le ' . date_fr($p['date_retour_prevue']),
        bonjour($p) . "\n\nPetit rappel : l’ouvrage « {$p['titre']} » est à rendre le " . date_fr($p['date_retour_prevue']) . ".\n"
        . "Besoin de plus de temps ? Faites une demande de prolongation depuis votre espace lecteur :\n" . site_url_absolue() . 'index.php/compte', 'rappel');
}

function mail_retard(array $p): void
{
    envoyer_mail($p['email'], 'Retard : merci de rapporter « ' . $p['titre'] . ' »',
        bonjour($p) . "\n\nL’ouvrage « {$p['titre']} » devait être rendu le " . date_fr($p['date_retour_prevue'])
        . ' (' . pluriel((int) $p['jours_retard'], 'jour') . " de retard).\n"
        . "Merci de le rapporter rapidement : tant qu’un prêt est en retard, vous ne pouvez ni emprunter ni réserver d’autres ouvrages.", 'retard');
}

function mail_reservation_disponible(int $reservationId): void
{
    $r = reservations('r.id = ?', [$reservationId])[0] ?? null;
    if (!$r || $r['statut'] !== 'disponible') {
        return;
    }
    envoyer_mail($r['email'], 'Votre réservation est disponible : ' . $r['titre'],
        bonjour($r) . "\n\nL’ouvrage « {$r['titre']} » que vous avez réservé vous attend à l’accueil de la bibliothèque"
        . ' jusqu’au ' . date_fr($r['expire_le']) . ".\nPassé ce délai, la réservation sera annulée et l’ouvrage proposé au lecteur suivant.", 'reservation');
}

function mail_reservation_annulee(int $reservationId): void
{
    $r = reservations('r.id = ?', [$reservationId])[0] ?? null;
    if (!$r) {
        return;
    }
    envoyer_mail($r['email'], 'Réservation annulée : ' . $r['titre'],
        bonjour($r) . "\n\nL’ouvrage « {$r['titre']} » n’est plus proposé au prêt par la bibliothèque : votre réservation est donc annulée.\n"
        . 'Nous vous prions de nous excuser pour ce désagrément.', 'reservation');
}

function mail_decision_prolongation(int $demandeId): void
{
    $d = demandes_prolongation('dp.id = ?', [$demandeId])[0] ?? null;
    if (!$d || !in_array($d['statut'], ['acceptee', 'refusee'], true)) {
        return;
    }
    if ($d['statut'] === 'acceptee') {
        $texte = "Votre demande de prolongation pour « {$d['titre']} » est acceptée.\nNouvelle date de retour : " . date_fr($d['nouvelle_date']) . '.';
        if ($d['reponse'] !== '') {
            $texte .= "\nMessage de la bibliothèque : {$d['reponse']}";
        }
    } else {
        $texte = "Votre demande de prolongation pour « {$d['titre']} » n’a pas pu être acceptée.\nMotif : {$d['reponse']}\n"
            . 'L’ouvrage reste à rendre le ' . date_fr($d['date_retour_prevue']) . '.';
    }
    envoyer_mail($d['email'], 'Prolongation ' . ($d['statut'] === 'acceptee' ? 'acceptée' : 'refusée') . ' : ' . $d['titre'],
        bonjour($d) . "\n\n" . $texte, 'prolongation');
}

function mail_compte_valide(array $u): void
{
    envoyer_mail($u['email'], 'Votre compte lecteur est activé',
        bonjour($u) . "\n\nVotre compte lecteur a été validé par la bibliothèque. Vous pouvez vous connecter pour réserver des ouvrages et suivre vos prêts :\n"
        . site_url_absolue() . 'index.php/compte/connexion', 'compte');
}

function mail_mot_de_passe(array $u, string $motDePasse): void
{
    envoyer_mail($u['email'], 'Votre nouveau mot de passe',
        bonjour($u) . "\n\nLa bibliothèque a réinitialisé votre mot de passe. Mot de passe provisoire : $motDePasse\n"
        . "Connectez-vous puis changez-le dans votre espace lecteur :\n" . site_url_absolue() . 'index.php/compte/connexion', 'compte');
}

/* ---------- Notifications différées (après la fin de la transaction en cours) ---------- */

/** Programme l'avis « réservation disponible » pour la fin de la requête. */
function notifier_reservation_disponible(int $reservationId): void
{
    static $enregistre = false;
    $GLOBALS['reservations_a_notifier'][$reservationId] = true;
    if (!$enregistre) {
        $enregistre = true;
        register_shutdown_function(function () {
            foreach (array_keys($GLOBALS['reservations_a_notifier'] ?? []) as $id) {
                mail_reservation_disponible((int) $id); // ne part que si la réservation est toujours « disponible »
            }
        });
    }
}

/* ---------- Tâches quotidiennes ---------- */

/**
 * Expire les réservations non retirées, envoie les rappels avant échéance et les relances de retard.
 * Lancée au premier passage dans l'admin chaque jour, ou par le planificateur (php app/taches.php).
 */
function taches_quotidiennes(bool $forcer = false): array
{
    if (!$forcer && reglage('taches_dernier_jour') === aujourdhui()) {
        return [];
    }
    q('REPLACE INTO reglages (cle, valeur) VALUES (?, ?)', ['taches_dernier_jour', aujourdhui()]);

    $bilan = ['reservations_expirees' => expirer_reservations(), 'rappels' => 0, 'relances' => 0];
    $avant = reglage_int('rappel_jours_avant', 2);
    foreach (prets("p.date_retour IS NULL AND p.rappel_envoye_le IS NULL
                    AND p.date_retour_prevue BETWEEN CURDATE() AND CURDATE() + INTERVAL ? DAY", [$avant]) as $p) {
        mail_rappel($p);
        q('UPDATE prets SET rappel_envoye_le = CURDATE() WHERE id = ?', [$p['id']]);
        $bilan['rappels']++;
    }
    $intervalle = max(1, reglage_int('relance_tous_les_jours', 7));
    foreach (prets("p.date_retour IS NULL AND p.date_retour_prevue < CURDATE()
                    AND (p.relance_envoyee_le IS NULL OR p.relance_envoyee_le <= CURDATE() - INTERVAL ? DAY)", [$intervalle]) as $p) {
        mail_retard($p);
        q('UPDATE prets SET relance_envoyee_le = CURDATE() WHERE id = ?', [$p['id']]);
        $bilan['relances']++;
    }
    return $bilan;
}
