<?php
/**
 * Petit générateur de PDF sans dépendance, pour les rapports de l'administration.
 *
 * Mise en page en millimètres (origine en haut à gauche), polices standard Helvetica / Helvetica-Bold
 * (encodage Windows-1252 : accents français pris en charge), tableaux paginés avec en-tête répété,
 * barres de graphique, et une image JPEG (le logo, seul élément en couleur). Tout le reste est en noir et gris.
 */
final class Pdf
{
    private const K = 72 / 25.4; // points par millimètre

    /** Largeurs Helvetica / Helvetica-Bold (millièmes d'em) des caractères ASCII 32 à 126. */
    private const LARGEURS = [
        false => [278, 278, 355, 556, 556, 889, 667, 191, 333, 333, 389, 584, 278, 333, 278, 278, 556, 556, 556, 556, 556, 556, 556, 556,
            556, 556, 278, 278, 584, 584, 584, 556, 1015, 667, 667, 722, 722, 667, 611, 778, 722, 278, 500, 667, 556, 833, 722, 778,
            667, 778, 722, 667, 611, 722, 667, 944, 667, 667, 611, 278, 278, 278, 469, 556, 333, 556, 556, 500, 556, 556, 278, 556,
            556, 222, 222, 500, 222, 833, 556, 556, 556, 556, 333, 500, 278, 556, 500, 722, 500, 500, 500, 334, 260, 334, 584],
        true => [278, 333, 474, 556, 556, 889, 722, 238, 333, 333, 389, 584, 278, 333, 278, 278, 556, 556, 556, 556, 556, 556, 556, 556,
            556, 556, 333, 333, 584, 584, 584, 611, 975, 722, 722, 722, 722, 667, 611, 778, 722, 278, 556, 722, 611, 833, 722, 778,
            667, 778, 722, 667, 611, 722, 667, 944, 667, 667, 611, 333, 278, 333, 584, 556, 333, 556, 611, 556, 611, 556, 333, 611,
            611, 278, 278, 556, 278, 889, 611, 611, 611, 611, 389, 556, 333, 611, 556, 778, 556, 556, 500, 389, 280, 389, 584],
    ];

    private array $pages = [];
    private string $flux = '';
    private float $y = 0;
    private ?array $logo = null;
    private bool $paysage;
    private float $l;   // largeur de page (mm)
    private float $h;   // hauteur de page (mm)
    private float $marge = 15;

    /** @param callable|null $entete fonction appelée en haut de chaque page ($pdf) */
    public function __construct(private string $titre, private string $sousTitre = '', bool $paysage = false, private ?string $cheminLogo = null)
    {
        $this->paysage = $paysage;
        [$this->l, $this->h] = $paysage ? [297, 210] : [210, 297];
        if ($cheminLogo && is_file($cheminLogo)) {
            $this->logo = self::jpegDepuisImage($cheminLogo);
        }
        $this->nouvellePage();
    }

    public function largeurUtile(): float
    {
        return $this->l - 2 * $this->marge;
    }

    public function y(): float
    {
        return $this->y;
    }

    /* ---------- Pages ---------- */

