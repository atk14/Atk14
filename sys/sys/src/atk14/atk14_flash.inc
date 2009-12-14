<?
class Atk14Flash{
	var $_FlashRead = false;

	/**
	* Konstruktor.
	*
	* Instance musi byt ziskavana volanim
	* $flash = &Atk14Flash::GetInstance();
	*
	* @access private
	*/
	function Atk14Flash(){
		
	}

	/**
	* Staticka metoda pro ziskani jedinacka.
	* 
	* 
	*/
	function &GetInstance(){
		static $instance;
		if(!isset($instance)){
			$instance = new Atk14Flash();
		}
		return $instance;
	}

	function setNotice($message){ $this->setMessage("notice",$message); }
	function notice($message = null){
		if(isset($message)){ return $this->setNotice($message); }
		return $this->getMessage("notice");
	}

	function setError($message){ $this->setMessage("error",$message); }
	function error($message = null){
		if(isset($message)){ return $this->setError($message); }
		return $this->getMessage("error");
	}

	function setSuccess($message){ $this->setMessage("success",$message); }
	function success($message = null){
		if(isset($message)){ return $this->setSuccess($message); }
		return $this->getMessage("success");
	}

	function setMessage($key,$message = null){
		$session = &Atk14Session::GetInstance();
		if(!isset($message)){
			$message = $key;
			$key = "notice";
		}
		settype($key,"string");
		settype($message,"string");

		if(!($flash_ar = $session->getValue("__flash__"))){ $flash_ar = array(); }

		$flash_ar["$key"] = $message;
		$session->setValue("__flash__",$flash_ar);
	}

	function getMessage($key = "notice"){
		$session = &Atk14Session::GetInstance();

		$out = "";
		$flash_ar = $session->getValue("__flash__");
		if(isset($flash_ar) && isset($flash_ar[$key])){
			$out = $flash_ar[$key];
		}

		$this->_FlashRead = true;

		return $out;
	}

	function clearMessages(){
		$session = &Atk14Session::GetInstance();
		$session->clearValue("__flash__");
	}

	function clearMessagesIfRead(){
		if($this->_FlashRead){ $this->clearMessages(); }
	}

	function reset(){
		$this->clearMessages();
		$this->_FlashRead = false;
	}
}
?>
