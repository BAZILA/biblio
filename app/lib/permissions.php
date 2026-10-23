<?php
/**
 * Rôles et permissions du personnel.
 *
 * Une permission s'écrit « module.action » (ex. citations.modifier). Le catalogue ci-dessous décrit
 * tous les modules de l'administration, regroupés comme le menu, avec leurs actions possibles.
 * Un rôle est une liste de permissions ; le rôle « tous les droits » (Administrateur) les a toutes,
 * y compris celles ajoutées plus tard au catalogue.
 */

/** Libellés des actions courantes (colonnes de la grille). */
const ACTIONS_STANDARD = ['voir' => 'Voir', 'ajouter' => 'Ajouter', 'modifier' => 'Modifier', 'supprimer' => 'Supprimer'];

/**
 * [groupe du menu => [module => [libellé, [action => libellé], aide]]]
 * Les actions de ACTIONS_STANDARD prennent leur libellé de colonne ; les autres sont des actions particulières.
 */
const PERMISSIONS = [
    'Bibliothèque' => [
        'tableau' => ['Tableau de bord', ['voir' => 'Voir'], 'Page d’accueil de l’administration.'],
        'guichet' => ['Guichet prêt / retour', ['voir' => 'Voir'], 'Ouvrir le guichet ; prêter et rendre dépendent des droits « Prêts ».'],
        'prets' => ['Prêts', [
            'voir' => 'Voir',
            'ajouter' => 'Ajouter',
            'retour' => 'Enregistrer un retour (avec constat)',
            'prolonger' => 'Prolonger un prêt',
            'forcer' => 'Prolonger malgré les règles (maximum, retard, file d’attente)',
        ], '« Ajouter » = bouton « Enregistrer le prêt » du guichet (et choix d’un étudiant de la liste sans compte). « Enregistrer un retour » = boutons Retour / Rendu.'],
        'demandes' => ['Demandes de prolongation', ['voir' => 'Voir', 'traiter' => 'Accepter ou refuser une demande'], ''],
        'reservations' => ['Réservations', ['voir' => 'Voir', 'remettre' => 'Remettre un ouvrage réservé (enregistre le prêt)', 'annuler' => 'Annuler une réservation'], '« Annuler » = bouton rouge « Annuler » des réservations.'],
        'lecteurs' => ['Lecteurs', [
            'voir' => 'Voir', 'ajouter' => 'Ajouter', 'modifier' => 'Modifier', 'supprimer' => 'Supprimer',
            'valider' => 'Valider, désactiver ou réactiver un compte',
            'mot_de_passe' => 'Réinitialiser le mot de passe',
        ], '« Ajouter » couvre aussi la création rapide au guichet. « Supprimer » = bouton « Supprimer ce lecteur » (lecteur sans aucun prêt). « Valider… » = boutons Valider, Activer, Désactiver, Réactiver.'],
        'etudiants' => ['Étudiants de l’université', ['voir' => 'Voir', 'activer' => 'Désactiver ou réactiver des étudiants de la liste'],
            'Liste officielle chargée par import (droit « Importer un fichier ») : inscription en ligne des étudiants et prêt au guichet sans compte. « Désactiver » = cases à cocher et boutons Désactiver / Réactiver ; les comptes déjà créés ne changent pas.'],
        'rapports' => ['Statistiques et rapports', ['voir' => 'Voir', 'exporter' => 'Télécharger les rapports PDF'],
            'Chaque rapport exige aussi de voir la rubrique concernée (prêts, ouvrages ou lecteurs).'],
    ],
    'Catalogue' => [
        'ouvrages' => ['Ouvrages', [
            'voir' => 'Voir', 'ajouter' => 'Ajouter', 'modifier' => 'Modifier', 'supprimer' => 'Supprimer',
            'desactiver' => 'Désactiver ou réactiver un ouvrage',
        ], '« Désactiver » = carte « Désactiver l’ouvrage » de la fiche (retiré du site et du prêt, historique gardé). « Supprimer » = lien « supprimer définitivement », seulement pour une fiche jamais prêtée.'],
        'exemplaires' => ['Exemplaires', [
            'ajouter' => 'Ajouter', 'modifier' => 'Modifier', 'supprimer' => 'Supprimer',
            'retirer' => 'Retirer de la circulation ou y remettre',
        ], 'Sur la fiche d’un ouvrage (nécessite « Ouvrages : voir ») : « Modifier » = bouton ✓ (état, notes), « Retirer » = boutons Retirer / Remettre, « Supprimer » = corbeille rouge (exemplaire jamais prêté).'],
        'auteurs' => ['Auteurs', ACTIONS_STANDARD, '« Supprimer » = bouton rouge en bas de la fiche (auteur lié à aucun ouvrage).'],
        'domaines' => ['Domaines', ACTIONS_STANDARD, '« Supprimer » = bouton rouge en bas de la fiche (domaine sans ouvrage).'],
        'types' => ['Types de documents', ACTIONS_STANDARD, '« Supprimer » = bouton rouge en bas de la fiche (ses ouvrages passent « sans type »).'],
        'import' => ['Importer un fichier', [
            'auteurs' => 'Importer des auteurs',
            'ouvrages' => 'Importer des ouvrages (crée aussi domaines et types)',
            'etudiants' => 'Charger ou mettre à jour la liste des étudiants',
        ], ''],
    ],
    'Site' => [
        'slides' => ['Diaporama d’accueil', ACTIONS_STANDARD, '« Modifier » couvre aussi publier / masquer ; « Supprimer » = bouton rouge en bas de la fiche.'],
        'citations' => ['Citations', ACTIONS_STANDARD, '« Modifier » couvre aussi publier / masquer ; « Supprimer » = bouton rouge en bas de la fiche.'],
        'blocs' => ['Textes du site', ['voir' => 'Voir', 'modifier' => 'Modifier'], 'Le HTML y est enregistré tel quel : à réserver aux personnes de confiance.'],
        'messages' => ['Messages reçus', ['voir' => 'Voir', 'supprimer' => 'Supprimer'], '« Supprimer » = corbeille de la liste et bouton de la fiche message.'],
    ],
    'Configuration' => [
        'reglages' => ['Réglages', [
            'voir' => 'Voir', 'modifier' => 'Modifier',
            'mails' => 'Modifier la configuration des e-mails (serveur SMTP, mot de passe)',
        ], '« Modifier » couvre le site, la page contact et les règles de prêt.'],
        'mails' => ['E-mails envoyés', ['voir' => 'Voir', 'envoyer' => 'Envoyer un test ou lancer les rappels'], ''],
        'personnel' => ['Comptes du personnel', ['voir' => 'Voir', 'ajouter' => 'Ajouter', 'modifier' => 'Modifier', 'desactiver' => 'Désactiver ou réactiver un compte'],
            'On ne peut attribuer qu’un rôle dont on possède soi-même tous les droits. « Désactiver » = case « Compte actif » de la fiche.'],
        'roles' => ['Rôles et permissions', ACTIONS_STANDARD, 'On ne peut accorder que des droits que l’on possède soi-même. « Supprimer » = bouton rouge de la fiche (rôle sans compte).'],
    ],
];

