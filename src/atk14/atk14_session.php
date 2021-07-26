<?php
/**
 * Class for managing sessions.
 *
 * @filesource
 */

/**
 * Class for simple access to sessions.
 *
 * Usage in controller:
 * ```
 * $this->session->setValue("user_id",123);
 * $this->session->getValue("user_id");
 * $this->session->clearValue("user_id");
 * if($this->session->defined("user_id")){
 * 		//...
 * }
 * ```
 *
 * @package Atk14\Core
 */
class Atk14Session{

	/**
	 * Instance of SessionStorer class that is responsible for storing values.
	 *
	 * @var SessionStorer
	 * @access private
	 */
	var $_SessionStorer = null;

	/**
	 * Constructor
	 *
	 * Instantiation examples
	 * ```
	 *	$session = Atk14Session::GetInstance();
	 *	$session = Atk14Session::GetInstance("eshop");
	 *	$secure_session = new Atk14Session(new SessionStorer(array("session" => "secure", "ssl_only" => true)));
	 *	$persistent_session = new Atk14Session(new SessionStorer(array("session_name" => "persistent", "cookie_expiration" => 86400*365))); // year
	 *	$session = new Atk14Session(); // !! use Atk14Session::GetInstance() instead
	 *	$session = new Atk14Session("eshop"); // !! use Atk14Session::GetInstance("eshop") instead
	 * ```
	 *
	 * @param mixed $section_or_session_storer
	 */
	function __construct($section_or_session_storer = "atk14"){
		$this->_SessionStorer = is_string($section_or_session_storer) ? new SessionStorer($section_or_session_storer) : $section_or_session_storer;
	}

	/**
	 * Static method for getting Atk14Session singleton.
	 *
	 * Get default instance
	 * ```
	 *	$session = &Atk14Session::GetInstance();
	 * ```
	 * Get users permanent session. Use his login as a session_key
	 * ```
	 *	$permanent_session = &Atk14Session::GetInstance($user->getLogin());
	 * ```
	 *
	 * @param string $section
	 * @return Atk14Session
	 */
	static function &GetInstance($section = "atk14"){
		static $INSTANCES = array();
		if(!isset($INSTANCES[$section])){
			$INSTANCES[$section] = new Atk14Session($section);
		}
		return $INSTANCES[$section];
	}

	/**
	 * Stores value into session.
	 *
	 * @param string $name
	 * @param mixed $value
	 * @param int $expiration number of seconds after which the value expires
	 */
	function setValue($name,$value,$expiration=null){
		$this->_SessionStorer->writeValue($name,$value, $expiration);
	}

	/**
	 * Alias to method {@link setValue()}
	 *
	 * {@see SessionStorer->writeValue()}
	 * @param string $name
	 * @param mixed $value
	 * @param int $expiration number of seconds after which the value expires
	 */
	function s($name,$value,$expiration=null){ return $this->setValue($name,$value,$expiration); }

	/**
	 * Get value from a session
	 *
	 * @param string $name
	 * @return string
	 *
	 */
	function getValue($name){
		return $this->_SessionStorer->readValue($name);
	}

	/**
	 * Alias to method {@link getValue()}
	 * @param mixed $name
	 */
	function g($name){ return $this->getValue($name); }

	/**
	 * Returns all values stored in the session as an associative array.
	 *
	 * It's perfect for inspecting a content of a session.
	 * ```
	 *	var_dump($session->toArray());
	 * ```
	 */
	function toArray(){
		return $this->_SessionStorer->toArray();
	}

	/**
	 * Clears a single session value.
	 *
	 * @param string $name
	 * @uses SessionStorer::writeValue()
	 */
	function clearValue($name){ $this->_SessionStorer->writeValue($name,null); }

	/**
	 * Clears session value
	 *
	 * Clears session value $name. When $name is omited, all values are cleared.
	 *
	 * Clear all values
	 * ```
	 *	$session->clear();
	 * ```
	 *
	 * .. or clear only a single value
	 * ```
	 *	$session->clear("user_id");
	 * ```
	 *
	 * @param string $name
	 * @uses clearValue()
	 */
	function clear($name = null){
		if(!isset($name)){
			$this->_SessionStorer->clear();
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
		$_val = $this->_SessionStorer->readValue($name);
		return isset($_val);
	}

	/**
	 * Checks if a user has cookies enabled.
	 *
	 * @return bool true if cookies are enabled or false
	 */
	function cookiesEnabled(){
		return $this->_SessionStorer->cookiesEnabled();
	}

	/**
	 * Returns the token that identifies current session.
	 *
	 * The token is stored in browser in a cookie.
	 *
	 * Returns null when cookies are not enabled.
	 * ```
	 *	echo $session->getSecretToken(); // 3386.iNWdSQcTGok1COA49ME2JPhU85zHgjRF
	 * ```
	 *
	 * @return string
	 */
	function getSecretToken(){
		return $this->_SessionStorer->getSecretToken();
	}

	/**
	 * Changes the session token value.
	 * This may help against session fixation attack.
	 *
	 * Returns the new token.
	 *
	 * Do nothing when cookies are not enabled.
	 *
	 * @return string
	 */
	function changeSecretToken(){
		return $this->_SessionStorer->changeSecretToken();
	}
}
