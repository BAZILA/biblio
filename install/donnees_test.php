<?php
/**
 * Données de test : catalogue complet (avec couvertures), liste des étudiants de l'université, lecteurs de toutes
 * catégories, comptes du personnel, prêts (en cours, en retard, rendus avec constat), réservations, demandes de
 * prolongation, messages, diaporama et citations.
 *
 *   php install/donnees_test.php            : ajoute les données de test (une seule fois)
 *   php install/donnees_test.php --retirer  : retire tout ce que ce script a ajouté (avant la mise en service réelle)
 *
 * Rien n'est effacé de ce qui existait avant : un ouvrage déjà présent (même cote) est gardé tel quel.
 * Aucun e-mail n'est envoyé. Les adresses des comptes de test sont fictives (@exemple.test).
 * Le mot de passe des comptes de test est tiré au hasard et écrit dans install/IDENTIFIANTS_TEST.txt.
 */
if (PHP_SAPI !== 'cli') {
    exit('À lancer en ligne de commande.');
}
require dirname(__DIR__) . '/app/bootstrap.php';

const DOMAINE_TEST = 'exemple.test';
$fichierIdentifiants = __DIR__ . '/IDENTIFIANTS_TEST.txt';

/** Lignes ajoutées, par table : c'est ce que --retirer efface. */
$ajouts = json_decode(reglage('donnees_test') ?: '[]', true) ?: [];
function noter(string $table, int $id): int
{
    $GLOBALS['ajouts'][$table][] = $id;
    return $id;
}
function enregistrer_suivi(): void
{
    q('REPLACE INTO reglages (cle, valeur) VALUES (?, ?)', ['donnees_test', json_encode($GLOBALS['ajouts'])]);
}
function ids(string $table): array
{
    return array_map('intval', $GLOBALS['ajouts'][$table] ?? []);
}
function liste_in(array $ids): string
{
    return $ids ? implode(',', array_map('intval', $ids)) : '0';
}

