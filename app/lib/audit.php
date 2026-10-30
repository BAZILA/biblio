<?php
/**
 * Journal d'audit : qui a fait quoi, quand, d'où, et avec quel résultat.
 *
 * - Toute requête de l'administration (pages consultées, actions, exports) et toute action du site public
 *   (connexion, création de compte, réservation, demande de prolongation, message…) est enregistrée
 *   automatiquement en fin de requête, avec le message affiché à l'utilisateur comme résultat.
 * - Les champs envoyés sont conservés (sans mots de passe ni jetons) pour savoir ce qui a été modifié.
 * - Le journal ne se modifie pas et ne se vide pas depuis l'administration.
 * Les actions du compte Système apparaissent sous le nom « Système ».
 */

/** Champs jamais enregistrés dans le journal. */
const AUDIT_CHAMPS_SECRETS = ['_csrf', 'mot_de_passe', 'mot_de_passe2', 'actuel', 'nouveau', 'smtp_mot_de_passe', 'password', 'site_web'];

/** Rubriques de l'administration → libellé. */
const AUDIT_SECTIONS = [
    '' => 'Tableau de bord', 'guichet' => 'Guichet', 'prets' => 'Prêts', 'reservations' => 'Réservations', 'lecteurs' => 'Lecteurs',
    'etudiants' => 'Étudiants de l’université', 'ouvrages' => 'Ouvrages', 'exemplaires' => 'Exemplaires', 'auteurs' => 'Auteurs',
    'domaines' => 'Domaines', 'types' => 'Types de documents', 'slides' => 'Diaporama', 'citations' => 'Citations',
    'actualites' => 'Actualités et évènements', 'personnel' => 'Comptes du personnel', 'blocs' => 'Textes du site', 'messages' => 'Messages',
    'import' => 'Import', 'mails' => 'E-mails', 'reglages' => 'Réglages', 'roles' => 'Rôles et permissions', 'systeme' => 'Système',
    'rapports' => 'Statistiques et rapports', 'mon-compte' => 'Mon compte', 'audit' => 'Journal d’audit', 'connexion' => 'Connexion',
    'deconnexion' => 'Déconnexion',
];

/** Actions → verbe affiché. */
const AUDIT_ACTIONS = [
    '' => 'consulter la liste', 'voir' => 'consulter', 'nouveau' => 'créer', 'modifier' => 'modifier', 'supprimer' => 'supprimer',
    'activer' => 'activer', 'desactiver' => 'désactiver', 'reactiver' => 'réactiver', 'retirer' => 'retirer', 'remettre' => 'remettre',
    'ajouter' => 'ajouter', 'pret' => 'enregistrer un prêt', 'retour' => 'enregistrer un retour', 'rendre' => 'enregistrer un retour',
    'prolonger' => 'prolonger', 'demande' => 'traiter une demande', 'demandes' => 'consulter les demandes', 'annuler' => 'annuler',
    'rapide' => 'créer au guichet', 'mot-de-passe' => 'réinitialiser le mot de passe', 'analyser' => 'analyser un fichier',
    'apercu' => 'aperçu', 'confirmer' => 'confirmer l’import', 'modele' => 'télécharger le modèle', 'exporter' => 'exporter',
    'pdf' => 'télécharger un PDF', 'activation' => 'activer / désactiver', 'etudiant' => 'choisir un étudiant de la liste',
    'dupliquer' => 'dupliquer', 'taches' => 'lancer les rappels', 'test' => 'envoyer un test', 'non-lu' => 'marquer non lu',
];

/** Ligne explicite déjà écrite pour cette requête ; remarques à ajouter à la ligne automatique (connexion refusée…). */
$GLOBALS['audit_fait'] = false;
$GLOBALS['audit_notes'] = [];

/** Ajoute une remarque (et éventuellement une catégorie) à la ligne de journal de la requête en cours. */
function audit_noter(string $statut, string $message, ?string $categorie = null): void
{
    $GLOBALS['audit_notes'][] = ['type' => $statut, 'message' => $message, 'categorie' => $categorie];
}

