<!DOCTYPE html>
<!--[if lt IE 7 ]> <html lang="en-us" dir="ltr" class="no-js ie6"> <![endif]-->
<!--[if IE 7 ]>    <html lang="en-us" dir="ltr" class="no-js ie7"> <![endif]-->
<!--[if IE 8 ]>    <html lang="en-us" dir="ltr" class="no-js ie8"> <![endif]-->
<!--[if IE 9 ]>    <html lang="en-us" dir="ltr" class="no-js ie9"> <![endif]-->
<!--[if (gt IE 9)|!(IE)]><!--> <html lang="en-us" dir="ltr" class="no-js"> <!--<![endif]-->
<head>
    <title>CSS Unity</title>
    <meta charset="utf-8" />
    <meta name="description" content="CSS Unity is a utility that combines a stylesheet's external resources, such as images, into the stylesheet itself as base64 encoded text by using data URIs and MHTML." />
    <meta name="keywords" content="css unity, css, unity, stylesheet, images, data URI, MHTML" />
    <!--[if lt IE 9]><script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script><![endif]-->
    <!--<link type="text/css" rel="stylesheet" href="styles/tango-demo.css" />-->
    <link type="text/css" rel="stylesheet" href="styles/css.php?s=reset.css,tango-demo.css" />
</head>
<body>
    <header>
        <hgroup>
            <h1><a href="http://oroboto.com/">Oroboto</a></h1>
            <h2><a href="http://oroboto.com/labs/css-unity/">CSS Unity</a></h2>
        </hgroup>
    </header>
    <div id="content">
        <article id="demo">
            <h1>Demo</h1>
            <?php
            $images = glob(dirname(__FILE__) . '/images/tango-icon-theme-0.8.90/32x32/actions/*.png');
            if (count($images) > 0) { echo '<ul>'; }
            foreach ($images as $path) {
                $filename = basename($path);
                $filenoext = basename($path, '.png');
                echo "<li class=\"$filenoext\">$filename</li>\n";
            }
            if (count($images) > 0) { echo '</ul>'; }
            ?>
        </article>
    </div>
</body>
</html>

