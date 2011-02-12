<?php
/*
CSS Unity

Copyright (C) 2011 Oroboto

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

    // $matches[1] = [comment]
    // $matches[2] = [ruleset]
    // $matches[3] = [selector]
    // $matches[4] = [declaration]
    // $matches[5] = [property]
    // $matches[6] = [value]
    // $matches[7] = [filepath]
    // $matches[8] = [filenoext]
    // $matches[9] = [extension]
    private $matches;

    const CSS_PATTERN = '/(?P<comment>\/\*.*?\*\/)*\s*(?P<ruleset>(?P<selector>[-\w.]+?)\s*{[^}]*?(?P<declaration>(?P<property>[-\w*]+)\s*:(?P<value>[^;]*url\([\'"]?(?P<filepath>(?P<filenoext>.+)?\.(?P<extension>gif|jpg|png))[\'"]?\)[^;]*?);?)[^}]*})/i';
    const CSS_COMMENT_PATTERN = '/(?P<comment>\/\*(?:\s|.)*?\*\/)/';
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
        $this->encode_resources($type, $separate);

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
                $this->text .= file_get_contents($stylesheet);
            }

            // add ending semicolons as needed
            $this->text = preg_replace(self::CSS_NO_SEMICOLON_PATTERN, '$1;$2', $this->text);
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

        // tidy
        include('../lib/CSSTidy/class.csstidy.php');
        $css = new csstidy();
        $css->set_cfg('preserve_css',true);
        $css->set_cfg('remove_last_;',false);
        $css->parse($this->text);
        return $css->print->plain();
    }

    private function _capture_groups() {
        if (empty($this->text)) {
            // read files in array and append to single string
            $this->combine_stylesheets();
        }

        // strip comments
        $text_without_comments = preg_replace(self::CSS_COMMENT_PATTERN, '', $this->text);

        // fill match array
        preg_match_all(self::CSS_PATTERN, $text_without_comments, $this->matches);
    }

    public function encode_resources($type=false, $separate=false) {
        if (empty($this->matches)) {
            // capture groups into array
            $this->_capture_groups();
        }

        // $matches[1] = [comment]
        // $matches[2] = [ruleset]
        // $matches[3] = [selector]
        // $matches[4] = [declaration]
        // $matches[5] = [property]
        // $matches[6] = [value]
        // $matches[7] = [filepath]
        // $matches[8] = [filenoext]
        // $matches[9] = [extension]

        if (!$type || $type == 'data_uri') {
            // map arrays for data uri declarations
            $data_uri_declarations = array_map(array($this, '_get_data_uri_declaration'),
                $this->matches['filepath'], $this->matches['extension'],
                $this->matches['value'], $this->matches['property']);

            $this->text = $separate ?
                implode("\n", array_map(array($this, '_get_separated_data_uri_ruleset'),
                    $this->matches['selector'], $data_uri_declarations)) :
                str_replace($this->matches['declaration'], $data_uri_declarations, $this->text);
        }

        return $this->text;
    }

    private function _get_base64encoded_resource($filepath) {
        if (!file_exists($filepath)) { return; }
        return base64_encode(file_get_contents($filepath));
    }

    private function _get_data_uri($filepath, $extension) {
        $base64 = $this->_get_base64encoded_resource($filepath);
        if (empty($base64)) { return $filepath; }
        return "data:image/$extension;base64,$base64";
    }

    private function _get_data_uri_value($filepath, $extension, $oldvalue) {
        $data_uri = $this->_get_data_uri($filepath, $extension);
        return str_replace($filepath, $data_uri, $oldvalue);
    }

    private function _get_data_uri_declaration($filepath, $extension, $oldvalue, $property) {
        $data_uri_value = $this->_get_data_uri_value($filepath, $extension, $oldvalue);
        return "$property:$data_uri_value;";
    }

    private function _get_separated_data_uri_ruleset($selector, $declaration) {
        return "$selector { $declaration }";
    }
}
?>
