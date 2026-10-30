# Déploiement (installation neuve)

## Hébergement avec panneau, sans sudo ni terminal

1. **PHP** : dans le panneau, choisissez PHP 8.2 ou plus pour le domaine (extensions pdo_mysql, mbstring, gd).
2. **Base** : créez une base MySQL/MariaDB et un utilisateur avec tous les droits dessus (le panneau ajoute souvent un préfixe, ex. `alpha_biblio`). Notez nom, utilisateur, mot de passe.
3. **Fichiers** : décompressez le zip **directement** dans le dossier du domaine (on doit y voir `index.php`, `admin/`, `app/`… et les fichiers cachés `.htaccess`).
4. **Configuration** : dans le gestionnaire de fichiers, copiez `app/config.example.php` en `app/config.php` et renseignez `name`, `user`, `password` de la base (`host` reste en général `localhost`), `debug` à `false`.
5. **Installation** : ouvrez `https://votre-domaine/installation.php`, saisissez le mot de passe de la base (preuve que vous êtes le propriétaire), l'e-mail et le mot de passe du compte Système. La page crée la base vide et le compte, puis **s'efface toute seule** (sinon, supprimez `installation.php`).
6. **Vérifications de sécurité** : ces adresses doivent être refusées (erreur 403 ou 404) : `/install/schema.sql`, `/app/config.example.php`, `/README.md`. Si l'une s'affiche, le serveur ignore les `.htaccess` : demandez à l'hébergeur de les activer (AllowOverride All) ou d'appliquer les règles de la variante Nginx ci-dessous.
7. **Tâche quotidienne** : dans « Tâches planifiées / Cron » du panneau, une fois par jour : `php /chemin/du/domaine/app/taches.php` (le panneau indique le chemin de `php` si besoin, ex. `/usr/bin/php`).
8. Connectez-vous sur `https://votre-domaine/admin/` avec le compte Système et poursuivez au point 5.3 ci-dessous.

---

## Serveur avec accès administrateur (VPS)

Ce paquet installe le site **à vide** : aucune donnée de catalogue, de lecteur ni de prêt.
La base ne contient au départ que les rôles par défaut, les réglages, les textes de l'interface du site
et **un seul compte : le compte Système** (super administrateur invisible des autres comptes).

Exemple ci-dessous : Ubuntu 22.04 / 24.04, Apache, MariaDB, domaine `biblio.exemple.org`.
Remplacez le domaine, les mots de passe et les chemins par les vôtres.

## 1. Logiciels

```bash
sudo apt update
sudo apt install -y apache2 mariadb-server php php-mysql php-mbstring php-gd php-xml php-zip php-intl unzip libapache2-mod-php
sudo a2enmod rewrite headers expires
```

PHP 8.2 ou plus récent est requis (`php -v`).

## 2. Base de données

```bash
sudo mysql
```

```sql
CREATE DATABASE biblio CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'biblio'@'localhost' IDENTIFIED BY 'UN_MOT_DE_PASSE_SOLIDE';
GRANT ALL PRIVILEGES ON biblio.* TO 'biblio'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

## 3. Fichiers

```bash
sudo mkdir -p /var/www/biblio
sudo unzip biblio-deploiement.zip -d /var/www/biblio
cd /var/www/biblio
sudo cp app/config.example.php app/config.php
sudo nano app/config.php        # nom de la base, utilisateur, mot de passe ; 'debug' => false
sudo chown -R www-data:www-data /var/www/biblio
sudo find /var/www/biblio -type d -exec chmod 755 {} \;
sudo find /var/www/biblio -type f -exec chmod 644 {} \;
sudo chmod 640 app/config.php
```

Le serveur web doit pouvoir écrire dans `images/ouvrages` (couvertures) et `images/biblio` (images des réglages et du diaporama).

## 4. Apache

`/etc/apache2/sites-available/biblio.conf` :

```apache
<VirtualHost *:80>
    ServerName biblio.exemple.org
    DocumentRoot /var/www/biblio
    <Directory /var/www/biblio>
        AllowOverride All
        Require all granted
    </Directory>
    ErrorLog ${APACHE_LOG_DIR}/biblio-error.log
    CustomLog ${APACHE_LOG_DIR}/biblio-access.log combined
