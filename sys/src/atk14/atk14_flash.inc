<?
/**
 * Class for displaying messages
 *
 * @package Atk14
 * @subpackage Core
 * @author Jaromir Tomek
 */

/**
 * Class for displaying messages.
 *
 * The message is available in current session to the next request and then it is deleted automatically.
 * It is used inside a controller using instance variable $flash.
 *
 * Uses three types of messages: notice, success, error
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
 * @package Atk14
 * @subpackage Core
 * @author Jaromir Tomek
 */
class Atk14Flash{

	/**
	 * @access private
	 * @var bool
	 */
	var $_FlashRead = false;

	/**
	* Constructor.
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
	 * @static
	 * @return Atk14Flash instance of class Atk14Flash
	 *
	 */

	function &GetInstance(){
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