/** Liste plate de toutes les permissions existantes. */
function toutes_permissions(): array
{
    $liste = [];
    foreach (PERMISSIONS as $modules) {
        foreach ($modules as $module => [, $actions]) {
            foreach (array_keys($actions) as $action) {
                $liste[] = "$module.$action";
            }
        }
    }
    return $liste;
}

/** Libellé lisible d'une permission (« Citations : Modifier »). */
function libelle_permission(string $perm): string
{
    [$module, $action] = explode('.', $perm, 2) + [1 => ''];
    foreach (PERMISSIONS as $modules) {
        if (isset($modules[$module])) {
            return $modules[$module][0] . ' : ' . mb_strtolower($modules[$module][1][$action] ?? $action);
        }
    }
    return $perm;
}

/**
 * Permissions masquées par le compte Système : elles disparaissent des grilles, des menus et des droits
 * de tous les rôles (Administrateur compris). Seul le compte Système les conserve.
 */
function permissions_masquees(): array
{
    $liste = array_filter(array_map('trim', explode(',', reglage('permissions_masquees'))));
    return array_values(array_intersect($liste, toutes_permissions()));
}

/** Permissions utilisables par les rôles : le catalogue moins les permissions masquées. */
function permissions_visibles(): array
{
    return array_values(array_diff(toutes_permissions(), permissions_masquees()));
}

/**
 * Rôle de l'utilisateur connecté (ou de $u) avec ses permissions effectives, mis en cache pour la requête.
 * Système : tout ; Administrateur : toutes les permissions visibles ; autres rôles : les leurs, hors masquées.
 */
function role_de(?array $u = null): ?array
{
    static $cache = [];
    $u ??= user();
    if (!$u || $u['role'] !== 'personnel' || !$u['role_id']) {
        return null;
    }
    $id = (int) $u['role_id'];
    if (!array_key_exists($id, $cache)) {
        $role = row('SELECT * FROM roles WHERE id = ?', [$id]);
        if ($role) {
            $role['permissions'] = match (true) {
                (bool) $role['systeme']     => toutes_permissions(),
                (bool) $role['tous_droits'] => permissions_visibles(),
                default => array_values(array_intersect(permissions_du_role($id), permissions_visibles())),
            };
        }
        $cache[$id] = $role;
    }
    return $cache[$id];
}

