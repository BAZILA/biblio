<?php
/**
 * Liste officielle des étudiants de l'université (table etudiants), chargée et mise à jour par import :
 * n° de suivi, nom, prénoms, date et lieu de naissance — rien d'autre.
 *
 * - Inscription en ligne : un étudiant de la liste crée seul son compte ; son identité est vérifiée contre la liste,
 *   sans validation par la bibliothèque. Il complète lui-même faculté, parcours, e-mail et mot de passe.
 * - Guichet : les étudiants de la liste sans fiche lecteur peuvent emprunter ; leur fiche est créée au premier prêt.
 * - Un rechargement n'enlève jamais personne. La bibliothèque peut désactiver (et réactiver) des étudiants de la liste :
 *   ils ne peuvent alors plus créer de compte ni être choisis au guichet sans fiche ; les comptes existants ne changent pas.
 * Le lien avec la fiche lecteur est le matricule (utilisateurs.matricule = etudiants.numero_suivi).
 */

/** N° de suivi comparable : sans espaces (« 2024 000 125 » = « 2024000125 »). Les zéros en tête sont conservés. */
function normaliser_suivi(string $numero): string
{
    return mb_strtoupper(preg_replace('/\s+/u', '', $numero));
}

/** Un n° de suivi ne contient que des chiffres. */
function suivi_valide(string $numero): bool
{
    $numero = normaliser_suivi($numero);
    return $numero !== '' && ctype_digit($numero);
}

/** Nom ou lieu comparable : minuscules, sans accents, tirets, apostrophes ni ordre des mots (« KOFFI Ama-Rose » = « ama rose koffi »). */
function cle_identite(string $texte): string
{
    $mots = explode('-', slugify($texte));
    sort($mots);
    return implode(' ', $mots);
}

/** Date saisie ou importée (31/12/2004, 31-12-2004, 2004-12-31) vers AAAA-MM-JJ ; null si invalide. */
function date_naissance_iso(string $texte): ?string
{
    $texte = trim($texte);
    if (preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})$/', $texte, $m)) {
        [, $a, $mo, $j] = $m;
    } elseif (preg_match('#^(\d{1,2})[/.\-](\d{1,2})[/.\-](\d{4})$#', $texte, $m)) {
        [, $j, $mo, $a] = $m;
    } else {
        return null;
    }
    return checkdate((int) $mo, (int) $j, (int) $a) && (int) $a > 1900 ? sprintf('%04d-%02d-%02d', $a, $mo, $j) : null;
}

/** Fiche lecteur rattachée à un n° de suivi (ou null). */
function lecteur_par_suivi(string $numero): ?array
{
    return row("SELECT * FROM utilisateurs WHERE role = 'lecteur' AND matricule = ?", [normaliser_suivi($numero)]);
}

/**
 * Vérifie l'identité saisie à l'inscription contre la liste des étudiants.
 * Renvoie l'étudiant trouvé, ou un message d'erreur volontairement général (on ne dit pas quel champ diffère).
 */
function verifier_etudiant(array $s): array|string
{
    $e = row('SELECT * FROM etudiants WHERE numero_suivi = ?', [normaliser_suivi($s['numero_suivi'])]);
    $concorde = $e
        && cle_identite($e['nom']) === cle_identite($s['nom'])
        && cle_identite($e['prenom']) === cle_identite($s['prenom'])
        && $e['date_naissance'] === date_naissance_iso($s['date_naissance'])
        && cle_identite($e['lieu_naissance']) === cle_identite($s['lieu_naissance']);
    if (!$concorde) {
        return 'Ces informations ne correspondent à aucun étudiant de la liste. Vérifiez votre n° de suivi, vos nom et prénoms, '
            . 'votre date et votre lieu de naissance (tels qu’enregistrés par la scolarité).';
    }
    if (!$e['actif']) {
        return 'Votre n° de suivi a été désactivé par la bibliothèque : présentez-vous à l’accueil.';
    }
    return $e;
}

/** Colonnes d'une fiche lecteur tirées de la liste des étudiants (faculté et parcours sont complétés par l'étudiant). */
function lecteur_depuis_etudiant(array $e): array
{
    return ['role' => 'lecteur', 'categorie' => 'etudiant', 'matricule' => normaliser_suivi($e['numero_suivi']), 'nom' => $e['nom'], 'prenom' => $e['prenom']];
}

/**
 * Fiche lecteur d'un étudiant de la liste, créée si besoin (sans accès en ligne) : sert au prêt au guichet.
 * Renvoie l'identifiant du lecteur.
 */
