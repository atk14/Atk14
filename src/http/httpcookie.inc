<?php
/**
 * Class provides operations on cookies.
 *
 * @package Atk14
 * @subpackage Http
 * @filesource
 * @author Jaromir Tomek
 */

/**
 * Class provides operations on cookies.
 *
 * Basic usage:
 * <code>
 * $cookie = new HTTPCookie("last_login_timestamp", "1297764352");
 * $cookie->setSecure();
 * $cookie->setDomain("atk14.net");
 * </code>
 *
 * @package Atk14
 * @subpackage Http
 *
 * @author Jaromir Tomek
 */
class HTTPCookie{

	/**
	 * @access private
	 */
	var $_Name = null;

	/**
	 * @access private
	 */
	var $_Value = null;

	/**
	 * @access private
	 */
	var $_Expire = 0;

	/**
	 * @access private
	 */
	var $_Path = "/";

	/**
	 * @access private
	 */
	var $_Domain = "";

	/**
	 * @access private
	 */
	var $_Secure = false;	
	
	/**
	 * Creates instantiated cookie object.
	 *
	 * @param string $cookie_name
	 * @param string $cookie_value
	 */
	function HTTPCookie($cookie_name,$cookie_value){
		settype($cookie_name,"string");
		settype($cookie_value,"string");

		$this->_Name = $cookie_name;
		$this->_Value = $cookie_value;
	}

	/**
	 * Gets name of cookie.
	 *
	 * @return string
	 */
	function getName(){ return $this->_Name; }

	/**
	 * Gets value of cookie.
	 * @return string
	 */
	function getValue(){ return $this->_Value; }

	/**
	 * Sets expiration of cookie.
	 *
	 * It tells the browser when to delete the cookie.
	 *
	 * @param integer $expire_timestamp
	 */
	function setExpire($expire_timestamp) {
		settype($expire_timestamp,"integer");
		$this->_Expire = $expire_timestamp;
	}

	/**
	 * Gets expiration time of cookie.
	 *
	 * @return integer
	 */
	function getExpire(){ return $this->_Expire; }

	/**
	 * Sets the path scope of the cookie.
	 *
	 * Path scoped cookie should be sent by the browser only for specified path.
	 *
	 * @param string $path
	 */
	function setPath($path){
		settype($path,"string");
		$this->_Path = $path;
	}

	/**
	 * Gets path scope of the cookie.
	 *
	 * @return string
	 */
	function getPath(){ return $this->_Path; }

	/**
	 * Sets the domain scope of the cookie.
	 *
	 * Domain scoped cookie should be sent by the browser only for specified domain.
	 *
	 * @param string $domain
	 */
	function setDomain($domain){
		settype($domain,"string");
		$this->_Domain = $domain;
	}

	/**
	 * Gets domain scope of the cookie.
	 *
	 * @return string
	 */
	function getDomain(){ return $this->_Domain; }

	/**
	 * Sets the cookie as secure.
	 *
	 * Secured cookie is then used only when a browser is visiting a server via HTTPS protocol and encrypted.
	 */
	function setSecure(){
		$this->_Secure = true;
	}

	/**
	 * Checks if the cookie is set as secure.
	 *
	 * @return true
	 */
	function isSecure(){ return $this->_Secure; }
}
