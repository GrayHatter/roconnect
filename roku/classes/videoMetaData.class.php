<?php

class videoMetaData {

    private $dir;
    private $movies;
    private $doc;
    private $globs;
    private $count;
    private $pos = 0;
    private $total = 0;

    private $validVideoFormats = array("MKV", "MP4", "MOV", "M4V", "ASF", "WMV");
    private $validAudioFormats = array("MP3", "WMA");

    function __construct() {
        $this->movies = $this->getMovieList(ALL_XML);
        $this->globs = new globals(false);
        $this->dir = $this->globs->getVideoPath();
    }

    public function getMovies() {
        return $this->movies;
    }

    static function cmp($a, $b) {
        return strcmp(strtoupper(trim($b->title)), strtoupper(trim($a->title)));
    }

    //Returns all of the movies currently in the file supplied
    public function getMovieList($movielistFile) {

        error_log("getMovieList");

        $file = $movielistFile;

        $this->doc = simplexml_load_file($file);

        $movies = array();

        foreach ($this->doc->item as $item) {

            $movie = new movie((String) $item->contentId);

            foreach ($item->attributes() as $attKey => $attValue) {
                $movie->$attKey = (String) $attValue;
            }
            foreach ($item as $key => $value) {
                if (count($value) > 0) {
                    foreach ($value as $subKey => $subValue) {
                        $movie->$subKey = (String) $subValue;
                    }
                } else {
                    $movie->$key = (String) $value;
                }
            }

            $fileName = explode("/", $movie->file);

            $movies[$this->removeAnd(end($fileName))] = $movie;
        }

        return $movies;
    }

    public function ipChange($oldIp, $newIp) {

        foreach ($this->doc->item as $item) {
            $attr = $item->attributes();
            foreach ($item->attributes() as $attKey => $attValue) {
                $newAttValue = str_replace($oldIp, $newIp, $attValue);
                $attr[$attKey] = $newAttValue;
            }
            foreach ($item as $key => $value) {
                if ($key == "streamUrl") {
                    $newValue = str_replace($oldIp, $newIp, $value);
                    $item->$key = $newValue;
                }
            }
        }
        file_put_contents(ALL_XML, $this->doc->asXML());
    }

    public function indexMovies() {
        if ($this->dir != "") {
            $this->pos = 0;
            $this->total = 0;
            $this->count = 0;
            $this->updateProgress(0, "Initializing");
            $this->moviesToIndex($this->dir, false, "");
            
            //Delete xml records from all.xml of movie files that have been deleted/removed
            $files = $this->deleteMovies($this->dir, "");
            $this->deleteFoundMovies($files);

            //$this->movies = $this->getMovieList(ALL_XML);

            //Add xml records to all.xml of movie files that have been added
            $this->addMovies($this->dir, false, "");

            usort($this->movies, array("VideoMetaData", "cmp"));
        } else {
            error_log("Cannot Index Movies: No video path defined");
        }
    }

    public function deleteMovies($dir, $subDir) {

        $files = array();

        if ($handle = opendir($dir)) {

            while (false !== ($file = readdir($handle))) {
                if (filetype($dir . $file) == "dir" && $file != "." && $file != ".." && $this->globs->getRecursive() == "true") {
                    $returnFiles = $this->deleteMovies($dir . $file . "/", $subDir.$file."/");
                    $files = array_merge($files, $returnFiles);
                }
                if ($this->validFormat($dir, $file) && $file != "." && $file != "..") {
                    $files[] = $this->removeAnd($subDir.$file);
                }
            }
        }

        return $files;
    }

    private function deleteFoundMovies($files) {

        $count = 0;

        if (count($this->movies) > 0) {
            $count = 0;
            foreach ((array) $this->movies as $movie) {
                $file = explode("/", $movie->file);
                //foreach ($files as $filename)
                if(array_search($movie->file, (array) $files) === false) {
                    error_log("Not Found: " . $movie->file);
                    $this->removeMovies($movie);
                    unset($this->movies[$this->removeAnd($movie->file)]);
                    $count++;
                }
            }
        }
    }

