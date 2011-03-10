<?php
/**
 * CSS Unity Option Parser
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
class CSSUnityOptionParser {
    public $input;
    public $type;
    public $output_dir;
    public $output_name;
    public $separate;
    public $mhtml_uri;
    public $root;
    public $substitute;
    public $recursive;

    /**
     * Creates a new instance of this class.
     *
     * @param array $options string array of options
     * @param bool $recursive if input contains directories, recurses through all subdirectories
     */
    function __construct($options) {
        if (empty($options)) {
            $this->_error_write("Options argument is required.", 1);
        }

        if (!is_array($options)) {
            $this->_error_write("Options argument must be a string array of options.", 2);
        }

        // parse options into variables
        $this->input = $this->_getopt_or_default($options, array('input', 'i'), null);
        $this->type = $this->_getopt_or_default($options, array('type', 't'), 'all');
        $this->separate = isset($options['separate']) || isset($options['s']);

        if ($this->_is_cli()) {
            $this->output_dir = $this->_getopt_or_default($options, array('output', 'o'), getcwd());
            $inputs = explode(',', $this->input);
            $inputpath = pathinfo($inputs[0]);
            $this->output_name = $this->_getopt_or_default($options, array('output-name', 'n'), $inputpath['filename']);
            $this->mhtml_uri = $this->_getopt_or_default($options, array('mhtml-uri', 'm'), false);
            if ($this->mhtml_uri) {
                // add trailing slash
                $this->mhtml_uri .= '/';

                // remove duplicates
                $this->mhtml_uri = preg_replace('/\/{2}$/', '/', $this->mhtml_uri);
            } else {
                if ($this->type === 'all' || $this->type === 'mhtml') {
                    $this->_error_write("Absolute URI for MHTML is required if type is 'all' or 'mhtml'.");
                    $this->_error_write("Try 'unify.php' for more information.", 3);
                }
            }
        }

        $this->root = $this->_getopt_or_default($options, array('root', 'r'), '');
        $this->substitute = $this->_getopt_or_default($options, array('substitute', 'S'), '');
        $this->recursive = isset($options['recursive']) || isset($options['R']);
    }

    private function _getopt_or_default($options, $keys, $default) {
        $value = $default;
        foreach ($keys as $key) {
            if (isset($options[$key])) {
                return $options[$key];
            }
        }
        return $value;
    }

    private function _is_cli() {
        return (php_sapi_name() == 'cli' && empty($_SERVER['REMOTE_ADDR']));
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