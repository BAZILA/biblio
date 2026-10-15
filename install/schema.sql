-- Bibliothèque universitaire — schéma de la base (MariaDB / MySQL 5.7+)
-- Import : mysql -u root biblio < install/schema.sql

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS role_permissions, roles, demandes_prolongation, mails, tentatives_connexion, reservations, prets, exemplaires, ouvrage_auteur, ouvrages,
  auteurs, types, domaines, slides, citations, blocs, reglages, messages, utilisateurs;

SET FOREIGN_KEY_CHECKS = 1;

-- Rôles du personnel ; leurs droits sont dans role_permissions (« module.action », voir app/lib/permissions.php)
CREATE TABLE roles (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nom         VARCHAR(100) NOT NULL,
  description VARCHAR(255) NOT NULL DEFAULT '',
  tous_droits TINYINT(1) NOT NULL DEFAULT 0,   -- 1 = toutes les permissions visibles, présentes et futures (Administrateur)
  systeme     TINYINT(1) NOT NULL DEFAULT 0,   -- 1 = compte Système (super admin) : tout, invisible des autres comptes
  cree_le     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_nom (nom)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE role_permissions (
  role_id    INT UNSIGNED NOT NULL,
  permission VARCHAR(60) NOT NULL,
  PRIMARY KEY (role_id, permission),
  CONSTRAINT fk_rp_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO roles (id, nom, description, tous_droits, systeme) VALUES
  (1, 'Administrateur', 'Tous les droits, y compris la gestion du personnel et des rôles.', 1, 0),
  (2, 'Bibliothécaire', 'Catalogue, lecteurs, prêts, réservations, contenus du site et réglages.', 0, 0),
  (3, 'Agent de prêt', 'Guichet : prêts, retours et remises de réservations ; consultation du catalogue et des lecteurs.', 0, 0),
  (4, 'Éditeur du site', 'Contenus de la page d’accueil : diaporama, citations et textes du site.', 0, 0),
  (5, 'Système', 'Compte technique lié au système : tous les droits, masque des permissions, invisible des autres comptes.', 1, 1);

INSERT INTO role_permissions (role_id, permission) VALUES
  (2, 'tableau.voir'), (2, 'guichet.voir'),
  (2, 'prets.voir'), (2, 'prets.ajouter'), (2, 'prets.retour'), (2, 'prets.prolonger'), (2, 'prets.forcer'),
  (2, 'demandes.voir'), (2, 'demandes.traiter'),
  (2, 'reservations.voir'), (2, 'reservations.remettre'), (2, 'reservations.annuler'),
  (2, 'lecteurs.voir'), (2, 'lecteurs.ajouter'), (2, 'lecteurs.modifier'), (2, 'lecteurs.supprimer'), (2, 'lecteurs.valider'), (2, 'lecteurs.mot_de_passe'),
  (2, 'ouvrages.voir'), (2, 'ouvrages.ajouter'), (2, 'ouvrages.modifier'), (2, 'ouvrages.supprimer'), (2, 'ouvrages.desactiver'),
  (2, 'exemplaires.ajouter'), (2, 'exemplaires.modifier'), (2, 'exemplaires.supprimer'), (2, 'exemplaires.retirer'),
  (2, 'auteurs.voir'), (2, 'auteurs.ajouter'), (2, 'auteurs.modifier'), (2, 'auteurs.supprimer'),
  (2, 'domaines.voir'), (2, 'domaines.ajouter'), (2, 'domaines.modifier'), (2, 'domaines.supprimer'),
  (2, 'types.voir'), (2, 'types.ajouter'), (2, 'types.modifier'), (2, 'types.supprimer'),
  (2, 'import.auteurs'), (2, 'import.ouvrages'),
  (2, 'slides.voir'), (2, 'slides.ajouter'), (2, 'slides.modifier'), (2, 'slides.supprimer'),
  (2, 'citations.voir'), (2, 'citations.ajouter'), (2, 'citations.modifier'), (2, 'citations.supprimer'),
  (2, 'messages.voir'), (2, 'messages.supprimer'),
  (2, 'reglages.voir'), (2, 'reglages.modifier'), (2, 'reglages.mails'),
  (2, 'mails.voir'), (2, 'mails.envoyer'),
  (2, 'rapports.voir'), (2, 'rapports.exporter'),
  (3, 'tableau.voir'), (3, 'guichet.voir'),
  (3, 'prets.voir'), (3, 'prets.ajouter'), (3, 'prets.retour'), (3, 'prets.prolonger'),
  (3, 'demandes.voir'), (3, 'reservations.voir'), (3, 'reservations.remettre'),
  (3, 'lecteurs.voir'), (3, 'lecteurs.ajouter'), (3, 'ouvrages.voir'),
  (4, 'tableau.voir'),
  (4, 'slides.voir'), (4, 'slides.ajouter'), (4, 'slides.modifier'), (4, 'slides.supprimer'),
  (4, 'citations.voir'), (4, 'citations.ajouter'), (4, 'citations.modifier'), (4, 'citations.supprimer'),
  (4, 'blocs.voir'), (4, 'blocs.modifier'), (4, 'messages.voir');

-- Comptes : personnel (avec un rôle) et lecteurs (espace lecteur + prêts)
CREATE TABLE utilisateurs (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  role          ENUM('personnel','lecteur') NOT NULL DEFAULT 'lecteur',
  role_id       INT UNSIGNED NULL,               -- personnel : rôle et permissions (table roles)
  nom           VARCHAR(100) NOT NULL,
  prenom        VARCHAR(100) NOT NULL DEFAULT '',
  email         VARCHAR(190) NULL,               -- facultatif pour un lecteur sans accès en ligne
  mot_de_passe  VARCHAR(255) NOT NULL DEFAULT '',  -- vide = pas d'accès à l'espace lecteur
  matricule     VARCHAR(50)  NULL,
  categorie     ENUM('etudiant','enseignant','personnel','externe') NULL,
  faculte       VARCHAR(150) NOT NULL DEFAULT '',  -- étudiants
  departement   VARCHAR(150) NOT NULL DEFAULT '',
  telephone     VARCHAR(50)  NOT NULL DEFAULT '',
  actif         TINYINT(1)   NOT NULL DEFAULT 1,
  en_attente    TINYINT(1)   NOT NULL DEFAULT 0,   -- inscription en ligne pas encore validée
  derniere_connexion DATETIME NULL,
  cree_le       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_email (email),
  UNIQUE KEY uq_matricule (matricule),
  CONSTRAINT fk_utilisateur_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE SET NULL
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
  actif        TINYINT(1) NOT NULL DEFAULT 1,   -- 0 = ouvrage désactivé : retiré du site, du prêt et des réservations
  desactive_le DATETIME NULL,
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
  notes              VARCHAR(255) NOT NULL DEFAULT '',   -- remarque à la remise
  etat_sortie        ENUM('neuf','bon','use','abime') NULL,      -- état de l'exemplaire remis au lecteur
  etat_retour        ENUM('conforme','use','abime','perdu') NULL, -- constat au retour
  note_retour        VARCHAR(255) NOT NULL DEFAULT '',
  agent_retour_id    INT UNSIGNED NULL,       -- bibliothécaire qui a enregistré le retour
  rappel_envoye_le   DATE NULL,               -- rappel « à rendre bientôt » déjà envoyé
  relance_envoyee_le DATE NULL,               -- dernière relance de retard envoyée
  KEY ix_encours (date_retour, date_retour_prevue),
  KEY ix_lecteur (lecteur_id),
  CONSTRAINT fk_pret_exemplaire FOREIGN KEY (exemplaire_id) REFERENCES exemplaires(id),
  CONSTRAINT fk_pret_lecteur    FOREIGN KEY (lecteur_id)    REFERENCES utilisateurs(id),
  CONSTRAINT fk_pret_agent      FOREIGN KEY (agent_id)      REFERENCES utilisateurs(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Demandes de prolongation faites par les lecteurs ; le personnel accepte ou refuse
CREATE TABLE demandes_prolongation (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  pret_id      INT UNSIGNED NOT NULL,
  motif        VARCHAR(255) NOT NULL DEFAULT '',
  statut       ENUM('en_attente','acceptee','refusee','annulee') NOT NULL DEFAULT 'en_attente',
  demandee_le  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  traitee_le   DATETIME NULL,
  agent_id     INT UNSIGNED NULL,
  reponse      VARCHAR(255) NOT NULL DEFAULT '',      -- motif du refus ou message au lecteur
  nouvelle_date DATE NULL,
  KEY ix_statut (statut),
  KEY ix_pret (pret_id),
  CONSTRAINT fk_dp_pret  FOREIGN KEY (pret_id)  REFERENCES prets(id) ON DELETE CASCADE,
  CONSTRAINT fk_dp_agent FOREIGN KEY (agent_id) REFERENCES utilisateurs(id) ON DELETE SET NULL
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
-- Journal des e-mails (envoyés, en échec, ou seulement journalisés si l'envoi est désactivé)
CREATE TABLE mails (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  destinataire VARCHAR(190) NOT NULL,
  sujet        VARCHAR(255) NOT NULL,
  corps        TEXT NOT NULL,
  motif        VARCHAR(40)  NOT NULL DEFAULT '',   -- pret, rappel, retard, reservation, compte…
  statut       ENUM('envoye','echec','journal') NOT NULL,
  erreur       VARCHAR(255) NOT NULL DEFAULT '',
  cree_le      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY ix_date (cree_le)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  ('par_page', '12'),
  ('couverture_defaut', ''),
  ('photo_apropos', 'images/biblio/apropos-rayonnages.jpg'),
  ('site_adresse', 'http://localhost/biblio/'),
  ('rappel_jours_avant', '2'),
  ('relance_tous_les_jours', '7'),
  ('mail_mode', 'journal'),
  ('mail_expediteur', ''),
  ('mail_expediteur_nom', 'Bibliothèque universitaire'),
  ('smtp_hote', ''),
  ('smtp_port', '587'),
  ('smtp_securite', 'tls'),
  ('smtp_utilisateur', ''),
  ('smtp_mot_de_passe', ''),
  ('taches_dernier_jour', ''),
  ('permissions_masquees', '');
