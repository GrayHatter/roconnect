<?php

class playlist {

    private $data = array();

    function __construct() {
        $a = func_get_args();
        $i = func_num_args();
        if (method_exists($this, $f = '__construct' . $i)) {
            call_user_func_array(array($this, $f), $a);
        }
    }

    function __construct1($array) {
        foreach ($array as $k => $v)
            $this->data[$k] = $array[$k];
    }

    function __construct4($id, $title, $image, $description) {
        $this->data["id"] = $id;
        $this->data["title"] = $title;
        $this->data["image"] = $image;
        $this->data["description"] = $description;
    }
    
    function __construct5($id, $title, $image, $description, $feed) {
        $this->data["id"] = $id;
        $this->data["title"] = $title;
        $this->data["image"] = $image;
        $this->data["description"] = $description;
        $this->data["feed"] = $feed;
    }

    public function __get($member) {
        if (isset($this->data[$member])) {
            return $this->data[$member];
        }
    }

    public function __set($member, $value) {
        $this->data[$member] = $value;
    }

    public function toJSON() {
        $json = array('id' => $this->data['id'], 'title' => $this->data['title'], 'image' => $this->imageRelativePath(), 'description' => $this->data['description'], 'file' => $this->movielistFile());
        return json_encode($json);
    }

    private function imageRelativePath() {
        return substr($this->data['image'], strpos($this->data['image'], "/")+1);
    }

    public function movielistFile() {
        return substr($this->data['feed'], strrpos($this->data['feed'], "/")+1);
    }

}

?>
