<?php

    include("CONSTANTS.php");

    if(!isset($_GET['setup'])) {
        include_once("setup.php");
    }

    $message = "";
    $globs = new globals();

    if(isset($_POST['video'])) {

        $videoPath = $_POST['video'];
        $serverConf = $_POST['serverConf'];
        $myIp = $_POST['myIp'];
        $IMDb = $_POST['IMDb'];
        $recursive = $_POST['recursive'];

        // Change of the server configuration file, add alias to new conf file and restart server
        if(file_exists($serverConf) && $globs->getServerConf() != $serverConf) {
            $globs->setServerConf($serverConf);
        } elseif(!file_exists($serverConf))
            $message .= "ERROR: " . $serverConf . " is not a valid file!<br/>";

        // Change to the video folder, requires change to alias and server restart
        if(is_dir($videoPath) && $globs->getVideoPath() != $videoPath) {
            $trail = substr($videoPath, strlen($videoPath) - 1, 1);
            if($trail != "/" && $trail != "\\") {
                $videoPath .= "/";
            }
            $globs->setVideoPath($videoPath);
        } elseif(!is_dir($videoPath))
            $message .= "ERROR: " . $videoPath . " is not a valid directory!<br/>";

        if($globs->getIMDb() != $IMDb)
            $globs->setIMDb ($IMDb);

        if($globs->getRecursive() != $recursive)
            $globs->setRecursive ($recursive);

        // Change to server url, requires change to all xml files
        if($globs->getIp() != "http://$myIp/") {
            if(!checkIP($myIp)) {
                $message = "Error: Bad IP<br/>";
            } else {
                if(!testIP($myIp)) {
                    $message = "Error: No roConnect Server at this IP<br/>";
                } else {
                    $globs->setIp($myIp);
                }
            }
        }

        if($message == "") {
            $message = "Configuration Saved!";
        }
    }

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html>
<head>
    <LINK href="css/style.css" rel="stylesheet" type="text/css">
    <LINK href="css/jquery.jgrowl.css" rel="stylesheet" type="text/css">
    <!--[if IE]>
    <link rel="stylesheet" type="text/css" href="css/iehacks.css" />
    <![endif]-->

    <script type="text/javascript" src="javascript/ajax.js"></script>
    <script type="text/javascript" src="javascript/jquery.js"></script>
    <script type="text/javascript" src="javascript/jquery.ui.all.js"></script>
    <script type="text/javascript" src="javascript/jquery.jgrowl.js"></script>
    <script type="text/javascript">
    $(function() {

        if (navigator.appVersion.indexOf("Win") != -1) {
            $('#openFolder').hide();
        }

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
        <?php
        if($message != "") {
            echo "$.jGrowl('$message');";
        }
        ?>
    });

    function getIp() {
        $.ajax({
            url: "ajaxController.php?function=ip",
            context: document.body,
            success: function(data){
                $('[name=myIp]').val(data);
            }
        });
    }

    function openFolder() {
        $.ajax({
            url: "ajaxController.php?function=open&location=" + $('input[name="video"]').val(),
            context: document.body,
            success: function(data){
                alert('done');
            }
        });
    }
    </script>
</head>

    <body>
        <div id="wrapper">
            <div id="logo"></div>
            <div id="nav-left"></div>
            <div id="nav-right"></div>
            <div id="nav">
                <ul id="menu">
                    <li>
                        <a class="media" href="index.php"><span></span></a>
                    </li>
                    <li class="" style="">
                        <a class="roku" href="roku.php"><span></span></a>
                    </li>
                </ul>
            </div>
            <div id="playercontainer">
                <center>
                    <?php
                    if(isset($_GET['new'])) {
                        echo "<h1>Welcome to roConnect</h1>";
                        echo "<ol><li>Please take a moment to verify and update these configuration settings</li>";
                        echo "<li>Go to the 'Media' tab and click 'Index Videos' to create a list of videos to stream</li>";
                        echo "<li>Go to the 'Roku' tab and connect to a Roku player to begin streaming to your TV</li></ol>";
                    }
                    ?>
                    <h2>Settings</h2>
                    <form name="configs" action="configuration.php" method="POST">
                        <?php
                        if(isset($_GET['new'])) echo '<input type="hidden" name="initUrl" value="true" />';
                        $checkTrue = "";
                        $checkFalse = "";
                        $recursiveTrue = "";
                        $recursiveFalse = "";
                        if($globs->getIMDb() == "true")
                            $checkTrue = 'selected="selected"';
                        else
                            $checkFalse = 'selected="selected"';
                        if($globs->getRecursive() == "true")
                            $recursiveTrue = 'selected="selected"';
                        else
                            $recursiveFalse = 'selected="selected"';
                        ?>
                        <table id="configs">
                            <tr><td class="setting">Version:</td><td><?php echo $globs->getVersion(); ?></td></tr>
                            <tr><td class="setting">Auto-IMDb Query:</td><td>
                                <select name="IMDb">
                                    <option value="true" <?php echo $checkTrue; ?> >On</option>
                                    <option value="false" <?php echo $checkFalse; ?> >Off</option>
                                </select>
                            </td></tr>
                            <tr><td class="setting">Path to Video Folder:</td><td><input type="text" name="video" size="30" value="<?php echo $globs->getVideoPath(); ?>" /> <input id="openFolder" type="button" onclick="openFolder();" value="Open Folder" /></td></tr>
                            <tr><td class="setting">Search Sub-Folders:</td><td>
                                <select name="recursive">
                                    <option value="true" <?php echo $recursiveTrue; ?> >On</option>
                                    <option value="false" <?php echo $recursiveFalse; ?> >Off</option>
                                </select>
                            </td></tr>
                            <tr><td class="setting">Server IP:</td><td><input type="text" name="myIp" size="30" value="<?php echo $globs->getPureIp(); ?>" /> <input type="button" onclick="getIp();" value="Get IP" /> (Like "192.168.1.1")</td></tr>
                            <tr><td class="setting">Server Config File:</td><td><input type="text" name="serverConf" size="30" value="<?php echo $globs->getServerConf(); ?>" /></td></tr>
                            <tr><td colspan="2"><input type="submit" name="submit" value="Save" /></td></tr>
                        </table>
                    </form>
                </center>
            </div>
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

<?php

    function checkIP($IP) {
        if (preg_match('/^(([1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5]).){3}([1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])(:[0-9]{1,5})?$/',$IP)) {
            return true;
        } else {
            return false;
        }
    }

    function testIP($IP) {
        error_log("http://$IP/roku/ipCheck.php");
        $ctx = stream_context_create(array(
                'http' => array(
                    'timeout' => 1
                )
                    )
        );
        if(file_get_contents("http://$IP/roku/ipCheck.php", 0, $ctx) == "Good")
                return true;
            else
                return false;
    }

?>