    //Removes the contentId node from the all.xml file
    private function removeMovies($movie) {

        // Removes movie from all playlists
        $playlistXML = new playlistXML();
        $playlistXML->removeFromAllPlaylists($movie);

        $count = 0;

        foreach ($this->doc->item as $item) {

            if ((String) $item->contentId == $movie->contentId) {
                error_log("Movie " . $movie->contentId . " = " . $movie->file);
                error_log("Removing Item " . $this->doc->item[$count]->file . " contentId: " . $this->doc->item[$count]->contentId);
                unset($this->doc->item[$count]);
                break;
            }
            $count++;
        }

        file_put_contents(ALL_XML, $this->doc->asXML());
        //error_log($this->doc->asXML());
    }

    public function moviesToIndex($dir, $isSub, $subDir) {
        // Open a known directory, and proceed to read its contents
        if (is_dir($dir)) {

            if ($dh = opendir($dir)) {

                while (($file = readdir($dh)) !== false) {

                    if (filetype($dir . $file) == "dir" && $file != "." && $file != ".." && $this->globs->getRecursive() == "true") {
                        $this->moviesToIndex($dir . $file . "/", true, $subDir . $file);
                    }

                    if ($this->validFormat($dir, $file)) {
                        $this->total++;
                    }
                }
            }
        }
    }

    // Makes sure the file format is one the Roku player will read
    function validFormat($dir, $file) {
        if (filetype($dir . $file) != "dir") {
            $format = strtoupper(substr($file, strrpos($file, ".") + 1));
            return (in_array($format, $this->validVideoFormats) || in_array($format, $this->validAudioFormats));
        } else {
            return false;
        }
    }

    // Checks to see if this is a valid audio file format
    function isAudioFormat($dir, $file) {
        if (filetype($dir . $file) != "dir") {
            $format = strtoupper(substr($file, strrpos($file, ".") + 1));
            return in_array($format, $this->validAudioFormats);
        } else {
            return false;
        }
    }

    // Checks to see if this is a valid video file format
    function isVideoFormat($dir, $file) {
        if (filetype($dir . $file) != "dir") {
            $format = strtoupper(substr($file, strrpos($file, ".") + 1));
            return in_array($format, $this->validVideoFormats);
        } else {
            return false;
        }
    }

    //Adds any movie files added to the movie directory to the all.xml file
    public function addMovies($dir, $isSub, $subDir) {

        $medias = array();
        $aliases = array();

        $trail = substr($subDir, strlen($subDir) - 1, 1);
        if($trail != "/" && $trail != "\\" && $subDir != "") {
            $subDir .= "/";
        }

        // Open a known directory, and proceed to read its contents
        if (is_dir($dir)) {

            if ($dh = opendir($dir)) {

                while (($file = readdir($dh)) !== false) {

                    if (filetype($dir . $file) == "dir" && $file != "." && $file != ".." && $this->globs->getRecursive() == "true") {
                        $aliases = array_merge($this->addMovies($dir . $file . "/", true, $subDir . $file), $aliases);
                    }

                    if ($this->validFormat($dir, $file) && !array_key_exists($this->removeAnd($file), (array) $this->movies)) {

                        //error_log("Did Not Find Key: " . $this->removeAnd($file));

                        $this->pos++;
                        $this->updateProgress((int)(($this->pos/$this->total)*100), "Processing File: $file");

                        //Turn on output buffering
                        ob_start();

                        $escPath = escapeshellarg($dir . $file);
                        $escMediaInfoPath = escapeshellarg($this->globs->getMediaInfo());

                        //Pass the command through passthru:
                        passthru($escMediaInfoPath . " --Output=XML $escPath", $return);

                        //get the result
                        $content = ob_get_contents();

                        ob_end_clean();

                        $xmlVars = null;

                        try {

                            $content = utf8_encode($content);
                            $xml = new SimpleXMLElement($content);
                            $xmlVars = get_object_vars($xml);

                            $media = null;

                            if (!is_null($xmlVars['File']->track[1])) {

                                if ($this->isVideoFormat($dir, $file)) {
                                    $media = $this->processVideo($xmlVars, $file);
                                } elseif ($this->isAudioFormat($dir, $file)) {
                                    $media = $this->processAudio($xmlVars, $file);
                                }

                                $temp = null;

                                if ($isSub) {
                                    $media->file = $subDir . $file;
                                    $temp = new aliases("/movies/" . $subDir, $this->dir . $subDir);

                                } else {
                                    $media->file = $file;
                                    $temp = new aliases("/movies", $this->dir);
                                }
                                if (!in_array($temp, $aliases))
                                    $aliases[] = $temp;

                                $this->getMetaData($media, $this->isVideoFormat($dir, $file));

                                $medias[] = $media;
                            }

                        } catch (Exception $exc) {
                            error_log("Could Not Process File: $file");
                        }
                    } elseif (array_key_exists($this->removeAnd($file), (array) $this->movies)) {
                        $media = $this->movies[$this->removeAnd($file)];
                        if ($isSub) {
                            $media->file = $subDir . $file;
                            $temp = new aliases("/movies/" . $subDir, $this->dir . $subDir);
                        } else {
                            $media->file = $file;
                            $temp = new aliases("/movies", $this->dir);
                        }
                        
                        if (!in_array($temp, $aliases))
                            $aliases[] = $temp;
                    }
                }
                closedir($dh);
            }
        }
        if (count($medias) > 0)
            $this->writeNew($medias);
        if ($isSub)
            return $aliases;
        else {
            $this->updateProgress(100, "Indexing Complete");
            echo "Index Complete";
            $this->globs->writeAliases($aliases);
        }
    }

