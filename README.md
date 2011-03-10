CSS Unity
=========

Trying to increase your web site's performance by reducing the number of HTTP
requests, but tired of creating and maintaining [CSS sprites](http://css-tricks.com/css-sprites)
for all of your images?

**CSS Unity** is a utility that combines a stylesheet's external resources, such
as images, into the stylesheet itself as base64 encoded text by using data URIs
and MHTML.

Features
--------

* encodes resources into data URIs, supported by
  [modern browsers](http://en.wikipedia.org/wiki/Data_URI_scheme#Web_browser_support)
* encodes resources into MHTML as well, to support IE6/7
* combines multiple stylesheets into one request
* uses [CSSTidy](https://github.com/oroboto/CSSTidy) to optimize styles
* embeds encoded resources into one stylesheet, or can be split into separate
  stylesheets
* can be used in realtime on your web server, or generate files from the command
  line instead

Requirements
------------

* PHP 5+
    * Ubuntu requires `php-cli` package for command line functionality.

Usage
-----

### Web Server

1.  Copy `lib/` and `src/` directories to your server.
2.  Modify the path in `styles/css.php` to point to `src/CSSUnity.class.php` on
    your server.
3.  Copy `styles/css.php` to your server, ideally to the same directory as your
    stylesheets, but so that your modified path in step 2 works. If you place
    `css.php` in a different directory, modify the variable `$CSSUNITY_DIR` to
    be relative to your new location.
4.  Change your web pages' stylesheet links to use `css.php`. Options are
    provided via query string parameters, using the same names as are used on
    the command line (see Command Line Options below).

### Command Line

PHP command line script can be found at `src/unify.php`.

You can execute the script using the PHP command line interpreter:

    $ php unify.php

Optionally, you can grant execute permissions to the script and run it directly:

    $ chmod +x unify.php
    $ unify.php

See [PHP Manual: Executing PHP Files](http://php.net/manual/en/features.commandline.usage.php)
for further information.

#### Options

Executing `unify.php` from the command line without options will display the
following:

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
    -s, --separate           split resources into separate file(s) e.g.
                             name.nores.css, name.datauri.css, name.mhtml.css
    -m, --mhtml-uri <uri>    absolute URI of directory to use for MHTML,
                             required if type is 'all' or 'mhtml'
                             Value must include scheme, authority, and path to
                             output directory e.g. http://domain.com/path/
                             (trailing slash is optional)
    -r, --root <dir>         root directory, only necessary from command line
                             when using absolute URLs e.g. /path/to/image.png
    -S, --substitute <text,replacement>
                             replaces <text> in URLs with <replacement>, where
                             <replacement> is relative to your current working
                             directory; helpful for rewritten URLs
    -R, --recursive          recurses through subdirectories of directories in
                             input (currently disabled)

License
-------

[MIT License](http://www.opensource.org/licenses/mit-license.php)

CSSTidy is distributed under terms of the GNU Lesser General Public License
(LGPL) 2.1.

Future (TODO)
-------------

*   support font resources in @font-face rules
*   include stylesheets from @import rules into combined text, ignoring duplicates
*   combine rulesets to avoid duplicate data URIs
*   remove dependency on third party libraries (CSSTidy)
*   implement subdirectory recursion and support for stylesheets from different
    directories
*   port to other languages, such as Python/Ruby/Java/C#
*   add support for underscore/star hacks?
*   gzip/deflate compression and browser caching?

Further Reading
---------------

### Data URIs
* [RFC 2397 - The "data" URL scheme](http://tools.ietf.org/html/rfc2397)
* [Data URI scheme - Wikipedia, the free encyclopedia](http://en.wikipedia.org/wiki/Data_URI_scheme)
* [data Protocol on MSDN](http://msdn.microsoft.com/en-us/library/cc848897.aspx)
* [data:urls – what are they and how to use them / Stoyan's phpied.com](http://www.phpied.com/data-urls-what-are-they-and-how-to-use/)

### MHTML
* [MHTML - Wikipedia, the free encyclopedia](http://en.wikipedia.org/wiki/MHTML)
* [MHTML – when you need data: URIs in IE7 and under / Stoyan's phpied.com](http://www.phpied.com/mhtml-when-you-need-data-uris-in-ie7-and-under/)
* [The proper MHTML syntax / Stoyan's phpied.com](http://www.phpied.com/the-proper-mhtml-syntax/)
* [Inline MHTML+Data URIs / Stoyan's phpied.com](http://www.phpied.com/inline-mhtml-data-uris/)

### CSSEmbed
* [Automatic data URI embedding in CSS files | NCZOnline](http://www.nczonline.net/blog/2009/11/03/automatic-data-uri-embedding-in-css-files/)
* [cssembed on GitHub](https://github.com/nzakas/cssembed/)