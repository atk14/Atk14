<?
/**
 * Class for managing sessions.
 *
 * @package Atk14
 * @subpackage Core
 * @author Jaromir Tomek
 */

/**
 *
 * Class for simple access to sessions.
 *
 * Usage in controller:
 * <code>
 * $this->session->setValue("user_id",123);
 * $this->session->getValue("user_id");
 * $this->session->clearValue("user_id");
 * if($this->session->defined("user_id")){
 *			//...
 * }
 * </code>
 * @package Atk14
 * @subpackage Core
 * @author Jaromir Tomek
 */
class Atk14Session{

	/**
	 * Instance of SessionStorer.
	 *
	 * @var SessionStorer
	 * @access private
	 */
	var $_SessionStorrer = null;

	/**
	 *
	 * Instance must be created by calling
	 * <code>
	 * $session = &Atk14Session::GetInstance();
	 * </code>
	 *
	 * @param string $session_key
	 * @static
	 * @access private
	 */
	function Atk14Session($session_key = "atk14"){
		$this->_SessionStorrer = new SessionStorer($session_key);
	}

	/**
	 * Static method for getting Atk14Session singleton.
	 *
	 * Get default instance:
	 * <code>
	 * $session = &Atk14Session::GetInstance();
	 * </code>
	 *
	 * Get users permanent session. Use his login as a session_key
	 * <code>
	 * $permanent_session = &Atk14Session::GetInstance($user->getLogin());
	 * </code>
	 *
	 * @param string $session_key
	 * @return Atk14Session
	 * @static
	 */
	function &GetInstance($session_key = "atk14"){
		static $instances;
		if(!isset($instances)){ $instances = array(); }
		if(!isset($instances[$session_key])){
			$instances[$session_key] = new Atk14Session($session_key);
		}
		return $instances[$session_key];
	}

	/**
	 * Stores value into session.
	 *
	 * @param string $name
	 * @param string $value
	 */
	function setValue($name,$value){
		$this->_SessionStorrer->writeValue($name,$value);
	}
	/**
	 * Alias to method {@link setValue()}
	 */
	function s($name,$value){ return $this->setValue($name,$value); }

	/**
	 * Get value from a session
	 *
	 * @param string $name
	 * @return string
	 *
	 */
	function getValue($name){
		return $this->_SessionStorrer->readValue($name);
	}

	/**
	 * Alias to method {@link getValue()}
	 */
	function g($name){ return $this->getValue($name); }

	/**
	 * Clears a single session value.
	 *
	 * @param string $name
	 * @uses SessionStorer::writeValue()
	 */
	function clearValue($name){ $this->_SessionStorrer->writeValue($name,null); }

	/**
	 * Clears session value
	 *
	 * Clears session value $name. When $name is omited, all values are cleared.
	 *
	 * Clear all values:
	 * <code>
	 * 	$session->clear();
	 * </code>
	 *
	 * .. or clear only a single value
	 * <code>
	 * 	$session->clear("user_id");
	 * </code>
	 *
	 * @param string $name
	 * @uses clearValue()
	 */
	function clear($name = null){
		if(!isset($name)){
			$this->_SessionStorrer->clear();
			return;
		}
		$this->clearValue($name);
	}

	/**
	 * Checks if a session value is defined.
	 *
	 * @param string $name
	 * @return bool
	 */
	function defined($name){
		$_val = $this->_SessionStorrer->readValue($name);
		return isset($_val);
	}

	/**
	 * Checks if a user has cookies enabled.
	 *
	 * @return bool true if cookies are enabled or false
	 */
	function cookiesEnabled(){
		return $this->_SessionStorrer->cookiesEnabled();
	}
}
?>