    private function processVideo($xmlVars, $file) {
        $video = get_object_vars($xmlVars['File']->track[1]);

        $title = explode(".", $file);

        $seconds = $this->getTime($video['Duration']);

        $width = explode(" ", $video['Width']);
        if (count($width) > 2)
            $finalWidth = $width[0] . $width[1];
        else
            $finalWidth = $width[0];
        $height = explode(" ", $video['Height']);
        if (count($height) > 2)
            $finalHeight = $height[0] . $height[1];
        else
            $finalHeight = $height[0];
        $size = $finalWidth . "x" . $finalHeight;

        $bitrate = explode(" ", $video['Bit_rate']);
        if (count($bitrate) > 2)
            $finalBitrate = $bitrate[0] . $bitrate[1];
        else
            $finalBitrate = $bitrate[0];

        $frameRate = explode(" ", $video['Frame_rate']);

        $movie = new movie();
        $movie->title = $title[0];
        $movie->runtime = $seconds;
        $movie->streamBitrate = (int) $finalBitrate;
        if ((int) $finalBitrate >= 2000) {
            $movie->isHD = "True";
            $movie->HDBranded = "True";
            $movie->streamQuality = "HD";
        } else {
            $movie->streamQuality = "SD";
        }
        $movie->FrameRate = (int) $frameRate[0];
        $movie->size = $size;
        $movie->streamFormat = $title[(count($title) - 1)];

        return $movie;
    }

    private function processAudio($xmlVars, $file) {
        $track = get_object_vars($xmlVars['File']->track[0]);
        $audio = get_object_vars($xmlVars['File']->track[1]);

        $title = explode(".", $file);

        $seconds = $this->getTime($audio['Duration']);

        $bitrate = explode(" ", $audio['Bit_rate']);
        if (count($bitrate) > 2)
            $finalBitrate = $bitrate[0] . $bitrate[1];
        else
            $finalBitrate = $bitrate[0];

        $movie = new movie();
        if (array_key_exists('Track_name', $track))
            $movie->title = $track['Track_name'];
        else
            $movie->title = $title[0];
        if (array_key_exists('Performer', $track))
            $movie->Artist = $track['Performer'];
        if (array_key_exists('Recorded_date', $track))
            $movie->ReleaseDate = $track['Recorded_date'];
        if (array_key_exists('Genre', $track))
            $movie->genres = $track['Genre'];
        $movie->streamFormat = $title[(count($title) - 1)];
        $movie->runtime = $seconds;
        $movie->streamBitrate = (int) $finalBitrate;

        return $movie;
    }

    // Returns the amount of time in seconds
    private function getTime($time) {
        preg_match('/(([0-9]{0,2})h)?\s?(([0-9]{0,2})mn)?\s?(([0-9]{0,2})s)?/', $time, $duration);
        $duration = array_pad($duration, 7, 0);
        return ((int) $duration[2] * 3600) + ((int) $duration[4] * 60) + round($duration[6]);
    }

