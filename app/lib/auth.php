<?php
/**
 * Authentification : personnel et lecteurs partagent la table utilisateurs.
 * Les droits du personnel dépendent de son rôle (voir permissions.php).
 */

const ROLES_PERSONNEL = ['personnel'];

function user(): ?array
{
    static $user = false;
    if ($user === false) {
        $id = $_SESSION['user_id'] ?? null;
        $user = $id ? row('SELECT * FROM utilisateurs WHERE id = ? AND actif = 1', [$id]) : null;
        if ($id && !$user) {
            unset($_SESSION['user_id']);
        }
    }
    return $user;
}

function is_personnel(): bool
{
    $u = user();
    return $u !== null && in_array($u['role'], ROLES_PERSONNEL, true);
}

/** Rôle « tous les droits » (Administrateur). */
function is_admin(): bool
{
    return is_personnel() && tous_droits();
}

/** Échecs tolérés sur 15 minutes avant blocage : par adresse e-mail, et par adresse IP. */
const ECHECS_MAX_EMAIL = 5;
const ECHECS_MAX_IP = 20;

/**
 * Vérifie email + mot de passe. Renvoie l'utilisateur ou un message d'erreur.
 * Les échecs sont comptés en base (par e-mail et par IP) : vider ses cookies ne contourne pas le blocage.
 */
function tenter_connexion(string $email, string $motDePasse): array|string
{
    $email = mb_strtolower(trim($email));
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'cli';
    q('DELETE FROM tentatives_connexion WHERE le < NOW() - INTERVAL 1 DAY');
    $parEmail = (int) val('SELECT COUNT(*) FROM tentatives_connexion WHERE email = ? AND le > NOW() - INTERVAL 15 MINUTE', [$email]);
    $parIp = (int) val('SELECT COUNT(*) FROM tentatives_connexion WHERE ip = ? AND le > NOW() - INTERVAL 15 MINUTE', [$ip]);
    if ($parEmail >= ECHECS_MAX_EMAIL || $parIp >= ECHECS_MAX_IP) {
        return 'Trop de tentatives. Réessayez dans un quart d’heure.';
    }

    $u = row('SELECT * FROM utilisateurs WHERE email = ?', [$email]);
    if (!$u || $u['mot_de_passe'] === '' || !password_verify($motDePasse, $u['mot_de_passe'])) {
        insert('tentatives_connexion', ['email' => mb_substr($email, 0, 190), 'ip' => mb_substr($ip, 0, 45)]);
        return 'E-mail ou mot de passe incorrect.';
    }
    if (!$u['actif']) {
        return $u['en_attente']
            ? 'Ce compte n’est pas encore activé par la bibliothèque.'
            : 'Ce compte a été désactivé : contactez la bibliothèque.';
    }

    q('DELETE FROM tentatives_connexion WHERE email = ?', [$email]);
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $u['id'];
    q('UPDATE utilisateurs SET derniere_connexion = NOW() WHERE id = ?', [$u['id']]);
    if (password_needs_rehash($u['mot_de_passe'], PASSWORD_DEFAULT)) {
        q('UPDATE utilisateurs SET mot_de_passe = ? WHERE id = ?', [password_hash($motDePasse, PASSWORD_DEFAULT), $u['id']]);
    }
    return $u;
}

function deconnexion(): void
{
    $_SESSION = [];
    session_regenerate_id(true);
}

function nom_complet(array $u): string
{
    return trim($u['prenom'] . ' ' . $u['nom']);
}

/** Exige un compte du personnel, sinon renvoie vers la connexion admin. */
function exiger_personnel(): array
{
    if (!is_personnel()) {
        redirect(admin_url('connexion', ['retour' => $_SERVER['REQUEST_URI'] ?? '']));
    }
    return user();
}

function exiger_admin(): array
{
    $u = exiger_personnel();
    if (!is_admin()) {
        http_response_code(403);
        if (function_exists('admin_page')) {
            admin_page('message', ['message' => 'Cette page est réservée aux administrateurs.'], 'Accès refusé', '');
        }
        exit('Accès réservé aux administrateurs.');
    }
    return $u;
}

/** Exige un lecteur connecté côté site public. */
function exiger_lecteur(): array
{
    $u = user();
    if (!$u) {
        flash('info', 'Connectez-vous pour accéder à votre espace lecteur.');
        redirect(url('compte/connexion', ['retour' => $_SERVER['REQUEST_URI'] ?? '']));
    }
    return $u;
}

/**
 * N'accepte comme URL de retour qu'un chemin interne au site (évite les redirections ouvertes) :
 * un seul « / » initial, aucun antislash ni caractère de contrôle (« /\site.com » serait lu « //site.com »),
 * et sous la racine du site.
 */
function retour_sur(string $retour, string $defaut): string
{
    $sain = preg_match('#^/(?![/\\\\])[^\\\\\x00-\x1f\x7f]*$#', $retour) === 1
        && !preg_match('#(^|/)\.\.?(/|\?|$)#', $retour)
        && str_starts_with($retour, base_url() . '/');
    return $sain ? $retour : $defaut;
}
