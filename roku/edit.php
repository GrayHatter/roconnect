<?php

require_once("CONSTANTS.php");

$movieMeta = new videoMetaData();
$movies = $movieMeta->getMovies();
$movies = array_values($movies);

$globs = new globals(false);

$id = "";
$thisMovie = null;

if(isset($_GET['contentId']))
    $id = $_GET['contentId'];

foreach ($movies as $movie) {
    if($movie->contentId == $id)
        $thisMovie = $movie;
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">

<html>
<head>
    <LINK href="css/style.css" rel="stylesheet" type="text/css">
    <LINK href="css/demo_table.css" rel="stylesheet" type="text/css">
    <LINK href="css/jquery.jgrowl.css" rel="stylesheet" type="text/css">
    <!--[if IE]>
    <link rel="stylesheet" type="text/css" href="css/iehacks.css" />
    <![endif]-->
        <link rel="stylesheet" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.7.2/themes/ui-lightness/jquery-ui.css" type="text/css" />

    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.0/jquery.min.js"></script>
    <script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js"></script>
    <script type="text/javascript" language="javascript" src="javascript/jquery.dataTables.js"></script>
    <script type="text/javascript" src="javascript/ajax.js"></script>
    <script type="text/javascript" src="javascript/jquery.jgrowl.js"></script>

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

    var oTable;
    var giRedraw = false;

    $(document).ready(function() {

        /* Add a click handler to the rows - this could be used as a callback */
	$("#movTable tbody").click(function(event) {
		$(oTable.fnSettings().aoData).each(function (){
			$(this.nTr).removeClass('row_selected');
		});
		$(event.target.parentNode).addClass('row_selected');

                //Click code here
                var data = String(fnGetSelected(oTable)).split(",");
                func1(data[0]);
	});

	/* Init the table */
	oTable = $('#movTable').dataTable( {
            "aoColumns": [
                /* contentId */ {"bVisible": false,
                                 "bSearchable": false},
                /* Title */     null
            ],
            "bProcessing": true,
            "bServerSide": true,
            "iDisplayLength": 5,
            "sPaginationType": "two_button",
            "bLengthChange": false,
            "bAutoWidth": false,
            "fnServerData": function ( sSource, aoData, fnCallback ) {
			/* Add some extra data to the sender */
                        var episode = document.getElementById("episode").checked;
			aoData.push( { "name": "episode", "value": episode } );
			$.getJSON( sSource, aoData, function (json) {
				/* Do whatever additional processing you want on the callback, then tell DataTables */
				fnCallback(json)
			} );
		},
            "sAjaxSource": "classes/dataTable.class.php?type=imdb&title=<?php echo $thisMovie->title; ?>"
        } );

    } );

    /* Get the rows which are currently selected */
    function fnGetSelected( oTableLocal )
    {
	var aReturn = new Array();
	var aTrs = oTableLocal.fnGetNodes();

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

    var arr = new Array("function","imdbid");

    function func1 (mid) {
    if($.inArray("mid", arr) > -1) {
            arr[$.inArray("mid", arr)+1] = mid;
        } else {
            arr.push("mid", mid);
        }
        myAjax("ajaxController.php", arr);
        $("#progressbar").css("height", "22px");
        $("#progressbar").progressbar({ value: 100 });
    }

    function ajax_ready (text) {
        var values;
        eval("values="+text);
        document.getElementById("movieTitle").innerHTML = $("<div/>").html(values.aaData[0]).text();
        document.getElementById("popupPoster").src = values.aaData[1];
        document.getElementsByName("synopsis").item(0).value = $("<div/>").html(values.aaData[2]).text();
        document.getElementsByName("Actors").item(0).value = $("<div/>").html(values.aaData[3]).text();
        document.getElementsByName("Director").item(0).value = $("<div/>").html(values.aaData[4]).text();
        document.getElementsByName("ReleaseDate").item(0).value = $("<div/>").html(values.aaData[5]).text();
        document.getElementsByName("genres").item(0).value = $("<div/>").html(values.aaData[6]).text();
        document.getElementsByName("Rating").item(0).value = $("<div/>").html(values.aaData[7]).text();
        document.getElementsByName("StarRating").item(0).value = $("<div/>").html(values.aaData[8]).text();
        if(values.aaData[1] == "") {
            document.getElementsByName("image").item(0).value = '<?php echo "roku/images/unknown.png"; ?>'
        } else {
            document.getElementsByName("image").item(0).value = $("<div/>").html(values.aaData[1]).text();
        }
        document.getElementsByName("title").item(0).value = $("<div/>").html(values.aaData[0]).text();

        if ($(".sidebar").height() < $(".main").height())
            $(".sidebar").height($(".main").height());
        else
            $(".main").height($(".sidebar").height());

        $("#progressbar").progressbar("destroy");
        $("#progressbar").css("height", "0");
    }

    </script>
    <style>
        .ui-progressbar-value { background-image: url(images/site/pbar-ani.gif); }
    </style>

<html>
    <body>
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
            <div class="panel-container">

                <div class="panel">
                    <div class="sidebar">
                        <input type="checkbox" onclick="oTable.fnDraw(false);" id="episode" value="" />&nbsp;Include Episodes
                        <table cellpadding="0" cellspacing="0" border="0" class="display" id="movTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Title</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="2" class="dataTables_empty">Loading data from server</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th>ID</th>
                                    <th>Title</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div class="main">
                        <div id="progressbar"></div>
                    <?php

                        //print_r($thisMovie);

                        echo '<form action="/roku/saveMeta.php" method="POST" enctype="multipart/form-data">';
                        echo '<input type="hidden" name="contentId" value="'.$thisMovie->contentId.'" />';
                        echo '<input type="hidden" name="page" value="'.$_GET['page'].'" />';
                        echo '<input type="hidden" name="playlistId" value="'.$_GET['playlistId'].'" />';
                        echo '<img id="popupPoster" src="'.$thisMovie->imageRelativePath().'" alt="" />';
                        echo '<div id="metaInfo">';
                        echo '    <h2 id="movieTitle">'.$thisMovie->title.'</h2>';
                        echo '    <p><b>File:</b> '.$thisMovie->file.'</p>';
                        echo '    <b>Title:</b> <input type="text" name="title" value="'.$thisMovie->title.'" /><br/>';
                        if ($thisMovie->contentType == "movie") {
                            echo '    <b>Synopsis: </b><textarea row="60" cols="42" name="synopsis">'.$thisMovie->synopsis.'</textarea>';
                        }
                        echo '    <table id="metaTable">';
                        echo '        <tr>';
                        echo '            <td><b>Genre: </b></td><td><input type="text" name="genres" value="'.$thisMovie->genres.'" /></td>';
                        echo '            <td><b>Year: </b></td><td><input type="text" name="ReleaseDate" value="'.$thisMovie->ReleaseDate.'" /></td>';
                        echo '        </tr>';
                        echo '        <tr>';
                        echo '            <td><b>Rating: </b></td><td><input type="text" name="Rating" value="'.$thisMovie->Rating.'" /></td>';
                        echo '            <td><b>Rank: </b></td><td><input type="text" name="StarRating" value="'.$thisMovie->StarRating.'" /></td>';
                        echo '        </tr>';
                        if ($thisMovie->contentType == "movie") {
                            echo '        <tr>';
                            echo '            <td><b>Director: </b></td><td colspan="3"><input type="text" name="Director" value="'.$thisMovie->Director.'" /></td>';
                            echo '        </tr>';
                            echo '        <tr>';
                            echo '            <td><b>Actors: </b></td><td colspan="3"><input type="text" name="Actors" value="'.$thisMovie->Actors.'" /></td>';
                            echo '        </tr>';
                        } else {
                            echo '        <tr>';
                            echo '            <td><b>Artist: </b></td><td colspan="3"><input type="text" name="Artist" value="'.$thisMovie->Artist.'" /></td>';
                            echo '        </tr>';
                            echo '        <tr>';
                            echo '            <td><b>Album: </b></td><td colspan="3"><input type="text" name="Album" value="'.$thisMovie->Album.'" /></td>';
                            echo '        </tr>';
                        }
                        echo '        <tr>';
                        echo '            <td><b>Image: </b></td><td  colspan="3"><input id="hdImg" type="file" name="hdImg" /></td><input type="hidden" name="image" value="'.$thisMovie->imageRelativePath().'" />';
                        echo '        </tr>';
                        echo '        <tr>';
                        echo '            <td><input type="submit" value="Save" /></td>';
                        echo '        </tr>';
                        echo '    </table>';
                        echo '</div>';
                        echo '</form>';

                    ?>
                    </div>
                </div>
            <div id="panel-cont-bot"></div>
            </div>
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