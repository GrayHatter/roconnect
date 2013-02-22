<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">

<html>
<head>
    <LINK href="css/style.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" type="text/css" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.7.2/themes/overcast/jquery-ui.css">
    <!--[if IE]>
    <link rel="stylesheet" type="text/css" href="css/iehacks.css" />
    <![endif]-->

    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.0/jquery.min.js"></script>
    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js"></script>

    <script type="text/javascript">

    $(function() {

        $("#opener").hide();

        var $dialog = $('<div></div>')
        .html('Enter the name and IP address:<br/>')
        .append('<table>')
        .append('<tr><td>Name:</td><td><input type="text" name="name" value="" /></td></tr>')
        .append('<tr><td>IP Addr: </td><td><input type="text" name="location" value="" /></td></tr>')
        .append('<tr><td><input id="closer" type="button" onclick="addRoku();" value="Enter" /></td></tr>')
        .dialog({
            autoOpen: false,
            title: 'Add New Roku'
        });

        $('#opener').click(function() {
            $dialog.dialog('open');
            // prevent the default action, e.g., following a link
            return false;
        });

        $('#closer').click(function() {
            $dialog.dialog('close');
            // prevent the default action, e.g., following a link
            return false;
        });

        getRokus();

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
        if ($(".sidebar").height() < $(".main").height())
            $(".sidebar").height($(".main").height());
        else
            $(".main").height($(".sidebar").height());
    });

    function getRokus() {
        $("#rokuTable").find("tr:gt(0)").remove();
        $('.loading').show();
        $('#opener').hide();
        $.ajax({
            url: "ajaxController.php?function=rokus",
            context: document.body,
            success: function(data){
                var obj = JSON.parse(data);
                var count = 0;
                for (var key in obj) {
                    if (obj.hasOwnProperty(key)) {
                        processRoku(obj[key]);
                    }
                    count++;
                }
                if(count == 0) {
                    $('#rokuTable > tbody:last').after('<tr><td>No Roku Players Found</td></tr>');
                }
                $('.loading').hide();
                $("#opener").show();
            }
        });
    }

    function addRoku() {
        $.ajax({
            url: "ajaxController.php?function=addRoku&location=" + escape("http://" + $('input[name="location"]').val() + ":8060/") + "&name=" + escape($('input[name="name"]').val()),
            context: document.body,
            success: function(data){
                $('input[name="location"]').val("");
                $('input[name="name"]').val("");
                processRoku(JSON.parse(data));
            }
        });
    }

    function addRokuName(usn, location) {
        var name=prompt("Please enter a name","Living Room");
        if (name != null && name != "") {
            $.ajax({
                url: "ajaxController.php?function=addRokuName&location=" + escape(location) + "&name=" + escape(name) + "&usn=" + escape(usn),
                context: document.body,
                success: function(data){
                    if (data == 'success') {
                        getRokus();
                    }
                }
            });
        }
    }

    function deleteRoku(usn) {
        $.ajax({
            url: "ajaxController.php?function=deleteRoku&usn=" + escape(usn),
            context: document.body,
            success: function(data){
                $('#'+usn).remove();
            }
        });
    }

    function launch(url) {
        $.ajax({
            url: "ajaxController.php?function=launch&url=" + escape(url),
            context: document.body
        });
    }

    function processRoku(data) {
        var roku = JSON.parse(data);
        var name = roku['usn'];
        if (roku['name'] != "") {
                name = roku['name'];
        }
        switch(parseInt(roku['status'])) {
            case 0:
                $('#rokuTable > tbody:last').after('<tr id="'+roku['usn']+'"><td><img src="images/site/rokuDevice_yellow.png" alt="" /></td><td>'+name+'</td><td> (Device Offline)</td><td><input class="button_fill" type="button" onclick="addRokuName(\''+roku['usn']+'\', \''+roku['location']+'\')" value="Edit" /></td><td><input type="button" onclick="deleteRoku(\''+roku['usn']+'\')" value="Delete" /></td></tr>');
                break;
            case 1:
                $('#rokuTable > tbody:last').after('<tr id="'+roku['usn']+'"><td><img src="images/site/rokuDevice_red.png" alt="" /></td><td>'+name+'</td><td><input class="button_fill" type="button" onclick="window.open(\'https://owner.roku.com/add/roconnect\');" value="Add Channel" /></td><td><input type="button" onclick="addRokuName(\''+roku['usn']+'\', \''+roku['location']+'\')" value="Edit" /></td><td><input type="button" onclick="deleteRoku(\''+roku['usn']+'\')" value="Delete" /></td></tr>');
                break;
            case 2:
                $('#rokuTable > tbody:last').after('<tr id="'+roku['usn']+'"><td><img src="images/site/rokuDevice_green.png" alt="" /></td><td>'+name+'</td><td><input class="button_fill" type="button" onclick="launch(\''+roku['location']+'\')" value="Connect" /></td><td><input type="button" onclick="addRokuName(\''+roku['usn']+'\', \''+roku['location']+'\')" value="Edit" /></td><td><input type="button" onclick="deleteRoku(\''+roku['usn']+'\')" value="Delete" /></td></tr>');
                break;
            default:
                $('#rokuTable > tbody:last').after('<tr><td>Error Retrieving Roku Player Information</td></tr>');
                break;
        }
    }

    </script>
</head>

    <body class="roku">
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
            <div class="panel-container">

                <div class="sidebar">
                    <div class="sidebarText">This scan shows all of the Roku devices found on the local area network.  If the Roku device light is:
                        <ul>
                            <li><b>Red</b> means that the roConnect application is not yet installed.</li>
                            <li><b>Green</b> means the roConnect application is installed and ready to connect.</li>
                            <li><b>Yellow</b> means the Roku player is not online.</li>
                        </ul>
                        <p>To manually add a Roku player click the "Add Roku" button and supply the Roku's IP address (i.e. 192.168.1.1) and any name.</p>
                    </div>
                </div>
                <div class="main">
                    <div class="loading">
                        <img src="images/site/roku-loader.gif" alt="" /><br/><span class="loading">Searching...</span>
                    </div>
                    <table id="rokuTable">
                        <tr></tr>
                    </table>
                    <br/>
                    <button id="opener">Add Roku</button>
                </div>

            </div>

            <div id="panel-cont-bot"></div>

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
