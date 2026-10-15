-- Bibliothèque universitaire — schéma de la base (MariaDB / MySQL 5.7+)
-- Import : mysql -u root biblio < install/schema.sql

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS tentatives_connexion, reservations, prets, exemplaires, ouvrage_auteur, ouvrages,
  auteurs, types, domaines, slides, citations, blocs, reglages, messages, utilisateurs;

SET FOREIGN_KEY_CHECKS = 1;

-- Comptes : personnel (admin, bibliothécaire) et lecteurs (espace lecteur + prêts)
CREATE TABLE utilisateurs (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  role          ENUM('admin','bibliothecaire','lecteur') NOT NULL DEFAULT 'lecteur',
  nom           VARCHAR(100) NOT NULL,
  prenom        VARCHAR(100) NOT NULL DEFAULT '',
  email         VARCHAR(190) NOT NULL,
  mot_de_passe  VARCHAR(255) NOT NULL DEFAULT '',
  matricule     VARCHAR(50)  NULL,
  categorie     ENUM('etudiant','enseignant','personnel','externe') NULL,
  telephone     VARCHAR(50)  NOT NULL DEFAULT '',
  actif         TINYINT(1)   NOT NULL DEFAULT 1,
  en_attente    TINYINT(1)   NOT NULL DEFAULT 0,   -- inscription en ligne pas encore validée
  derniere_connexion DATETIME NULL,
  cree_le       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_email (email),
  UNIQUE KEY uq_matricule (matricule)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE domaines (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nom         VARCHAR(150) NOT NULL,
  slug        VARCHAR(150) NOT NULL,
  description TEXT NULL,
  icone       VARCHAR(60)  NOT NULL DEFAULT 'fa-book',
  ordre       INT NOT NULL DEFAULT 0,
  UNIQUE KEY uq_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE types (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nom         VARCHAR(100) NOT NULL,          -- « Roman »
  nom_pluriel VARCHAR(100) NOT NULL,          -- « Romans » (menu)
  slug        VARCHAR(100) NOT NULL,          -- « type-roman » (URL)
  description TEXT NULL,
  ordre       INT NOT NULL DEFAULT 0,
  UNIQUE KEY uq_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE auteurs (
  id   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nom  VARCHAR(190) NOT NULL,
  slug VARCHAR(190) NOT NULL,                 -- « auteur-victor-hugo »
  UNIQUE KEY uq_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE ouvrages (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  titre        VARCHAR(255) NOT NULL,
  slug         VARCHAR(255) NOT NULL,
  domaine_id   INT UNSIGNED NOT NULL,
  type_id      INT UNSIGNED NULL,
  resume       TEXT NULL,                     -- HTML court (listes, cartes)
  description  MEDIUMTEXT NULL,               -- HTML complet (fiche)
  editeur      VARCHAR(190) NOT NULL DEFAULT '',
  annee        VARCHAR(20)  NOT NULL DEFAULT '',
  isbn         VARCHAR(30)  NOT NULL DEFAULT '',
  cote         VARCHAR(50)  NOT NULL DEFAULT '',
  pages        INT UNSIGNED NULL,
  langue       VARCHAR(50)  NOT NULL DEFAULT 'Français',
  image        VARCHAR(255) NOT NULL DEFAULT '',
  consultation_sur_place TINYINT(1) NOT NULL DEFAULT 0,  -- exclu du prêt
  coup_de_coeur TINYINT(1) NOT NULL DEFAULT 0,
  publie       TINYINT(1) NOT NULL DEFAULT 1,
  cree_le      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  modifie_le   DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_slug (domaine_id, slug),
  KEY ix_type (type_id),
  FULLTEXT KEY ft_recherche (titre, resume, editeur),
  CONSTRAINT fk_ouvrage_domaine FOREIGN KEY (domaine_id) REFERENCES domaines(id),
  CONSTRAINT fk_ouvrage_type    FOREIGN KEY (type_id)    REFERENCES types(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE ouvrage_auteur (
  ouvrage_id INT UNSIGNED NOT NULL,
  auteur_id  INT UNSIGNED NOT NULL,
  ordre      INT NOT NULL DEFAULT 0,
  PRIMARY KEY (ouvrage_id, auteur_id),
  KEY ix_auteur (auteur_id),
  CONSTRAINT fk_oa_ouvrage FOREIGN KEY (ouvrage_id) REFERENCES ouvrages(id) ON DELETE CASCADE,
  CONSTRAINT fk_oa_auteur  FOREIGN KEY (auteur_id)  REFERENCES auteurs(id)  ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Exemplaires physiques : c'est eux qu'on prête
CREATE TABLE exemplaires (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  ouvrage_id  INT UNSIGNED NOT NULL,
  code        VARCHAR(50) NOT NULL,           -- code-barres / numéro d'inventaire
  etat        ENUM('neuf','bon','use','abime') NOT NULL DEFAULT 'bon',
  statut      ENUM('disponible','prete','reserve','retire') NOT NULL DEFAULT 'disponible',
  notes       VARCHAR(255) NOT NULL DEFAULT '',
  cree_le     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_code (code),
  KEY ix_ouvrage_statut (ouvrage_id, statut),
  CONSTRAINT fk_ex_ouvrage FOREIGN KEY (ouvrage_id) REFERENCES ouvrages(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE prets (
  id                 INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  exemplaire_id      INT UNSIGNED NOT NULL,
  lecteur_id         INT UNSIGNED NOT NULL,
  agent_id           INT UNSIGNED NULL,       -- bibliothécaire qui a enregistré le prêt
  date_pret          DATE NOT NULL,
  date_retour_prevue DATE NOT NULL,
  date_retour        DATE NULL,               -- NULL = en cours
  prolongations      TINYINT UNSIGNED NOT NULL DEFAULT 0,
  notes              VARCHAR(255) NOT NULL DEFAULT '',
  KEY ix_encours (date_retour, date_retour_prevue),
  KEY ix_lecteur (lecteur_id),
  CONSTRAINT fk_pret_exemplaire FOREIGN KEY (exemplaire_id) REFERENCES exemplaires(id),
  CONSTRAINT fk_pret_lecteur    FOREIGN KEY (lecteur_id)    REFERENCES utilisateurs(id),
  CONSTRAINT fk_pret_agent      FOREIGN KEY (agent_id)      REFERENCES utilisateurs(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Réservations faites par les lecteurs depuis le site
CREATE TABLE reservations (
  id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  ouvrage_id      INT UNSIGNED NOT NULL,
  lecteur_id      INT UNSIGNED NOT NULL,
  exemplaire_id   INT UNSIGNED NULL,          -- exemplaire mis de côté quand il est prêt
  statut          ENUM('en_attente','disponible','honoree','annulee','expiree') NOT NULL DEFAULT 'en_attente',
  demandee_le     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  disponible_le   DATETIME NULL,
  expire_le       DATE NULL,
  KEY ix_ouvrage_statut (ouvrage_id, statut),
  KEY ix_lecteur (lecteur_id),
  CONSTRAINT fk_res_ouvrage    FOREIGN KEY (ouvrage_id)    REFERENCES ouvrages(id) ON DELETE CASCADE,
  CONSTRAINT fk_res_lecteur    FOREIGN KEY (lecteur_id)    REFERENCES utilisateurs(id) ON DELETE CASCADE,
  CONSTRAINT fk_res_exemplaire FOREIGN KEY (exemplaire_id) REFERENCES exemplaires(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Diaporama de l'accueil
CREATE TABLE slides (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  titre       VARCHAR(190) NOT NULL,          -- un retour à la ligne = <br>
  texte       TEXT NULL,
  bouton      VARCHAR(100) NOT NULL DEFAULT '',
  lien        VARCHAR(255) NOT NULL DEFAULT '',
  image       VARCHAR(255) NOT NULL,
  ordre       INT NOT NULL DEFAULT 0,
  publie      TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- « Paroles d'auteurs » sur l'accueil
CREATE TABLE citations (
  id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  texte     TEXT NOT NULL,
  auteur    VARCHAR(190) NOT NULL,
  source    VARCHAR(190) NOT NULL DEFAULT '',
  image     VARCHAR(255) NOT NULL DEFAULT '',
  ordre     INT NOT NULL DEFAULT 0,
  publie    TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Blocs de contenu HTML éditables (à propos, horaires, pied de page…)
CREATE TABLE blocs (
  cle      VARCHAR(60) PRIMARY KEY,
  titre    VARCHAR(190) NOT NULL,
  contenu  MEDIUMTEXT NOT NULL,
  modifie_le DATETIME NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Réglages simples clé / valeur (nom du site, règles de prêt…)
CREATE TABLE reglages (
  cle    VARCHAR(60) PRIMARY KEY,
  valeur TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Échecs de connexion (limite anti-force brute par e-mail et par IP)
CREATE TABLE tentatives_connexion (
  id    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(190) NOT NULL,
  ip    VARCHAR(45)  NOT NULL,
  le    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY ix_email (email, le),
  KEY ix_ip (ip, le)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Messages reçus via le formulaire de contact
CREATE TABLE messages (
  id       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nom      VARCHAR(190) NOT NULL,
  email    VARCHAR(190) NOT NULL,
  sujet    VARCHAR(255) NOT NULL,
  message  TEXT NOT NULL,
  lu       TINYINT(1) NOT NULL DEFAULT 0,
  recu_le  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO reglages (cle, valeur) VALUES
  ('site_nom', 'Université de Kara'),
  ('site_description', 'Bibliothèque de l’Université de Kara : catalogue en ligne des ouvrages, recherche par domaine, par type de document et par auteur.'),
  ('contact_email', 'contact@biblio.local'),
  ('pret_duree_jours', '14'),
  ('pret_max_etudiant', '3'),
  ('pret_max_enseignant', '6'),
  ('pret_max_personnel', '4'),
  ('pret_max_externe', '1'),
  ('pret_max_prolongations', '1'),
  ('reservation_delai_jours', '3'),
  ('par_page', '12');
