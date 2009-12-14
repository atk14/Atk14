<?
/**
* V controlleru pouzivat nalsledujici:
*
* 	$this->session->setValue("user_id",123);
*		$this->session->getValue("user_id");
*		$this->session->clearValue("user_id");
* 	if($this->session->defined("user_id")){
*			//...
*		}
*		
*/
class Atk14Session{

	/**
	* Konstruktor.
	* 
	* Instance musi byt vytavarena volanim
	* $session = &Atk14Session::GetInstance();
	* 
	* @access private
	*/
	function Atk14Session($session_key = "atk14"){
		$this->_SessionStorrer = new SessionStorer($session_key);
	}

	/**
	* Staticka metoda pro ziskani jedinacka s prislusnym $session_key.
	* 
	* // ziskani defaultni instance
	* $session = &Atk14Session::GetInstance();
	*
	* // priklad ziskani permanentni session pro prihlaseneho uzivatele
	* // jako klic nam poslouzi jeho login;
	* $permanent_session = &Atk14Session::GetInstance($user->getLogin());
	*/
	function &GetInstance($session_key = "atk14"){
		static $instances;
		if(!isset($instances)){ $instances = array(); }
		if(!isset($instances[$session_key])){
			$instances[$session_key] = new Atk14Session($session_key);
		}
		return $instances[$session_key];
	}

	function setValue($name,$value){
		$this->_SessionStorrer->writeValue($name,$value);
	}
	function s($name,$value){ return $this->setValue($name,$value); }

	function getValue($name){
		return $this->_SessionStorrer->readValue($name);
	}
	function g($name){ return $this->getValue($name); }

	function clearValue($name){ $this->_SessionStorrer->writeValue($name,null); }
	/**
	* Cleares all values...
	* 	$session->clear();
	*
	* ... or only a single value:
	* 	$session->clear("user_id");
	*/
	function clear($name = null){
		if(!isset($name)){
			$this->_SessionStorrer->clear();
			return;
		}
		$this->clearValue($name);
	}

	function defined($name){
		$_val = $this->_SessionStorrer->readValue($name);
		return isset($_val);
	}

	function cookiesEnabled(){
		return $this->_SessionStorrer->cookiesEnabled();
	}
}
?>
