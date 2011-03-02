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
    private $stylesheets;
    private $text = '';
    private $mhtml = "/*\nContent-Type: multipart/related; boundary=\"|\"\n";

    // regular expression patterns
    const CSS_URL_PATTERN = '/url\([\'"]?(?P<filepath>(?P<filenoext>.+)?\.(?P<extension>[^\'")?#]+).*?)[\'"]?\)/i';
    const CSS_COMMENT_PATTERN = '/(?P<comment>\/\*(?:\s|.)*?\*\/)/';
    const CSS_MULTIPLE_URL_PATTERN = '/(,)(url)/i';
    const CSS_EMPTY_RULESET_PATTERN = '/[^}]+{\s*}/';

    /**
     * Creates a new instance of this class.
     *
     * @param array|string $input string array or comma-separated string of paths to stylesheets
     */
    function __construct($input) {
        if (empty($input)) { die; }

        // set array argument to private array
        if (is_array($input)) {
            $this->stylesheets = $input;
        }

        // split string argument to private array
        if (is_string($input)) {
            $this->stylesheets = explode(',', $input);
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
                $this->text .= "/* FILE: $stylesheet */";
                $this->text .= trim(file_get_contents($stylesheet));
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
            $this->combine_stylesheets();
        }

        // exit early if still empty
        if (empty($this->text)) {
            return $this->text;
        }

        // CSSTidy
        include('../lib/CSSTidy/class.csstidy.php');
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
     *     - datauri - converts data uris
     *     - mhtml - converts MHTML for IE6/7
     *     - none - strips all resources from text
     * @param string $separate outputs only the specified type and relevant text
     * @return string
     */
    public function parse($type=false, $separate=false) {
        // get normalized CSS
        if (empty($this->text)) {
            $this->normalize();
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

        // variables to provide loop lookbehind
        $at_block = '';
        $font_face_family = '';
        $selector = '';

        // loop through lines
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
                    $selector = '';
                } else {
                    $at_block = '';
                    $font_face_family = '';
                }
            } else {
                // inside block
                if ($inside_font_face) {
                    $font_face_family = $starts_with_font_family ? $line : $font_face_family;
                    // TODO: convert fonts to data uris
                } else {
                    // fill match array
                    // $matches[1] = [filepath]
                    // $matches[2] = [filenoext]
                    // $matches[3] = [extension]
                    preg_match(self::CSS_URL_PATTERN, $line, $matches);
                    if (!empty($matches)) {
                        // go to next line if resources are stripped
                        if ($type === 'none') { continue; }

                        // TODO: add support for underscore/star hacks
                        if (preg_match('/^[_*]/', $line)) {
                            $parsed_text .= "$line\n";
                            continue;
                        }

                        $filepath = $matches['filepath'];
                        $base64 = $this->_get_base64_encoded_resource($filepath);

                        // data URI
                        // TODO: add support for fonts
                        if ($write_data_uri) {
                            $parsed_text .= str_replace($filepath,
                                $this->_get_data_uri($filepath, 'image/' . $matches['extension'], $base64), $line) . "\n";
                        }

                        // MHTML
                        if ($write_mhtml && !empty($base64)) {
                            $content_location = str_replace('/', '_', $filepath);
                            $parsed_text .= "*" . str_replace($filepath, $this->_get_mhtml_uri($content_location), $line);
                            $this->mhtml .= "\n--|\n";
                            $this->mhtml .= "Content-Location:$content_location\n";
                            $this->mhtml .= "Content-Transfer-Encoding:base64\n\n";
                            $this->mhtml .= "$base64\n";
                        }

                        continue;
                    } else {
                        // no action was performed on the line, so skip
                        if ($separate === true) { continue; }
                    }
                }
            }

            // no action was performed on the line, so write as-is
            $parsed_text .= "$line\n";
        }

        // clean up empty rulesets
        $parsed_text = preg_replace(self::CSS_EMPTY_RULESET_PATTERN, '', $parsed_text);

        if ($write_mhtml) {
            // append MHTML ending
            $this->mhtml .= "\n--|--\n*/\n";

            // prepend MHTML to beginning
            $parsed_text = $this->mhtml . $parsed_text;
        }

        $this->text = $parsed_text;
        return $this->text;
    }

    private function _get_base64_encoded_resource($filepath) {
        if (!file_exists($filepath)) { return; }
        return base64_encode(file_get_contents($filepath));
    }

    private function _get_data_uri($input, $type, $base64=false) {
        if ($base64 === false) {
            $base64 = $this->_get_base64_encoded_resource($input);
        }
        if (empty($base64)) { return $input; }
        return "data:$type;base64,$base64";
    }

    private function _get_mhtml_uri($content_location) {
        $full_page_url = $this->_get_absolute_uri();
        return "mhtml:$full_page_url!$content_location";
    }

    private function _get_absolute_uri() {
        $scheme = isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on" ? "https://" : "http://";
        $port = $_SERVER["SERVER_PORT"] != "80" ? ":" . $_SERVER["SERVER_PORT"] : "";
        return $scheme . $_SERVER["SERVER_NAME"] . $port . $_SERVER["REQUEST_URI"];
    }
}
?>
