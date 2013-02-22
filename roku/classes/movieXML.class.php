<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class movieXML {

    private $file;
    private $doc;
    private $allMovies;

    function __construct ($feed) {
        $this->file = $feed;
        $this->doc = simplexml_load_file($this->file);
        $videoMeta = new videoMetaData();
        $this->allMovies = $videoMeta->getMovieList(ALL_XML);
    }

    public function readMovies() {

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

            $file = explode("/", $movie->file);

            $movies[$this->removeAnd(end($file))] = $movie;
        }

        return $movies;
    }

    private function removeAnd($value) {
        if (strpos($value, "&") !== FALSE || strpos($value, "&amp;") !== FALSE) {
            $value = str_replace("&", "and", $value);
            $value = str_replace("&amp;", "and", $value);
        }
        return $value;
    }

    //Removes the contentId node from the all.xml file
    public function removeMovie($movie) {

        $count = 0;

        foreach ($this->doc->item as $item) {

            if ((String) $item->contentId == $movie->contentId) {
                unset($this->doc->item[$count]);
                break;
            }
            $count++;
        }
        
        file_put_contents($this->file, $this->doc->asXML());

    }

    public function writeMovies($movies) {

        $count = count($this->doc->item);

        // Removes all movies not in the passed movie array
        for ($count--; $count >= 0; $count--) {
            if (($pos = $this->movieIdExist($this->doc->item[$count]->contentId, $movies)) === FALSE) {
                $this->removeMovie($this->doc->item[$count]);
            }
        }

        // Loop through all movies in passed array of movie objects
        foreach ((array) $movies as $movie) {

            // If the movie from the passed array does not exist in the xml document add
            // it otherwise update it
            if (($pos = $this->movieIdExist($movie->contentId, $this->doc->item)) === FALSE) {
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
            } else {

                foreach ($this->doc->item as $item) {

                    if ((String) $item->contentId == $movie->contentId) {
                        foreach ($movie->getData() as $key => $value) {
                            if ($key != "hdImg")
                                $item->$key = $value;
                        }
                        $attr = $item->attributes();
                        $attr['sdImg'] = $movie->hdImg;
                        $attr['hdImg'] = $movie->hdImg;
                    }
                }
            }
        }

        file_put_contents($this->file, $this->doc->asXML());
    }

    public function loadMovie($contentId) {
        foreach ($this->allMovies as $movie) {
            $id = $movie->contentId;
            if ($id == $contentId) {

                return $movie;
            }
        }
        return FALSE;
    }

    public function updateMovie($movie) {
        foreach ($this->doc->item as $item) {

            if ((String) $item->contentId == $movie->contentId) {
                foreach ($movie->getData() as $key => $value) {
                    if ($key != "hdImg")
                        $item->$key = $value;
                }
                $attr = $item->attributes();
                $attr['sdImg'] = $movie->hdImg;
                $attr['hdImg'] = $movie->hdImg;
            }
        }
        
        file_put_contents($this->file, $this->doc->asXML());
    }

    function movieIdExist($contentId, $movieTags) {
        $pos = 0;
        foreach ($movieTags as $movie) {
            $id = $movie->contentId;
            if ($id == $contentId) {

                return $pos;
            }
            $pos++;
        }
        return FALSE;
    }

}

?>
