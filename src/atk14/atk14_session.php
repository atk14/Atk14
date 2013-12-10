<?php
/**
 * Class for managing sessions.
 *
 * @package Atk14
 * @subpackage Core
 * @author Jaromir Tomek
 * @filesource
 */

/**
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
	var $_SessionStorer = null;

	/**
	 * <code>
	 *	$session = new Atk14Session(); // !! do not do this
	 *	$session = new Atk14Session("shop"); // !! do not do this
	 *	$secure_session = new Atk14Session(new SessionStorer(array("session" => "secure", "ssl_only" => true)));
	 *	$persistent_session = new Atk14Session(new SessionStorer(array("session_name" => "persistent", "cookie_expiration" => 86400*365))); // year
	 * </code>
	 *
	 * An instance is usually created by calling
	 * <code>
	 * 	$session = Atk14Session::GetInstance();
	 * 	$session = Atk14Session::GetInstance("shop");
	 * </code>
	 *
	 * @param string $session_key
	 */
	function __construct($section_or_session_storer = "atk14"){
		$this->_SessionStorer = is_string($section_or_session_storer) ? new SessionStorer($section_or_session_storer) : $section_or_session_storer;
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
	 * @param string $value
	 */
	function setValue($name,$value){
		$this->_SessionStorer->writeValue($name,$value);
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
		return $this->_SessionStorer->readValue($name);
	}

	/**
	 * Alias to method {@link getValue()}
	 */
	function g($name){ return $this->getValue($name); }

	/**
	 * Returns all values stored in the session as an associative array.
	 *
	 * It's perfect for inspecting a content of a session.
	 * <code>
	 * 	var_dump($session->toArray());
	 * </code>
	 */
	function toArray(){
		// TODO: to be rewritten...
		$this->_SessionStorer->_initialize();
		$out = array();
		foreach($this->_SessionStorer->_ValuesStore as $key => $value){
			$out[$key] = $this->getValue($key);
		}
		return $out;
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
	 *
	 *	{code}
	 *		echo $session->getSecretToken(); // 3386.iNWdSQcTGok1COA49ME2JPhU85zHgjRF
	 * 	{/code}
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
