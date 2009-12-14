<?
class HTTPCookie{
	var $_Name = null;
	var $_Value = null;

	var $_Expire = 0;

	var $_Path = "/";
	var $_Domain = "";
	var $_Secure = false;	
	
	function HTTPCookie($cookie_name,$cookie_value){
		settype($cookie_name,"string");
		settype($cookie_value,"string");

		$this->_Name = $cookie_name;
		$this->_Value = $cookie_value;
	}

	function getName(){ return $this->_Name; }
	function getValue(){ return $this->_Value; }
	
	function setExpire($expire_timestamp) {
		settype($expire_timestamp,"integer");
		$this->_Expire = $expire_timestamp;
	}
	function getExpire(){ return $this->_Expire; }

	function setPath($path){
		settype($path,"string");
		$this->_Path = $path;
	}
	function getPath(){ return $this->_Path; }
	
	function setDomain($domain){
		settype($domain,"string");
		$this->_Domain = $domain;
	}
	function getDomain(){ return $this->_Domain; }
	
	function setSecure(){
		$this->_Secure = true;
	}
	function isSecure(){ return $this->_Secure; }
}
?>
