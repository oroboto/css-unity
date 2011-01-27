<?php
/*
CSS Unity

Copyright (C) 2011 Ryan Sullivan

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

    // $matches[0] = css rule
    // $matches[1] = [selector]
    // $matches[2] = [declaration]
    // $matches[3] = [property]
    // $matches[4] = [value]
    // $matches[5] = [filepath]
    // $matches[6] = [filenoext]
    // $matches[7] = [extension]
    private $matches;

    const pattern = '/(?P<selector>.+)\s*{\s*(?P<declaration>(?P<property>.+):(?P<value>.*url\([\'"]*(?P<filepath>(?P<filenoext>.+)?\.(?P<extension>gif|jpg|png))[\'"]*\)[^;]*?);*)\s*}/i';

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
        }
        return $this->text;
    }

    private function _capture_groups() {
        if (empty($this->text)) {
            // read files in array and append to single string
            $this->combine_stylesheets();
        }

        preg_match_all(self::pattern, $this->text, $this->matches);
        return $this->text;
    }

    public function encode_resources($type=false, $separate=false) {
        if (empty($this->matches)) {
            // capture groups into array
            $this->_capture_groups();
        }

        // $matches[0] = css rule
        // $matches[1] = [selector]
        // $matches[2] = [declaration]
        // $matches[3] = [property]
        // $matches[4] = [value]
        // $matches[5] = [filepath]
        // $matches[6] = [filenoext]
        // $matches[7] = [extension]

        // map arrays for array-based string replacement
        $data_uri_declarations = array_map(array($this, '_get_data_uri_declaration'), $this->matches['filepath'], $this->matches['extension'], $this->matches['value'], $this->matches['property']);
        $this->text = str_replace($this->matches['declaration'], $data_uri_declarations, $this->text);
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
}
?>
