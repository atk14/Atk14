<?php
/**
 * Class provides operations on cookies.
 *
 * @filesource
 */

/**
 * Class provides operations on cookies.
 *
 * Basic usage:
 *
 * 	$cookie = new HTTPCookie("last_login_timestamp", "1297764352");
 * 	$cookie->setSecure();
 * 	$cookie->setDomain("atk14.net");
 *
 * @package Atk14\Http
 */
class HTTPCookie{

	/**
	 * Cookie name
	 * 
	 * @var string
	 */
	private $_Name;

	/**
	 * Cookie value
	 *
	 */
	private $_Value;

	/**
	 * Expiration of cookie
	 *
	 * @var integer
	 */
	private $_Expire;

	/**
	 * Cookie path
	 *
	 * @var string
	 */
	private $_Path;

	/**
	 * Cookie domain
	 *
	 * @var string
	 */
	private $_Domain;

	/**
	 * Flag if the cookie is used on ssl
	 *
	 * @var boolean
	 */
	private $_Secure;

	/**
	 * Flag for HTTP only
	 *
	 * @var boolean
	 */
	private $_HttpOnly;

	/**
	 *
	 * @var string
	 */
	private $_SameSite;


	static protected $DEFAULT_OPTIONS = array(
		"expire" => 0,
		"path" => "/",
		"domain" => "",
		"secure" => false,
		"httponly" => false,
		"samesite" => "", // None || Lax  || Strict
	);

	/**
	 *
	 * ```
	 *	HTTPCookie::DefaultOptions(["secure" => true, "samesite" => "None"]); // sets default options
	 *	HTTPCookie::DefaultOptions(); // returns current default options
	 *
	 *	$def_secure = HTTPCookie::DefaultOptions("secure"); // returns default value of the given option
	 * ```
	 */
	static function DefaultOptions($options = null){
		if(isset($options) && !is_array($options)){
			$name = (string)$options;
			return self::$DEFAULT_OPTIONS[$name];
		}

		if(is_array($options)){
			self::$DEFAULT_OPTIONS = $options + self::$DEFAULT_OPTIONS;
		}

		return self::$DEFAULT_OPTIONS;
	}
	
	/**
	 * Creates instantiated cookie object.
	 *
	 * @param string $cookie_name
	 * @param string $cookie_value
	 */
	function __construct($cookie_name,$cookie_value,$options = array()){
		settype($cookie_name,"string");
		settype($cookie_value,"string");

		$options += self::$DEFAULT_OPTIONS;

		$this->_Name = $cookie_name;
		$this->_Value = $cookie_value;

		$this->_Expire = $options["expire"];
		$this->_Path = $options["path"];
		$this->_Domain = $options["domain"];
		$this->_Secure = $options["secure"];
		$this->_HttpOnly = $options["httponly"];
		$this->_SameSite = $options["samesite"];
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
	function setSecure($secure = true){
		$this->_Secure = $secure;
	}

	/**
	 * Checks if the cookie is set as secure.
	 *
	 * @return boolean
	 */
	function isSecure(){ return $this->_Secure; }

	function setHttpOnly($httponly = true){
		$this->_HttpOnly = $httponly;
	}

	/**
	 * Checks whether the given cookie is for HTTP only (not readable for Javascript)
	 *
	 * @return boolean
	 */
	function isHttpOnly(){ return $this->_HttpOnly; }

	/**
	 * Sets the SameSite option for the cookie
	 *
	 * Valid values are "None", "Lax" or "Strict".
	 *
	 * ```
	 *	$cookie->setSameSite("None");
	 * ```
	 *
	 * @param string $samesite
	 */
	function setSameSite($samesite){
		$this->_SameSite = $samesite;
	}

	/**
	 *
	 * @return string
	 */
	function getSameSite(){
		return $this->_SameSite;
	}

	/**
	 * Does this cookie expire?
	 */
	function isExpired(){
		$expire = $this->getExpire();
		return $expire>0 && $expire<time();
	}

	/**
	 * Is the cookie acceptable for the given HTTP request?
	 *
	 * <code>
	 *	if($cookie->isDesignatedFor($request)){
	 *		// cool, this cookie is for you
	 *	}
	 * </code>
	 */
	function isDesignatedFor($request){
		if($this->isSecure() && !$request->sslActive()){ return false; }
		return true;
	}
}
