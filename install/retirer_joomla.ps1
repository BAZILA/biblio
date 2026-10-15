# Retire l'ancien Joomla du site, sans rien supprimer :
# tout est DÉPLACÉ dans un dossier d'archive hors du dossier web (réversible).
# Usage :  powershell -ExecutionPolicy Bypass -File install\retirer_joomla.ps1
# La base de données Joomla (« exeltis ») n'est pas touchée.

$racine  = Split-Path -Parent $PSScriptRoot
$archive = Join-Path (Split-Path -Parent (Split-Path -Parent $racine)) ("archives\biblio-joomla-" + (Get-Date -Format "yyyyMMdd-HHmm"))

$elements = @(
    # Cœur et extensions Joomla
    'administrator', 'api', 'bin', 'cache', 'cli', 'components', 'includes', 'installation', 'language',
    'layouts', 'libraries', 'logs', 'media', 'modules', 'plugins', 't3-assets', 'templates', 'tmp',
    'configuration.php', 'configuration.bak.php', 'htaccess.txt', 'web.config.txt', 'robots.txt.dist',
    'LICENSE.txt', 'README.txt', 'files',
    # Images d'exemple de Joomla / du thème (les images du site sont dans images\biblio et images\ouvrages)
    'images\banners', 'images\headers', 'images\sampledata', 'images\spsimpleportfolio',
    'images\joomla_black.png', 'images\powered_by.png', 'images\home.png', 'images\index.html',
    # Variantes inutilisées du thème copiées dans assets
    'assets\theme\css\rtl', 'assets\theme\css\themes', 'assets\theme\images\demo',
    'assets\theme\css\default.css', 'assets\theme\css\error.css', 'assets\theme\css\frontend-edit.css',
    'assets\theme\css\home.css', 'assets\theme\css\k2.css', 'assets\theme\css\legacy-grid.css',
    'assets\theme\css\legacy-navigation.css', 'assets\theme\css\off-canvas.css', 'assets\theme\js\styleswitcher.js',
    # Captures de l'ancien site ayant servi à la comparaison
    '_ref'
)

New-Item -ItemType Directory -Force $archive | Out-Null
$deplaces = 0
foreach ($e in $elements) {
    $source = Join-Path $racine $e
    if (Test-Path $source) {
        $cible = Join-Path $archive $e
        New-Item -ItemType Directory -Force (Split-Path -Parent $cible) | Out-Null
        Move-Item -LiteralPath $source -Destination $cible
        $deplaces++
        Write-Output "déplacé : $e"
    }
}
Write-Output ""
Write-Output "$deplaces élément(s) archivé(s) dans : $archive"
Write-Output "Pour revenir en arrière : redéplacer le contenu de ce dossier dans $racine."
