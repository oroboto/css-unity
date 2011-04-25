#!/usr/bin/php
<?php
// show errors
error_reporting(E_ALL);
ini_set('display_errors', '1');

$usage = "Usage: unify.php [options]
Executes CSS Unity from the command line.

Options
  -i, --input <paths>      comma-separated list of file or directory paths
  -t, --type <type>        convert external resources to specified type
                           Possible values:
                           all (default) all possible types written to one file
                           datauri       writes data URIs
                           mhtml         writes MHTML for IE6/7
                           nores         strips all resources from output
  -o, --output <dir>       output directory; defaults to current directory
  -n, --output-name <name> name of file(s) to be written
                           defaults to name of first input path specified
  -s, --separate           split resources into separate file(s)
  -m, --mhtml-uri <uri>    absolute URI to use for MHTML
                           Required if type is 'all' or 'mhtml'
                           Value must include scheme, authority, and path to
                           output directory (e.g. http://domain.com/path/,
                           trailing slash is optional)
  -r, --root <dir>         root directory, only necessary from command line
                           when using absolute URLs e.g. /path/to/image.png
  -S, --substitute <text,replacement>
                           replaces <text> in URLs with <replacement>, where
                           <replacement> is relative to your current working
                           directory; helpful for rewritten URLs
  -r, --recursive          recurses through subdirectories of directories in
                           input (currently disabled)
";

// display usage text if no arguments were provided
if ($argc < 2) {
    echo $usage;
    exit(1);
}

// define command line options
$shortopts  = "i:t:o:n:sm:r:S:R";
$longopts  = array(
    "input:",
    "type:",
    "output:",
    "output-name:",
    "separate",
    "mhtml-uri:",
    "root:",
    "substitute:",
    "recursive"
);
$options = getopt($shortopts, $longopts);

/*
echo '$argv: ';
var_dump($argv);
echo '$options: ';
var_dump($options);
//*/

// parse options
require_once('CSSUnityOptionParser.class.php');
$options = new CSSUnityOptionParser($options);

// instantiate object with input
require_once('CSSUnity.class.php');
$cssunity = new CSSUnity($options->input);
$cssunity->root = $options->root;
$cssunity->substitute = $options->substitute;

// other public functions can be used, for simple combination or normalization
//$output = $cssunity->combine_files();
//$output = $cssunity->normalize();

function write_file($name, $cssunity, $type, $separate, $mhtml_uri) {
    $css = $cssunity->parse($type, $separate, $mhtml_uri . basename($name));
    if (empty($css)) { return; }
    file_put_contents($name, $css);
}

// parse stylesheets according to specified options and write to file(s)
if ($options->separate) {
    // determine output types to be written
    $output_types[] = $options->type;
    if ($options->type === 'all') {
        $output_types = array('nores', 'datauri', 'mhtml');
    }

    // write file for each output type
    foreach ($output_types as $output_type) {
        $output_basename = $options->output_dir . $options->output_name . ".$output_type.css";
        write_file($output_basename, $cssunity, $output_type, $options->separate, $options->mhtml_uri);
    }
} else {
    $output_basename = $options->output_dir . $options->output_name . '.' . $options->type . '.css';
    $type = $options->type !== 'all' ? $options->type : false;
    write_file($output_basename, $cssunity, $type, $options->separate, $options->mhtml_uri);
}
?>
