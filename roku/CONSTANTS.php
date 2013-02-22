<?php

require("classes/roku.class.php");
require("classes/http_response.class.php");
require("classes/scan.class.php");
require("classes/rokuXML.class.php");
require("classes/externalcontrol.class.php");
require("classes/movie.class.php");
require("classes/videoMetaData.class.php");
require("classes/playlistXML.class.php");
require("classes/movieXML.class.php");
require("classes/aliases.class.php");

define('DIR_PREFIX', dirname(__FILE__).'/xml/');
define('ROKU_XML', dirname(__FILE__).'/xml/rokus.xml');
define('ALL_XML', dirname(__FILE__).'/xml/all.xml');
define('PLAYLIST_XML', dirname(__FILE__).'/xml/playlist.xml');
define('ROKU_CHANNEL', 'roConnect.zip');
define('MEDIA_INFO', dirname(__FILE__).'/mediainfo');
define('PROGRESS_XML', dirname(__FILE__).'/xml/progress.xml');

/**
 * Path to apache apachectl file to gracefully restart server
 */
define('APACHECTL', '/Applications/MAMP/Library/bin/apachectl');
define('APACHECTL_LIN', '/usr/sbin/apachectl');

class globals {

    private $xml;
    private $xmlVars;
    public $movieMeta;
    private $plXml;

    function __construct ($regular = true) {
        $this->xml = simplexml_load_file(dirname(__FILE__)."/xml/rokuConfig.xml");
        $this->xmlVars = get_object_vars($this->xml);
        if($regular)
            $this->initialize();
    }

    function initialize() {
        $this->movieMeta = new videoMetaData();
        $this->plXml = new playlistXML();
    }

    function setup($serverConf, $videoPath, $ip) {
        $this->initialize();
        $this->setServerConf($serverConf);
        $this->xml->videoPath = $videoPath;
        $this->setIp($ip);
        $this->refreshXml();
    }

    function getVersion() {
        return $this->xmlVars['version'];
    }

    function getServerId() {
        $serverId = $this->xmlVars['serverId'];
        if($serverId == "") {
            $serverId = uniqid();
            $this->xml->serverId = $serverId;
            $this->refreshXml();
        }
        return $serverId;
    }

    function getIp() {
        return "http://" . $this->xmlVars['myIp'] ."/";
    }

    function getPureIp() {
        return $this->xmlVars['myIp'];
    }

    function getVideoPath() {
        return $this->xmlVars['videoPath'];
    }

    function getServerConf() {
        return $this->xmlVars['serverConf'];
    }

    function getIMDb() {
        return $this->xmlVars['IMDb'];
    }

    function getRecursive() {
        return $this->xmlVars['recursive'];
    }

    function isNew() {
        if($this->xmlVars['new'] == "true") {
            $this->xml->new = "false";
            $this->refreshXml();
            return true;
        } else
            return false;
    }
    
    function refreshXml() {
        file_put_contents(dirname(__FILE__)."/xml/rokuConfig.xml", $this->xml->asXML());
        $this->xml = simplexml_load_file(dirname(__FILE__)."/xml/rokuConfig.xml");
        $this->xmlVars = get_object_vars($this->xml);
    }
    
    function setIp($ip) {
        $this->xml->myIp = $ip;
        $this->refreshXml();
    }

    function setVideoPath($path) {
        $this->xml->videoPath = $path;
        $this->refreshXml();
        //$this->movieMeta->indexMovies();
        error_log("Video Path");
    }

    function setServerConf($path) {
        $this->xml->serverConf = $path;
        $this->refreshXml();
        $this->addAliasInclude($path);
    }

    function setIMDb($bool) {
        $this->xml->IMDb = $bool;
        $this->refreshXml();
    }

    function setRecursive($bool) {
        $this->xml->recursive = $bool;
        $this->refreshXml();
    }

    function rootDir() {
        return dirname(__FILE__);
    }

    function writeAliases($aliases) {
        // alias file we are writing to
        $myFile = "movies.conf";
        $temp = "";

        // opens file
        $fh = @fopen($myFile, 'w') or die("Could not add alias to server configuration file, please see user guide to do this manually, sorry.");

        // loops through each alias passed and create alias for apache
        foreach($aliases as $alias) {
            
            $temp .= 'Alias "'.$alias->name.'" "'.$alias->path.'"
            <Directory "'.$alias->path.'">
                Options Indexes FollowSymLinks MultiViews
                AllowOverride all
                Order allow,deny
                Allow from all
            </Directory>
            ';
        }
        // writes to file
        @fwrite($fh, $temp);
        fclose($fh);

        error_log("Aliases written to movies.conf");
        
        // restart server so aliases take effect
        if(count($aliases) > 0)
            $this->restartServer();
    }
    
    function addAliasInclude($serverConf) {
        $test = "";
        $text = "Include '".dirname(__FILE__)."/movies.conf'";
        $fh = @fopen($serverConf, 'a+') or die("Could not add alias to server configuration file, please see user guide to do this manually, sorry.");
        $data = fread($fh, filesize($serverConf));
        if(preg_match("~$text~", $data) < 1) {
            @fwrite($fh, "\n".$text);
			if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN')
				$this->restartServer();
            error_log("Alias include ADDED to $serverConf");
        } else {
            error_log("Include statement, $text FOUND in $serverConf");
        }
        fclose($fh);
    }

    function restartServer() {
        //Linux machine
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'LIN') {
            exec(APACHECTL_LIN . " -k graceful");
        }
        //Mac Machine
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'DAR') {
            exec(APACHECTL . " -k graceful");
        }
        //Windows Machine
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            exec("net stop wampapache & net start wampapache");
        }
        error_log("Server Restarted");
    }

    function getMediaInfo() {
        //Linux Machine
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'LIN') {
		return '/usr/bin/mediainfo';
	}
        //Mac Machine
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'DAR') {
            return dirname(__FILE__).'/mediainfo';
        }
        //Windows Machine
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            return dirname(__FILE__).'\MediaInfo.exe';
        }
    }
    
}

?>