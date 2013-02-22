<?php

class externalcontrol {

    private $address;
    private $port;

    function __construct($url) {
        $regex = "";
        $regex .= "/.*?\/\/([0-9-.]*)"; // Host or IP
        $regex .= ":([0-9]{2,5})\//"; // Port
        preg_match($regex, $url, $matches);
        $this->address = $matches[1];
        $this->port = $matches[2];
    }

    public function isOnline() {

        $url = "http://" . $this->address . ":" . $this->port . "/";
        if($url == NULL) return false;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $data = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if($httpcode >= 200 && $httpcode < 300){
            return true;
        } else {
            return false;
        }
    }

    public function getChannels() {
        $data = $this->send("GET", "/query/apps");
        $regex = '/id="([0-9]*)".*?>(.*?)<\/app>/';
        preg_match_all($regex, $data, $matches);
        for ($i = 0; $i < count($matches[0]); $i++) {
            $channels[$matches[1][$i]] = $matches[2][$i];
        }
        return $channels;
    }

    public function hasChannel($channel) {
        $data = $this->send("GET", "/query/apps");
        $regex = '/id="([0-9a-zA-Z]*)".*?>(.*?)<\/app>/';
        preg_match_all($regex, $data, $matches);
        for ($i = 0; $i < count($matches[0]); $i++) {
            //echo (trim($matches[2][$i]) != trim($channel))."<br/>";
            //print_r($matches);
            if(trim($channel) === trim($matches[2][$i])) {
                return true;
            }
        }
        return false;
    }

    public function getChannelId($channel) {
        $data = $this->send("GET", "/query/apps");
        $regex = '/id="([0-9a-zA-Z]*)".*?>(.*?)<\/app>/';
        preg_match_all($regex, $data, $matches);
        for ($i = 0; $i < count($matches[0]); $i++) {
            //echo (trim($matches[2][$i]) != trim($channel))."<br/>";
            //print_r($matches);
            if(trim($channel) === trim($matches[2][$i])) {
                return $matches[1][$i];
            }
        }
        return false;
    }

    public function install() {

        $request_url = $this->address.'/plugin_install';
	$post_params['mysubmit'] = urlencode('Replace');
        $post_params['archive'] = '@'.ROKU_CHANNEL;
        $post_params['submit'] = urlencode('submit');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $request_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_params);
        $result = curl_exec($ch);
        curl_close($ch);

        if (strpos($result, "Application Received"))
            echo true;
        else
            echo false;

    }

    public function keyPressRight() {
        $this->send("POST", "/keypress/Right");
    }

    public function keyPressLeft() {
        $this->send("POST", "/keypress/Left");
    }

    public function keyPressLetter($letter) {
        $this->send("POST", "/keypress/Lit_$letter");
    }

    public function keyPressButton($button) {
        $this->send("POST", "/keypress/$button");
    }

    public function launch($app, $param) {
        $this->send("POST", "/launch/$app$param");
    }

    private function send($method, $command) {
		$response = "";
        $fp = fsockopen($this->address, $this->port, $errno, $errstr, 30);
        if (!$fp) {
            echo "$errstr ($errno)<br />\n";
        } else {

            $out = "$method $command HTTP/1.1\r\n";
            $out .= "Host: " . $this->address . ":" . $this->port . "\r\n";
            $out .= "Connection: Close\r\n\r\n";

            fwrite($fp, $out);
            while (!feof($fp)) {
                $response .= fgets($fp, 128);
            }
            fclose($fp);
        }
        return $response;
    }

}

?>