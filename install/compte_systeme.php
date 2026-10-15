<?php
/**
 * Crée le compte Système (super administrateur invisible), ou réinitialise son mot de passe s'il existe.
 *   php install/compte_systeme.php [adresse-email]
 * Les identifiants sont écrits dans install/SYSTEME.txt (dossier inaccessible depuis le web) :
 * lisez-les, connectez-vous, changez le mot de passe dans « Mon compte », puis supprimez le fichier.
 */
if (PHP_SAPI !== 'cli') {
    exit('À lancer en ligne de commande.');
}
require __DIR__ . '/../app/bootstrap.php';

$roleId = (int) val('SELECT id FROM roles WHERE systeme = 1 ORDER BY id LIMIT 1');
if (!$roleId) {
    exit("Rôle Système absent : lancez d'abord php install/mettre_a_jour.php\n");
}
$email = mb_strtolower(trim($argv[1] ?? ''));
if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    exit("Adresse e-mail invalide.\n");
}
$motDePasse = rtrim(strtr(base64_encode(random_bytes(15)), '+/', 'Kx'), '=');
$compte = row("SELECT * FROM utilisateurs WHERE role = 'personnel' AND role_id = ? ORDER BY id LIMIT 1", [$roleId]);

if ($compte) {
    if ($email !== '' && val('SELECT 1 FROM utilisateurs WHERE email = ? AND id <> ?', [$email, $compte['id']])) {
        exit("Cette adresse est déjà utilisée par un autre compte.\n");
    }
    $email = $email ?: $compte['email'];
    q('UPDATE utilisateurs SET email = ?, mot_de_passe = ?, actif = 1 WHERE id = ?', [$email, password_hash($motDePasse, PASSWORD_DEFAULT), $compte['id']]);
    q('DELETE FROM tentatives_connexion WHERE email = ?', [$email]);
    echo "Compte Système existant : mot de passe réinitialisé.\n";
} else {
    $email = $email ?: 'systeme@biblio.local';
    if (val('SELECT 1 FROM utilisateurs WHERE email = ?', [$email])) {
        exit("Cette adresse est déjà utilisée par un autre compte : indiquez-en une autre.\n");
    }
    insert('utilisateurs', ['role' => 'personnel', 'role_id' => $roleId, 'nom' => 'Système', 'prenom' => '', 'email' => $email,
        'mot_de_passe' => password_hash($motDePasse, PASSWORD_DEFAULT), 'actif' => 1]);
    echo "Compte Système créé.\n";
}

file_put_contents(__DIR__ . '/SYSTEME.txt', "Compte Système (super administrateur) — " . date('d/m/Y H:i') . "\n"
    . "Adresse : " . base_url() . "/admin/\nE-mail : $email\nMot de passe : $motDePasse\n\n"
    . "Connectez-vous, changez ce mot de passe dans « Mon compte », puis supprimez ce fichier.\n");
echo "Identifiants écrits dans install/SYSTEME.txt\n";
