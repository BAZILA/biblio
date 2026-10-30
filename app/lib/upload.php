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

const VIDEOS_AUTORISEES = ['video/mp4' => 'mp4', 'video/webm' => 'webm'];

/** Taille maximale d'une vidéo : la plus petite des limites de PHP (upload_max_filesize, post_max_size). */
function video_taille_max(): int
{
    $octets = function (string $v): int {
        $n = (int) $v;
        return match (strtolower(substr(trim($v), -1))) { 'g' => $n << 30, 'm' => $n << 20, 'k' => $n << 10, default => $n };
    };
    return min($octets((string) ini_get('upload_max_filesize')), $octets((string) ini_get('post_max_size')) ?: PHP_INT_MAX);
}

/**
 * Enregistre la vidéo envoyée (mp4 ou webm) sous images/videos/. Renvoie le chemin relatif, null si aucun fichier,
 * ou lève une RuntimeException si le fichier est refusé.
 */
function televerser_video(string $champ, string $nomBase): ?string
{
    $f = $_FILES[$champ] ?? null;
    if (!$f || $f['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    $max = round(video_taille_max() / 1048576);
    if (in_array($f['error'], [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)) {
        throw new RuntimeException("Vidéo trop lourde : $max Mo maximum sur ce serveur (raccourcissez-la ou compressez-la).");
    }
    if ($f['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('L’envoi de la vidéo a échoué.');
    }
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
    if (!isset(VIDEOS_AUTORISEES[$mime])) {
        throw new RuntimeException('Format de vidéo non accepté : MP4 ou WebM uniquement.');
    }
    $repertoire = ROOT_DIR . '/images/videos';
    if (!is_dir($repertoire) && !mkdir($repertoire, 0755, true)) {
        throw new RuntimeException('Dossier des vidéos inaccessible.');
    }
    $base = slugify($nomBase);
    $ext = VIDEOS_AUTORISEES[$mime];
    $nom = "$base.$ext";
    for ($i = 2; is_file("$repertoire/$nom"); $i++) {
        $nom = "$base-$i.$ext";
    }
    if (!move_uploaded_file($f['tmp_name'], "$repertoire/$nom")) {
        throw new RuntimeException('Impossible d’enregistrer la vidéo.');
    }
    return "images/videos/$nom";
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
    if (!is_dir($repertoire) && !mkdir($repertoire, 0755, true)) {
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

const DOCUMENTS_AUTORISES = ['application/pdf' => 'pdf'];
const DOCUMENT_TAILLE_MAX = 8 * 1024 * 1024;

/**
 * Téléverse un document PDF (charte du lecteur, règlement) dans images/documents et renvoie son chemin.
 * Renvoie null si aucun fichier n'a été envoyé.
 */
function televerser_document(string $champ, string $nomBase): ?string
{
    $f = $_FILES[$champ] ?? null;
    if (!$f || $f['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($f['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('L’envoi du document a échoué (fichier trop lourd ?).');
    }
    if ($f['size'] > DOCUMENT_TAILLE_MAX) {
        throw new RuntimeException('Document trop lourd : 8 Mo maximum.');
    }
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
    if (!isset(DOCUMENTS_AUTORISES[$mime])) {
        throw new RuntimeException('Format non accepté : PDF uniquement.');
    }
    $repertoire = ROOT_DIR . '/images/documents';
    if (!is_dir($repertoire) && !mkdir($repertoire, 0755, true)) {
        throw new RuntimeException('Dossier des documents inaccessible.');
    }
    $base = slugify($nomBase) ?: 'document';
    $nom = "$base.pdf";
    for ($i = 2; is_file("$repertoire/$nom"); $i++) {
        $nom = "$base-$i.pdf";
    }
    if (!move_uploaded_file($f['tmp_name'], "$repertoire/$nom")) {
        throw new RuntimeException('Impossible d’enregistrer le document.');
    }
    return "images/documents/$nom";
}
