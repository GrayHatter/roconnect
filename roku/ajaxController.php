<?php

include_once("CONSTANTS.php");
require("classes/imdbphp2-2.0.4/imdbsearch.class.php");
require("classes/imdbphp2-2.0.4/imdb.class.php");

$function = "default";
$globs = new globals();

if(isset($_GET['function']))
    $function = $_GET['function'];

switch($function) {

    case "updateCheck":
        $url = "http://www.ro-connect.com/updateCheck.php/" . $globs->getServerId() . "/" . $globs->getVersion();
        $output = file_get_contents($url);
        echo $output;
        break;

    case "index":
        error_log("AJAX INDEXING CALLED");
        echo $globs->movieMeta->indexMovies();
        break;

    case "imdbid":
        $movie = new movie();
        $imdb = new imdb($_GET["mid"]);
        $imdb->setid($_GET["mid"]);

        if (($photo_url = $imdb->photo_localurl()) != FALSE) {
            $movie->sdImg = substr($photo_url, 2);
            $movie->hdImg = substr($photo_url, 2);
        }

        $movie->title = str_replace("and#x27;", "'", $imdb->title());
        $movie->synopsis = str_replace("and#x27;", "'", str_replace("\n", "", str_replace("&raquo;","",str_replace("&nbsp;","",strip_tags($imdb->plotoutline())))));
        $movie->genres = $imdb->genre();
        $mpaa = $imdb->mpaa();
        if(array_key_exists('USA', $mpaa))
            $movie->Rating = $mpaa['USA'];
        else
            $movie->Rating = "NR";
        $movie->StarRating = ((int) $imdb->rating() * 10);
        $movie->Actors = arrayToString(array_slice($imdb->cast(), 0, 3));
        $movie->Director = arrayToString(array_slice($imdb->director(), 0, 3));
        $movie->ReleaseDate = $imdb->year();
        $sOutput = '{';
        $sOutput .= '"aaData": [ ';
        $sOutput .= '"'.$movie->title.'","'.$movie->hdImg.'",'.json_encode(str_replace(",","&#44;",$movie->synopsis)).','.json_encode(str_replace(",","&#44;",$movie->Actors)).','.json_encode(str_replace(",","&#44;",$movie->Director)).',"'.$movie->ReleaseDate.'","'.$movie->genres.'","'.$movie->Rating.'","'.$movie->StarRating.'"';
        $sOutput .= "]";
        $sOutput .= '}';
        echo $sOutput;
        break;

    case "launch":
        $url = $_GET['url'];
        $excon = new externalcontrol($url);
        $baseUrl = urlencode($globs->getIp());
        $excon->launch(urlencode($excon->getChannelId("roConnect")), "?baseUrl=$baseUrl");
        echo "1";
        break;

    case "install":
        $url = $_GET['url'];
        $excon = new externalcontrol($url);
        $excon->install();
        break;

    case "ip":
        $matches = array();

        //Linux machine
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'LIN') {
            $interface = false;
            exec("ifconfig", $output, $return);
            foreach ($output as $line) {
                if (strpos($line, "th0"))
                    $interface = true;
                if ($interface) {
                    if (strpos($line, "inet ")) {
                        if(preg_match_all('/([0-9]{1,3}\.){3}[0-9]{1,3}/', $line, $matches) !== false)
                            break;
                    }
                }
            }
            if(count($matches) > 0) {
                echo $matches[0][0];
            } else
                echo "0.0.0.0";
        }
        //Mac Machine
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'DAR') {
            $interface = false;
            exec("ifconfig", $output, $return);
            foreach ($output as $line) {
                if (strpos($line, "n0"))
                    $interface = true;
                if ($interface) {
                    if (strpos($line, "net ")) {
                        if(preg_match_all('/([0-9]{1,3}\.){3}[0-9]{1,3}/', $line, $matches) !== false)
                            break;
                    }
                }
            }
            if(count($matches) > 0) {
                echo $matches[0][0].":8888";
            } else
                echo "0.0.0.0";
        }
        //Windows Machine
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $interface = false;
            exec("ipconfig /all", $output, $return);
            foreach ($output as $line) {
                if (strpos($line, "IPv4") || strpos($line, "IP Address")) {
                    if(preg_match_all('/([0-9]{1,3}\.){3}[0-9]{1,3}/', $line, $matches) !== false)
                        break;
                }
            }
            if(count($matches) > 0) {
                echo $matches[0][0];
            } else
                echo "0.0.0.0";
        }
        break;

    case "open":
        $location = $_GET['location'];

        //Linux machine
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'LIN') {
            exec("open '$location'", $output, $return);
        }
        //Mac Machine
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'DAR') {
            exec("open '$location'", $output, $return);
        }
        //Windows Machine
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
			error_log("Open Windows");
            echo "REsponse: " . exec("explorer '$location'", $output, $return);
        }
        break;

    case "rokus":
        $xml = new rokuXML();
        $scan = new scan($xml);
        $jsonArray = array();

        $xml->writeRokuXML($scan->getDevices());
        $devices = $xml->readRokuXml();
        foreach($devices as $device) {
            $excon = new externalcontrol($device->location);
            if($excon->isOnline()) {
                if($excon->hasChannel("roConnect")) {
                    $device->status = "2";
                } else {
                    $device->status = "1";
                }
            } else {
                $device->status = "0";
            }

            $jsonArray[] = $device->toJSON();
        }
        echo json_encode($jsonArray);
        break;

    case "addRoku":
        $data = array("usn" => time(), "location" => $_GET['location'], "name" => $_GET['name']);
        $roku = new roku($data);
        $excon = new externalcontrol($roku->location);
        if($excon->isOnline()) {
            if($excon->hasChannel("roConnect")) {
                $roku->status = "2";
            } else {
                $roku->status = "1";
            }
        } else {
            $roku->status = "0";
        }
        $xml = new rokuXML();
        $xml->writeRokuXML(array($roku));
        echo json_encode($roku->toJSON());
        break;

    case "addRokuName":
        $data = array("usn" => $_GET['usn'], "location" => $_GET['location'], "name" => $_GET['name']);
        $roku = new roku($data);
        $xml = new rokuXML();
        $xml->writeRokuXML(array($roku));
        echo "success";
        break;

    case "deleteRoku":
        $xml = new rokuXML();
        $xml->deleteRoku($_GET['usn']);
        echo "Done";
        break;

    case "playlists":
        $jsonArray = array();
        $playlistXML = new playlistXML();
        $playlists = $playlistXML->readPlaylistXml();
        foreach($playlists as $playlist) {
            $jsonArray[] = $playlist->toJSON();
        }
        echo json_encode($jsonArray);
        break;

    case "allMovies":
        $jsonArray = array();
        $movieMeta = new videoMetaData();
        $movies = $movieMeta->getMovieList(ALL_XML);
        foreach ($movies as $movie) {
            $jsonArray[] = $movie->toJSON();
        }
        echo json_encode($jsonArray);
        break;

    case "movies":
        $jsonArray = array();
        $movieMeta = new videoMetaData();
        $movieXML = new movieXML(DIR_PREFIX.$_GET['movielistFile']);
        $movies = $movieXML->readMovies();
        foreach ($movies as $movie) {
            $jsonArray[] = $movie->toJSON();
        }
        echo json_encode($jsonArray);
        break;

    case "updatePlaylist":
        $jsonPlaylist = json_decode(stripslashes($_GET['playlist']), true);
        
        $id = ($jsonPlaylist['id'] == "") ? uniqid() : $jsonPlaylist['id'];
        $feed = ($jsonPlaylist['feed'] == "") ? 'roku/xml/' . $id . ".xml" : 'roku/xml/' . $jsonPlaylist['feed'];
        $image = str_replace(" ", "%20", $jsonPlaylist['image']);
        $image = 'roku/' . $image;

        $playlist = new playlist($id, $jsonPlaylist['title'], $image, $jsonPlaylist['description'], $feed);

        $feedFile = $jsonPlaylist['feed'];
        $playlistXML = new playlistXML();
        if ($playlistXML->writePlaylistXml(array($playlist)) !== false) {
            if ($jsonPlaylist['feed'] == "") {
                $playlistXML->createMovieXML(dirname(__FILE__)."/xml/$id.xml");
                $feedFile = "$id.xml";
            }

            $movieXML = new movieXML(dirname(__FILE__)."/xml/$feedFile");
            $movies = array();

            // Create movie objects for each movie
            foreach ($jsonPlaylist['movies'] as $contentId) {
                
                $mov = $movieXML->loadMovie($contentId['contentId']);
                if($mov !== false)
                    $movies[] = $mov;
            }

            $movieXML->writeMovies($movies);
            echo "success";
        }

        break;

    case "deletePlaylist":


        $playlistXML = new playlistXML();
        if($playlistXML->removePlaylist($_GET['playlist_id']) !== false) {

            // Delete playlist movie list file
            unlink(dirname(__FILE__)."/xml/".$_GET['playlist_id'].".xml");
            
            echo "success";
        }

        break;

    case "default":
        error_log("Default AJAX Controller Option, no function: $function");
        break;

}

function arrayToString($array) {

    $out = "";
    foreach ($array as $subKey => $subValue) {
        $out .= $subValue['name'].", ";
    }
    $out = substr($out,0,(strlen($out)-2));

    return $out;

}

?>
