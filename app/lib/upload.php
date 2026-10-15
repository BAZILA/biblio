<?php
/** Envoi d'images depuis l'administration (couvertures, diaporama, avatars). */

const IMAGES_AUTORISEES = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp',
    'image/gif'  => 'gif',
];
const IMAGE_TAILLE_MAX = 5 * 1024 * 1024;

/**
 * Supprime du disque une couverture d'ouvrage qui n'est plus utilisée par aucun ouvrage.
 * Ne touche qu'aux fichiers de images/ouvrages/.
 */
function supprimer_couverture(string $chemin): void
{
    if (!preg_match('#^images/ouvrages/[A-Za-z0-9._-]+$#', $chemin)) {
        return;
    }
    if (val('SELECT COUNT(*) FROM ouvrages WHERE image = ?', [$chemin])) {
        return;
    }
    $fichier = ROOT_DIR . '/' . $chemin;
    if (is_file($fichier)) {
        @unlink($fichier);
    }
}

/**
 * Enregistre l'image envoyée dans le champ $champ sous images/$dossier/.
 * Renvoie le chemin relatif (« images/ouvrages/titre.jpg »), null si aucun fichier,
 * ou lève une RuntimeException si le fichier est refusé.
 */
function televerser_image(string $champ, string $dossier, string $nomBase): ?string
{
    $f = $_FILES[$champ] ?? null;
    if (!$f || $f['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($f['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('L’envoi de l’image a échoué (fichier trop lourd ?).');
    }
    if ($f['size'] > IMAGE_TAILLE_MAX) {
        throw new RuntimeException('Image trop lourde : 5 Mo maximum.');
    }
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
    if (!isset(IMAGES_AUTORISEES[$mime]) || @getimagesize($f['tmp_name']) === false) {
        throw new RuntimeException('Format non accepté : JPG, PNG, WebP ou GIF uniquement.');
    }

    $dossier = trim(preg_replace('/[^a-z0-9_-]/', '', $dossier), '/');
    $repertoire = ROOT_DIR . '/images/' . $dossier;
    if (!is_dir($repertoire) && !mkdir($repertoire, 0775, true)) {
        throw new RuntimeException('Dossier d’images inaccessible.');
    }
    $base = slugify($nomBase);
    $ext  = IMAGES_AUTORISEES[$mime];
    $nom  = "$base.$ext";
    for ($i = 2; is_file("$repertoire/$nom"); $i++) {
        $nom = "$base-$i.$ext";
    }
    if (!move_uploaded_file($f['tmp_name'], "$repertoire/$nom")) {
        throw new RuntimeException('Impossible d’enregistrer l’image.');
    }
    return "images/$dossier/$nom";
}