/** Nom affiché d'un utilisateur dans le journal. */
function audit_acteur(?array $u): string
{
    if (!$u) {
        return 'Visiteur';
    }
    if ($u['role'] === 'personnel' && $u['role_id'] && val('SELECT systeme FROM roles WHERE id = ?', [$u['role_id']])) {
        return 'Système';
    }
    return trim($u['prenom'] . ' ' . $u['nom']) . ($u['role'] === 'lecteur' ? ' (lecteur)' : '');
}

/** Champs envoyés, sans secrets, tronqués. */
function audit_champs(array $post): array
{
    $propres = [];
    foreach ($post as $k => $v) {
        if (in_array($k, AUDIT_CHAMPS_SECRETS, true) || str_contains((string) $k, 'mot_de_passe')) {
            continue;
        }
        $propres[$k] = is_array($v) ? array_slice(array_map(fn($x) => is_scalar($x) ? mb_substr(chaine_requete(['v' => (string) $x], 'v', '', false), 0, 80) : '…', $v), 0, 50)
            : mb_substr(chaine_requete(['v' => (string) $v], 'v', '', false), 0, 300); // même lecture que post_str() (formulaires envoyés en Windows-1252)
    }
    foreach ($_FILES as $k => $f) {
        if (is_array($f) && ($f['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            $propres[$k] = 'fichier « ' . mb_substr((string) ($f['name'] ?? ''), 0, 120) . ' » (' . round(($f['size'] ?? 0) / 1024) . ' Ko)';
        }
    }
    return $propres;
}

/**
 * Enregistre une ligne du journal. Ne lève jamais d'erreur : l'audit ne doit pas bloquer l'action.
 * $categorie : connexion, action, consultation, export, securite.
 */
function audit(string $categorie, string $action, string $resultat = '', string $statut = 'info', array $details = [], ?array $u = null, ?string $espace = null): void
{
    try {
        $u ??= user();
        insert('audit', [
            'utilisateur_id' => $u['id'] ?? null,
            'acteur' => mb_substr(audit_acteur($u), 0, 190),
            'espace' => $espace ?? (defined('ESPACE_ADMIN') ? 'admin' : 'site'),
            'categorie' => $categorie,
            'action' => mb_substr($action, 0, 190),
            'methode' => $_SERVER['REQUEST_METHOD'] ?? 'CLI',
            'chemin' => mb_substr(PHP_SAPI === 'cli' ? implode(' ', $_SERVER['argv'] ?? []) : (string) ($_SERVER['REQUEST_URI'] ?? ''), 0, 255),
            'resultat' => mb_substr($resultat, 0, 500),
            'statut' => in_array($statut, ['succes', 'erreur', 'info'], true) ? $statut : 'info',
            'details' => $details ? mb_substr(json_encode($details, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE), 0, 6000) : null,
            'ip' => mb_substr((string) ($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45),
            'navigateur' => mb_substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255),
        ]);
        $GLOBALS['audit_fait'] = true;
    } catch (Throwable) {
        // le journal ne doit jamais empêcher une action
    }
}

/** Libellé lisible d'une adresse de l'administration (« Prêts : prolonger »). */
function audit_libelle_admin(array $segments, string $methode): string
{
    $section = $segments[0] ?? '';
    $action = $segments[1] ?? '';
    if ($section === 'connexion') {
        return $methode === 'POST' ? 'Connexion à l’administration' : 'Page de connexion de l’administration';
    }
    if ($section === 'deconnexion') {
        return 'Déconnexion de l’administration';
    }
    $libelle = AUDIT_SECTIONS[$section] ?? ucfirst($section);
    if ($action === '' && $methode === 'GET') {
        return $libelle . ($section === '' ? '' : ' : consulter la liste');
    }
    $verbe = AUDIT_ACTIONS[$action] ?? str_replace('-', ' ', $action);
    if ($action === 'modifier' && $methode === 'GET') {
        $verbe = 'ouvrir la fiche';
    } elseif ($action === 'nouveau' && $methode === 'GET') {
        $verbe = 'ouvrir le formulaire de création';
    }
    return $libelle . ($verbe !== '' ? ' : ' . $verbe : '') . (isset($segments[2]) && ctype_digit($segments[2]) ? ' n° ' . $segments[2] : '');
}

/**
 * Journalisation automatique de la requête en cours, faite à la fin (après l'action et ses messages).
 * Administration : tout (pages consultées et actions). Site public : les actions (POST) seulement.
 * $libelle : fonction qui donne le libellé de la requête ; $ignorer : requêtes techniques à ne pas journaliser.
 */
function audit_requete(callable $libelle, bool $journaliserConsultations, bool $ignorer = false): void
{
    if ($ignorer || PHP_SAPI === 'cli') {
        return;
    }
    $methode = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    if ($methode !== 'POST' && !$journaliserConsultations) {
        return;
    }
    $avant = user(); // acteur au début (utile pour une déconnexion)
    $nbFlashes = count($_SESSION['flash'] ?? []);
    $post = $methode === 'POST' ? audit_champs($_POST) : [];
    register_shutdown_function(function () use ($libelle, $methode, $avant, $nbFlashes, $post) {
        if ($GLOBALS['audit_fait']) {
            return; // déjà journalisée explicitement (connexion…)
        }
        $nouveaux = array_merge($GLOBALS['audit_notes'], array_slice($_SESSION['flash'] ?? [], $nbFlashes));
        $categorie = null;
        foreach ($GLOBALS['audit_notes'] as $n) {
            $categorie = $n['categorie'] ?? $categorie;
        }
        $messages = array_map(fn($f) => strip_tags($f['message']), $nouveaux);
        $statut = 'info';
        foreach ($nouveaux as $f) {
            $statut = $f['type'] === 'erreur' ? 'erreur' : ($statut === 'erreur' ? 'erreur' : ($f['type'] === 'succes' ? 'succes' : $statut));
        }
        $code = http_response_code();
        if ($code >= 400) {
            $statut = 'erreur';
            $messages[] = $code === 403 || $code === 404 ? 'Accès refusé ou page introuvable (' . $code . ')' : 'Erreur ' . $code;
        }
        $u = $avant;
        if (!$u && !empty($_SESSION['user_id'])) {
            $u = row('SELECT * FROM utilisateurs WHERE id = ?', [$_SESSION['user_id']]);
        }
        $estExport = $methode === 'GET' && (str_contains((string) ($_SERVER['REQUEST_URI'] ?? ''), '/pdf') || str_contains((string) ($_SERVER['REQUEST_URI'] ?? ''), '/modele')
            || str_contains((string) ($_SERVER['REQUEST_URI'] ?? ''), '/exporter'));
        audit($categorie ?? ($estExport ? 'export' : ($methode === 'POST' ? 'action' : 'consultation')), $libelle(), implode(' ', $messages), $statut, $post, $u);
    });
}

/** Navigateur et système en clair (« Chrome · Windows »), pour la lecture du journal. */
function navigateur_court(string $ua): string
{
    if ($ua === '') {
        return '';
    }
    $nav = match (true) {
        str_contains($ua, 'Edg/') => 'Edge', str_contains($ua, 'OPR/') => 'Opera', str_contains($ua, 'Firefox/') => 'Firefox',
        str_contains($ua, 'Chrome/') => 'Chrome', str_contains($ua, 'Safari/') => 'Safari', str_contains($ua, 'curl/') => 'curl', default => 'Autre',
    };
    $os = match (true) {
        str_contains($ua, 'Android') => 'Android', str_contains($ua, 'iPhone') || str_contains($ua, 'iPad') => 'iOS',
        str_contains($ua, 'Windows') => 'Windows', str_contains($ua, 'Mac OS') => 'macOS', str_contains($ua, 'Linux') => 'Linux', default => '',
    };
    return $nav . ($os !== '' ? " · $os" : '');
}
