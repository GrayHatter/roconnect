<?php
include_once("CONSTANTS.php");
include_once("setup.php");

$xml = new rokuXML();
$scan = new scan($xml);

$devices = $scan->getDevices();

$GLOBALS['level'] = 0;
$GLOBALS['message'] = "";
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
    "http://www.w3.org/TR/html4/loose.dtd">

<html>
    <head>
        <link rel="stylesheet" type="text/css" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.7.2/themes/overcast/jquery-ui.css">
        <LINK href="css/fileinput.css" rel="stylesheet" type="text/css">
        <LINK href="css/style.css" rel="stylesheet" type="text/css">
        <LINK href="css/demo_table.css" rel="stylesheet" type="text/css">
        <LINK href="css/jquery.jgrowl.css" rel="stylesheet" type="text/css">
        <!--[if IE]>
        <link rel="stylesheet" type="text/css" href="css/iehacks.css" />
        <![endif]-->

        <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.0/jquery.min.js"></script>
        <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js"></script>
        <script type="text/javascript" src="javascript/jquery.fileinput.min.js"></script>
        <script type="text/javascript" src="javascript/ajaxfileupload.js"></script>
        <script type="text/javascript" language="javascript" src="javascript/jquery.dataTables.js"></script>
        <script type="text/javascript" language="javascript" src="javascript/popup.js"></script>
        <script type="text/javascript" src="javascript/jquery.jgrowl.js"></script>
        <script type="text/javascript" src="javascript/progress.js"></script>

        <script type="text/javascript">

            var oTable;
            var currentPlaylistId = "<?php echo isset($_GET['playlistId']) ? $_GET['playlistId'] : "0"; ?>";

            function checkVersion() {
                //alert("Version: " + currentVersion + " ID: " + serverId);
                $.ajax({
                    url: "ajaxController.php?function=updateCheck",
                    context: document.body,
                    success: function(data){
                        var info = data.split(',');
                        if (info[0] == 'update') {
                            $.jGrowl('A newer version (v' + info[1] + ') of roConnect is available!  Visit <a href="http://www.ro-connect.com/">www.ro-connect.com</a> for more information.');
                        }
                    }
                });
            }

            function newPlaylist() {
                $('.loading_movielist').hide();
                resetDialog();
                openDialog();
            }

            function processPlaylist(data) {
                var playlist = JSON.parse(data);
                
                //var editButton = '<td><input id="#edit'+playlist['id']+'" type="button" onclick="" value="Edit" /></td>';

                var test = $('<button/>',
                {
                    text: 'Edit',
                    click: function () {
                        resetDialog();
                        getMovies(playlist['file']);
                        $('input[name="feed"]').val(playlist['file']);
                        $('input[name="id"]').val(playlist['id']);
                        $('input[name="title"]').val(playlist['title']);
                        $('textarea#tableDescription').val(playlist['description']);
                        $('#image_left').attr('src', playlist['image']);
                        openDialog();
                    }
                });

                var parent = $('<tr id="'+playlist['id']+'"><td id="select'+playlist['id']+'">'+playlist['title']+'</td><td class="button"></td></tr>');

                if (playlist['id'] != '0') {
                    parent.children('.button').append(test).end()
                }

                $('#playlistTable tr:last').after(parent);
                $('#select'+playlist['id']).click(function(){
                    currentPlaylistId = playlist['id'];
                    oTable = initTable();
                    //alert("done");
                });

                $('#edit'+playlist['id']).bind('click', function(){
                    resetDialog();
                    getMovies(playlist['file']);
                    $('input[name="feed"]').val(playlist['file']);
                    $('input[name="id"]').val(playlist['id']);
                    $('input[name="title"]').val(playlist['title']);
                    $('textarea#tableDescription').val(playlist['description']);
                    $('#image_left').attr('src', playlist['image']);
                    openDialog();
                });
                
                $('#'+playlist['id']).hover(function () {
                    $(this).addClass('highlight_row');
                },
                function() {
                    $(this).removeClass('highlight_row');
                });
            }

            function resetDialog() {
                $('input[name="feed"]').val("");
                $('input[name="id"]').val("");
                $('input[name="title"]').val("");
                $('textarea#tableDescription').val("");
                $('#image_left').attr('src', "images/unknown_playlist.png");
                $('.movie').prop('checked', false);
                $('#playlistImage').fileinput("reset");
                $('#playlistImage').fileinput({
                    buttonText: 'Browse...'
                });
            }

            function openDialog() {
                $("body").css("overflow", "hidden");
                $( "#dialog" ).dialog({
                    width: 510,
                    height: 615,
                    resizable: false,
                    buttons: {
                        "Delete": function() {
                            $(this).dialog("close");
                            $("body").css("overflow", "auto");
                            deletePlaylist();
                        },
                        "Save": function() {
                            $(this).dialog("close");
                            $("body").css("overflow", "auto");
                            updatePlaylist();
                        }
                    },
                    show: 'fade',
                    hide: 'fade',
                    modal: true,
                    draggable: false
                });
            }

            function processMovie(data) {
                var movie = JSON.parse(data);
                $('input[name="'+movie['contentId']+'"]').prop('checked', true);
            }

            function processAllMovie(data, count) {
                var movie = JSON.parse(data);

                $('#movielistTable > tbody:last').after('<tr' + ((count%2 == 1) ? ' class="odd"' : ' class="even"') + '><td>'+movie['title']+'</td><td><input class="movie" type="checkbox" name="'+movie['contentId'] + '" value="'+movie['contentId'] + '" /></td></tr>');
            }

            function getAllMovies() {
                $("#movielistTable").find("tr:gt(0)").remove();
                $.ajax({
                    url: "ajaxController.php?function=allMovies",
                    context: document.body,
                    success: function(data){
                        var obj = JSON.parse(data);
                        var count = 0;
                        for (var key in obj) {
                            if (obj.hasOwnProperty(key)) {
                                processAllMovie(obj[key], count);
                            }
                            count++;
                        }
                    }
                });
            }

            function getMovies(movieFile) {
                $.ajax({
                    url: "ajaxController.php?function=movies&movielistFile=" + movieFile,
                    context: document.body,
                    success: function(data){
                        var obj = JSON.parse(data);
                        var count = 0;
                        for (var key in obj) {
                            if (obj.hasOwnProperty(key)) {
                                processMovie(obj[key]);
                            }
                            count++;
                        }
                        $('.loading_movielist').hide();
                    }
                });
            }

            function deletePlaylist() {

                if ($('input[name="id"]').val() != "") {
                    var r=confirm('Are you sure you want to delete "'+$('input[name="title"]').val()+'"?');
                    if (r==true) {

                        $.ajax({
                            url: "ajaxController.php?function=deletePlaylist&playlist_id=" + $('input[name="id"]').val(),
                            context: document.body,
                            success: function(data){
                                if(data == "success")
                                    getPlaylists();
                            }
                        });
                    }
                }
            }

            function updatePlaylist() {
                var sList = [];
                $('input[type=checkbox]').each(function () {
                    if (this.checked) {
                        sList.push({"contentId":$(this).val()});
                    }
                });
                if ($('#playlistImage').fileinput("getValue") != "") {
                    $.ajaxFileUpload({
                        url:'doajaxfileupload.php',
                        secureuri:false,
                        fileElementId:'playlistImage',
                        dataType: 'json',
                        success: function (data, status) {
                            if(typeof(data.error) != 'undefined') {
                                if(data.error != '') {
                                    alert(data.error);
                                } else {
                                    var jsonResponse = {"id":$('input[name="id"]').val(),"feed":$('input[name="feed"]').val(),"title":$('input[name="title"]').val(),"description":$('#tableDescription').val(),"image":data.msg,"movies":sList};
                                    sendPlaylist(jsonResponse);
                                }
                            }
                        },
                        error: function (data, status, e) {
                            alert(e);
                        }
                    });
                } else {
                    var jsonResponse = {"id":$('input[name="id"]').val(),"feed":$('input[name="feed"]').val(),"title":$('input[name="title"]').val(),"description":$('#tableDescription').val(),"image":$('#image_left').attr('src'),"movies":sList};
                    sendPlaylist(jsonResponse);
                }
            }

            function sendPlaylist(jsonPlaylist) {
                $.ajax({
                    url: "ajaxController.php?function=updatePlaylist&playlist=" + JSON.stringify(jsonPlaylist),
                    context: document.body,
                    success: function(data){
                        if(data == "success") {
                            getPlaylists();
                            oTable.fnDraw(false);
                        }
                    }
                });
            }

            function getPlaylists() {
                $("#playlistTable").find("tr:gt(0)").remove();
                $.ajax({
                    url: "ajaxController.php?function=playlists",
                    context: document.body,
                    success: function(data){
                        var obj = JSON.parse(data);
                        var count = 0;
                        for (var key in obj) {
                            if (obj.hasOwnProperty(key)) {
                                processPlaylist(obj[key]);
                            }
                            count++;
                        }
                        if(count == 0) {
                            $('#playlistTable > tbody:last').after('<tr><td>No Playlists Found</td></tr>');
                        }
                        $('.loading_playlist').hide();
                    }
                });
            }

            $(function() {

                // Checks to see if the software is up to date
                checkVersion();

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
                $("#progress_info").css("opacity", "0");


                getAllMovies();
                $('#playlistImage').fileinput({
                    buttonText: 'Browse...'
                });

                getPlaylists();
            });

            var giRedraw = false;
            var count = 1;

            //Handles data table info
            $(document).ready(function() {

                /* Add a click handler to the rows - this could be used as a callback */
                $("#movTable tbody").click(function(event) {
                    if(event.target.nodeName != 'A') {
                        $(oTable.fnSettings().aoData).each(function (){
                            $(this.nTr).removeClass('row_selected');
                        });
                        $(event.target.parentNode).addClass('row_selected');
                        centerPopup();
                        //load popup
                        loadPopup(fnGetSelected(oTable, -1), $(".paginate_active").html(), currentPlaylistId);
                    }
                });


                /* Init the table */
                oTable = initTable('0');
        
                var oSettings = oTable.fnSettings();

<?php
$page = 0;
if (isset($_GET['page']))
    $page = $_GET['page'] - 1;
?>

        oSettings._iDisplayStart = <?php echo $page; ?> * oSettings._iDisplayLength;
        oTable.fnDraw(false);

    });

    function initTable() {

        $('#movTable').hide();

        var mTable;
        /* Init the table */
        mTable = $('#movTable').dataTable( {
            "aoColumns": [
                /* contentId */ {"bVisible": false,
                    "bSearchable": false},
                {"bVisible": false,
                    "bSearchable": false},
                {"bVisible": false,
                    "bSearchable": false},
                {"bVisible": false,
                    "bSearchable": false},
                {"bVisible": false,
                    "bSearchable": false},
                {"bVisible": false,
                    "bSearchable": false},
                {"bVisible": false,
                    "bSearchable": false},
                /* Title */     null,
                /* Genre */     null,
                /* Rating */    null,
                /* Runtime */   null,
                /* Rank */      null,
                {"bVisible": false,
                    "bSearchable": false}
            ],
            "bProcessing": true,
            "bDestroy": true,
            "bServerSide": true,
            "sPaginationType": "full_numbers",
            "bLengthChange": false,
            "bAutoWidth": false,
            "sAjaxSource": "classes/dataTable.class.php?type=movies&playlist=" + currentPlaylistId,
            "fnInitComplete": function(oSettings, json) { $('#movTable').show(); },
            "fnRowCallback": function (nRow, aData, iDisplayIndex) {
                for (var i=0; i<aData.length; i++) {
                    if (aData[i] == "" && i > 6) {
                        $('td:eq('+(i-7)+')', nRow).html('-');
                    }
                }
                $(nRow).append('<div class="tableMenu"><a class="watchButtonNormal" href="#" onclick="watchMovie(\''+aData[0]+'\')"></a></div>');
                $(nRow).hover(
                function () {
                    // animate opacity to full
                    $(this).children("div").stop().css('z-index', '1').animate({
                        opacity: 1
                    }, "slow");
                },
                // on mouse out
                function () {
                    // animate opacity to nill
                    $(this).children("div").stop().css('opacity', '0').css('z-index', '-1');
                }
            );
                return nRow;
            }
        } );

        //alert("defined");

        $('#movTable').width('600px');

        //alert("returning");

        return mTable;
    }
    
    /* Get the rows which are currently selected */
    function fnGetSelected(oTableLocal, row)
    {
        var aReturn = new Array();
        var aTrs = oTableLocal.fnGetNodes();

        if (row >= 0) {
            aReturn.push(oTableLocal.fnGetData(row));
            return aReturn;
        }

        for ( var i=0 ; i<aTrs.length ; i++ )
        {
            if ( $(aTrs[i]).hasClass('row_selected') )
            {
                //aReturn.push( aTrs[i] );
                aReturn.push(oTableLocal.fnGetData(i));
            }
        }
        return aReturn;
    }

    var popupStatus = 0;

    //Ajax calls

    function fadeInfo() {
        $("#progress_info").animate({
            opacity: 0
        }, "slow")
    }

    function func1 () {
        console.log("indexing");
        $.jGrowl("Indexing Started");
        $("#progress_info").css("opacity", "1");
        polling_start();
        $.ajax({
            url: "ajaxController.php?function=index",
            context: document.body,
            success: function(data){
                getAllMovies();
                $.jGrowl(data);
                oTable.fnDraw(false);
                setTimeout("fadeInfo()",5000);
            }
<?php if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
    echo ",timeout: 1000"; ?>
                                });
                            }

                            function watchMovie (id) {
                                window.location = "watch.php?contentId="+id+"&page="+$(".paginate_active").html()+"&playlistId="+currentPlaylistId;
                            }

                            $(document).ready(function() {

                                $('.myButton').mousedown(function() {

                                    $(this).removeClass('myButton').addClass('myButton_pressed');

                                }).mouseup(function() {

                                    $(this).removeClass('myButton_pressed').addClass('myButton');
                                });
                            });

                            var blnSiteUp = true;

        </script>

    </head>

    <body class="media">
        <div id="popupContact">
            <a id="popupContactClose"><span style="cursor: pointer">x</span></a>
            <img id="popupPoster" src="" alt="" />
            <div id="metaInfo">
                <h1 id="popupTitle"></h1>
                <b>Synopsis</b><p id="popupSynopsis"></p>
                <table>
                    <tr>
                        <td><b>Genre</b></td><td id="popupGenre"></td>
                        <td><b>Runtime</b></td><td id="popupRuntime"></td>
                    </tr>
                    <tr>
                        <td><b>Rating</b></td><td id="popupRating"></td>
                        <td><b>Rank</b></td><td id="popupRank"></td>
                    </tr>
                    <tr>
                        <td><b>Year</b></td><td id="popupYear"></td>
                    </tr>
                    <tr id="director">
                        <td><b>Director</b></td><td id="popupDirector" colspan="3" class="three"></td>
                    </tr>
                    <tr id="actors">
                        <td><b>Actors</b></td><td id="popupActors" colspan="3" class="three"></td>
                    </tr>
                    <tr id="artist">
                        <td><b>Artist</b></td><td id="popupArtist" colspan="3" class="three"></td>
                    </tr>
                    <tr id="album">
                        <td><b>Album</b></td><td id="popupAlbum" colspan="3" class="three"></td>
                    </tr>
                    <tr>
                        <td><b>Filename</b></td><td id="popupFilename" colspan="3" class="three"></td>
                    </tr>
                </table>
                <a id="popupEdit" style="float: right" href="#">Edit</a>
            </div>
            <a id="watchNow" class="watchButton" style="float: right" href="#" onclick=""><img src="images/site/watchButton.png" alt="Watch" /></a>
        </div>
        <div id="backgroundPopup"></div>
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

            <div id="table_cont" class="panel-container">

                <div class="sidebar">
                    <a href="configuration.php" class="myButton">Configuration</a>
                    <a onclick="func1();" class="myButton">Index Media</a>
                    <a onclick="newPlaylist();" class="myButton">Add Playlist</a>
                    <div id="playlists">
                        <div class="loading_playlist">
                            <img src="images/site/roku-loader.gif" alt="" /><br/><span class="loading">Loading...</span>
                        </div>
                        <table id="playlistTable" cellpadding="0" cellspacing="0" border="0">
                            <tr><td class="head" colspan="2">Playlists</td></tr>
                        </table>
                    </div>
                    <br/>
                    <center>
                        <div id="progress_info">
                            <div id="progressbar"></div>
                            <div id="message"></div>
                        </div>
                    </center>
                </div>
                <div class="main">
                    <table cellpadding="0" cellspacing="0" border="0" class="display" id="movTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>ID</th>
                                <th>ID</th>
                                <th>ID</th>
                                <th>ID</th>
                                <th>ID</th>
                                <th>ID</th>
                                <th width="70%">Title</th>
                                <th width="7%">Genre</th>
                                <th width="7%">Rating</th>
                                <th width="7%">Runtime</th>
                                <th width="7%">Rank</th>
                                <th>ID</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="5" class="dataTables_empty">Loading data from server . . .</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th>ID</th>
                                <th>ID</th>
                                <th>ID</th>
                                <th>ID</th>
                                <th>ID</th>
                                <th>ID</th>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Genre</th>
                                <th>Rating</th>
                                <th>Runtime</th>
                                <th>Rank</th>
                                <th>ID</th>
                            </tr>
                        </tfoot>
                    </table>
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
        <div id="serverCheck" style="display: block;"></div>
        <div id="dialog" title="Edit Playlist">
            <form name="form" action="" method="POST" enctype="multipart/form-data">
                <img id="image_left" src="" alt="" />
                <div class="dialog_info">
                    <table class="dialog_table">
                        <tr><td>Title</td><td><input name="title" type="text" value="" size="30" /></td></tr>
                        <tr><td>Description</td><td><textarea id="tableDescription" name="description" rows="3" cols="30"></textarea></td></tr>
                        <tr><td>Image</td><td><input id="playlistImage" name="playlistImage" type="file" /></td></tr>
                        <input name="id" type="hidden" value="" />
                        <input name="feed" type="hidden" value="" />
                    </table>
                </div>
                <p>Media:</p>
                <div id="media_list">
                    <div class="loading_movielist">
                        <img src="images/site/roku-loader.gif" alt="" /><br/><span class="loading">Loading...</span>
                    </div>
                    <table cellpadding="0" cellspacing="0" border="0"  id="movielistTable" class="display">
                        <tr></tr>
                    </table>
                </div>
            </form>
        </div>
    </body>
</html>
