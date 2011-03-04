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
  -r, --recursive          recurses through subdirectories of directories in
                           input (currently disabled)
";

// display usage text if no arguments were provided
if ($argc < 2) {
    echo $usage;
    exit(1);
}

// define command line options
$shortopts  = "i:t:o:n:sm:r";
$longopts  = array(
    "input:",
    "type:",
    "output:",
    "output-name:",
    "separate",
    "mhtml-uri:",
    "recursive"
);
$options = getopt($shortopts, $longopts);

/*
echo '$argv: ';
var_dump($argv);
echo '$options: ';
var_dump($options);
//*/

function getopt_or_default($options, $keys, $default) {
    $value = $default;
    foreach ($keys as $key) {
        if (isset($options[$key])) {
            return $options[$key];
        }
    }
    return $value;
}

// parse command line options into variables
$input = getopt_or_default($options, array('input', 'i'), null);
$type = getopt_or_default($options, array('type', 't'), 'all');
$output_dir = getopt_or_default($options, array('output', 'o'), getcwd());
$inputs = explode(',', $input);
$inputpath = pathinfo($inputs[0]);
$output_name = getopt_or_default($options, array('output-name', 'n'), $inputpath['filename']);
$separate = isset($options['separate']) || isset($options['s']);
$mhtml_uri = getopt_or_default($options, array('mhtml-uri', 'm'), false);
if ($mhtml_uri) {
    // add trailing slash
    $mhtml_uri .= '/';

    // remove duplicates
    $mhtml_uri = preg_replace('/\/{2}$/', '/', $mhtml_uri);
} else {
    if ($type === 'all' || $type === 'mhtml') {
        fwrite(STDERR, "Absolute URI for MHTML is required if type is 'all' or 'mhtml'.\n");
        fwrite(STDERR, "Try 'unify.php' for more information.\n");
        exit(2);
    }
}
$recursive = isset($options['recursive']) || isset($options['r']);

// include class
require_once('CSSUnity.class.php');

// instantiate object with input
$cssunity = new CSSUnity($input);

// other public functions can be used, for simple combination or normalization
//$output = $cssunity->combine_files();
//$output = $cssunity->normalize();

// parse stylesheets according to specified options and write to file(s)
if ($separate) {
    // determine output types to be written
    $output_types[] = $type;
    if ($type === 'all') {
        $output_types = array('nores', 'datauri', 'mhtml');
    }

    // write file for each output type
    foreach ($output_types as $output_type) {
        $output_basename = "$output_name.$output_type.css";
        //echo "file_put_contents($output_basename, \$cssunity->parse($output_type, $separate, $mhtml_uri$output_basename))\n";
        //echo $cssunity->parse($output_type, $separate, "$mhtml_uri$output_basename");
        file_put_contents($output_basename, $cssunity->parse($output_type, $separate, "$mhtml_uri$output_basename"));
    }
} else {
    $output_basename = "$output_name.uni.css";
    $type = $type !== 'all' ? $type : false;
    file_put_contents($output_basename, $cssunity->parse($type, $separate, "$mhtml_uri$output_basename"));
}
?>
