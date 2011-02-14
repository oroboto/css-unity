<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
require_once('../src/CSSUnity.class.php');
$cssunity = new CSSUnity($_GET['s']);
//echo $cssunity->combine_files();
//echo $cssunity->encode_resources();
$cssunity->unify(isset($_GET['type']) ? $_GET['type'] : false, isset($_GET['separate']));
?>
