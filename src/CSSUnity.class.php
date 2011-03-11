<?php
/**
 * CSS Unity
 * @author Ryan <ryan@oroboto.com>
 * @version 0.1
 * @copyright Copyright (c) 2011 Oroboto
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 *
 * Copyright (c) 2011 Oroboto. All rights reserved.
 *
 * Permission is hereby granted, free of charge, to any person
 * obtaining a copy of this software and associated documentation
 * files (the "Software"), to deal in the Software without
 * restriction, including without limitation the rights to use,
 * copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following
 * conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
 * OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 * WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
 * OTHER DEALINGS IN THE SOFTWARE.
 */
class CSSUnity {
    private $stylesheets = array();
    private $text = '';

    public $root = '';
    public $substitute = '';

    // regular expression patterns
    const CSS_URL_PATTERN = '/url\([\'"]?(?P<filepath>.*?)[\'"]?\)/i';
    const CSS_URL_PARTS_PATTERN = '/url\([\'"]?(?P<filepath>(?P<filenoext>.+)?\.(?P<extension>[^\'")?#]+).*?)[\'"]?\)/i';
    const CSS_COMMENT_PATTERN = '/(?P<comment>\/\*(?:\s|.)*?\*\/)/';
    const CSS_MULTIPLE_URL_PATTERN = '/(,)(url)/i';

    /**
     * Creates a new instance of this class.
     *
     * @param array|string $input string array or comma-separated string of paths to stylesheets
     * @param bool $recursive if input contains directories, recurses through all subdirectories
     */
    function __construct($input, $recursive=false) {
        if (empty($input)) {
            $this->_error_write("Input is required.", 1);
        }

        $filepaths;
        if (is_array($input)) {
            // set array argument to private array
            $filepaths = $input;
        } else if (is_string($input)) {
            // split string argument to private array
            $filepaths = explode(',', $input);
        } else {
            $this->_error_write("Input must be a string array or comma-separated string of paths.", 2);
        }

        // add files to stylesheet array
        foreach ($filepaths as $filepath) {
            $this->_add_files_to_array($filepath, $this->stylesheets, $recursive);
        }
    }

    /**
     * Combines multiple stylesheets into one request.
     *
     * @return string
     */
    public function combine_stylesheets() {
        foreach ($this->stylesheets as $stylesheet) {
            // add space between individual stylesheets
            if (!empty($this->text)) {
                $this->text .= "\n\n";
            }

            // concatenate stylesheet contents
            if (file_exists($stylesheet)) {
                $stylesheet_text = "/* FILE: $stylesheet */";
                $stylesheet_text .= trim(file_get_contents($stylesheet));
                // TODO: include stylesheets from @import rules into combined text, ignoring duplicates
                $this->text .= $stylesheet_text;
            }
        }

        return $this->text;
    }

    /**
     * Normalizes stylesheets to aid in parsing.
     * Uses CSSTidy for formatting.
     *
     * @return string
     */
    public function normalize() {
        // read files in array and append to single string
        if (empty($this->text)) {
            $this->text = $this->combine_stylesheets();
        }

        // exit early if still empty
        if (empty($this->text)) {
            return $this->text;
        }

        // CSSTidy
        include($this->_get_dirname(__FILE__) . '../lib/CSSTidy/class.csstidy.php');
        $css = new csstidy();
        $css->set_cfg('preserve_css', true);
        $css->set_cfg('remove_last_;', false);
        $css->set_cfg('compress_font-weight', false);
        $css->parse($this->text);
        return $css->print->plain();
    }