/* ======================= Retrait ======================= */
if (in_array('--retirer', $argv, true)) {
    if (!$ajouts) {
        exit("Aucune donnée de test à retirer.\n");
    }
    $lecteurs = liste_in(array_merge(ids('utilisateurs'), array_map('intval', array_column(rows("SELECT u.id FROM utilisateurs u JOIN etudiants e
        ON e.numero_suivi = u.matricule WHERE u.role = 'lecteur' AND e.id IN (" . liste_in(ids('etudiants')) . ')'), 'id'))));
    $ouvrages = liste_in(ids('ouvrages'));
    $exemplairesTest = "SELECT id FROM exemplaires WHERE ouvrage_id IN ($ouvrages)";
    db()->beginTransaction();
    try {
        // Exemplaires réels prêtés ou mis de côté pour un lecteur de test : de nouveau disponibles
        q("UPDATE exemplaires e JOIN prets p ON p.exemplaire_id = e.id AND p.date_retour IS NULL
           SET e.statut = 'disponible' WHERE p.lecteur_id IN ($lecteurs) AND e.ouvrage_id NOT IN ($ouvrages)");
        q("UPDATE exemplaires e JOIN reservations r ON r.exemplaire_id = e.id AND r.statut = 'disponible'
           SET e.statut = 'disponible' WHERE r.lecteur_id IN ($lecteurs) AND e.ouvrage_id NOT IN ($ouvrages)");
        $n = [];
        $n['prêts'] = q("DELETE FROM prets WHERE id IN (" . liste_in(ids('prets')) . ") OR lecteur_id IN ($lecteurs) OR exemplaire_id IN ($exemplairesTest)")->rowCount();
        $n['réservations'] = q("DELETE FROM reservations WHERE lecteur_id IN ($lecteurs) OR ouvrage_id IN ($ouvrages)")->rowCount();
        q("UPDATE prets SET agent_id = NULL WHERE agent_id IN ($lecteurs)");
        q("UPDATE prets SET agent_retour_id = NULL WHERE agent_retour_id IN ($lecteurs)");
        q("UPDATE demandes_prolongation SET agent_id = NULL WHERE agent_id IN ($lecteurs)");
        $n['ouvrages'] = q("DELETE FROM ouvrages WHERE id IN ($ouvrages)")->rowCount(); // exemplaires et liens auteurs suivent
        $n['auteurs'] = q('DELETE FROM auteurs WHERE id IN (' . liste_in(ids('auteurs')) . ') AND id NOT IN (SELECT auteur_id FROM ouvrage_auteur)')->rowCount();
        $n['types'] = q('DELETE FROM types WHERE id IN (' . liste_in(ids('types')) . ') AND id NOT IN (SELECT type_id FROM ouvrages WHERE type_id IS NOT NULL)')->rowCount();
        $n['domaines'] = q('DELETE FROM domaines WHERE id IN (' . liste_in(ids('domaines')) . ') AND id NOT IN (SELECT domaine_id FROM ouvrages)')->rowCount();
        $n['comptes'] = q("DELETE FROM utilisateurs WHERE id IN ($lecteurs)")->rowCount();
        $n['étudiants'] = q('DELETE FROM etudiants WHERE id IN (' . liste_in(ids('etudiants')) . ')')->rowCount();
        $n['messages'] = q('DELETE FROM messages WHERE id IN (' . liste_in(ids('messages')) . ')')->rowCount();
        $n['diapositives'] = q('DELETE FROM slides WHERE id IN (' . liste_in(ids('slides')) . ')')->rowCount();
        $n['citations'] = q('DELETE FROM citations WHERE id IN (' . liste_in(ids('citations')) . ')')->rowCount();
        $n['e-mails du journal'] = q('DELETE FROM mails WHERE destinataire LIKE ?', ['%@' . DOMAINE_TEST])->rowCount();
        q("DELETE FROM tentatives_connexion WHERE email LIKE ?", ['%@' . DOMAINE_TEST]);
        q("DELETE FROM reglages WHERE cle = 'donnees_test'");
        db()->commit();
    } catch (Throwable $t) {
        db()->rollBack();
        throw $t;
    }
    @unlink($fichierIdentifiants);
    echo "Données de test retirées :\n";
    foreach ($n as $quoi => $nb) {
        echo "  $quoi : $nb\n";
    }
    exit;
}

/* ======================= Ajout ======================= */
if ($ajouts) {
    exit("Les données de test sont déjà présentes. Pour les recréer : php install/donnees_test.php --retirer, puis relancez ce script.\n");
}
mt_srand(2026); // mêmes données à chaque installation
$hasard = fn(array $liste) => $liste[mt_rand(0, count($liste) - 1)];
$jour = fn(int $decalage) => date('Y-m-d', strtotime("$decalage days"));
$moment = fn(int $decalage, string $heure = '10:00:00') => date('Y-m-d', strtotime("$decalage days")) . ' ' . $heure;
$motDePasse = mot_de_passe_provisoire();
$hash = password_hash($motDePasse, PASSWORD_DEFAULT);
$bilan = [];

db()->beginTransaction();
try {
    /* ---------- Catalogue ---------- */
    $donnees = json_decode(file_get_contents(__DIR__ . '/donnees_test.json'), true);
    $domaines = $types = [];
    foreach ($donnees['domaines'] as $d) {
        $domaines[$d['slug']] = (int) (val('SELECT id FROM domaines WHERE slug = ?', [$d['slug']]) ?: noter('domaines', insert('domaines', $d)));
    }
    foreach ($donnees['types'] as $t) {
        $types[$t['slug']] = (int) (val('SELECT id FROM types WHERE slug = ?', [$t['slug']]) ?: noter('types', insert('types', $t)));
    }
    $auteursAvant = array_column(rows('SELECT id FROM auteurs'), 'id', 'id');
    $etats = ['neuf', 'bon', 'bon', 'use', 'bon'];
    $bilan['ouvrages'] = $bilan['exemplaires'] = 0;
    foreach ($donnees['ouvrages'] as $i => $o) {
        if ($o['cote'] !== '' && val('SELECT 1 FROM ouvrages WHERE cote = ?', [$o['cote']])) {
            continue; // déjà au catalogue
        }
        $domaineId = $domaines[$o['domaine']];
        $slug = slugify($o['titre']);
        for ($base = $slug, $k = 2; val('SELECT 1 FROM ouvrages WHERE domaine_id = ? AND slug = ?', [$domaineId, $slug]); $k++) {
            $slug = "$base-$k";
        }
        $image = $o['image'] !== '' && is_file(ROOT_DIR . '/' . $o['image']) ? $o['image'] : '';
        $id = noter('ouvrages', insert('ouvrages', [
            'titre' => $o['titre'], 'slug' => $slug, 'domaine_id' => $domaineId, 'type_id' => $o['type'] ? $types[$o['type']] : null,
            'resume' => $o['resume'], 'description' => $o['description'], 'editeur' => $o['editeur'], 'annee' => $o['annee'], 'isbn' => $o['isbn'],
            'cote' => $o['cote'], 'pages' => $o['pages'], 'langue' => $o['langue'], 'image' => $image,
            'consultation_sur_place' => $o['sur_place'], 'coup_de_coeur' => $o['coup_de_coeur'],
            'cree_le' => $moment(-(count($donnees['ouvrages']) - $i) * 4), // dates d'entrée échelonnées : « Derniers ouvrages » ordonné
        ]));
        lier_auteurs($id, $o['auteurs']);
        for ($n = 1; $n <= max(1, $o['exemplaires']); $n++) {
            $code = $o['cote'] . '-' . $n;
            if (!val('SELECT 1 FROM exemplaires WHERE code = ?', [$code])) {
                insert('exemplaires', ['ouvrage_id' => $id, 'code' => $code, 'etat' => $etats[($i + $n) % count($etats)]]);
                $bilan['exemplaires']++;
            }
        }
        $bilan['ouvrages']++;
    }
    foreach (rows('SELECT id FROM auteurs') as $a) {
        if (!isset($auteursAvant[$a['id']])) {
            noter('auteurs', (int) $a['id']);
        }
    }

    /* ---------- Liste des étudiants de l'université ---------- */
    $prenomsH = ['Komlan', 'Kossi', 'Kodjo', 'Yao', 'Kokou', 'Koffi', 'Kwami', 'Essohanam', 'Abalo', 'Essozimna', 'Mawuli', 'Edem', 'Elom', 'Tchilabalo',
        'Pidassa', 'Magnim', 'Lidawa', 'Bassirou', 'Ibrahim', 'Fousséni', 'Aklesso', 'Kpatcha', 'Damtaré', 'Wiyaou'];
    $prenomsF = ['Akossiwa', 'Afi', 'Ama', 'Abla', 'Adjo', 'Akouvi', 'Essi', 'Mawuena', 'Sena', 'Yawa', 'Kafui', 'Massama', 'Essowè', 'Pélagie',
        'Rachida', 'Mariama', 'Délali', 'Enyonam', 'Pouwèdéou', 'Aïcha', 'Nafissatou', 'Hodalo'];
    $noms = ['AGBOKA', 'ASSOGBA', 'TCHALIM', 'PALI', 'BATAWILA', 'KPATCHA', 'AKAKPO', 'AMEGAN', 'DJOBO', 'KANTE', 'BAKALI', 'ATCHOLI', 'TCHAKONDO',
        'WALLA', 'PASSOM', 'KAOUNA', 'BOUKARI', 'ABALO', 'KOUMA', 'DOGBE', 'EDOH', 'MENSAH', 'AYITE', 'GBADOE', 'KOLANI', 'NABINE', 'POYODE',
        'TCHEDRE', 'SAMA', 'BODJONA', 'ESSONAM', 'KASSA', 'TAKOUGNADI', 'ALASSANI', 'DERMAN', 'BIGNA', 'LAWSON', 'ADJAMAGBO'];
    $lieux = ['Kara', 'Kara', 'Kara', 'Lomé', 'Lomé', 'Sokodé', 'Bassar', 'Niamtougou', 'Kanté', 'Pagouda', 'Bafilo', 'Dapaong', 'Atakpamé',
        'Kpalimé', 'Tchamba', 'Mango', 'Sotouboua', 'Tsévié', 'Guérin-Kouka', 'Kétao'];
    $cursus = [
        'Faculté des Sciences et Techniques' => ['Informatique', 'Mathématiques', 'Physique', 'Chimie', 'Biologie'],
        'Faculté de Droit et des Sciences Politiques' => ['Droit privé', 'Droit public', 'Science politique'],
        'Faculté des Sciences Économiques et de Gestion' => ['Économie', 'Gestion', 'Banque et finance'],
        'Faculté des Lettres, Langues et Arts' => ['Lettres modernes', 'Anglais', 'Allemand'],
        'Faculté des Sciences de l’Homme et de la Société' => ['Histoire', 'Géographie', 'Sociologie', 'Philosophie'],
        'Faculté des Sciences de la Santé' => ['Médecine', 'Sciences infirmières'],
    ];
    $niveaux = ['L1', 'L1', 'L2', 'L2', 'L3', 'L3', 'M1', 'M2'];
    $etudiants = [];
    $vusNoms = [];
    for ($i = 1; count($etudiants) < 150; $i++) {
        $femme = mt_rand(0, 1) === 1;
        $prenom = $hasard($femme ? $prenomsF : $prenomsH) . (mt_rand(0, 4) === 0 ? ' ' . $hasard($femme ? $prenomsF : $prenomsH) : '');
        $nom = $hasard($noms);
        if (isset($vusNoms["$nom $prenom"])) {
            continue;
        }
        $vusNoms["$nom $prenom"] = true;
        $niveau = $hasard($niveaux);
        $annee = (int) date('Y') - (int) substr($niveau, 1) - ($niveau[0] === 'M' ? 3 : 0) + 1;
        $faculte = $hasard(array_keys($cursus));
        $carte = sprintf('%d%06d', $annee, 100 + $i * 37 % 9000); // n° de suivi : chiffres uniquement
        if (val('SELECT 1 FROM etudiants WHERE numero_suivi = ?', [$carte])) {
            continue;
        }
        // La liste ne contient que l'identité ; faculté, parcours et téléphone sont ceux que l'étudiant donnera en créant son compte
        $e = [
            'numero_suivi' => $carte, 'nom' => $nom, 'prenom' => $prenom,
            'date_naissance' => sprintf('%04d-%02d-%02d', $annee - mt_rand(18, 22), mt_rand(1, 12), mt_rand(1, 28)),
            'lieu_naissance' => $hasard($lieux),
            'actif' => count($etudiants) < 145 ? 1 : 0, // les 5 derniers : désactivés par la bibliothèque (exemple)
            'importe_le' => $moment(-40, '08:00:00'),
        ];
        $e['id'] = noter('etudiants', insert('etudiants', $e));
        $etudiants[] = $e + ['faculte' => $faculte, 'parcours' => $hasard($cursus[$faculte]),
            'telephone' => sprintf('+228 9%d %02d %02d %02d', mt_rand(0, 9), mt_rand(0, 99), mt_rand(0, 99), mt_rand(0, 99))];
    }
    $bilan['étudiants (liste de l’université)'] = count($etudiants);

    /* ---------- Comptes ---------- */
    $email = function (string $prenom, string $nom): string {
        static $pris = [];
        $base = slugify(explode(' ', $prenom)[0]) . '.' . slugify($nom);
        for ($adresse = $base, $i = 2; isset($pris[$adresse]) || val('SELECT 1 FROM utilisateurs WHERE email = ?', [$adresse . '@' . DOMAINE_TEST]); $i++) {
            $adresse = $base . $i;
        }
        $pris[$adresse] = true;
        return $adresse . '@' . DOMAINE_TEST;
    };
    $comptes = []; // pour IDENTIFIANTS_TEST.txt
    $lecteur = function (array $champs) use ($moment): int {
        $id = noter('utilisateurs', insert('utilisateurs', $champs + ['role' => 'lecteur', 'actif' => 1, 'en_attente' => 0, 'cree_le' => $moment(-mt_rand(30, 200))]));
        return $id;
    };

    // Personnel de test : un compte par rôle (hors Administrateur et Système)
    $personnel = [];
    foreach (['Bibliothécaire' => ['Afi', 'BATAWILA'], 'Agent de prêt' => ['Kodjo', 'AMEGAN'], 'Éditeur du site' => ['Sena', 'DOGBE']] as $role => [$p, $n]) {
        $roleId = val('SELECT id FROM roles WHERE nom = ? AND systeme = 0', [$role]);
        if (!$roleId) {
            continue;
        }
        $adresse = slugify($role) . '@' . DOMAINE_TEST;
        if (val('SELECT 1 FROM utilisateurs WHERE email = ?', [$adresse])) {
            continue;
        }
        $personnel[$role] = noter('utilisateurs', insert('utilisateurs', ['role' => 'personnel', 'role_id' => $roleId, 'prenom' => $p, 'nom' => $n,
            'email' => $adresse, 'mot_de_passe' => $hash, 'actif' => 1]));
        $comptes[] = "Personnel — $role : $adresse";
    }
    $agent = $personnel['Bibliothécaire'] ?? $personnel['Agent de prêt'] ?? null;

    // Étudiants de la liste avec un compte en ligne (créé eux-mêmes) ou une simple fiche guichet
    $avecCompte = $ficheGuichet = [];
    foreach (array_slice($etudiants, 0, 22) as $k => $e) {
        $base = lecteur_depuis_etudiant($e);
        if ($k < 14) {
            $adresse = $email($e['prenom'], $e['nom']);
            $avecCompte[] = $lecteur($base + ['email' => $adresse, 'mot_de_passe' => $hash, 'faculte' => $e['faculte'], 'parcours' => $e['parcours'], 'telephone' => $e['telephone']]);
            if ($k < 3) {
                $comptes[] = 'Étudiant — ' . $e['prenom'] . ' ' . $e['nom'] . ' : ' . $adresse;
            }
        } else {
            $ficheGuichet[] = $lecteur($base); // emprunte au guichet, pas encore de compte en ligne
        }
    }
    // Enseignants, personnel de l'université, lecteurs externes
    $autres = [];
    foreach ([
        ['enseignant', 'Kossivi', 'ADJAMAGBO', 'ENS-0412', 'Faculté des Sciences et Techniques', 'Mathématiques'],
        ['enseignant', 'Essowè', 'TCHEDRE', 'ENS-0388', 'Faculté de Droit et des Sciences Politiques', 'Droit public'],
        ['enseignant', 'Mariama', 'ALASSANI', 'ENS-0521', 'Faculté des Lettres, Langues et Arts', 'Lettres modernes'],
        ['enseignant', 'Yao', 'LAWSON', 'ENS-0275', 'Faculté des Sciences Économiques et de Gestion', 'Économie'],
        ['personnel', 'Délali', 'BIGNA', 'PER-1104', '', ''],
        ['personnel', 'Abalo', 'KASSA', 'PER-1187', '', ''],
        ['externe', 'Pélagie', 'DERMAN', '', '', ''],
        ['externe', 'Ibrahim', 'SAMA', '', '', ''],
    ] as [$categorie, $p, $n, $matricule, $fac, $dep]) {
        $adresse = $email($p, $n);
        $autres[] = $lecteur(['categorie' => $categorie, 'prenom' => $p, 'nom' => $n, 'matricule' => $matricule ?: null, 'email' => $adresse,
            'mot_de_passe' => $hash, 'telephone' => '+228 90 ' . mt_rand(10, 99) . ' ' . mt_rand(10, 99) . ' ' . mt_rand(10, 99)]);
        if (count($autres) === 1 || $categorie === 'externe' && count($autres) === 7) {
            $comptes[] = ucfirst(LIBELLES_CATEGORIE[$categorie]) . " — $p $n : $adresse";
        }
    }
    // Demandes de compte en attente de validation, et un compte désactivé
    foreach ([
        ['enseignant', 'Hodalo', 'BODJONA', 'ENS-0610', '', ''],
        ['externe', 'Wiyaou', 'TAKOUGNADI', '', '', ''],
        ['etudiant', 'Nafissatou', 'BOUKARI', '2025007771', 'Faculté des Sciences de la Santé', 'Médecine'], // pas encore dans la liste
    ] as [$categorie, $p, $n, $matricule, $fac, $dep]) {
        $lecteur(['categorie' => $categorie, 'prenom' => $p, 'nom' => $n, 'matricule' => $matricule ?: null, 'email' => $email($p, $n),
            'mot_de_passe' => $hash, 'faculte' => $fac, 'parcours' => $dep, 'telephone' => '+228 91 ' . mt_rand(10, 99) . ' ' . mt_rand(10, 99) . ' 00',
            'actif' => 0, 'en_attente' => 1, 'cree_le' => $moment(-mt_rand(0, 3), '09:30:00')]);
    }
    $lecteur(['categorie' => 'externe', 'prenom' => 'Damtaré', 'nom' => 'KOLANI', 'email' => $email('Damtaré', 'KOLANI'), 'mot_de_passe' => $hash,
        'telephone' => '+228 92 00 00 00', 'actif' => 0]);
    $bilan['comptes (lecteurs et personnel)'] = count(ids('utilisateurs'));

    /* ---------- Prêts ---------- */
    $duree = reglage_int('pret_duree_jours', 14);
    $emprunteurs = array_merge($avecCompte, $ficheGuichet, array_slice($autres, 0, 6));
    $pretable = "SELECT e.id, e.ouvrage_id, e.etat FROM exemplaires e JOIN ouvrages o ON o.id = e.ouvrage_id
                 WHERE o.consultation_sur_place = 0 AND o.actif = 1 AND e.statut = 'disponible'";
    // D'abord les ouvrages à exemplaire unique (ils deviennent indisponibles : de quoi tester la file d'attente)
    $exemplaires = rows("$pretable ORDER BY (SELECT COUNT(*) FROM exemplaires e2 WHERE e2.ouvrage_id = e.ouvrage_id) = 1 DESC, e.id");
    $unique = array_values(array_filter($exemplaires, fn($x) => (int) val('SELECT COUNT(*) FROM exemplaires WHERE ouvrage_id = ?', [$x['ouvrage_id']]) === 1));
    $pris = [];
    $prendre = function (bool $uniqueDAbord = false) use (&$pris, $exemplaires, $unique) {
        foreach ($uniqueDAbord ? $unique : array_reverse($exemplaires) as $x) {
            if (!isset($pris[$x['id']])) {
                $pris[$x['id']] = true;
                return $x;
            }
        }
        return null;
    };
    // En cours : [jours depuis le prêt, prolongations] — échéance = prêt + durée (+ durée par prolongation)
    $enCours = [[30, 0], [21, 0], [16, 0], [13, 0], [12, 0], [10, 1], [9, 0], [7, 0], [5, 0], [4, 0], [3, 0], [2, 0], [1, 0], [0, 0], [18, 1]];
    $pretsEnCours = [];
    foreach ($enCours as $k => [$depuis, $prol]) {
        $x = ($k < 2 ? $prendre(true) : null) ?? $prendre();
        if (!$x) {
            break;
        }
        $lecteurId = $emprunteurs[$k % count($emprunteurs)];
        $echeance = $jour(-$depuis + $duree * (1 + $prol));
        $pretsEnCours[] = $pretId = noter('prets', insert('prets', [
            'exemplaire_id' => $x['id'], 'lecteur_id' => $lecteurId, 'agent_id' => $agent, 'date_pret' => $jour(-$depuis),
            'date_retour_prevue' => $echeance, 'prolongations' => $prol, 'etat_sortie' => $x['etat'],
            // Rappels et relances considérés comme faits aujourd'hui : pas d'envoi massif vers les adresses fictives
            'rappel_envoye_le' => $echeance <= $jour(reglage_int('rappel_jours_avant', 2)) ? $jour(0) : null,
            'relance_envoyee_le' => $echeance < $jour(0) ? $jour(0) : null,
        ]));
        q("UPDATE exemplaires SET statut = 'prete' WHERE id = ?", [$x['id']]);
    }
    // Rendus : six derniers mois, avec constat de retour
    $constats = array_merge(array_fill(0, 26, 'conforme'), ['use', 'use', 'use', 'abime', 'abime', 'perdu']);
    $notes = ['abime' => 'Couverture déchirée et pages cornées.', 'perdu' => 'Déclaré perdu par le lecteur ; remboursement convenu à l’accueil.'];
    foreach ($constats as $k => $etat) {
        $x = $exemplaires[($k * 7) % count($exemplaires)];
        $debut = -mt_rand(25, 180);
        $retour = min(-1, $debut + mt_rand(5, $duree + 6));
        $pretId = noter('prets', insert('prets', [
            'exemplaire_id' => $x['id'], 'lecteur_id' => $emprunteurs[($k * 5) % count($emprunteurs)], 'agent_id' => $agent,
            'date_pret' => $jour($debut), 'date_retour_prevue' => $jour($debut + $duree), 'date_retour' => $jour($retour),
            'etat_sortie' => 'bon', 'etat_retour' => $etat, 'note_retour' => $notes[$etat] ?? '', 'agent_retour_id' => $agent,
        ]));
        if ($etat === 'use' || $etat === 'abime') {
            q('UPDATE exemplaires SET etat = ? WHERE id = ?', [$etat, $x['id']]);
        }
        if ($etat === 'perdu' && !isset($pris[$x['id']])) {
            $pris[$x['id']] = true;
            q("UPDATE exemplaires SET statut = 'retire', notes = ? WHERE id = ?", ['Perdu le ' . date('d/m/Y', strtotime("$retour days")) . ' : ' . $notes['perdu'], $x['id']]);
        }
    }
    $bilan['prêts'] = count(ids('prets'));

    /* ---------- Réservations ---------- */
    $lecteursEnLigne = array_merge($avecCompte, array_slice($autres, 0, 4));
    // File d'attente sur les ouvrages devenus indisponibles
    $indisponibles = array_column(rows("SELECT o.id FROM ouvrages o WHERE o.consultation_sur_place = 0 AND o.actif = 1
        AND NOT EXISTS (SELECT 1 FROM exemplaires e WHERE e.ouvrage_id = o.id AND e.statut = 'disponible')
        AND EXISTS (SELECT 1 FROM exemplaires e WHERE e.ouvrage_id = o.id AND e.statut = 'prete') ORDER BY o.id LIMIT 2"), 'id');
    foreach ($indisponibles as $k => $ouvrageId) {
        foreach ([0, 1] as $rang) {
            $par = $lecteursEnLigne[(5 + $k * 2 + $rang) % count($lecteursEnLigne)];
            if (!val("SELECT 1 FROM prets p JOIN exemplaires e ON e.id = p.exemplaire_id WHERE p.lecteur_id = ? AND e.ouvrage_id = ? AND p.date_retour IS NULL", [$par, $ouvrageId])) {
                noter('reservations', insert('reservations', ['ouvrage_id' => $ouvrageId, 'lecteur_id' => $par, 'statut' => 'en_attente',
                    'demandee_le' => $moment(-3 + $rang, '1' . (1 + $rang) . ':15:00')]));
            }
        }
    }
    // Exemplaires mis de côté, à retirer à l'accueil
    foreach ([0, 1] as $k) {
        $x = $prendre();
        if ($x) {
            q("UPDATE exemplaires SET statut = 'reserve' WHERE id = ?", [$x['id']]);
            noter('reservations', insert('reservations', ['ouvrage_id' => $x['ouvrage_id'], 'lecteur_id' => $lecteursEnLigne[9 + $k], 'exemplaire_id' => $x['id'],
                'statut' => 'disponible', 'demandee_le' => $moment(-2 - $k), 'disponible_le' => $moment(-1 - $k, '15:00:00'),
                'expire_le' => $jour(reglage_int('reservation_delai_jours', 3) - 1 - $k)]));
        }
    }
    // Historique : honorées, annulées, expirées
    foreach (['honoree', 'honoree', 'honoree', 'annulee', 'annulee', 'expiree'] as $k => $statut) {
        $x = $exemplaires[($k * 11 + 3) % count($exemplaires)];
        noter('reservations', insert('reservations', ['ouvrage_id' => $x['ouvrage_id'], 'lecteur_id' => $lecteursEnLigne[$k % count($lecteursEnLigne)],
            'statut' => $statut, 'demandee_le' => $moment(-60 - $k * 9), 'disponible_le' => $statut === 'annulee' ? null : $moment(-58 - $k * 9),
            'expire_le' => $statut === 'annulee' ? null : $jour(-55 - $k * 9)]));
    }
    $bilan['réservations'] = count(ids('reservations'));

    /* ---------- Demandes de prolongation (prêts en cours des lecteurs ayant un compte) ---------- */
    $pretsEnLigne = rows('SELECT p.* FROM prets p JOIN utilisateurs u ON u.id = p.lecteur_id WHERE p.id IN (' . liste_in($pretsEnCours) . ")
                          AND u.mot_de_passe <> '' ORDER BY p.date_retour_prevue");
    $motifs = ['Mémoire en cours de rédaction, j’ai encore besoin de l’ouvrage.', 'Examens la semaine prochaine.', 'Exposé à préparer pour le TD.',
        'Je suis en stage hors de Kara jusqu’à la fin du mois.', 'Lecture pas encore terminée.', 'Travail de groupe avec d’autres étudiants.'];
    foreach (array_slice($pretsEnLigne, 0, 6) as $k => $p) {
        $statut = ['en_attente', 'en_attente', 'en_attente', 'acceptee', 'acceptee', 'refusee'][$k];
        $nouvelle = $statut === 'acceptee' ? plus_jours($p['date_retour_prevue'], $duree) : null;
        noter('demandes_prolongation', insert('demandes_prolongation', [
            'pret_id' => $p['id'], 'motif' => $motifs[$k], 'statut' => $statut, 'demandee_le' => $moment(-1 - $k % 2, '08:4' . $k . ':00'),
            'traitee_le' => $statut === 'en_attente' ? null : $moment(0, '09:00:00'), 'agent_id' => $statut === 'en_attente' ? null : $agent,
            'reponse' => $statut === 'refusee' ? 'Ouvrage réservé par d’autres lecteurs : merci de le rendre à la date prévue.' : '',
            'nouvelle_date' => $nouvelle,
        ]));
        if ($nouvelle) {
            q('UPDATE prets SET date_retour_prevue = ?, prolongations = prolongations + 1 WHERE id = ?', [$nouvelle, $p['id']]);
        }
    }
    $bilan['demandes de prolongation'] = count(ids('demandes_prolongation'));

    /* ---------- Messages reçus ---------- */
    foreach ([
        ['Kossi AGBOKA', 'Horaires pendant les examens', 'Bonjour, la bibliothèque sera-t-elle ouverte le samedi pendant la période des examens ? Merci.', 0],
        ['Akouvi MENSAH', 'Ouvrage introuvable au rayon', 'Le catalogue indique que « Réseaux » est disponible mais je ne l’ai pas trouvé au rayon informatique.', 0],
        ['Yao LAWSON', 'Suggestion d’acquisition', 'Je suggère l’acquisition du manuel « Microéconomie » de Pindyck et Rubinfeld pour les étudiants de L2.', 1],
        ['Pélagie DERMAN', 'Inscription d’un lecteur externe', 'Bonjour, quelles pièces faut-il fournir pour s’inscrire comme lectrice externe ?', 1],
        ['Edem KPATCHA', 'Mot de passe oublié', 'J’ai oublié le mot de passe de mon espace lecteur. Comment le récupérer ?', 0],
    ] as $k => [$nom, $sujet, $texte, $lu]) {
        noter('messages', insert('messages', ['nom' => $nom, 'email' => slugify($nom) . '@' . DOMAINE_TEST, 'sujet' => $sujet,
            'message' => $texte, 'lu' => $lu, 'recu_le' => $moment(-$k * 3, '1' . $k . ':20:00')]));
    }

    /* ---------- Diaporama et citations de l'accueil (s'ils sont vides) ---------- */
    $sql = file_get_contents(__DIR__ . '/contenu_accueil.sql');
    foreach (['slides' => '/INSERT INTO slides .*?;\n/s', 'citations' => '/INSERT INTO citations .*?;\n/s'] as $table => $motif) {
        if (!val("SELECT COUNT(*) FROM $table") && preg_match($motif, $sql, $m)) {
            $avant = (int) val("SELECT COALESCE(MAX(id), 0) FROM $table");
            db()->exec($m[0]);
            foreach (rows("SELECT id FROM $table WHERE id > ?", [$avant]) as $r) {
                noter($table, (int) $r['id']);
            }
        }
    }
    $bilan['diapositives'] = count(ids('slides'));
    $bilan['citations'] = count(ids('citations'));

    enregistrer_suivi();
    db()->commit();
} catch (Throwable $t) {
    db()->rollBack();
    throw $t;
}

/* ---------- Identifiants de test ---------- */
$sansCompte = array_slice(array_filter(array_slice($etudiants, 22), fn($e) => $e['actif']), 0, 3);
$texte = "Comptes de TEST de la bibliothèque — créés par install/donnees_test.php le " . date('d/m/Y H:i') . "\n"
    . "À retirer avant la mise en service réelle :  php install/donnees_test.php --retirer\n\n"
    . "Mot de passe de tous les comptes de test : $motDePasse\n\n"
    . "Quelques comptes (tous les lecteurs de test ont une adresse en @" . DOMAINE_TEST . ") :\n- " . implode("\n- ", $comptes) . "\n\n"
    . "Pour essayer la création de compte d'un étudiant (Espace lecteur → Créer un compte → Étudiant de l'université),\n"
    . "ces étudiants de la liste n'ont pas encore de compte :\n";
foreach ($sansCompte as $e) {
    $texte .= sprintf("- n° de suivi %s : %s %s, né(e) le %s à %s (faculté et parcours au choix)\n", $e['numero_suivi'], $e['nom'], $e['prenom'], date('d/m/Y', strtotime($e['date_naissance'])), $e['lieu_naissance']);
}
$texte .= "\nAu guichet, tapez par exemple « " . $etudiants[30]['nom'] . " » dans le choix du lecteur : les étudiants de la liste sans compte apparaissent aussi.\n";
file_put_contents($fichierIdentifiants, $texte);
@chmod($fichierIdentifiants, 0600);

echo "Données de test ajoutées :\n";
foreach ($bilan as $quoi => $nb) {
    echo "  $quoi : $nb\n";
}
echo "\nIdentifiants des comptes de test : install/IDENTIFIANTS_TEST.txt\n";
echo "Pour tout retirer plus tard : php install/donnees_test.php --retirer\n";
