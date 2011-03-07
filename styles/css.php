<?php
// show errors
error_reporting(E_ALL);
ini_set('display_errors', '1');

// set response header
header('Content-type: text/css');

// include class
require_once('../src/CSSUnity.class.php');

// instantiate object with comma-separated list of file paths from query string
$cssunity = new CSSUnity($_GET['i']);

// other public functions can be used, for simple combination or normalization
//echo $cssunity->combine_files();
//echo $cssunity->normalize();

// parse stylesheets according to options specified in query string
// examples:
//     type=none
//     type=datauri
//     type=mhtml&separate
// default arguments (false, false) are used if no options are specified
echo $cssunity->parse(isset($_GET['type']) ? $_GET['type'] : false, isset($_GET['separate']));
?>
