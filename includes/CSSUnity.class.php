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

    const pattern = '/.+url\([\'"]*(.+)\.(gif|jpg|png)[\'"]*\).*/i';

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

    public function unify() {
        // convert external resources to encoded data URIs
        $this->encode_resources();

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

    public function encode_resources() {
        if (empty($this->text)) {
            // read files in array and append to single string
            $this->combine_stylesheets();
        }

        // $matches[0] = full match
        // $matches[1] = full path without extension
        // $matches[2] = file extensions
        preg_match_all(self::pattern, $this->text, $matches);

        // map arrays for array-based string replacement
        $filenames = array_map(array($this, '_get_filename'), $matches[1], $matches[2]);
        $data_uris = array_map(array($this, '_get_data_uri'), $matches[2], $filenames);
        $this->text = str_replace($filenames, $data_uris, $this->text);
        return $this->text;
    }

    private function _get_filename($filenoext, $fileext) {
        return "$filenoext.$fileext";
    }

    private function _get_base64encoded_resource($filename) {
        if (!file_exists($filename)) { return; }
        return base64_encode(file_get_contents($filename));
    }

    private function _get_data_uri($fileext, $filename) {
        $base64 = $this->_get_base64encoded_resource($filename);
        if (empty($base64)) { return $filename; }
        return "data:image/$fileext;base64,$base64";
    }
}
?>
