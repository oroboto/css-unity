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
    <link type="text/css" rel="stylesheet" href="styles/css.php?s=reset.css,style.css&amp;type=none" />
    <!--[if (gt IE 7)|!(IE)]><!--><link type="text/css" rel="stylesheet" href="styles/css.php?s=style.css&amp;type=datauri&amp;separate" /><!--<![endif]-->
    <!--[if lte IE 7]><link type="text/css" rel="stylesheet" href="styles/css.php?s=style.css&amp;type=mhtml&amp;separate" /><![endif]-->
</head>
<body>
    <header>
        <hgroup>
            <h1><a href="http://oroboto.com/">Oroboto</a></h1>
            <h2><a href="http://oroboto.com/labs/css-unity/">CSS Unity</a></h2>
        </hgroup>
    </header>
    <nav>
        <ul>
            <li><a href="./">Home</a></li>
            <li><a href="./datauri">Data URI</a></li>
            <li><a href="./mhtml">MHTML</a></li>
            <li><a href="./separate">Separate</a></li>
            <li><a href="./original">Original</a></li>
        </ul>
    </nav>
    <div id="content">
        <article id="demo">
            <h1>Demo</h1>
            <h2>Images</h2>
            <?php
            $icons = glob(dirname(__FILE__) . '/images/tango-icon-theme-0.8.90/32x32/actions/*.png');
            if (count($icons) > 0) { echo '<ul class="icons clearfix">'; }
            foreach ($icons as $path) {
                $filename = basename($path);
                $filenoext = basename($path, '.png');
                echo "<li class=\"$filenoext\">$filename</li>\n";
            }
            if (count($icons) > 0) { echo '</ul>'; }
            ?>
            <h2>Fonts</h2>
            <ul class="fonts">
                <li class="quick-end-jerk">Quick End Jerk</li>
                <li class="mountains-of-christmas">Mountains of Christmas</li>
            </ul>
        </article>
    </div>
</body>
</html>
