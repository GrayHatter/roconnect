<?php

class movie {

    private $data = array();

    function __construct() {
        $a = func_get_args();
        $i = func_num_args();
        if (method_exists($this, $f = '__construct' . $i)) {
            call_user_func_array(array($this, $f), $a);
        }
    }

    function __construct0() {
        $this->data["contentId"] = uniqid();
    }

    function __construct1($contentId) {
        $this->data["contentId"] = $contentId;
    }

    public function __get($member) {
        if (isset($this->data[$member])) {
            return $this->data[$member];
        }
    }

    public function __set($member, $value) {
        // The ID of the dataset is read-only
        if ($member == "contentId") {
            return;
        }

        $this->data[$member] = $value;
    }

    public function imageRelativePath() {
        return substr($this->data['hdImg'], strpos($this->data['hdImg'], "/")+1);
    }

    public function getData() {
        return $this->data;
    }

    public function toJSON() {
        $json = array('contentId' => $this->data['contentId'], 'title' => $this->data['title']);
        return json_encode($json);
    }

}

?>