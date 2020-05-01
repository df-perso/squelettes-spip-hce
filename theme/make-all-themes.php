<?php
include __DIR__ . '/scssphp/scss.inc.php';

use ScssPhp\ScssPhp\Compiler;
use ScssPhp\ScssPhp\Parser;
use ScssPhp\ScssPhp\Version;

$aThemes = array(
    "gravel-road",
    "rhyolite",
    "colorful",
    "green-and-blue",
    "muddy",
    "mountain-sunset",
);

$sSCCS = file_get_contents('theme.scss');

foreach($aThemes as $sTheme)
{
    $sModifiedSCCS = str_replace('$selected-theme: muddy;', "\$selected-theme: $sTheme;", $sSCCS);
    
    $oCompiler = new Compiler();
    
    echo "Compiling stylesheet for theme: $sTheme...\n";
    file_put_contents("../css/themes/{$sTheme}.css", $oCompiler->compile($sModifiedSCCS, 'theme.scss'));
}
echo "Completed.\n";