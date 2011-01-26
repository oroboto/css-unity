<?php // this file is only for generating CSS for the demo
header('Content-type: text/css');
$imagedir = '../images/tango-icon-theme-0.8.90/32x32/actions/';
$images = glob($imagedir . '*.png');
foreach ($images as $path) {
    $filename = basename($path);
    $filenoext = basename($path, '.png');
    echo "li.$filenoext {\n";
    echo "    background-image:url($imagedir$filename);\n";
    echo "}\n";
}
?>