    public function nouvellePage(): void
    {
        if ($this->flux !== '') {
            $this->pages[] = $this->flux;
        }
        $this->flux = '';
        $m = $this->marge;
        // En-tête : logo (couleur), titre et sous-titre en noir, filet gris
        $x = $m;
        if ($this->logo) {
            $hl = 12;
            $ll = $hl * $this->logo['l'] / $this->logo['h'];
            $this->flux .= sprintf("q %.2F 0 0 %.2F %.2F %.2F cm /Im1 Do Q\n", $ll * self::K, $hl * self::K, $m * self::K, ($this->h - $m - $hl) * self::K);
            $x = $m + $ll + 6;
        }
        $this->texte($x, $m + 4.5, $this->titre, 13, true);
        if ($this->sousTitre !== '') {
            $this->texte($x, $m + 10, $this->sousTitre, 9, false, 0.35);
        }
        $this->ligne($m, $m + 15, $this->l - $m, $m + 15, 0.3, 0.6);
        // Pied de page : date d'édition et pagination (le total est remplacé à la fin)
        $this->ligne($m, $this->h - 12, $this->l - $m, $this->h - 12, 0.2, 0.75);
        $this->texte($m, $this->h - 8, 'Édité le ' . date('d/m/Y à H:i') . ' — ' . reglage('site_nom', 'Bibliothèque'), 7.5, false, 0.4);
        $pied = 'Page ' . (count($this->pages) + 1) . ' / {nb}';
        $this->texte($this->l - $m - $this->largeurTexte($pied, 7.5, false), $this->h - 8, $pied, 7.5, false, 0.4);
        $this->y = $m + 22;
    }

    /** Passe à la page suivante si $hauteur ne tient plus. Renvoie vrai si une page a été ajoutée. */
    public function place(float $hauteur): bool
    {
        if ($this->y + $hauteur > $this->h - 16) {
            $this->nouvellePage();
            return true;
        }
        return false;
    }

    /* ---------- Contenu ---------- */

    public function titreSection(string $texte): void
    {
        $this->place(14);
        $this->y += 2;
        $this->texte($this->marge, $this->y + 4, $texte, 11, true);
        $this->ligne($this->marge, $this->y + 6.5, $this->marge + 40, $this->y + 6.5, 0.6, 0);
        $this->y += 11;
    }

    /** Paragraphe avec retour à la ligne automatique. */
    public function paragraphe(string $texte, float $taille = 9.5, bool $gras = false, float $gris = 0.15): void
    {
        foreach ($this->couper($texte, $this->largeurUtile(), $taille, $gras) as $ligne) {
            $this->place($taille * 0.5);
            $this->texte($this->marge, $this->y + $taille * 0.35, $ligne, $taille, $gras, $gris);
            $this->y += $taille * 0.48;
        }
        $this->y += 2;
    }

    public function espace(float $mm): void
    {
        $this->y += $mm;
    }

    /**
     * Chiffres clés en tuiles encadrées : [[libellé, valeur], ...], $parLigne tuiles par ligne.
     */
    public function chiffres(array $tuiles, int $parLigne = 4): void
    {
        $ecart = 4;
        $lt = ($this->largeurUtile() - ($parLigne - 1) * $ecart) / $parLigne;
        foreach (array_chunk($tuiles, $parLigne) as $rang) {
            $this->place(19);
            foreach ($rang as $i => [$libelle, $valeur]) {
                $x = $this->marge + $i * ($lt + $ecart);
                $this->rectangle($x, $this->y, $lt, 16, null, 0.7);
                $this->texte($x + 3, $this->y + 7, (string) $valeur, 14, true);
                $this->texte($x + 3, $this->y + 12.5, $this->tronquer($libelle, $lt - 6, 7.5, false), 7.5, false, 0.35);
            }
            $this->y += 20;
        }
    }

