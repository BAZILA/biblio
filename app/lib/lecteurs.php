<?php
/**
 * Fiche lecteur : lecture du formulaire et règles communes à l'inscription en ligne,
 * à la création dans l'admin et à la création rapide au guichet.
 *
 * Obligatoires : prénom, nom, téléphone, catégorie ; matricule (sauf lecteur externe) ;
 * faculté et département pour un étudiant ; e-mail seulement si le lecteur a un accès en ligne.
 */

const CHAMPS_LECTEUR = ['prenom', 'nom', 'email', 'telephone', 'categorie', 'matricule', 'faculte', 'departement'];

/** Lit les champs lecteur envoyés en POST par-dessus les valeurs existantes. */
function lecteur_depuis_post(array $base): array
{
    foreach (CHAMPS_LECTEUR as $k) {
        $base[$k] = post_str($k, '');
    }
    $base['email'] = mb_strtolower($base['email']);
    if ($base['categorie'] !== 'etudiant') {
        $base['faculte'] = $base['departement'] = '';
    }
    return $base;
}

/** Contrôle une fiche lecteur ; renvoie les erreurs par champ. */
function valider_lecteur(array $l, bool $accesEnLigne): array
{
    $erreurs = [];
    foreach (['prenom' => 'Prénom', 'nom' => 'Nom', 'telephone' => 'Téléphone'] as $k => $lib) {
        if ($l[$k] === '') {
            $erreurs[$k] = 'Champ requis.';
        }
    }
    if (!isset(LIBELLES_CATEGORIE[$l['categorie']])) {
        $erreurs['categorie'] = 'Choisissez une catégorie.';
    }
    if ($l['categorie'] !== 'externe' && $l['matricule'] === '') {
        $erreurs['matricule'] = 'Champ requis (sauf pour un lecteur externe).';
    } elseif ($l['matricule'] !== '' && val('SELECT 1 FROM utilisateurs WHERE matricule = ? AND id <> ?', [$l['matricule'], (int) ($l['id'] ?? 0)])) {
        $erreurs['matricule'] = 'Matricule déjà attribué à un autre lecteur.';
    }
    if ($l['categorie'] === 'etudiant') {
        foreach (['faculte' => 'Faculté', 'departement' => 'Département'] as $k => $lib) {
            if ($l[$k] === '') {
                $erreurs[$k] = 'Champ requis pour un étudiant.';
            }
        }
    }
    if ($accesEnLigne && $l['email'] === '') {
        $erreurs['email'] = 'Requis pour l’accès en ligne et les e-mails de rappel.';
    }
    if ($l['email'] !== '') {
        if (!filter_var($l['email'], FILTER_VALIDATE_EMAIL)) {
            $erreurs['email'] = 'Adresse e-mail invalide.';
        } elseif (val('SELECT 1 FROM utilisateurs WHERE email = ? AND id <> ?', [$l['email'], (int) ($l['id'] ?? 0)])) {
            $erreurs['email'] = 'Un autre compte utilise déjà cette adresse.';
        }
    }
    return $erreurs;
}

/** Colonnes à enregistrer pour une fiche lecteur validée. */
function lecteur_donnees(array $l): array
{
    return [
        'prenom' => $l['prenom'], 'nom' => $l['nom'], 'telephone' => $l['telephone'], 'categorie' => $l['categorie'],
        'email' => $l['email'] !== '' ? $l['email'] : null,
        'matricule' => $l['matricule'] !== '' ? $l['matricule'] : null,
        'faculte' => $l['faculte'], 'departement' => $l['departement'],
    ];
}

/** Mot de passe provisoire lisible (sans caractères ambigus). */
function mot_de_passe_provisoire(): string
{
    $alphabet = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789';
    $mdp = '';
    for ($i = 0; $i < 10; $i++) {
        $mdp .= $alphabet[random_int(0, strlen($alphabet) - 1)];
    }
    return $mdp;
}

/** Valeurs déjà saisies (facultés, départements) pour les suggestions des formulaires. */
function valeurs_existantes(string $colonne): array
{
    if (!in_array($colonne, ['faculte', 'departement'], true)) {
        return [];
    }
    return array_column(rows("SELECT DISTINCT $colonne AS v FROM utilisateurs WHERE $colonne <> '' ORDER BY $colonne"), 'v');
}