    /**
     * Parses CSS, converting external resources to encoded text.
     *
     * @param bool|string $type converts external resources to specified type
     *     - false (default) - converts all resources into one request
     *     - datauri - converts data URIs
     *     - mhtml - converts MHTML for IE6/7
     *     - nores - strips all resources from text
     * @param string $separate outputs only the specified type and relevant text
     * @param string $mhtml_uri absolute URI to use for MHTML
     * @return string
     */
    public function parse($type=false, $separate=false, $mhtml_uri=false) {
        // get normalized CSS
        if (empty($this->text)) {
            $this->text = $this->normalize();
        }

        // exit early if still empty
        if (empty($this->text)) {
            return $this->text;
        }

        // Boolean variables to determine output
        $write_data_uri = $type === false || $type === 'datauri';
        $write_mhtml = $type === false || $type === 'mhtml';

        // strip comments
        $text = preg_replace(self::CSS_COMMENT_PATTERN, '', $this->text);

        // split multiple @font-face urls into separate lines
        $text = preg_replace(self::CSS_MULTIPLE_URL_PATTERN, "$1\n$2", $text);

        $parsed_text = '';
        $mhtml = '';

        if ($write_mhtml) {
            // write MHTML header
            $mhtml = "/*\nContent-Type: multipart/related; boundary=\"|\"\n";
        }

        // variables to provide loop lookbehind
        $at_block = '';
        $font_face_family = '';
        $selector = '';

        // loop through lines
        $mhtml_seed = 0;
        foreach (preg_split("/(\r?\n)/", $text) as $line) {
            if (empty($line)) { continue; }
            $starts_with_at = strpos($line, '@') === 0;
            $inside_font_face = strpos($at_block, '@font-face') === 0;
            $starts_with_font_family = strpos($line, 'font-family') === 0;
            $ends_with_open_curly_brace = !empty($line) && substr_compare($line, '{', -1, 1) === 0;

            // save at/selector blocks for later use; otherwise, parse line normally
            if ($ends_with_open_curly_brace) {
                // start of block; set lookbehinds
                $at_block = $starts_with_at ? $line : $at_block;
                $selector = !$starts_with_at ? $line : $selector;
            } else if ($line === '}') {
                // end of block; remove related lookbehinds
                if (!empty($selector)) {
                    // remove empty ruleset
                    $parsed_text = $this->_str_remove_end($parsed_text, "$selector\n", $empty);
                    $selector = '';
                    if ($empty) { continue; }
                } else {
                    // remove empty at-block
                    $parsed_text = $this->_str_remove_end($parsed_text, "$at_block\n", $empty);
                    $at_block = '';
                    $font_face_family = '';
                    if ($empty) { continue; }
                }
            } else {
                // inside block
                if ($inside_font_face) {
                    $font_face_family = $starts_with_font_family ? $line : $font_face_family;
                    // TODO: convert fonts to data URIs
                } else {
                    // fill match array

                    // $matches[1] = [filepath]
                    preg_match(self::CSS_URL_PATTERN, $line, $matches);
                    $filepath = empty($matches) ? '' : $matches['filepath'];
                    $is_data_uri = strpos($filepath, 'data:') === 0;

                    // $matches[1] = [filepath]
                    // $matches[2] = [filenoext]
                    // $matches[3] = [extension]
                    preg_match(self::CSS_URL_PARTS_PATTERN, $line, $matches);
                    if (isset($matches['extension']) || $is_data_uri) {
                        // go to next line if resources are stripped
                        if ($type === 'nores') { continue; }

                        // TODO: include stylesheets from @import rules into combined text, ignoring duplicates
                        // ignore @import rules
                        if (strpos($line, '@import') === 0) {
                            $line = str_replace('"', '', $line);
                            $parsed_text .= "$line\n";
                            continue;
                        }

                        // TODO: add support for underscore/star hacks
                        // skip lines that have underscore/star hacks
                        if (preg_match('/^[_*]/', $line)) {
                            $parsed_text .= "$line\n";
                            continue;
                        }

                        // ignore URLs that point off-server
                        if (strpos($filepath, 'http') === 0) {
                            $parsed_text .= "$line\n";
                            continue;
                        }

                        // get base64 encoded resource
                        $base64 = $this->_get_base64_encoded_resource($filepath);

                        // write line as-is if file was not found
                        if (empty($base64)) {
                            $parsed_text .= "$line\n";
                            continue;
                        }

                        // get data URI
                        $data_uri_type = isset($matches['extension']) ? 'image/' . $matches['extension'] : '';
                        $data_uri = $this->_get_data_uri($filepath, $data_uri_type, $base64);

                        // ignore data URIs larger than 32KB for IE8 compatibility
                        if (strlen($data_uri) > 32768) {
                            $parsed_text .= "$line\n";
                            continue;
                        }

                        // data URI
                        if ($write_data_uri) {
                            $parsed_text .= str_replace($filepath, $data_uri, $line) . "\n";
                        }

                        // MHTML
                        if ($write_mhtml) {
                            $mhtml_seed++;
                            $content_location = $is_data_uri ? 'datauri' : basename($filepath);
                            $content_location .= "_$mhtml_seed";
                            if ($type !== 'mhtml') {
                                $parsed_text .= '*';
                            }
                            $parsed_text .= str_replace($filepath,
                                $this->_get_mhtml_uri($mhtml_uri, $content_location), $line) . "\n";
                            $mhtml .= "\n--|\n";
                            $mhtml .= "Content-Location:$content_location\n";
                            $mhtml .= "Content-Transfer-Encoding:base64\n\n";
                            $mhtml .= "$base64\n";
                        }

                        continue;
                    } else {
                        // no matches
                        if ($separate) {
                            // write line as-is
                            if ($type === 'nores') {
                                $parsed_text .= "$line\n";
                            }

                            // skip to next line
                            continue;
                        }
                    } // matches
                } // inside font face
            } // inside block

            // no action was performed on the line, so write line as-is
            $parsed_text .= "$line\n";
        } // foreach

        if ($write_mhtml) {
            // append MHTML footer
            $mhtml .= "\n--|--\n*/\n";

            // prepend MHTML to beginning
            $parsed_text = $mhtml . $parsed_text;
        }

        return trim($parsed_text);
    }

