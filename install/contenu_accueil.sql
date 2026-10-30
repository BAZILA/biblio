-- Contenus de démonstration de la page d'accueil : diaporama et citations (« Paroles d'auteurs »).
-- Chargé par install/ajouter_contenu_accueil.php ; les images sont dans images/biblio/.

INSERT INTO slides (titre, texte, bouton, lien, image, ordre, publie) VALUES
  ('Bienvenue à la\nbibliothèque', 'Université de Kara — consultez en ligne l’ensemble de nos ouvrages, classés par domaine, par type et par auteur.', 'Parcourir le catalogue', 'catalogue', 'images/biblio/photo1.jpg', '1', '1'),
  ('Explorez nos\ndomaines', 'Informatique, sciences, médecine, droit, économie, littérature, histoire, philosophie… trouvez rapidement les ouvrages de votre discipline.', 'Voir les domaines', 'domaines', 'images/biblio/photo3.jpg', '2', '1'),
  ('Livres, thèses,\nrevues et mémoires', 'Manuels universitaires, romans, dictionnaires, revues, thèses et mémoires : filtrez le catalogue par type de document.', 'Voir les types', 'types', 'images/biblio/photo8.jpg', '3', '1'),
  ('Découvrez\nles auteurs', 'De Victor Hugo à Cheikh Anta Diop, retrouvez tous les ouvrages d’un auteur en un clic.', 'Index des auteurs', 'auteurs', 'images/biblio/photo2.jpg', '4', '1');

INSERT INTO citations (texte, auteur, source, image, ordre, publie) VALUES
  ('En Afrique, quand un vieillard meurt, c’est une bibliothèque qui brûle.', 'Amadou Hampâté Bâ', 'Discours à l’UNESCO, 1960', 'images/biblio/avatar-amadou-hampate-ba.png', '1', '1'),
  ('Le bon sens est la chose du monde la mieux partagée.', 'René Descartes', 'Discours de la méthode', 'images/biblio/avatar-rene-descartes.png', '2', '1'),
  ('L’homme est né libre, et partout il est dans les fers.', 'Jean-Jacques Rousseau', 'Du contrat social', 'images/biblio/avatar-jean-jacques-rousseau.png', '3', '1'),
  ('Pour qu’on ne puisse abuser du pouvoir, il faut que, par la disposition des choses, le pouvoir arrête le pouvoir.', 'Montesquieu', 'De l’esprit des lois', 'images/biblio/avatar-montesquieu.png', '4', '1'),
  ('Aujourd’hui, maman est morte. Ou peut-être hier, je ne sais pas.', 'Albert Camus', 'L’Étranger', 'images/biblio/avatar-albert-camus.png', '5', '1'),
  ('Aïssatou, j’ai reçu ton mot. En guise de réponse, j’ouvre ce cahier, point d’appui dans mon désarroi.', 'Mariama Bâ', 'Une si longue lettre', 'images/biblio/avatar-mariama-ba.png', '6', '1');
