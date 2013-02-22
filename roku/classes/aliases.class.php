<?php

class aliases {

    private $data = array();

    function __construct() {
        $a = func_get_args();
        $i = func_num_args();
        if (method_exists($this, $f = '__construct' . $i)) {
            call_user_func_array(array($this, $f), $a);
        }
    }

    function __construct2($name, $path) {
        $this->data["name"] = $name;
        $this->data["path"] = $path;
    }

    public function __get($member) {
        if (isset($this->data[$member])) {
            return $this->data[$member];
        }
    }

    public function __set($member, $value) {
        if (isset($this->data[$member])) {
            $this->data[$member] = $value;
        }
    }

}

?>
