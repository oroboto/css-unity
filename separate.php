<!DOCTYPE html>
<!--[if lt IE 7]><html lang="en-us" dir="ltr" class="no-js ie ie6 lte9 lte8 lte7 lte6"><![endif]-->
<!--[if IE 7]>   <html lang="en-us" dir="ltr" class="no-js ie ie7 lte9 lte8 lte7"><![endif]-->
<!--[if IE 8]>   <html lang="en-us" dir="ltr" class="no-js ie ie8 lte9 lte8"><![endif]-->
<!--[if IE 9]>   <html lang="en-us" dir="ltr" class="no-js ie ie9 lte9"><![endif]-->
<!--[if (gt IE 9)|!(IE)]><!--><html lang="en-us" dir="ltr" class="no-js"><!--<![endif]-->
<head>
    <title>CSS Unity</title>
    <meta charset="utf-8" />
    <meta name="description" content="CSS Unity is a utility that combines a stylesheet's external resources, such as images, into the stylesheet itself as base64 encoded text by using data URIs and MHTML." />
    <meta name="keywords" content="css unity, css, unity, stylesheet, images, data URI, MHTML" />
    <!--[if lt IE 9]><script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script><![endif]-->
    <link type="text/css" rel="stylesheet" href="styles/css.php?i=reset.css,style.css&amp;type=nores" />
    <!--[if (gt IE 7)|!(IE)]><!--><link type="text/css" rel="stylesheet" href="styles/css.php?i=style.css&amp;type=datauri&amp;separate" /><!--<![endif]-->
    <!--[if lte IE 7]><link type="text/css" rel="stylesheet" href="styles/css.php?i=style.css&amp;type=mhtml&amp;separate" /><![endif]-->
</head>
<body>
    <header>
        <hgroup>
            <h1><a href="http://oroboto.com/">Oroboto</a></h1>
            <h2><a href="http://oroboto.com/labs/css-unity/">CSS Unity</a></h2>
        </hgroup>
    </header>
    <nav>
        <ul class="clearfix">
            <li>
                <a href="./">Home</a>
                <p>CSS Unity default behavior in action. All image resources have been converted to data URIs and MHTML and are included as one CSS file.</p>
            </li>
            <li>
                <a href="./datauri">Data URI</a>
                <p>Only data URIs are written. IE8+/Moz/Webkit/Opera only (IE6/7 will use original image).</p>
            </li>
            <li>
                <a href="./mhtml">MHTML</a>
                <p>Only MHTML is written. IE6/7 only.</p>
            </li>
            <li>
                <strong>Separate</strong>
                <p>Plain CSS, data URI-related CSS, and MHTML CSS are split into separate requests. Conditional comments are used to target supported browsers.</p>
            </li>
            <li>
                <a href="./original">Original</a>
                <p>Original CSS before any manipulation by CSS Unity.</p>
            </li>
        </ul>
    </nav>
    <div id="content">
        <section class="icons">
            <h2>Images</h2>
            <?php
            $icons = glob(dirname(__FILE__) . '/images/tango-icon-theme-0.8.90/32x32/actions/*.png');
            if (count($icons) > 0) { echo '<ul class="clearfix">'; }
            foreach ($icons as $path) {
                $filename = basename($path);
                $filenoext = basename($path, '.png');
                echo "<li class=\"$filenoext\">$filename</li>\n";
            }
            if (count($icons) > 0) { echo '</ul>'; }
            ?>
        </section>
        <section class="fonts">
            <h2>Fonts</h2>
            <ul>
                <li class="quick-end-jerk">Quick End Jerk</li>
                <li class="mountains-of-christmas">Mountains of Christmas</li>
            </ul>
        </section>
    </div>
    <a href="https://github.com/oroboto/css-unity"><img style="position: absolute; top: 0; right: 0; border: 0;" src="https://d3nwyuy0nl342s.cloudfront.net/img/71eeaab9d563c2b3c590319b398dd35683265e85/687474703a2f2f73332e616d617a6f6e6177732e636f6d2f6769746875622f726962626f6e732f666f726b6d655f72696768745f677261795f3664366436642e706e67" alt="Fork me on GitHub"></a>
</body>
</html>
