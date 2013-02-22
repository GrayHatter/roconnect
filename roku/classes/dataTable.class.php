<?php

require_once("imdbphp2-2.0.4/imdbsearch.class.php");
require_once("imdbphp2-2.0.4/imdb.class.php");
require_once("../CONSTANTS.php");

if(isset($_GET['type']))
    $type = $_GET['type'];

switch($type) {

    case 'imdb':

        /*
         * Filtering
         * NOTE this does not match the built-in DataTables filtering which does it
         * word by word on any field. It's possible to do here, but concerned about efficiency
         * on very large tables, and MySQL's regex functionality is very limited
         */
        if ($_GET['sSearch'] != "") {
            $title = $_GET['sSearch'];
        } else {
            $title = $_GET['title'];
        }

        if($_GET['episode'] == 'true')
            $episode = TRUE;
        else
            $episode = FALSE;

        $search = new imdbsearch();
        $search->search_episodes($episode);
        $search->setsearchname($title);
        $results = $search->results();

        $start = 0;
        $length = count($results);

        if(isset($_GET['iDisplayStart']))
            $start = $_GET['iDisplayStart'];

        if(isset($_GET['iDisplayLength']))
            $length = $_GET['iDisplayLength'];

        if(($length + $start) > count($results))
            $length = count($results);
        else
            $length += $start;

        /*
         * Output
         */
        $sOutput = '{';
        $sOutput .= '"sEcho": ' . intval($_GET['sEcho']) . ', ';
        $sOutput .= '"iTotalRecords": ' . count($results) . ', ';
        $sOutput .= '"iTotalDisplayRecords": ' . count($results) . ', ';
        $sOutput .= '"aaData": [ ';
        for ($i=$start; $i<($length); $i++) {

            $sOutput .= "[";
            $sOutput .= '"'.$results[$i]->imdbid().'","'.$results[$i]->title().' ('.$results[$i]->year().')",';
            /*
             * Optional Configuration:
             * If you need to add any extra columns (add/edit/delete etc) to the table, that aren't in the
             * database - you can do it here
             */


            $sOutput = substr_replace($sOutput, "", -1);
            $sOutput .= "],";
        }
        $sOutput = substr_replace($sOutput, "", -1);
        $sOutput .= '] }';

        echo $sOutput;
        break;
    case 'movies':

        $movieMeta = new videoMetaData();
        $movies = $movieMeta->getMovies();

        if (isset($_GET['playlist'])) {
            $playlistXML = new playlistXML();
            $playlists = $playlistXML->readPlaylistXml();
            foreach($playlists as $playlist) {
                if ($playlist->id == $_GET['playlist']) {
                    $movieXML = new movieXML(DIR_PREFIX.$playlist->movielistFile());
                    $movies = $movieXML->readMovies();
                    break;
                }
            }
        }

        if(count($movies) == 0) {
            $sOutput = '{';
            $sOutput .= '"sEcho": 1, ';
            $sOutput .= '"iTotalRecords": 0, ';
            $sOutput .= '"iTotalDisplayRecords": 0, ';
            $sOutput .= '"aaData": [ ';
            $sOutput .= '] }';
            echo $sOutput;
			break;
        }

        $movies = array_values($movies);
        usort($movies, "titleAZ");


        /*
         * Ordering
         */
        if (isset($_GET['iSortCol_0'])) {
            switch ((int)$_GET['iSortCol_0'] - 7) {
                case 0:
                    if($_GET['sSortDir_0'] == 'asc')
                        usort($movies, "titleAZ");
                    else
                        usort($movies, "titleZA");
                    break;
                case 1:
                    if($_GET['sSortDir_0'] == 'asc')
                        usort($movies, "genreAZ");
                    else
                        usort($movies, "genreZA");
                    break;
                case 2:
                    if($_GET['sSortDir_0'] == 'asc')
                        usort($movies, "ratingAZ");
                    else
                        usort($movies, "ratingZA");
                    break;
                case 3:
                    if($_GET['sSortDir_0'] == 'asc')
                        usort($movies, "runtimeAZ");
                    else
                        usort($movies, "runtimeZA");
                    break;
                case 4:
                    if($_GET['sSortDir_0'] == 'asc')
                        usort($movies, "rankAZ");
                    else
                        usort($movies, "rankZA");
                    break;
            }
        }


        /*
         * Filtering
         * NOTE this does not match the built-in DataTables filtering which does it
         * word by word on any field. It's possible to do here, but concerned about efficiency
         * on very large tables, and MySQL's regex functionality is very limited
         */
        if ($_GET['sSearch'] != "") {
            $movies = search($movies, $_GET['sSearch']);
        }

        $start = 0;
        $length = count($movies);

        if(isset($_GET['iDisplayStart']))
            $start = $_GET['iDisplayStart'];

        if(isset($_GET['iDisplayLength']))
            $length = $_GET['iDisplayLength'];

        if(($length + $start) > count($movies))
            $length = count($movies);
        else
            $length += $start;

        $count = 0;

        /*
         * Output
         */
        $sOutput = '{';
        $sOutput .= '"sEcho": ' . intval($_GET['sEcho']) . ', ';
        $sOutput .= '"iTotalRecords": ' . count($movies) . ', ';
        $sOutput .= '"iTotalDisplayRecords": ' . count($movies) . ', ';
        $sOutput .= '"aaData": [ ';
        for ($i=$start; $i<($length); $i++) {
            //Figuring out the time
            $runtime = sec2hms($movies[$i]->runtime);
            $sOutput .= "[";
            $sOutput .= '"'.$movies[$i]->contentId
                    .'","'.$movies[$i]->imageRelativePath()
                    .'",'.json_encode(str_replace(",","&#44;",$movies[$i]->synopsis))
                    .','.(($movies[$i]->contentType == "movie") ? json_encode(str_replace(",","&#44;",$movies[$i]->Actors)) : json_encode(str_replace(",","&#44;",$movies[$i]->Artist)))
                    .','.(($movies[$i]->contentType == "movie") ? json_encode(str_replace(",","&#44;",$movies[$i]->Director)) : json_encode(str_replace(",","&#44;",$movies[$i]->Album)))
                    .',"'.$movies[$i]->ReleaseDate
                    .'","'.$movies[$i]->file
                    .'","'.$movies[$i]->title
                    .'","'.$movies[$i]->genres
                    .'","'.$movies[$i]->Rating
                    .'","'.$runtime
                    .'","'.$movies[$i]->StarRating
                    .'","'.$movies[$i]->contentType.'",';
            $sOutput = substr_replace($sOutput, "", -1);
            $sOutput .= "],";
            $count++;
        }

        $sOutput = substr_replace($sOutput, "", -1);
        $sOutput .= '] }';

        echo $sOutput;
        break;
}

