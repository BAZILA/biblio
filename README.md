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

Après une mise à jour du code, adapter une base existante **sans rien effacer** : `php install/mettre_a_jour.php`
(le script vérifie chaque étape ; on peut le relancer sans risque).

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

- Les prêts se font au **guichet** (admin → Guichet) : choisir le lecteur, l'ouvrage (l'exemplaire est choisi automatiquement, ou scanné) et la date de retour. L'état de l'exemplaire remis est enregistré comme référence.
- Un ouvrage **réservé** se remet avec le bouton « Remettre » : une fenêtre fait vérifier l'état de l'exemplaire, la date de retour et une remarque avant d'enregistrer le prêt.
- Au **retour**, on note le constat : rendu tel quel, usé, abîmé (description obligatoire, réparation possible) ou perdu (circonstances obligatoires, exemplaire retiré). Sinon, si des lecteurs attendent l'ouvrage, l'exemplaire est mis de côté pour le premier de la file.
- Les **lecteurs** créent leur compte sur le site (`index.php/compte`) ; un bibliothécaire le valide. Ils peuvent ensuite réserver depuis la fiche d'un ouvrage et suivre leurs prêts.
- **Ouvrage retiré du fonds** : sur sa fiche, « Désactiver » le retire du site, du prêt et des réservations (réservations en cours annulées, lecteurs prévenus) sans perdre son historique ; « Réactiver » le remet au catalogue. La suppression définitive n'est proposée que pour une fiche jamais prêtée.
- **Prolongation** : le lecteur ne prolonge pas lui-même, il en fait la **demande** depuis son espace. Le personnel accepte (en ajustant la date si besoin) ou refuse avec un motif dans admin → Demandes de prolongation ; le lecteur voit la réponse et la reçoit par e-mail. Le personnel peut aussi prolonger directement un prêt.
- Règles (durée, nombre de prêts par catégorie, prolongations, délai de retrait) : admin → Réglages.
- Un lecteur en retard ne peut ni emprunter ni réserver ; un lecteur ne peut pas demander la prolongation d'un prêt en retard, déjà prolongé au maximum ou attendu par d'autres lecteurs (le personnel peut passer outre).

## Rôles et permissions du personnel

- Chaque compte du personnel reçoit un **rôle** (admin → Configuration → Comptes du personnel). Les rôles se gèrent dans **Rôles et permissions**.
- Une permission s'écrit `rubrique.action` : pour chaque rubrique du menu, *voir*, *ajouter*, *modifier*, *supprimer*, plus des actions particulières (prêter, enregistrer un retour, prolonger malgré les règles, valider un lecteur, réinitialiser un mot de passe, désactiver un ouvrage, configuration SMTP…). Le catalogue complet est dans `app/lib/permissions.php`.
- Le menu n'affiche que les rubriques permises ; sans « modifier », une fiche s'ouvre en **lecture seule** ; chaque action est aussi vérifiée côté serveur (une requête forgée reçoit « Accès refusé »).
- Rôles fournis : **Administrateur** (tous les droits, non modifiable), **Bibliothécaire**, **Agent de prêt**, **Éditeur du site**. « Dupliquer » un rôle est le moyen le plus simple d'en créer un nouveau.
- Garde-fous : on n'accorde que des droits que l'on possède ; on n'attribue qu'un rôle inclus dans ses propres droits ; on ne modifie pas le compte d'une personne plus puissante ; on ne change pas son propre rôle ; il reste toujours un administrateur actif ; un rôle encore attribué ne peut pas être supprimé.
- **Compte Système** (super administrateur) : créé par `php install/compte_systeme.php [email]`, qui écrit ses identifiants dans `install/SYSTEME.txt` (à supprimer après avoir changé le mot de passe ; relancer la commande réinitialise le mot de passe). Il a tous les droits, il est **invisible** des autres comptes (ni dans le personnel, ni dans les rôles, pages introuvables pour eux), et ses actions apparaissent comme faites par « Système ».
- Depuis sa page **Système**, il **masque des permissions** à tous les autres comptes, Administrateur compris : elles disparaissent des menus, des boutons et de la grille des rôles (masquer « Voir » masque toute la rubrique). Les rôles qui les avaient les retrouvent si on les démasque.

## Statistiques et rapports

- Admin → **Statistiques et rapports** : chiffres d'une période (prêts, retours, taux de retour à temps, durée moyenne, lecteurs, réservations, prolongations), situation à ce jour, répartitions par mois, domaine, catégorie de lecteurs, faculté, ouvrages les plus empruntés, état des exemplaires au retour.
- Six rapports **PDF** prêts à imprimer, en noir et blanc (seul le logo UK est en couleur) : rapport statistique, prêts en cours et retards, historique des prêts, inventaire du catalogue, liste des lecteurs, exemplaires abîmés / perdus / retirés. Générés par `app/lib/pdf.php`, sans bibliothèque externe.
- Permissions : « Statistiques et rapports : voir / télécharger les rapports PDF » ; chaque rapport exige aussi de voir la rubrique concernée.

## Déploiement sur un serveur neuf

Voir **DEPLOIEMENT.md** : paquet sans données, `php install/installer.php --url=…` crée la base vide avec le seul compte Système.
