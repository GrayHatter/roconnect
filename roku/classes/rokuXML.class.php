<?php

class rokuXML {

    private $file;
    private $doc;

    function __construct() {
        $this->file = ROKU_XML;
        $this->doc = new DOMDocument();
        $this->doc->load($this->file);
    }

    function getRokuName($usn) {
        $rokus = $this->doc->getElementsByTagName("roku");
        if (($pos = $this->rokuIdExist($usn, $rokus)) === FALSE) {
            return FALSE;
        } else {
            $ro = $rokus->item($pos);
            $name = $ro->getElementsByTagName("name");
            return $name->item(0)->nodeValue;
        }
    }

    function readRokuXml() {
        $devices = array();
        $rokus = $this->doc->getElementsByTagName("roku");
        foreach ($rokus as $roku) {
            $locations = $roku->getElementsByTagName("location");
            $data['location'] = $locations->item(0)->nodeValue;

            $names = $roku->getElementsByTagName("name");
            $data['name'] = $names->item(0)->nodeValue;

            $data['usn'] = $roku->getAttribute("id");
            $devices[] = new roku($data);
        }
        return $devices;
    }

    function writeRokuXML($rokuArray) {
        $rokus = $this->doc->getElementsByTagName("roku");
        $root = $this->doc->getElementsByTagName("devices")->item(0);
        foreach ($rokuArray as $roku) {
            if (($pos = $this->rokuIdExist($roku->usn, $rokus)) === FALSE) {
                $ro = $this->doc->createElement("roku");
                $ro->setAttribute("id", $roku->usn);

                $location = $this->doc->createElement("location");
                $location->appendChild($this->doc->createTextNode($roku->location));
                $ro->appendChild($location);

                $name = $this->doc->createElement("name");
                $name->appendChild($this->doc->createTextNode($roku->name));
                $ro->appendChild($name);

                $root->appendChild($ro);
                //echo "CREATED";
            } else {
                $ro = $rokus->item($pos);

                $location = $ro->getElementsByTagName("location");
                $location->item(0)->nodeValue = $roku->location;

                $name = $ro->getElementsByTagName("name");
                $name->item(0)->nodeValue = $roku->name;
                //echo "UPDATED";
            }
        }
        $this->doc->saveXML();
        $this->doc->save($this->file);
    }

    function deleteRoku($usn) {
        $root = $this->doc->getElementsByTagName("devices")->item(0);
        $rokus = $this->doc->getElementsByTagName("roku");
        foreach ($rokus as $roku) {

            if($usn == $roku->getAttribute("id")) {
                $root->removeChild($roku);
                break;
            }
        }
        $this->doc->saveXML();
        $this->doc->save($this->file);
    }

    function rokuIdExist($rokuId, $rokuTags) {
        $pos = 0;
        foreach ($rokuTags as $roku) {
            $id = $roku->getAttribute("id");
            if ($id == $rokuId) {
                //echo "Match found for id: $rokuId<br>";
                return $pos;
            }
            $pos++;
        }
        return FALSE;
    }

}

?>