function sec2hms ($sec, $padHours = false) {

    // start with a blank string
    $hms = "";

    // do the hours first: there are 3600 seconds in an hour, so if we divide
    // the total number of seconds by 3600 and throw away the remainder, we're
    // left with the number of hours in those seconds
    $hours = intval(intval($sec) / 3600);

    if ($hours != 0) {
        // add hours to $hms (with a leading 0 if asked for)
        $hms .= ($padHours)
              ? str_pad($hours, 2, "0", STR_PAD_LEFT). ":"
              : $hours. ":";
    }

    // dividing the total seconds by 60 will give us the number of minutes
    // in total, but we're interested in *minutes past the hour* and to get
    // this, we have to divide by 60 again and then use the remainder
    $minutes = intval(($sec / 60) % 60);

    // add minutes to $hms (with a leading 0 if needed)
    $hms .= ($hours != 0)
          ? str_pad($minutes, 2, "0", STR_PAD_LEFT). ":"
          : $minutes. ":";

    // seconds past the minute are found by dividing the total number of seconds
    // by 60 and using the remainder
    $seconds = intval($sec % 60);

    // add seconds to $hms (with a leading 0 if needed)
    $hms .= str_pad($seconds, 2, "0", STR_PAD_LEFT);

    // done!
    return $hms;

}

//Search Function

function search($movies, $needle) {
    $hits = array();
    foreach ($movies as $movie) {
        $good = false;
        foreach ($movie->getData() as $key => $value) {
            if($key == 'title' || $key == 'genres' || $key == 'Rating' || $key == 'runtime' || $key == 'StarRating') {
                if (stripos($value, $needle) !== FALSE)
                    $good = true;
            }
        }
        if($good)
            $hits[] = $movie;
    }
    return $hits;
}


//Sorting Functions

function titleZA($a, $b) {
    if (strcasecmp(trim($a->title), trim($b->title)) == 0)
            return 0;
    return (strcasecmp(trim($a->title), trim($b->title)) > 0) ? -1 : 1;
}

function titleAZ($a, $b) {
    if (strcasecmp(trim($a->title), trim($b->title)) == 0)
            return 0;
    return (strcasecmp(trim($a->title), trim($b->title)) < 0) ? -1 : 1;
}

function genreZA($a, $b) {
    if (strcasecmp($a->genres, $b->genres) == 0)
            return 0;
    return (strcasecmp($a->genres, $b->genres) > 0) ? -1 : 1;
}

function genreAZ($a, $b) {
    if (strcasecmp($a->genres, $b->genres) == 0)
            return 0;
    return (strcasecmp($a->genres, $b->genres) < 0) ? -1 : 1;
}

function ratingZA($a, $b) {
    if (strcasecmp($a->Rating, $b->Rating) == 0)
            return 0;
    return (strcasecmp($a->Rating, $b->Rating) > 0) ? -1 : 1;
}

function ratingAZ($a, $b) {
    if (strcasecmp($a->Rating, $b->Rating) == 0)
            return 0;
    return (strcasecmp($a->Rating, $b->Rating) < 0) ? -1 : 1;
}

function runtimeZA($a, $b) {
    if ($a->runtime == $b->runtime)
            return 0;
    return ($a->runtime > $b->runtime) ? -1 : 1;
}

function runtimeAZ($a, $b) {
    if ($a->runtime == $b->runtime)
            return 0;
    return ($a->runtime < $b->runtime) ? -1 : 1;
}

function rankZA($a, $b) {
    if ($a->StarRating == $b->StarRating)
            return 0;
    return ($a->StarRating > $b->StarRating) ? -1 : 1;
}

function rankAZ($a, $b) {
    if ($a->StarRating == $b->StarRating)
            return 0;
    return ($a->StarRating < $b->StarRating) ? -1 : 1;
}

?>
