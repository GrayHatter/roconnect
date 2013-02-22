<?php

include('playlist.class.php');

class playlistXML {
    
    private $file;
    private $doc;

    function __construct () {
        $this->file = PLAYLIST_XML;
        $this->doc = new DOMDocument();
        $this->doc->load($this->file);
    }

    public function readPlaylistXml() {
        $categories = $this->doc->getElementsByTagName("category");
        foreach ($categories as $category) {
            $data['id'] = $category->getAttribute("id");
            $data['title'] = $category->getAttribute("title");
            $data['image'] = $category->getAttribute("sd_img");
            $leaf = $category->getElementsByTagName("categoryLeaf");
            $data['description'] = $leaf->item(0)->getAttribute("description");
            $data['feed'] = $leaf->item(0)->getAttribute("feed");
            $cats[] = new playlist($data);
        }
        return $cats;
    }

    public function writePlaylistXml($playlistArray) {
        $playlists = $this->doc->getElementsByTagName("category");
        $root = $this->doc->getElementsByTagName("categories")->item(0);
        foreach ($playlistArray as $playlist) {
            if (($pos = $this->playlistIdExist($playlist->id, $playlists)) === FALSE) {
                $newPlaylist = $this->doc->createElement("category");
                $newPlaylist->setAttribute("id", $playlist->id);
                $newPlaylist->setAttribute("title", $playlist->title);
                $newPlaylist->setAttribute("sd_img", $playlist->image);
                $newPlaylist->setAttribute("hd_img", $playlist->image);
                $newPlaylist->setAttribute("description", $playlist->description);

                $leaf = $this->doc->createElement("categoryLeaf");
                $leaf->setAttribute("title", $playlist->title);
                $leaf->setAttribute("description", $playlist->description);
                $leaf->setAttribute("feed", $playlist->feed);
                $newPlaylist->appendChild($leaf);

                $root->appendChild($newPlaylist);
                //echo "CREATED";
            } else {
                $tPlaylist = $playlists->item($pos);
                $tPlaylist->setAttribute("title", $playlist->title);
                $tPlaylist->setAttribute("sd_img", $playlist->image);
                $tPlaylist->setAttribute("hd_img", $playlist->image);
                $tPlaylist->setAttribute("description", $playlist->description);

                $leaf = $tPlaylist->getElementsByTagName("categoryLeaf");
                $leaf->item(0)->setAttribute("title", $playlist->title);
                $leaf->item(0)->setAttribute("description", $playlist->description);
                $leaf->item(0)->setAttribute("feed", $playlist->feed);
                //echo "UPDATED";
            }
        }

        return $this->doc->save($this->file);
    }

    //Removes the id node from the playlist.xml file
    public function removePlaylist($playlistId) {

        $root = $this->doc->getElementsByTagName("categories")->item(0);
        $categories = $this->doc->getElementsByTagName("category");
        foreach ($categories as $category) {

            if($playlistId == $category->getAttribute("id")) {
                $root->removeChild($category);
                break;
            }
        }

        $this->doc->saveXML();
        return $this->doc->save($this->file);
    }

    public function createMovieXML($feed) {

        $dom = new DOMDocument("1.0", 'UTF-8');
        $root = $dom->createElement("feed");
        $dom->appendChild($root);

        $dom->saveXML();
        return $dom->save($feed);
    }

    public function removeFromAllPlaylists($movie) {
        foreach ($this->readPlaylistXml() as $playlist) {
            if($playlist->id != 0) {
                $movieXML = new movieXML(DIR_PREFIX . $playlist->movieListFile());
                $movieXML->removeMovie($movie);
            }
        }
    }

    public function updateAllPlaylists($movie) {
        foreach ($this->readPlaylistXml() as $playlist) {
            if($playlist->id != 0) {
                $movieXML = new movieXML(DIR_PREFIX . $playlist->movieListFile());
                $movieXML->updateMovie($movie);
            }
        }
    }

    function playlistIdExist($playlistId, $playlistTags) {
        $pos = 0;
        foreach ($playlistTags as $playlist) {
            $id = $playlist->getAttribute("id");
            if ($id == $playlistId) {
                
                return $pos;
            }
            $pos++;
        }
        return FALSE;
    }

}

?>