    private function getMetaData($movie, $isVideo) {

        //Standard meta data needed for all movie files
        $movie->streamUrl = "movies/" . $this->url($movie->file);

        if ($isVideo) {
            $movie->sdImg = "roku/images/unknown.png";
            $movie->hdImg = "roku/images/unknown.png";
            $movie->contentType = "movie";
        } else {
            $movie->sdImg = "roku/images/unknown_audio_sd.png";
            $movie->hdImg = "roku/images/unknown_audio.png";
            $movie->contentType = "audio";
        }

        if ($this->globs->getIMDb() == "true" && $isVideo) {
            $search = new imdbsearch();
            $search->search_episodes(FALSE);
            $search->setsearchname($movie->title);
            $results = $search->results();
            $engine = "imdb";

            //Additional meta data found from IMDB search
            if (count($results) > 0) {
                $imdb = new imdb($results[0]->imdbid());

                $imdb->setid($results[0]->imdbid());

                if (($photo_url = $imdb->photo_localurl()) != FALSE) {
                    $movie->sdImg = "roku" . substr($photo_url, 1);
                    $movie->hdImg = "roku" . substr($photo_url, 1);
                }

                $movie->title = $imdb->title();
                $movie->synopsis = rtrim(str_replace("&raquo;", "", str_replace("&nbsp;", "", strip_tags($imdb->plotoutline()))));
                $movie->genres = $imdb->genre();
                $mpaa = $imdb->mpaa();
                if (empty($mpaa['USA']))
                    $movie->Rating = "NR";
                else
                    $movie->Rating = $mpaa['USA'];
                $movie->StarRating = ((int) $imdb->rating() * 10);
                $movie->Actors = array_slice($imdb->cast(), 0, 3);
                $movie->Director = array_slice($imdb->director(), 0, 3);
                $movie->ReleaseDate = $imdb->year();
            }
        }
    }

    private function url($url) {
        $parts = explode("/", $url);
        $urlString = "";
        if (count($parts) > 0) {
            foreach ($parts as $part) {
                $urlString .= rawurlencode($part) . "/";
            }
            $urlString = substr($urlString, 0, (strlen($urlString) - 1));
            return $urlString;
        } else {
            return $url;
        }
    }

    public function writeNew($newMovies) {

        foreach ((array) $newMovies as $movie) {
            $newItem = $this->doc->addChild("item");

            foreach ($movie->getData() as $key => $value) {
                $value = $this->removeAnd($value);
                if (is_array($value)) {
                    $out = "";
                    foreach ($value as $subKey => $subValue) {
                        $out .= $subValue['name'] . ", ";
                    }
                    $subItem = $newItem->addChild($key, substr($out, 0, (strlen($out) - 2)));
                } else {
                    if ($key == "sdImg" || $key == "hdImg") {
                        $newItem->addAttribute($key, $value);
                    } else {
                        $newItem->addChild($key, $value);
                    }
                }
            }
        }

        $this->doc->resultLength = (count($newMovies) + count($this->movies));
        $this->doc->endIndex = (count($newMovies) + count($this->movies));

        file_put_contents(ALL_XML, $this->doc->asXML());

        $this->count++;
    }

    public function writeUpdate($movies) {

        $playlistXML = new playlistXML();

        foreach ((array) $movies as $movie) {

            foreach ($this->doc->item as $item) {

                if ((String) $item->contentId == $movie->contentId) {
                    foreach ($movie->getData() as $key => $value) {
                        if ($key != "hdImg")
                            $item->$key = $value;
                    }
                    $attr = $item->attributes();
                    $attr['sdImg'] = $movie->hdImg;
                    $attr['hdImg'] = $movie->hdImg;

                    $playlistXML->updateAllPlaylists($movie);
                }
            }
        }

        file_put_contents(ALL_XML, $this->doc->asXML());

        return "Updated " . count($movies) . " new movies to file<br>";
    }

    public function updateProgress($percent, $message) {
        $handle = fopen(PROGRESS_XML, 'r+');
        if (!$handle) {
            error_log("Video Meta could not open progress file");
        } else {
            flock($handle, LOCK_EX);
            $response = @stream_get_contents($handle);
            flock($handle, LOCK_UN);
            fclose($handle);
            $progress = simplexml_load_string($response);
            $progress->progress = $percent;
            $progress->message = $message;
            file_put_contents(PROGRESS_XML, $progress->asXML());
        }
    }

    private function removeAnd($value) {
        if (strpos($value, "&") !== FALSE || strpos($value, "&amp;") !== FALSE) {
            $value = str_replace("&", "and", $value);
            $value = str_replace("&amp;", "and", $value);
        }
        return $value;
    }

}

?>