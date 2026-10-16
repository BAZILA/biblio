# Déploiement sur o2switch (cPanel, sans terminal)

Exemple : domaine `bibliotheque.univkara.tg`, dossier `/home/alpha/bibliotheque.univkara.tg`, compte cPanel `alpha`.

## 1. Version de PHP du site (8.3)

Le fichier `.htaccess` fourni commence par :

```apache
AddHandler application/x-httpd-alt-php83___lsphp .php
```

Cette ligne fait tourner **ce site seulement** en PHP 8.3, sans toucher aux autres sites du compte
(méthode LiteSpeed décrite par o2switch : https://faq.o2switch.fr/guides/php/changer-version-php-et-php-ini/).

Si vous préférez régler la version pour tout le compte : cPanel → **Sélectionner une version de PHP** → 8.3 →
« Définir comme actuel », puis vous pouvez supprimer cette ligne.

**Extensions** (cPanel → Sélectionner une version de PHP → onglet Extensions, version 8.3) : `pdo_mysql`, `mysqlnd`,
`mbstring`, `gd`, `fileinfo`, `openssl`, `iconv`, `intl`, `zip`. La page d'installation vérifie leur présence.

**Réglages** (même écran, onglet Options) : `upload_max_filesize = 10M`, `post_max_size = 12M`, `memory_limit = 256M`,
`expose_php = Off`, `date.timezone = Africa/Lome`. Avec la ligne `___lsphp` ci-dessus, un fichier `php.ini` ou `.user.ini`
dans le dossier du site est ignoré : passez par cet écran.

## 2. Base de données

cPanel → **Bases de données MySQL** (ou « Assistant Base de données MySQL ») :
1. créez la base (ex. `alpha_biblio`) ;
2. créez l'utilisateur (ex. `alpha_biblio`) avec un mot de passe solide ;
3. ajoutez l'utilisateur à la base avec **tous les privilèges**.

## 3. Fichiers

cPanel → **Gestionnaire de fichiers** → dossier `bibliotheque.univkara.tg` :
1. videz le dossier (ancienne tentative) ;
2. envoyez le zip, clic droit → **Extraire** directement dans ce dossier : on doit y voir `index.php`, `installation.php`,
   `admin/`, `app/`… et le fichier caché `.htaccess` (Paramètres → « Afficher les fichiers cachés ») ;
3. droits : dossiers en **755**, fichiers en **644** (suPHP refuse 775 et 777).

## 4. Configuration

Dans le gestionnaire, copiez `app/config.example.php` en `app/config.php`, puis « Modifier » :

```php
'db' => [
    'host'     => 'localhost',
    'name'     => 'alpha_biblio',
    'user'     => 'alpha_biblio',
    'password' => 'le mot de passe de l’utilisateur MySQL',
],
'timezone' => 'Africa/Lome',
'debug' => false,
```

## 5. Installation

Ouvrez `https://bibliotheque.univkara.tg/installation.php` :
- si quelque chose manque (version de PHP, extension, config.php), la page le dit et explique quoi régler ;
- sinon, saisissez le **mot de passe de la base** (preuve que vous êtes le propriétaire), l'adresse du site, l'e-mail et le
  mot de passe de votre **compte Système**, puis « Installer ».

La base est créée **vide** avec un seul compte, le compte Système ; la page d'installation s'efface ensuite toute seule
(sinon, supprimez `installation.php`).

## 6. Vérifications

- `https://bibliotheque.univkara.tg/` s'affiche, ainsi que `https://bibliotheque.univkara.tg/index.php/catalogue` ;
- ces adresses doivent être **refusées** (403 ou 404) : `/app/config.php`, `/install/schema.sql`, `/README.md` ;
- HTTPS : o2switch installe le certificat automatiquement (cPanel → « Let's Encrypt » / AutoSSL si besoin).

## 7. Premiers pas

Connectez-vous sur `https://bibliotheque.univkara.tg/admin/` avec le compte Système :
1. **Configuration → Comptes du personnel** : créez le compte de l'établissement avec le rôle **Administrateur** ;
2. **Réglages → E-mails aux lecteurs** : serveur SMTP. Le plus simple sur o2switch : créez une adresse dans cPanel
   (**Comptes de messagerie**, ex. `bibliotheque@univkara.tg`), puis serveur `mail.univkara.tg`, port **465**, sécurité
   **SSL/TLS**, identifiant = l'adresse complète. Testez dans **E-mails envoyés** ;
3. ajoutez domaines, types, puis ouvrages (à la main ou **Importer un fichier**).

## 8. Tâche quotidienne (rappels et relances)

cPanel → **Tâches Cron** → une fois par jour, commande :

```bash
/opt/alt/php83/usr/bin/php /home/alpha/bibliotheque.univkara.tg/app/taches.php > /dev/null 2>&1
```

Le chemin complet de PHP 8.3 est indispensable : la commande `php` seule utilise la version par défaut du compte.

## 9. Sauvegardes

o2switch sauvegarde les comptes (cPanel → JetBackup). Pour une copie à vous : cPanel → phpMyAdmin → Exporter, et
téléchargez régulièrement le dossier `images`.

## 10. Mises à jour du code

Remplacez les fichiers (en gardant `app/config.php` et `images/`), puis, si une mise à jour de la base est nécessaire,
ajoutez une tâche Cron ponctuelle :
`/opt/alt/php83/usr/bin/php /home/alpha/bibliotheque.univkara.tg/install/mettre_a_jour.php`.
