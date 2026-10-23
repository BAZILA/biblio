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

**Extensions de la version 8.3** — à cocher même si le compte garde une autre version par défaut :
cPanel → **Sélectionner une version de PHP** → onglet **Extensions** → choisissez **8.3** dans la liste des versions de cet onglet
(sans cliquer sur « Définir comme actuel »), puis cochez : `mbstring`, `gd`, `fileinfo`, `dom`, `ctype`, `pdo_mysql`, `mysqlnd`,
`openssl`, `iconv`, `intl`, `zip`. L'enregistrement est immédiat ; rechargez ensuite `installation.php`, qui vérifie leur présence.

Avec un accès SSH, vérification directe :

```bash
/opt/alt/php83/usr/bin/php -m | grep -iE '^(mbstring|gd|fileinfo|dom|ctype|pdo_mysql|openssl|iconv)$'
```

Les 8 noms doivent s'afficher. Si l'onglet Extensions ne propose pas de choisir la version 8.3 sans la rendre
« actuelle » pour tout le compte, demandez au support o2switch d'activer ces extensions pour PHP 8.3 sur votre compte.

**Réglages** (même écran, onglet Options) : `upload_max_filesize = 10M`, `post_max_size = 12M`, `memory_limit = 256M`,
`expose_php = Off`, `date.timezone = Africa/Lome`. Avec la ligne `___lsphp` ci-dessus, un fichier `php.ini` ou `.user.ini`
dans le dossier du site est ignoré : passez par cet écran.

### Si des extensions manquent en 8.3 alors qu'elles sont cochées

Les cases du sélecteur s'appliquent à la version **actuelle** du compte, pas forcément à la 8.3 forcée par le `.htaccess`.
Vérifiez la version actuelle du compte :

```bash
php -v
php -m | grep -iE '^(mbstring|gd|fileinfo|dom|ctype|pdo_mysql|openssl|iconv)$'
```

**Cas A — le compte est déjà en PHP 8.2 ou plus avec ces extensions** : supprimez simplement la ligne `AddHandler … php83___lsphp`
du `.htaccess` ; le site utilise alors la version du compte.

**Cas B — le compte doit rester sur une version plus ancienne** (autres sites) : php.ini propre au site.
1. Déposez à la racine du site le fichier `php.ini` fourni (il charge `mbstring`, `gd`, `fileinfo`, `dom`) ;
2. testez-le avant de l'activer :

   ```bash
   /opt/alt/php83/usr/bin/php -c ~/bibliotheque.univ-kara.org/php.ini -m | grep -iE '^(mbstring|gd|fileinfo|dom|ctype|pdo_mysql|openssl|iconv)$'
   ```

   les 8 noms doivent s'afficher, sans message « Unable to load dynamic library » (si un tel message cite `dom`,
   ajoutez `extension=xml.so` juste avant `extension=dom.so`) ;
3. remplacez les 3 premières lignes du `.htaccess` (commentaires et `AddHandler … ___lsphp`) par :

   ```apache
   <FilesMatch \.php$>
   SetHandler application/x-httpd-php83
   </FilesMatch>
   AddHandler application/x-httpd-php83 .php
   suPHP_ConfigPath /home/VOTRE_LOGIN/bibliotheque.univ-kara.org/
   ```

   (o2switch : `suPHP_ConfigPath` n'est lu qu'avec cette forme `SetHandler`, pas avec la forme `___lsphp`) ;
4. la tâche Cron doit alors utiliser le même fichier : `/opt/alt/php83/usr/bin/php -c /home/VOTRE_LOGIN/bibliotheque.univ-kara.org/php.ini …`.

Si une page affiche du code PHP au lieu du site, le handler est mal écrit : corrigez-le immédiatement.

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
3. droits : dossiers en **755**, fichiers en **644**. Indispensable : un zip préparé sous Windows s'extrait souvent en 666/777,
   que suPHP refuse (erreur 500 « Internal Server Error … ErrorDocument »). En SSH :

   ```bash
   find ~/bibliotheque.univ-kara.org -type d -exec chmod 755 {} +
   find ~/bibliotheque.univ-kara.org -type f -exec chmod 644 {} +
   ```

   puis, une fois `app/config.php` créé : `chmod 600 ~/bibliotheque.univ-kara.org/app/config.php`
   (et `php.ini` s'il existe).

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

En SSH (avec le `php.ini` du site, cas B) :

```bash
/opt/alt/php83/usr/bin/php -c ~/bibliotheque.univ-kara.org/php.ini ~/bibliotheque.univ-kara.org/install/mettre_a_jour.php
```

## 11. Données de test

Pour une démonstration, un jeu complet de données fictives (catalogue avec couvertures, étudiants, lecteurs, personnel, prêts,
réservations…) s'ajoute sans rien effacer :

```bash
/opt/alt/php83/usr/bin/php -c ~/bibliotheque.univ-kara.org/php.ini ~/bibliotheque.univ-kara.org/install/donnees_test.php
cat ~/bibliotheque.univ-kara.org/install/IDENTIFIANTS_TEST.txt
```

Avant l'ouverture au public, retirez-les : même commande avec `--retirer` à la fin.