    /**
     * Tableau paginé. $colonnes : [[titre, largeur relative, 'G'|'D'|'C'], ...] ; $lignes : listes de cellules (texte).
     * La dernière ligne peut être un total en gras si $total est vrai.
     */
    public function tableau(array $colonnes, array $lignes, bool $total = false, float $taille = 8.5): void
    {
        $somme = array_sum(array_column($colonnes, 1));
        $largeurs = array_map(fn($c) => $c[1] / $somme * $this->largeurUtile(), $colonnes);
        $hl = $taille * 0.62;
        $entete = function () use ($colonnes, $largeurs, $hl, $taille) {
            $x = $this->marge;
            $this->rectangle($this->marge, $this->y, $this->largeurUtile(), $hl + 1, 0.9, null);
            foreach ($colonnes as $i => [$titre, , $align]) {
                $this->cellule($x, $this->y, $largeurs[$i], $hl + 1, $titre, $taille, true, $align ?? 'G');
                $x += $largeurs[$i];
            }
            $this->y += $hl + 1;
        };
        $this->place(2 * $hl + 2);
        $entete();
        if (!$lignes) {
            $this->texte($this->marge + 1.5, $this->y + $hl * 0.75, 'Aucune donnée pour cette sélection.', $taille, false, 0.45);
            $this->y += $hl + 3;
            return;
        }
        $n = count($lignes);
        foreach ($lignes as $k => $ligne) {
            if ($this->place($hl)) {
                $entete();
            }
            $gras = $total && $k === $n - 1;
            $x = $this->marge;
            foreach ($colonnes as $i => [, , $align]) {
                $this->cellule($x, $this->y, $largeurs[$i], $hl, (string) ($ligne[$i] ?? ''), $taille, $gras, $align ?? 'G');
                $x += $largeurs[$i];
            }
            $this->ligne($this->marge, $this->y + $hl, $this->marge + $this->largeurUtile(), $this->y + $hl, 0.15, $gras ? 0.2 : 0.8);
            $this->y += $hl;
        }
        $this->y += 4;
    }

    /** Diagramme en barres horizontales, en gris : [[libellé, valeur], ...]. */
    public function barres(array $donnees, float $taille = 8): void
    {
        if (!$donnees) {
            return;
        }
        $max = max(1, max(array_column($donnees, 1)));
        $lLib = min(70, $this->largeurUtile() * 0.35);
        $lBarre = $this->largeurUtile() - $lLib - 18;
        foreach ($donnees as [$libelle, $valeur]) {
            $this->place(6);
            $this->texte($this->marge, $this->y + 3.6, $this->tronquer((string) $libelle, $lLib - 2, $taille, false), $taille, false, 0.2);
            $lv = $lBarre * $valeur / $max;
            if ($lv > 0) {
                $this->rectangle($this->marge + $lLib, $this->y + 0.8, $lv, 3.6, 0.55, null);
            }
            $this->texte($this->marge + $lLib + $lv + 2, $this->y + 3.6, (string) $valeur, $taille, true);
            $this->y += 5.4;
        }
        $this->y += 3;
    }

    /* ---------- Sortie ---------- */

