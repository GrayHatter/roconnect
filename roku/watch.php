<?php

include_once("CONSTANTS.php");

$globs = new globals();

$id = 0;

if(isset($_GET['contentId']))
    $id = $_GET['contentId'];
else
    die("No Movie Selected!");

$movieMeta = new videoMetaData();
$movies = $movieMeta->getMovies();
foreach ($movies as $movie) {
    if($id == $movie->contentId) {
        $myMovie = $movie;
        break;
    }
}

$filename = $myMovie->file;
$dimensions = explode('x', $myMovie->size);
$image = $myMovie->imageRelativePath();
$description = str_replace("\n","",$myMovie->synopsis);
$description = str_replace("\r\n","",$description);
$description = str_replace("\r","",$description);
$description = str_replace("'","",$description);

$title = str_replace("'","",$myMovie->title);
$duration = $myMovie->runtime;

if ($myMovie->contentType == "movie") {
    $i=1;
    $result = $dimensions[0];

    for ($i; $result > 855; $i+=.1) {
        $result = $dimensions[0];
        $result /= $i;
    }

    $dimensions[0] /= $i;
    $dimensions[1] /= $i;
} else {
    $dimensions[0] = "470";
    $dimensions[1] = "320";
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">

<html>
<head>
    <LINK href="css/style.css" rel="stylesheet" type="text/css">
    <!--[if IE]>
    <link rel="stylesheet" type="text/css" href="css/iehacks.css" />
    <![endif]-->

    <script type="text/javascript" src="javascript/jquery.js"></script>
    <script type="text/javascript" src="jwplayer/swfobject.js"></script>
    <script type="text/javascript" src="jwplayer/jwplayer.js"></script>

    <script type="text/javascript">

    $(function() {
        // set opacity to nill on page load
        $("ul#menu span").css("opacity","0");
        // on mouse over
        $("ul#menu span").hover(function () {
            // animate opacity to full
            $(this).stop().animate({
                opacity: 1
            }, "slow");
        },
        // on mouse out
        function () {
            // animate opacity to nill
            $(this).stop().animate({
                opacity: 0
            }, "slow");
        });
    });

    </script>
</head>

    <body>
        <div id="backgroundPopup"></div>
        <div id="wrapper">
            <div id="logo"></div>
            <div id="nav-left"></div>
            <div id="nav-right"></div>
            <div id="nav">
                <ul id="menu">
                    <li>
                        <a class="media" href="index.php?page=<?php echo $_GET['page']; ?>&playlistId=<?php echo $_GET['playlistId']; ?>"><span></span></a>
                    </li>
                    <li class="" style="">
                        <a class="roku" href="roku.php"><span></span></a>
                    </li>
                </ul>
            </div>
            <div id="playercontainer">
                    <center><div id='mediaplayer'>Loading the player . . .</div></center>
            </div>
            <script type="text/javascript">
                jwplayer('mediaplayer').setup({
                    'flashplayer': '/roku/jwplayer/player.swf',
                    'id': 'playerID',
                    'width': '<?php echo $dimensions[0]; ?>',
                    'height': '<?php echo $dimensions[1]; ?>',
                    'file': '<?php echo $globs->getIp()."movies/".$filename; ?>',
                    'image': '<?php echo $image; ?>',
                    'controlbar': 'bottom',
                    'description': '<?php echo $description; ?>',
                    'title': '<?php echo $title; ?>',
                    'duration': '<?php echo $duration; ?>',
                    'autostart': 'true'
                });
            </script>

            <div id="panel-cont-bot-alt"></div>

            <div id="footer">
                <a href="http://www.linkedin.com/pub/saul-howard/24/194/67">Saul Howard Designs</a> &bull; <a href="http://www.ro-connect.com/support.php" target="_blank">Help</a><br/><br/>
                <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
                    <input type="hidden" name="cmd" value="_s-xclick">
                    <input type="hidden" name="hosted_button_id" value="Y3TRQVUME5K3G">
                    <input type="image" src="https://www.paypalobjects.com/WEBSCR-640-20110429-1/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
                    <img alt="" border="0" src="https://www.paypalobjects.com/WEBSCR-640-20110429-1/en_US/i/scr/pixel.gif" width="1" height="1">
                </form>
            </div>
        </div>
        <div id="jGrowl" class="top-right jGrowl"></div>
    </body>
</html>
