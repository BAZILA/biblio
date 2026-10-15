# Bibliothèque universitaire — Université de Kara

Site public du catalogue, administration sur mesure et module de gestion des prêts.
PHP 8.1+ et MySQL/MariaDB, sans framework ni CMS.

## Organisation

| Dossier | Contenu |
|---|---|
| `index.php` | Site public (mêmes adresses que l'ancien site : `index.php/catalogue`, `index.php/domaines/…`) |
| `admin/` | Administration : `admin/index.php` |
| `app/lib/` | Accès base, catalogue, règles de prêt (`prets.php`), authentification, utilitaires |
| `app/admin/`, `app/site/` | Contrôleurs de l'administration et de l'espace lecteur |
| `views/site/`, `views/admin/` | Gabarits HTML (le site reprend le thème « exeltis » d'origine) |
| `assets/` | CSS, JS, polices du thème et de l'admin |
| `images/` | Couvertures (`images/ouvrages`) et images du site (`images/biblio`) |
| `install/` | Schéma SQL et migration depuis l'ancien Joomla |

`app/`, `views/` et `install/` ne sont pas accessibles depuis le web (`.htaccess`).

## Installation

1. Copier `app/config.example.php` en `app/config.php` et renseigner la base de données.
2. Créer les tables et importer les données :
   - depuis l'ancien site Joomla : `php install/migrer_joomla.php [base_joomla] [prefixe]`
     (par défaut `exeltis` et `trcto_`) ;
   - ou base vide : `mysql -u root biblio < install/schema.sql`, puis créer un compte administrateur.
3. La migration écrit les identifiants du compte administrateur dans `install/IDENTIFIANTS.txt` :
   se connecter sur `admin/`, changer le mot de passe dans « Mon compte », puis supprimer ce fichier.

⚠ Relancer la migration **efface et recrée** toutes les tables de la nouvelle base.

## Imports

Admin → **Importer un fichier** : liste d’auteurs (un nom par ligne) ou catalogue d’ouvrages (CSV « point-virgule » enregistré depuis Excel ; modèle téléchargeable sur la page). Un aperçu ligne par ligne s’affiche avant tout enregistrement ; les doublons sont ignorés, les domaines et types inconnus sont créés.

## E-mails et rappels

- Réglages → **E-mails aux lecteurs** : tant que l’envoi est « désactivé », les messages sont seulement consignés (Admin → E-mails envoyés). Pour envoyer réellement, renseigner un serveur SMTP (celui de l’université, ou un compte Gmail/Outlook avec mot de passe d’application) et faire un envoi de test.
- Messages : confirmation de prêt, rappel avant échéance, relances de retard, réservation à retirer, compte validé, mot de passe provisoire. Un lecteur sans adresse e-mail ne reçoit rien (le guichet le signale).
- Les rappels et relances partent chaque jour au premier passage dans l’admin. Pour un envoi à heure fixe, créer une tâche planifiée Windows (une fois par jour) :
  ```
  schtasks /Create /SC DAILY /ST 07:00 /TN "Bibliotheque - rappels" /TR "C:\xampp\php\php.exe C:\xampp\htdocs\biblio\app\taches.php"
  ```

## Mise en production

- `app/config.php` : `'debug' => false` (les erreurs ne s'affichent plus, elles vont dans le journal PHP).
- Base de données : un compte MariaDB dédié avec mot de passe, limité à SELECT, INSERT, UPDATE, DELETE sur la base `biblio`.
- Déployer sans le dossier `.git` (il est bloqué par `.htaccess`, mais autant ne pas l'envoyer).
- Apache : `AllowOverride All` pour que les `.htaccess` (racine et `images/`) soient appliqués ; activer `mod_rewrite`, `mod_headers` et, si possible, `mod_expires`.
- Dans `php.ini` : `expose_php = Off` ; côté Apache : `ServerTokens Prod`, `ServerSignature Off`.

## Prêts et réservations

- Les prêts se font au **guichet** (admin → Guichet) : choisir le lecteur, scanner ou saisir le code de l'exemplaire (cote + numéro, ex. `INF-001-2`).
- Le **retour** se fait avec le seul code de l'exemplaire. Si des lecteurs attendent l'ouvrage, l'exemplaire est mis de côté pour le premier de la file.
- Les **lecteurs** créent leur compte sur le site (`index.php/compte`) ; un bibliothécaire le valide. Ils peuvent ensuite réserver depuis la fiche d'un ouvrage, suivre et prolonger leurs prêts.
- Règles (durée, nombre de prêts par catégorie, prolongations, délai de retrait) : admin → Réglages.
- Un lecteur en retard ne peut ni emprunter ni réserver ; un prêt en retard ou attendu par d'autres lecteurs ne peut pas être prolongé.