    public function sortie(string $nomFichier): never
    {
        $this->pages[] = $this->flux;
        $this->flux = '';
        $nb = count($this->pages);
        $objets = [];
        $kids = [];
        $premierePage = 5 + ($this->logo ? 1 : 0);
        foreach ($this->pages as $i => $contenu) {
            $contenu = str_replace('{nb}', (string) $nb, $contenu);
            $num = $premierePage + 2 * $i;
            $kids[] = "$num 0 R";
            $ressources = '/Font << /F1 3 0 R /F2 4 0 R >>' . ($this->logo ? ' /XObject << /Im1 5 0 R >>' : '');
            $objets[$num] = sprintf('<< /Type /Page /Parent 2 0 R /MediaBox [0 0 %.2F %.2F] /Resources << %s >> /Contents %d 0 R >>',
                $this->l * self::K, $this->h * self::K, $ressources, $num + 1);
            $z = function_exists('gzcompress') ? gzcompress($contenu) : $contenu;
            $objets[$num + 1] = '<< /Length ' . strlen($z) . (function_exists('gzcompress') ? ' /Filter /FlateDecode' : '') . " >>\nstream\n$z\nendstream";
        }
        $objets[1] = '<< /Type /Catalog /Pages 2 0 R >>';
        $objets[2] = '<< /Type /Pages /Kids [' . implode(' ', $kids) . '] /Count ' . $nb . ' >>';
        $objets[3] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>';
        $objets[4] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>';
        if ($this->logo) {
            $objets[5] = sprintf("<< /Type /XObject /Subtype /Image /Width %d /Height %d /ColorSpace /DeviceRGB /BitsPerComponent 8 /Filter /DCTDecode /Length %d >>\nstream\n%s\nendstream",
                $this->logo['l'], $this->logo['h'], strlen($this->logo['donnees']), $this->logo['donnees']);
        }
        $objets[] = '<< /Title (' . $this->chaine($this->titre) . ') /Producer (Bibliotheque) /CreationDate (D:' . date('YmdHis') . ') >>';
        $info = array_key_last($objets);
        ksort($objets);

        $pdf = "%PDF-1.4\n%\xE2\xE3\xCF\xD3\n";
        $positions = [];
        foreach ($objets as $n => $obj) {
            $positions[$n] = strlen($pdf);
            $pdf .= "$n 0 obj\n$obj\nendobj\n";
        }
        $xref = strlen($pdf);
        $pdf .= 'xref' . "\n0 " . ($info + 1) . "\n0000000000 65535 f \n";
        for ($n = 1; $n <= $info; $n++) {
            $pdf .= sprintf("%010d 00000 n \n", $positions[$n] ?? 0);
        }
        $pdf .= "trailer\n<< /Size " . ($info + 1) . " /Root 1 0 R /Info $info 0 R >>\nstartxref\n$xref\n%%EOF";

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $nomFichier . '"');
        header('Content-Length: ' . strlen($pdf));
        header('Cache-Control: private, no-store');
        echo $pdf;
        exit;
    }

    /* ---------- Primitives ---------- */

    public function texte(float $x, float $y, string $texte, float $taille, bool $gras = false, float $gris = 0): void
    {
        $this->flux .= sprintf("BT %.3F g /%s %.2F Tf %.2F %.2F Td (%s) Tj ET\n",
            $gris, $gras ? 'F2' : 'F1', $taille, $x * self::K, ($this->h - $y) * self::K, $this->chaine($texte));
    }

    public function ligne(float $x1, float $y1, float $x2, float $y2, float $epaisseur = 0.2, float $gris = 0): void
    {
        $this->flux .= sprintf("%.3F G %.2F w %.2F %.2F m %.2F %.2F l S\n", $gris, $epaisseur * self::K,
            $x1 * self::K, ($this->h - $y1) * self::K, $x2 * self::K, ($this->h - $y2) * self::K);
    }

    /** Rectangle : remplissage gris $fond (null = aucun), contour gris $contour (null = aucun). */
    public function rectangle(float $x, float $y, float $l, float $h, ?float $fond, ?float $contour): void
    {
        $op = $fond !== null && $contour !== null ? 'B' : ($fond !== null ? 'f' : 'S');
        $this->flux .= ($fond !== null ? sprintf('%.3F g ', $fond) : '') . ($contour !== null ? sprintf('%.3F G %.2F w ', $contour, 0.2 * self::K) : '')
            . sprintf("%.2F %.2F %.2F %.2F re %s\n", $x * self::K, ($this->h - $y - $h) * self::K, $l * self::K, $h * self::K, $op);
    }

    private function cellule(float $x, float $y, float $l, float $h, string $texte, float $taille, bool $gras, string $align): void
    {
        $texte = $this->tronquer(str_replace(["\r", "\n"], ' ', $texte), $l - 3, $taille, $gras);
        $lt = $this->largeurTexte($texte, $taille, $gras);
        $xt = match ($align) { 'D' => $x + $l - 1.5 - $lt, 'C' => $x + ($l - $lt) / 2, default => $x + 1.5 };
        $this->texte($xt, $y + $h * 0.68, $texte, $taille, $gras);
    }

    public function largeurTexte(string $texte, float $taille, bool $gras): float
    {
        $w = 0;
        foreach (preg_split('//u', $texte, -1, PREG_SPLIT_NO_EMPTY) as $c) {
            $base = self::sansAccent($c);
            $o = ord($base);
            $w += ($o >= 32 && $o <= 126) ? self::LARGEURS[$gras][$o - 32] : 556;
        }
        return $w * $taille / 1000 / self::K;
    }

    private function tronquer(string $texte, float $largeur, float $taille, bool $gras): string
    {
        if ($this->largeurTexte($texte, $taille, $gras) <= $largeur) {
            return $texte;
        }
        while (mb_strlen($texte) > 1 && $this->largeurTexte($texte . '…', $taille, $gras) > $largeur) {
            $texte = mb_substr($texte, 0, -1);
        }
        return rtrim($texte) . '…';
    }

    private function couper(string $texte, float $largeur, float $taille, bool $gras): array
    {
        $lignes = [];
        foreach (preg_split('/\R/u', $texte) as $para) {
            $ligne = '';
            foreach (preg_split('/\s+/u', trim($para)) as $mot) {
                $essai = $ligne === '' ? $mot : "$ligne $mot";
                if ($ligne !== '' && $this->largeurTexte($essai, $taille, $gras) > $largeur) {
                    $lignes[] = $ligne;
                    $ligne = $mot;
                } else {
                    $ligne = $essai;
                }
            }
            $lignes[] = $ligne;
        }
        return $lignes;
    }

    /** Chaîne PDF : conversion en Windows-1252 et échappement. */
    private function chaine(string $s): string
    {
        $s = strtr($s, ['’' => "'", '‘' => "'", '“' => '"', '”' => '"', '–' => '-', ' ' => ' ', ' ' => ' ']);
        $c = @iconv('UTF-8', 'Windows-1252//TRANSLIT', $s);
        $c = $c === false ? mb_convert_encoding($s, 'Windows-1252', 'UTF-8') : $c;
        return strtr($c, ['\\' => '\\\\', '(' => '\\(', ')' => '\\)', "\r" => '', "\n" => ' ']);
    }

    private static function sansAccent(string $c): string
    {
        static $table = ['à' => 'a', 'â' => 'a', 'ä' => 'a', 'á' => 'a', 'ç' => 'c', 'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
            'î' => 'i', 'ï' => 'i', 'í' => 'i', 'ô' => 'o', 'ö' => 'o', 'ó' => 'o', 'ù' => 'u', 'û' => 'u', 'ü' => 'u', 'ú' => 'u', 'ÿ' => 'y',
            'À' => 'A', 'Â' => 'A', 'Ç' => 'C', 'É' => 'E', 'È' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'Î' => 'I', 'Ï' => 'I', 'Ô' => 'O',
            'Ù' => 'U', 'Û' => 'U', 'Ü' => 'U', 'œ' => 'o', 'Œ' => 'O', '’' => "'", '«' => '<', '»' => '>', '…' => 'm', '—' => 'M', '€' => 'e'];
        return $table[$c] ?? (strlen($c) === 1 ? $c : 'n');
    }

    /** Convertit une image (PNG avec transparence comprise) en JPEG sur fond blanc pour l'insérer dans le PDF. */
    private static function jpegDepuisImage(string $chemin): ?array
    {
        $info = @getimagesize($chemin);
        if (!$info || !function_exists('imagecreatetruecolor')) {
            return null;
        }
        $src = match ($info[2]) {
            IMAGETYPE_PNG => @imagecreatefrompng($chemin),
            IMAGETYPE_JPEG => @imagecreatefromjpeg($chemin),
            default => false,
        };
        if (!$src) {
            return null;
        }
        [$l, $h] = [imagesx($src), imagesy($src)];
        $img = imagecreatetruecolor($l, $h);
        imagefill($img, 0, 0, imagecolorallocate($img, 255, 255, 255));
        imagecopy($img, $src, 0, 0, 0, 0, $l, $h);
        ob_start();
        imagejpeg($img, null, 92);
        return ['donnees' => ob_get_clean(), 'l' => $l, 'h' => $h];
    }
}
