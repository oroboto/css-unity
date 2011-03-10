<?php
// change this line if you move this file to a different location
$CSSUNITY_DIR = '../src';

// show errors
error_reporting(E_ALL);
ini_set('display_errors', '1');

// set response header
header('Content-type: text/css');

// parse options
require_once("$CSSUNITY_DIR/CSSUnityOptionParser.class.php");
$options = new CSSUnityOptionParser($_GET);

// instantiate object with input
require_once("$CSSUNITY_DIR/CSSUnity.class.php");
$cssunity = new CSSUnity($options->input);
$cssunity->substitute = $options->substitute;

// other public functions can be used, for simple combination or normalization
//echo $cssunity->combine_files();
//echo $cssunity->normalize();

// parse stylesheets according to options specified in query string
// examples:
//     type=none
//     type=datauri
//     type=mhtml&separate
// default arguments (false, false) are used if no options are specified
$type = $options->type !== 'all' ? $options->type : false;
echo $cssunity->parse($type, $options->separate);
?>