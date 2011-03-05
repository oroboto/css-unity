CSS Unity
=========

CSS Unity is a utility that combines a stylesheet's external resources, such as images, into the stylesheet itself as base64 encoded text by using data URIs and MHTML.

Features
--------

* encodes resources into data URIs, supported by [modern browsers](http://en.wikipedia.org/wiki/Data_URI_scheme#Web_browser_support)
* encodes resources into MHTML as well, to support IE6/7
* combines multiple stylesheets into one request
* uses [CSSTidy](https://github.com/oroboto/CSSTidy) to optimize styles
* embeds encoded resources into one stylesheet, or can be split into separate stylesheets
* can be used in realtime on your web server, or generate files from the command line instead

Requirements
------------

* PHP 5+

Command Line
------------

PHP command line script can be found at `src/unify.php`.

You can execute the script using the PHP command line interpreter:

    $ php unify.php

Optionally, you can grant execute permissions to the script and run it directly:

    $ chmod +x unify.php
    $ unify.php

See [PHP Manual: Executing PHP Files](http://php.net/manual/en/features.commandline.usage.php) for further information.

### Options

Executing the script without options will display the following:

    Usage: unify.php [options]
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

License
-------

[MIT License](http://www.opensource.org/licenses/mit-license.php)

CSSTidy is distributed under terms of the GNU Lesser General Public License (LGPL) 2.1.
