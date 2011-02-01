<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
require_once('../includes/CSSUnity.class.php');
$cssunity = new CSSUnity($_GET['s']);
//echo $cssunity->combine_files();
//echo $cssunity->encode_resources();
$cssunity->unify(false, isset($_GET['separate']));
?>