</VirtualHost>
```

```bash
sudo a2ensite biblio
sudo a2dissite 000-default
sudo systemctl reload apache2
```

Les fichiers `.htaccess` fournis protègent le code (`app`, `views`, `install`), les fichiers sensibles et le dossier des images.

HTTPS (recommandé) :

```bash
sudo apt install -y certbot python3-certbot-apache
sudo certbot --apache -d biblio.exemple.org
```

## 5. Installation de la base (vide + compte Système)

Sans terminal : ouvrez `https://votre-domaine/installation.php` (voir plus haut). Avec terminal :

```bash
cd /var/www/biblio
sudo -u www-data php install/installer.php --url=https://biblio.exemple.org/ --email=votre.adresse@exemple.org
sudo cat install/SYSTEME.txt
```

Puis :

1. connectez-vous sur `https://biblio.exemple.org/admin/` avec ces identifiants ;
2. changez le mot de passe dans **Mon compte**, puis supprimez le fichier : `sudo rm install/SYSTEME.txt` ;
3. créez le compte de l'établissement : **Configuration → Comptes du personnel → Nouveau compte**, rôle **Administrateur** ;
4. si besoin, masquez des permissions à tous les autres comptes depuis la page **Système** ;
5. réglez les e-mails : **Réglages → E-mails aux lecteurs** (serveur SMTP, expéditeur), puis testez dans **E-mails envoyés** ;
6. ajoutez le catalogue : domaines, types, puis ouvrages (à la main ou **Importer un fichier**).

L'installateur refuse de toucher une base qui contient déjà des tables. `--reinitialiser` efface tout et repart de zéro.
Le compte Système peut être réinitialisé à tout moment : `sudo -u www-data php install/compte_systeme.php`.

## 6. Tâche quotidienne (rappels, relances, réservations expirées)

```bash
sudo crontab -u www-data -e
```

```cron
0 7 * * * php /var/www/biblio/app/taches.php > /dev/null 2>&1
```

## 7. Réglages PHP conseillés

Dans `/etc/php/8.x/apache2/php.ini` : `expose_php = Off`, `upload_max_filesize = 8M`, `post_max_size = 10M`,
`date.timezone = Africa/Lome`. Puis `sudo systemctl reload apache2`.

## 8. Sauvegardes

```bash
sudo crontab -e
```

```cron
30 2 * * * mysqldump --single-transaction biblio | gzip > /var/backups/biblio-$(date +\%F).sql.gz
45 2 * * * tar czf /var/backups/biblio-images-$(date +\%F).tgz -C /var/www/biblio images
```

Pensez à copier régulièrement `/var/backups` hors du serveur.

## 9. Mises à jour ultérieures du code

Remplacez les fichiers (en gardant `app/config.php` et le dossier `images`), puis :

```bash
sudo -u www-data php install/mettre_a_jour.php
```

## Variante Nginx (au lieu d'Apache)

Les adresses du site passent par `index.php/…` et `admin/index.php/…` (PATH_INFO).

```nginx
server {
    server_name biblio.exemple.org;
    root /var/www/biblio;
    index index.php;
    client_max_body_size 10M;

    location ~ ^/(app|views|install)(/|$) { return 404; }
    location ~ /\.(?!well-known) { return 404; }
    location ~* \.(sql|md|log|ini|txt|sh|ps1)$ { return 404; }
    location = /robots.txt { }
    location ~* ^/images/.*\.(php\d?|phtml|phar|pl|py|cgi)$ { return 403; }

    location ~ ^(.+\.php)(/.*)?$ {
        fastcgi_split_path_info ^(.+\.php)(/.*)$;
        try_files $fastcgi_script_name =404;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param PATH_INFO $fastcgi_path_info;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
    }
    location / { try_files $uri $uri/ =404; }
}
```
