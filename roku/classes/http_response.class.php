<?php
class http_response {

	private $data = array();
	
	function __construct ($responseData) {
		$pattern = "/(.*?)Cache-Control:(.*?)st:(.*?)USN:(.*?)Ext:(.*?)Server:(.*?)Location:(.*)/is";
		if(preg_match($pattern, $responseData, $match)) {
			$this->data["head"] = trim($match[1]);
			$this->data["cacheControl"] = trim($match[2]);
			$this->data["st"] = trim($match[3]);
			$this->data["usn"] = trim($match[4]);
			$this->data["ext"] = trim($match[5]);
			$this->data["server"] = trim($match[6]);
			$this->data["location"] = trim($match[7]);
			return true;
		} else {
			return false;
		}
		
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