/**
 * L'utilisateur connecté a-t-il cette permission ? Accepte plusieurs permissions : vrai si l'une d'elles est accordée.
 * Le compte Système a tous les droits, y compris ceux qui n'existent que pour lui (systeme.voir).
 */
function peut(string ...$perms): bool
{
    $role = role_de();
    if (!$role) {
        return false;
    }
    if ($role['systeme']) {
        return true;
    }
    return (bool) array_intersect($perms, $role['permissions']);
}

/** Rôle Administrateur ou Système (toutes les permissions visibles) ? */
function tous_droits(?array $u = null): bool
{
    return (bool) (role_de($u)['tous_droits'] ?? false);
}

/** Compte Système : super administrateur lié au système, invisible des autres comptes. */
function is_systeme(?array $u = null): bool
{
    return (bool) (role_de($u)['systeme'] ?? false);
}

/**
 * Nom à afficher pour l'auteur d'une action (prêt enregistré, demande traitée…) :
 * ce que fait le compte Système apparaît comme fait par « Système ».
 */
function nom_acteur(?int $id): string
{
    static $cache = [];
    if (!$id) {
        return '';
    }
    if (!isset($cache[$id])) {
        $u = row('SELECT u.prenom, u.nom, r.systeme FROM utilisateurs u LEFT JOIN roles r ON r.id = u.role_id WHERE u.id = ?', [$id]);
        $cache[$id] = !$u ? '' : ($u['systeme'] ? 'Système' : trim($u['prenom'] . ' ' . $u['nom']));
    }
    return $cache[$id];
}

/** Refuse l'accès (page 403) si l'utilisateur n'a aucune des permissions données. */
function exiger(string ...$perms): void
{
    if (peut(...$perms)) {
        return;
    }
    // Une permission masquée par le Système n'existe pas pour ce compte : page introuvable, sans rien révéler
    if (!array_diff($perms, permissions_masquees()) && function_exists('admin_introuvable')) {
        admin_introuvable('Page');
    }
    http_response_code(403);
    $libelles = implode(' ou ', array_map(fn($p) => '« ' . libelle_permission($p) . ' »', $perms));
    if (function_exists('admin_page')) {
        admin_page('message', ['message' => "Votre rôle ne permet pas cette action ($libelles). Demandez à un administrateur de compléter vos droits."], 'Accès refusé', '');
    }
    exit('Accès refusé.');
}

/** Permissions d'un rôle (identifiant), pour la grille d'édition. */
function permissions_du_role(int $roleId): array
{
    return array_column(rows('SELECT permission FROM role_permissions WHERE role_id = ?', [$roleId]), 'permission');
}

/**
 * Normalise une liste de permissions cochées : ne garde que celles du catalogue,
 * et ajoute « voir » d'un module dès qu'une autre de ses actions est accordée (on ne modifie pas ce qu'on ne voit pas).
 */
function normaliser_permissions(array $cochees): array
{
    $valides = permissions_visibles();
    $perms = array_values(array_intersect(array_unique(array_map('strval', $cochees)), $valides));
    foreach ($perms as $p) {
        [$module] = explode('.', $p, 2);
        if (in_array("$module.voir", $valides, true) && !in_array("$module.voir", $perms, true)) {
            $perms[] = "$module.voir";
        }
        if ($module === 'exemplaires' && !in_array('ouvrages.voir', $perms, true)) {
            $perms[] = 'ouvrages.voir';
        }
    }
    sort($perms);
    return $perms;
}

/** Première page de l'administration accessible au rôle (si le tableau de bord ne l'est pas). */
function premiere_page_autorisee(): ?string
{
    $ordre = ['tableau' => '', 'guichet' => 'guichet', 'prets' => 'prets', 'demandes' => 'prets/demandes', 'reservations' => 'reservations',
        'lecteurs' => 'lecteurs', 'etudiants' => 'etudiants', 'rapports' => 'rapports', 'ouvrages' => 'ouvrages', 'auteurs' => 'auteurs', 'domaines' => 'domaines', 'types' => 'types',
        'slides' => 'slides', 'citations' => 'citations', 'blocs' => 'blocs', 'messages' => 'messages', 'reglages' => 'reglages',
        'mails' => 'mails', 'personnel' => 'personnel', 'roles' => 'roles'];
    foreach ($ordre as $module => $chemin) {
        if (peut("$module.voir")) {
            return $chemin;
        }
    }
    if (peut('import.auteurs', 'import.ouvrages', 'import.etudiants')) {
        return 'import';
    }
    return null;
}
