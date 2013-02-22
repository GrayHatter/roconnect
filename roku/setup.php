<?php

new setup();

class setup {

    private $globs;

    function __construct() {
        $this->globs = new globals(false);
        if($this->globs->isNew()) {
            $this->createConfig();
            header('location: /roku/configuration.php?new=true');
        }
    }

    private function createConfig() {

        //Linux machine
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'LIN') {
            $videoPath = "~/movies/";
            $serverConf = "/etc/apache2/apache2.conf";

            //Modify permissions for linux systems
            exec("chmod 777 /var/www/xml/all.xml");
            exec("chmod 777 /var/www/xml/configTemplate.xml");
            exec("chmod 777 /var/www/xml/playlist.xml");
            exec("chmod 777 /var/www/xml/rokuConfig.xml");
            exec("chmod 777 /var/www/movies.conf");
            exec("chmod 777 $serverConf");
        }
        //Mac Machine
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'DAR') {
            exec("chmod 777 '/Applications/MAMP/htdocs/roku/mediainfo'");
            $videoPath = exec("cd ~ && pwd")."/Movies/";
            $serverConf = "/Applications/MAMP/conf/apache/httpd.conf";
        }
        //Windows Machine
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $videoPath = "C:/Users/Public/Videos/";
            $serverConf = "C:/wamp/bin/apache/Apache2.2.17/conf/httpd.conf";
        }

        $this->globs->setup($serverConf, $videoPath, $this->getServerIp());

    }

    function getServerIp() {

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
                return $matches[0][0];
            } else
                return "0.0.0.0";
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
                return $matches[0][0].":8888";
            } else
                return "0.0.0.0";
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
                return $matches[0][0];
            } else
                return "0.0.0.0";
        }
    }

}

?>