    function _add_files_to_array($filepath, &$array, $recursive=false) {
        $filepath = realpath($filepath);
        // add file to array
        if (is_file($filepath)) {
            $array[] = $filepath;
            return;
        }

        // read directory
        if (is_dir($filepath)) {
            $files = scandir($filepath);
            foreach ($files as $file) {
                // skip . and ..
                if ($file === "." || $file === "..") { continue; }

                $fullpath = "$filepath/$file";

                // add file to array
                if (is_file($fullpath)) {
                    $array[] = $fullpath;
                    continue;
                }

                // TODO: add support for recursion (relative url paths need to be adjusted)
                //// recurse directory
                //if (is_dir($fullpath) && $recursive) {
                //    _add_files_to_array($fullpath, $array, $recursive);
                //}
            }
        }
    }

    private function _get_base64_encoded_resource($filepath) {
        if (strpos($filepath, 'data:') === 0) {
            return substr($filepath, strpos($filepath, ',') + 1);
        }

        $is_absolute = strpos($filepath, '/') === 0;

        // substitute text in URL with replacement
        $substitute = $this->substitute;
        if (empty($substitute)) {
            // use empty strings to be split into array
            $substitute = ',';
        }
        $substitute = explode(',', $substitute);
        $realpath = str_replace($substitute[0], $substitute[1], $filepath);

        if ($is_absolute) {
            $realpath = $this->_get_root() . $realpath;
        } else if (empty($this->substitute)) {
            // TODO: add support for stylesheets from different directories
            $realpath = $this->_get_dirname($this->stylesheets[0]) . $realpath;
        }
        $realpath = realpath($realpath);
        if (!file_exists($realpath)) { return; }
        return base64_encode(file_get_contents($realpath));
    }

    private function _get_data_uri($input, $type, $base64=false) {
        if (strpos($input, 'data:') === 0) {
            return $input;
        }

        if ($base64 === false) {
            $base64 = $this->_get_base64_encoded_resource($input);
        }

        if (empty($base64)) { return $input; }
        return "data:$type;base64,$base64";
    }

    private function _get_mhtml_uri($mhtml_uri, $content_location) {
        if (!$this->_is_cli()) {
            $mhtml_uri = $this->_get_absolute_uri();
        }
        return "mhtml:$mhtml_uri!$content_location";
    }

    private function _get_absolute_uri() {
        $scheme = isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on" ? "https://" : "http://";
        $port = $_SERVER["SERVER_PORT"] != "80" ? ":" . $_SERVER["SERVER_PORT"] : "";
        return $scheme . $_SERVER["SERVER_NAME"] . $port . $_SERVER["REQUEST_URI"];
    }

    private function _is_cli() {
        return (php_sapi_name() == 'cli' && empty($_SERVER['REMOTE_ADDR']));
    }

    private function _get_dirname($file) {
        return str_replace('//', '/', dirname($file) . '/');
    }

    private function _get_root() {
        if ($this->_is_cli() && !empty($this->root)) {
            return str_replace('//', '/', realpath($this->root) . '/');
        }

        // get web root
        $localpath = dirname($_SERVER["SCRIPT_NAME"]);
        $absolutepath = realpath('./');
        $absolutepath = str_replace("\\", "/", $absolutepath); // fix Windows slashes
        return substr($absolutepath, 0, strpos($absolutepath, $localpath));
    }

    private function _str_ends_with($haystack, $needle) {
        $haystacklen = strlen($haystack);
        $needlelen = strlen($needle);
        if ($needlelen > $haystacklen) return false;
        return substr_compare($haystack, $needle, -$needlelen) === 0;
    }

    private function _str_remove_end($haystack, $needle, &$ends_with_needle) {
        $ends_with_needle = $this->_str_ends_with($haystack, $needle);
        if ($ends_with_needle) {
            return substr($haystack, 0, strrpos($haystack, $needle));
        }
        return $haystack;
    }

    private function _error_write($message, $status=false) {
        if ($this->_is_cli()) {
            fwrite(STDERR, "$message\n");
        } else {
            echo "$message\n";
        }

        if (is_int($status)) {
            exit($status);
        }
    }
}
?>