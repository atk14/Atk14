<?php
/**
 * Class for displaying messages
 *
 * @package Atk14
 * @subpackage Core
 * @filesource
 */

/**
 * Class for displaying flash messages.
 *
 * The message is available in current session to the next request and then it is deleted automatically.
 * It is used inside a controller using instance variable $flash.
 *
 * Uses three types of messages, each of them having appropriate method to set:
 * - notice -> info
 * - success
 * - error
 * - warning
 *
 * <code>
 * class MyController extends ApplicationController {
 * 	function edit() {
 * 			...
 * 			$this->flash->notice("record was successfully updated");
 * 			...
 * 	}
 * }
 * </code>
 *
 * Custom message types can be created using the method {@link setMessage()}
 *
 * @package Atk14
 * @subpackage Core
 */
class Atk14Flash{

	/**
	 * Flag indicating if a flash message was read.
	 *
	 * @access private
	 * @var bool
	 */
	var $_FlashRead = false;

	/**
	 * Constructor.
	 *
	 * Do not use it. Instance must be initialized by call {@link GetInstance()}
	 *
	 * <code>
	 * $flash = &Atk14Flash::GetInstance();
	 * </code>
	 *
	 * @access private
	 */
	function Atk14Flash(){
		
	}

	/**
	 * Static method for getting singleton.
	 *
	 * @return Atk14Flash instance of class Atk14Flash
	 */

	static function &GetInstance(){
		static $instance;
		if(!isset($instance)){
			$instance = new Atk14Flash();
		}
		return $instance;
	}

	/**
	 * Method to set a notice message.
	 *
	 * @param string $message A notice string
	 *
	 */
	function setNotice($message){ $this->setMessage("notice",$message); }

	/**
	 * Getter for notice flash message. Can be used as alias to setNotice() method
	 *
	 * @param string $message - An notice string. When null, method returns the notice message, otherwise it sets the notice flash message.
	 * @return string Notice message
	 *
	 */
	function notice($message = null){
		if(isset($message)){ return $this->setNotice($message); }
		return $this->getMessage("notice");
	}

	// alias...
	function setInfo($message){ return $this->setNotice($message); }
	function info($message = null){
		return $this->notice($message);
	}

	/**
	 * Method to set a error message.
	 *
	 * @param string $message An error string
	 *
	 */
	function setError($message){ $this->setMessage("error",$message); }

	/**
	 * Getter for error flash message. Can be used as alias to setError() method
	 *
	 * @param string $message - An error string. When null, method returns the error message, otherwise it sets the error flash message.
	 * @return string Error message
	 *
	 */
	function error($message = null){
		if(isset($message)){ return $this->setError($message); }
		return $this->getMessage("error");
	}

	/**
	 * Method to set a success message.
	 *
	 * @param string $message A success string
	 *
	 */
	function setSuccess($message){ $this->setMessage("success",$message); }

	/**
	 * Getter for success message. Can be used as alias to setSuccess() method
	 *
	 * @param string $message - A success string. When null, method returns the success message, otherwise it sets the success flash message.
	 * @return string Success message
	 *
	 */
	function success($message = null){
		if(isset($message)){ return $this->setSuccess($message); }
		return $this->getMessage("success");
	}

	/**
	 * Method to set a warning message.
	 *
	 * @param string $message A warning string
	 *
	 */
	function setWarning($message){ $this->setMessage("warning",$message); }

	/**
	 * Getter for warning message. Can be used as alias to setWarning() method
	 *
	 * @param string $message - A warning string. When null, method returns the warning message, otherwise it sets the warning flash message.
	 * @return string Warning message
	 *
	 */
	function warning($message = null){
		if(isset($message)){ return $this->setWarning($message); }
		return $this->getMessage("warning");
	}

	/**
	 * Method to set a message with other than one of default keys.
	 *
	 * If the $message param is omitted the $key is used as $param as <strong>notice</strong>.
	 *
	 * @param string $key
	 * @param string $message
	 */
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

	/**
	 * Getter for flash messages with other than one of default keys.
	 *
	 * @param string $key
	 * @return string
	 */
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

	/**
	 * Clears all flash messages.
	 */
	function clearMessages(){
		$session = &Atk14Session::GetInstance();
		$session->clearValue("__flash__");
	}

	/**
	 * Clears all flash messages unless the _FlashRead flag is true
	 *
	 */
	function clearMessagesIfRead(){
		if($this->_FlashRead){ $this->clearMessages(); }
	}

	/**
	 * Clears all flash messages and sets $_FlashRead flag to true
	 */
	function reset(){
		$this->clearMessages();
		$this->_FlashRead = false;
	}
}
?>