function lecteur_pour_etudiant(int $etudiantId): int
{
    $e = row('SELECT * FROM etudiants WHERE id = ?', [$etudiantId]);
    if (!$e) {
        throw new RuntimeException('Étudiant introuvable dans la liste de l’université.');
    }
    $l = lecteur_par_suivi($e['numero_suivi']);
    if ($l) {
        return (int) $l['id'];
    }
    if (!$e['actif']) {
        throw new RuntimeException('Cet étudiant est désactivé dans la liste de l’université : réactivez-le d’abord (rubrique Étudiants).');
    }
    return insert('utilisateurs', lecteur_depuis_etudiant($e) + ['actif' => 1, 'en_attente' => 0]);
}

/**
 * Étudiants actifs de la liste qui n'ont pas de fiche lecteur, cherchés par nom, prénom ou n° de suivi.
 * Ceux qui ont une fiche apparaissent déjà parmi les lecteurs.
 */
function etudiants_sans_fiche(string $q, int $limite = 15): array
{
    $mots = array_filter(preg_split('/\s+/u', trim($q)) ?: [], fn($m) => mb_strlen($m) >= 2);
    if (!$mots) {
        return [];
    }
    $conds = [];
    $params = [];
    foreach ($mots as $m) {
        $conds[] = '(e.nom LIKE ? OR e.prenom LIKE ? OR e.numero_suivi LIKE ?)';
        array_push($params, "%$m%", "%$m%", "%$m%");
    }
    return rows("SELECT e.id, e.numero_suivi, e.nom, e.prenom FROM etudiants e
                 WHERE e.actif = 1 AND " . implode(' AND ', $conds) . "
                   AND NOT EXISTS (SELECT 1 FROM utilisateurs u WHERE u.role = 'lecteur' AND u.matricule = e.numero_suivi)
                 ORDER BY e.nom, e.prenom LIMIT " . (int) $limite, $params);
}

/** Active ou désactive des étudiants de la liste ; renvoie le nombre de lignes changées. */
function changer_activation_etudiants(array $ids, bool $actif): int
{
    $ids = array_values(array_filter(array_map('intval', $ids)));
    if (!$ids) {
        return 0;
    }
    return q('UPDATE etudiants SET actif = ?, modifie_le = NOW() WHERE actif <> ? AND id IN (' . implode(',', array_fill(0, count($ids), '?')) . ')',
        [(int) $actif, (int) $actif, ...$ids])->rowCount();
}

/* ---------- Import de la liste (Importer un fichier → Étudiants) ---------- */

/** Colonnes reconnues : nom normalisé de l'en-tête → champ. Les autres colonnes du fichier sont ignorées. */
const IMPORT_COLONNES_ETUDIANTS = [
    'numero_suivi' => 'numero_suivi', 'numero_suivie' => 'numero_suivi', 'numero_de_suivi' => 'numero_suivi', 'n_suivi' => 'numero_suivi',
    'no_suivi' => 'numero_suivi', 'n_de_suivi' => 'numero_suivi', 'num_suivi' => 'numero_suivi', 'suivi' => 'numero_suivi',
    'nom' => 'nom', 'nom_de_famille' => 'nom',
    'prenom' => 'prenom', 'prenoms' => 'prenom',
    'date_naissance' => 'date_naissance', 'date_de_naissance' => 'date_naissance', 'ne_le' => 'date_naissance', 'nee_le' => 'date_naissance',
    'lieu_naissance' => 'lieu_naissance', 'lieu_de_naissance' => 'lieu_naissance', 'ne_a' => 'lieu_naissance', 'nee_a' => 'lieu_naissance',
];
const CHAMPS_ETUDIANT = ['numero_suivi', 'nom', 'prenom', 'date_naissance', 'lieu_naissance'];
const LIBELLES_CHAMPS_ETUDIANT = ['numero_suivi' => 'n° de suivi', 'nom' => 'nom', 'prenom' => 'prénoms', 'date_naissance' => 'date de naissance', 'lieu_naissance' => 'lieu de naissance'];

/**
 * Analyse la liste : une ligne d'en-têtes puis un étudiant par ligne.
 * Statuts : nouveau (ajouté), maj (fiche corrigée), ignore (identique ou en double), erreur.
 */
function analyser_etudiants(array $lignes): array
{
    $entetes = array_map(fn($h) => IMPORT_COLONNES_ETUDIANTS[normaliser_entete($h)] ?? null, array_shift($lignes));
    $manquantes = array_diff(CHAMPS_ETUDIANT, $entetes);
    if ($manquantes) {
        throw new RuntimeException('Colonnes absentes de la première ligne : ' . implode(', ', $manquantes)
            . '. Attendu : numero_suivi ; nom ; prenom ; date_naissance ; lieu_naissance (téléchargez le modèle).');
    }
    $existants = [];
    foreach (rows('SELECT * FROM etudiants') as $e) {
        $existants[$e['numero_suivi']] = $e;
    }
    $resultat = [];
    $vus = [];
    foreach ($lignes as $i => $cellules) {
        $v = array_fill_keys(CHAMPS_ETUDIANT, '');
        foreach ($entetes as $j => $champ) {
            if ($champ !== null && $v[$champ] === '') {
                $v[$champ] = preg_replace('/\s+/u', ' ', trim($cellules[$j] ?? ''));
            }
        }
        $v['numero_suivi'] = normaliser_suivi($v['numero_suivi']);
        $messages = [];
        if (preg_match('/^\d+([.,]\d+)?E\+?\d+$/i', $v['numero_suivi'])) {
            $messages[] = 'N° de suivi écrit par Excel en notation scientifique (' . $v['numero_suivi'] . ') : mettez la colonne au format « Texte » puis enregistrez de nouveau.';
        } elseif ($v['numero_suivi'] !== '' && !suivi_valide($v['numero_suivi'])) {
            $messages[] = 'N° de suivi invalide (chiffres uniquement).';
        }
        foreach (['numero_suivi' => 'N° de suivi', 'nom' => 'Nom', 'prenom' => 'Prénom', 'lieu_naissance' => 'Lieu de naissance'] as $k => $lib) {
            if ($v[$k] === '') {
                $messages[] = "$lib manquant.";
            }
        }
        $date = date_naissance_iso($v['date_naissance']);
        if ($date === null) {
            $messages[] = 'Date de naissance invalide (JJ/MM/AAAA attendu).';
        }
        $v['date_naissance'] = (string) $date;
        foreach (['numero_suivi' => 50, 'nom' => 100, 'prenom' => 150, 'lieu_naissance' => 150] as $k => $max) {
            $v[$k] = mb_substr($v[$k], 0, $max);
        }

        if ($messages) {
            $statut = 'erreur';
        } elseif (isset($vus[$v['numero_suivi']])) {
            [$statut, $messages] = ['ignore', ['N° de suivi en double dans le fichier (ligne ' . $vus[$v['numero_suivi']] . ').']];
        } elseif (!isset($existants[$v['numero_suivi']])) {
            $statut = 'nouveau';
        } else {
            $ancien = $existants[$v['numero_suivi']];
            $changes = array_filter(CHAMPS_ETUDIANT, fn($k) => (string) $ancien[$k] !== $v[$k]);
            $statut = $changes ? 'maj' : 'ignore';
            $messages[] = $changes ? 'Corrigé : ' . implode(', ', array_map(fn($k) => LIBELLES_CHAMPS_ETUDIANT[$k], $changes)) . '.' : 'Déjà à jour.';
            if (!$ancien['actif']) {
                $messages[] = 'Désactivé dans la liste (réactivable depuis la rubrique Étudiants).';
            }
        }
        $vus[$v['numero_suivi']] ??= $i + 2;
        $resultat[] = ['ligne' => $i + 2, 'statut' => $statut, 'message' => implode(' ', $messages)] + $v;
    }
    return $resultat;
}

/**
 * Enregistre la liste analysée : ajoute les nouveaux, corrige les fiches modifiées.
 * Personne n'est retiré ni désactivé : un étudiant absent du fichier reste dans la liste avec ses droits.
 * Les fiches lecteurs déjà liées reprennent le nom et les prénoms corrigés.
 */
function importer_etudiants(array $analyse): array
{
    $bilan = ['nouveaux' => 0, 'maj' => 0];
    db()->beginTransaction();
    try {
        foreach ($analyse as $l) {
            $donnees = array_intersect_key($l, array_flip(CHAMPS_ETUDIANT));
            if ($l['statut'] === 'nouveau') {
                insert('etudiants', $donnees);
                $bilan['nouveaux']++;
            } elseif ($l['statut'] === 'maj') {
                q('UPDATE etudiants SET nom = ?, prenom = ?, date_naissance = ?, lieu_naissance = ?, modifie_le = NOW() WHERE numero_suivi = ?',
                    [$l['nom'], $l['prenom'], $l['date_naissance'], $l['lieu_naissance'], $l['numero_suivi']]);
                q("UPDATE utilisateurs SET nom = ?, prenom = ? WHERE role = 'lecteur' AND matricule = ?", [$l['nom'], $l['prenom'], $l['numero_suivi']]);
                $bilan['maj']++;
            }
        }
        db()->commit();
    } catch (Throwable $t) {
        db()->rollBack();
        throw $t;
    }
    return $bilan;
}

function modele_import_etudiants(): string
{
    return "numero_suivi;nom;prenom;date_naissance;lieu_naissance\n"
        . "2024000125;KOFFI;Ama Rose;14/03/2004;Kara\n"
        . "2023000872;PALI;Essohanam;02/11/2002;Bassar\n";
}

/** Adresse e-mail partiellement masquée (« a••••@gmail.com ») : rappel d'identifiant sans la dévoiler. */
function masquer_email(string $email): string
{
    [$nom, $domaine] = explode('@', $email, 2) + [1 => ''];
    return mb_substr($nom, 0, 1) . str_repeat('•', max(3, mb_strlen($nom) - 1)) . '@' . $domaine;
}
