<?php

##############################################################
##
## The scan class is in charge of finding Roku devices
## and creating roku objects out of the responses of active
## devices.  It also generates an xml file for saving
## names to specific devices
##
##############################################################

class scan {

    private $devices = array();

    ## Kicks of scanning procedures

    function __construct($xml) {
        $this->createRokus($this->findRoku(), $xml);
    }

    public function getDevices() {
        return $this->devices;
    }

    ## This function sends out a ssdp discover message
    ## to get info on all roku devices on the LAN

    public function findRoku() {
        $port = "1900";
        $multicast = "239.255.255.250";
        $response = array();

        $sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        $opt_ret = socket_set_option($sock, SOL_SOCKET, SO_BROADCAST, TRUE);

        $out = "M-SEARCH * HTTP/1.1\r\n";
        $out .= "Host: $multicast:$port\r\n";
        $out .= 'Man: "ssdp:discover"';
        $out .= "\r\nST: roku:ecp\r\n";
        $out .= "\r\n";

        $send_ret = socket_sendto($sock, $out, strlen($out), 0, $multicast, $port);

        //echo "Reading responses:\n";
        $buf = 'This is my buffer.';
        $bytes = 0;
        while (1) {
            $r[0] = $sock;
            $w = NULL;
            $e = NULL;
            if (socket_select($r, $w, $e, 1) == 0) {
                break;
            }
            $bytes += socket_recv($sock, $buf, 2048, MSG_WAITALL);
            $data = new http_response($buf);
            $response[] = $data;
        }

        //echo "\nRead $bytes bytes from socket_recv(). Closing socket...\n";
        socket_close($sock);
        return $response;
    }

    ## Creates roku objects from the httpResponse objects

    public function createRokus($httpResponses, $xml) {
        foreach ($httpResponses as $response) {
            if (strpos($response->head, "200 OK") !== FALSE && strpos($response->st, "roku") !== FALSE) {
                $usn = substr($response->usn, 14, strlen($response->usn));
                $loc = $response->location;
                if (($name = $xml->getRokuName($usn)) === FALSE) {
                    $roku = new roku($usn, $loc);
                    //echo "No name found $name<br>";
                } else {
                    $data = array("usn" => $usn, "location" => $loc, "name" => $name);
                    $roku = new roku($data);
                }
                $this->devices[] = $roku;
            }
        }
    }

}

?>