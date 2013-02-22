<?php

class roku {

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

    function __construct2($usn, $location) {
        $this->data["usn"] = $usn;
        $this->data["location"] = $location;
        $this->data["name"] = "";
    }

    public function __get($name) {
        if (array_key_exists($name, $this->data)) {
            return $this->data[$name];
        }

        $trace = debug_backtrace();
        trigger_error(
            'Undefined property via __get(): ' . $name .
            ' in ' . $trace[0]['file'] .
            ' on line ' . $trace[0]['line'],
            E_USER_NOTICE);
        return null;
    }

    public function __set($member, $value) {
        // The ID of the dataset is read-only
        if ($member == "usn") {
            return;
        }
        $this->data[$member] = $value;
    }

    public function __isset($name) {
        return isset($this->data[$name]);
    }

    public function toJSON() {
        $json = array('usn' => $this->data['usn'], 'location' => $this->data['location'], 'name' => $this->data['name'], 'status' => $this->data['status']);
        return json_encode($json);
    }
}

?>