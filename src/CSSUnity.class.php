<?php
/*
CSS Unity

Copyright (C) 2011 Oroboto. All rights reserved.

Permission is hereby granted, free of charge, to any person
obtaining a copy of this software and associated documentation
files (the "Software"), to deal in the Software without
restriction, including without limitation the rights to use,
copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the
Software is furnished to do so, subject to the following
conditions:

The above copyright notice and this permission notice shall be
included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
OTHER DEALINGS IN THE SOFTWARE.
*/
class CSSUnity {
    private $stylesheets;
    private $text = '';

    const CSS_COMMENT_PATTERN = '/(?P<comment>\/\*(?:\s|.)*?\*\/)/';
    const CSS_URL_PATTERN = '/url\([\'"]?(?P<filepath>(?P<filenoext>.+)?\.(?P<extension>[^\'")?#]+).*?)[\'"]?\)/i';
    const CSS_NO_SEMICOLON_PATTERN = '/([^;\s])(})/';

    function __construct($input) {
        header('Content-type: text/css');
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

    public function unify($type=false, $separate=false) {
        // convert external resources to encoded text
        $this->parse($type, $separate);

        // write text to response
        echo $this->text;
    }

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

        if (!empty($this->text)) {
            $this->text = $this->tidy();
        }

        return $this->text;
    }

    public function tidy() {
        if (empty($this->text)) {
            // read files in array and append to single string
            $this->combine_stylesheets();
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
     * Parses CSS.
     * @param string $type converts external resources to the specified type
     *     (datauri|mhtml|none); false (default) to generate all in one
     * @param string $separate outputs only the specified type and relevant text
     * @return string
     */
    public function parse($type=false, $separate=false) {
        if (empty($this->text)) {
            // read files in array and append to single string
            $this->combine_stylesheets();
        }

        // strip comments
        $text = preg_replace(self::CSS_COMMENT_PATTERN, '', $this->text);

        // split multiple @font-face urls into separate lines
        $text = preg_replace('/(,)(url)/i', "$1\n$2", $text);

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
                // start of block
                $at_block = $starts_with_at ? $line : $at_block;
                $selector = !$starts_with_at ? $line : $selector;
            } else if ($line === '}') {
                // end of block
                if (!empty($selector)) {
                    $selector = '';
                } else {
                    $at_block = '';
                    $font_face_family = '';
                }
            } else {
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
                        $filepath = $matches['filepath'];
                        // TODO: add support for fonts
                        $line = str_replace($filepath, $this->_get_data_uri($filepath, 'image/' . $matches['extension']), $line);
                        // TODO: copy $line to separate
                    }
                }
            }

            $parsed_text .= "$line\n";
        }

        $this->text = $parsed_text;
        return $this->text;
    }

    private function _get_base64encoded_resource($filepath) {
        if (!file_exists($filepath)) { return; }
        return base64_encode(file_get_contents($filepath));
    }

    private function _get_data_uri($filepath, $type) {
        $base64 = $this->_get_base64encoded_resource($filepath);
        if (empty($base64)) { return $filepath; }
        return "data:$type;base64,$base64";
    }
}
?>
