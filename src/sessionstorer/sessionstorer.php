<?php
/**
 *
 * A class for storing and reading values into a session.
 *
 * @package Atk\Sessions
 * @filesource
 */

/**
 * Sessions lifetime, count of seconds
 */
if(!defined("SESSION_STORER_SESSION_MAX_LIFETIME")){
	define("SESSION_STORER_SESSION_MAX_LIFETIME",60 * 60 * 24 * 1);  // a day
}

if(!defined("SESSION_STORER_DEFAULT_SESSION_NAME")){
	define("SESSION_STORER_DEFAULT_SESSION_NAME","session");
}

if(!defined("SESSION_STORER_DEFAULT_SECTION")){
	define("SESSION_STORER_DEFAULT_SECTION","default");
}

/**
 * Session cookie name
 */
if(!defined("SESSION_STORER_COOKIE_NAME_SESSION")){
	define("SESSION_STORER_COOKIE_NAME_SESSION","_%session_name%_"); // _ses_
}

/**
 * Checking cookie name
 *
 * Set SESSION_STORER_COOKIE_NAME_CHECK to empty string for disable sending the testing cookie.
 */
if(!defined("SESSION_STORER_COOKIE_NAME_CHECK")){
	define("SESSION_STORER_COOKIE_NAME_CHECK","_chk_");
}

/**
 * When there isn't a correct session cookie but there is a testing cookie,
 * shall be the session initialize in database? By default it should not.
 */
if(!defined("SESSION_STORER_INITIALIZE_DATABASE_SESSION_EARLY")){
	define("SESSION_STORER_INITIALIZE_DATABASE_SESSION_EARLY",false);
}

/**
 *
 * define("SESSION_STORER_SHARE_COOKIES_ON_SUBDOMAINS",true);
 * define("SESSION_STORER_SHARE_COOKIES_ON_SUBDOMAINS",".example.com");
 */
if(!defined("SESSION_STORER_SHARE_COOKIES_ON_SUBDOMAINS")){
	define("SESSION_STORER_SHARE_COOKIES_ON_SUBDOMAINS",false); 
}

if(!defined("SESSION_STORER_AUTO_GARBAGE_COLLECTION")){
	define("SESSION_STORER_AUTO_GARBAGE_COLLECTION",true);
}

/**
 *
 */
if(!defined("SESSION_STORER_SET_COOKIES_ONLY_ON_SSL_BY_DEFAULT")){
	define("SESSION_STORER_SET_COOKIES_ONLY_ON_SSL_BY_DEFAULT",false);
}

/**
 * A class for storing and reading values into a session.
 *
 * First value is sent encoded in cookie value in HTTP response.
 * Another values are stored in a database (using global $dbmole).
 *
 * Usage:
 *		$session = new SessionStorer();
 *		if(!$session->cookiesEnabled()){
 *			echo "Error: please, enable cookies in your browser";
 *		}
 *		// ...
 *		$session->writeValue("password_validated",true);
 *		//...
 *		if($session->readValue("password_validated") === true){
 *			//...
 *		}
 *
 * There are two tables needed in a database. See README for structure specifications.
 */
class SessionStorer{

	/**
	 * Name of the session
	 * @var string
	 */
	protected $_SessionName = "ses";

	/**
	 * Section name
	 *
	 * @todo explain
	 * @var string
	 */
	protected $_Section = "default";

	/**
	 * Id of a record from the table sessions
	 *
	 * @var integer
	 */
	protected $_SessionId = null;

	/**
	 * A random string, a security supplement for $_SessionId
	 *
	 * @var string
	 */
	protected $_SessionSecurity = null;

	/**
	 * All stored session values
	 * @var array
	 */
	protected $_ValuesStore = array();

	/**
	 * A flag that thit object has been already initialized
	 *
	 * @var boolean
	 */
	protected $_Initialized = false;

	/**
	 * A flag that the token has been already changed
	 *
	 * @var boolean
	 */
	protected $_TokenChanged = false;

	/**
	 * Internal index for counting data entries sent by cookies
	 * @see SessionStorer::_writeDataToCookie()
	 *
	 * @var integer
	 */
	protected $_CookieDataIndex = 0;

	/**
	 * Store for cookies sent by this object
	 * For testing purposes.
	 *
	 * @var array
	 */
	protected $_SentCookies = array();

	/**
	 * Instance of HttpRequest that was used during the request.
	 *
	 * @var HttpRequest
	 */
	protected $_ExtraRequest = null;

	protected $_response = null;

	/**
	 * DbMole instance with connection to database.
	 *
	 * @var DbMole
	 */
	protected $_dbmole = null;

	/**
	 * Max cookie lifetime.
	 *
	 * @var integer
	 */
	protected $_MaxLifetime = null;

	/**
	 * Session is only used via https requests.
	 *
	 * @var boolean
	 */
	protected $_SslOnly;

	/**
	 * Cookie name
	 *
	 * @var string
	 */
	protected $_CookieName = "";

	/**
	 * Time in seconds in which cookie expires.
	 *
	 * @var integer
	 */
	protected $_CookieExpiration = 0;

	/**
	 * Time to which $_CookieExpiration is relative.
	 *
	 * @var integer
	 */
	protected $_ForceCurrentTime = null;

	/**
	 * Constructor
	 *
	 * Several sessions could exists in a database in different sections.
	 *
	 * Example
	 *	 $session = new SessionStorer();
	 *	 $session = new SessionStorer("main_application");
	 *	 $session = new SessionStorer("admin");
	 *
	 * Options description
	 * - request
	 * - dbmole
	 * - session_name
	 * - section
	 * - max_lifetime
	 * - ssl_only
	 * - cookie_name
	 * - cookie_expiration - time in seconds in which cookie expires ( 0 -> until the browser is closed; 86400 -> 1 day )
	 * - current_time - for testing purposes. You can set base time to relate cookie_expiration to.
	 *
	 * @param string $section
	 * @param array $options
	 */
	function __construct($section = "",$options = array()){
		if(is_array($section)){
			$options = $section;
			$section = SESSION_STORER_DEFAULT_SECTION;
		}
		if(!$section){ $section = SESSION_STORER_DEFAULT_SECTION; }

		$options = array_merge(array(
			"request" => null,
			"response" => $GLOBALS["HTTP_RESPONSE"],

			"dbmole" => null,

			"session_name" => SESSION_STORER_DEFAULT_SESSION_NAME,
			"section" => $section,

			"max_lifetime" => null, // for garbage collection
			"ssl_only" => SESSION_STORER_SET_COOKIES_ONLY_ON_SSL_BY_DEFAULT,
			"cookie_name" => SESSION_STORER_COOKIE_NAME_SESSION,
			"cookie_expiration" => 0,

			"current_time" => null,
		),$options);

		$options["cookie_name"] = str_replace("%session_name%",$options["session_name"],$options["cookie_name"]); // "_%session_name%_" -> "_ses_"
		if($options["cookie_expiration"]==0 && !isset($options["max_lifetime"])){
			$options["max_lifetime"] = SESSION_STORER_SESSION_MAX_LIFETIME;
		}
		if($options["cookie_expiration"]>0 && !isset($options["max_lifetime"])){
			$options["max_lifetime"] = round($options["cookie_expiration"] * 1.1); // threshold
		}

		$this->_SessionName = (string)$options["session_name"];
		$this->_Section = (string)$options["section"];
		$this->_ExtraRequest = $options["request"];
		$this->_response = $options["response"];

		$this->_MaxLifetime = $options["max_lifetime"];
		$this->_SslOnly = $options["ssl_only"];
		$this->_CookieName = $options["cookie_name"];
		$this->_CookieExpiration = $options["cookie_expiration"];

		$this->_ForceCurrentTime = $options["current_time"];

		if($options["dbmole"]){
			$this->_dbmole = $options["dbmole"];
		}elseif(isset($GLOBALS["dbmole"])){
			$this->_dbmole = &$GLOBALS["dbmole"];
		}elseif(class_exists("PgMole")){
			$this->_dbmole = &PgMole::GetInstance();
		}elseif(class_exists("OracleMole")){
			$this->_dbmole = &OracleMole::GetInstance();
		}

		$this->_setCheckCookieWhenNeeded();
	}

	/**
	 * Get session name.
	 *
	 * Example
	 * 	$storer = new SessionStorer("default",array("session_name" => "secure", "ssl_only" => true));
	 * 	echo $storer->getSessionName(); // "secure"
	 *
	 * @return string
	 */
	function getSessionName(){ return $this->_SessionName; }

	/**
	 * Get cookie section.
	 *
	 * Example
	 * 	$storer = new SessionStorer("default");
	 * 	echo $session->getSection(); // "default"
	 *
	 * @return string
	 */
	function getSection(){ return $this->_Section; }

	/**
	 * Get cookie name.
	 *
	 * @return string
	 */
	function getCookieName(){ return $this->_CookieName; }

	/**
	 * Get cookie expiration time
	 *
	 * @return integer
	 */
	function getCookieExpiration(){ return $this->_CookieExpiration; }

	/**
	 * Checks whether client has cookies enabled
	 *
	 * @return bool
	 */
	function cookiesEnabled(){
		$request = $this->_getRequest();
		return $request->cookiesEnabled();
	}

	/**
	 * Reads a session value
	 *
	 * Examples
	 * 	$session->readValue("fruit"); // null
	 * 	$session->writeValue("fruit","orange");
	 * 	$session->readValue("fruit"); // "orange"
	 *
	 * @param string $key
	 * @return mixed
	 */
	function readValue($key){
		settype($key,"string");

		$this->_initialize();

		if(isset($this->_ValuesStore[$key]) && (!isset($this->_ValuesStore[$key]["expiration"]) || $this->_ValuesStore[$key]["expiration"]>$this->_getCurrentTime())){
			return $this->_unpackValue($this->_ValuesStore[$key]["packed_value"]);
		}

		return null;
	}

	/**
	 * Writes values into the session
	 *
	 * @param string $key
	 * @param mixed $value				an integer, an string, an array, an object...
	 * @param int $expiration			pocet vterin, po kterou ma hodnota platit
	 */
	function writeValue($key,$value,$expiration = null){
		settype($key,"string");
		if(isset($expiration)){
			settype($expiration,"integer");
		}

		$this->_initialize();

		if((!isset($value) || (isset($expiration) && $expiration<=0)) && !isset($this->_ValuesStore[$key])){
			return; // there is no need to do anything
		}


		if(isset($value)){

			$packed_value = $this->_packValue($value);
			$this->_ValuesStore[$key] = array(
				"packed_value" => $packed_value,
				"expiration" => isset($expiration) ? $this->_getCurrentTime() + $expiration : null
			);

		}else{

			unset($this->_ValuesStore[$key]);

		}

		if(!$this->_isSessionInitializedInDatabase() && $this->cookiesEnabled() && SESSION_STORER_INITIALIZE_DATABASE_SESSION_EARLY){
			$this->_createNewDatabaseSession();
		}

		if($this->_isSessionInitializedInDatabase()){
			$this->_writeDataToDatabase($key);
		}else{
			$this->_writeDataToCookie($key);
		}

	}

	/**
	 * Cleares all values
	 */
	function clear(){
		$this->_initialize();
		foreach($this->_ValuesStore as $k => $v){ $this->writeValue($k,null); }
	}

	function toArray(){
		$this->_initialize();
		$out = array();
		foreach($this->_ValuesStore as $key => $value){
			$out[$key] = $this->readValue($key);
		}
		return $out;
	}

	/**
	 * Returns a secret content of user`s cookie which identifies current session
	 *
	 * Actualy the secret token is the value of the session cookie.
	 *
	 *	echo $session->getSecretToken(); // 1215.WKN7voIUyCGER4OzkPwl2B3eJ1QM68mL
	 */
	function getSecretToken(){
		$this->_initialize();
		if(isset($this->_SessionId) && isset($this->_SessionSecurity)){
			return $this->_SessionId.".".$this->_SessionSecurity;
		}
	}

	/**
	 * Changes the content of user`s cookie belongs to current session.
	 * This helps to prevent against session fixation.
	 *
	 * Returns the content od the new cookie.
	 *
	 *	$current_token = $session->getSecretToken();
	 *	$new_token = $session->changeSecretToken();
	 *	assert($new_token!=$current_token);
	 *
	 * @return string changed token
	 */
	function changeSecretToken(){
		$this->_initialize();

		if($this->_TokenChanged){
			return $this->getSecretToken();
		}

		if(isset($this->_SessionId)){
			$this->_SessionSecurity = SessionStorer::_RandomString();
			$this->_dbmole->doQuery("UPDATE sessions SET security=:security WHERE id=:id",array(
				":id" => $this->_SessionId,
				":security" => $this->_SessionSecurity
			));
			$this->_TokenChanged = true;
			$this->_setSessionCookie();
		}

		return $this->getSecretToken();
	}

	/**
	 * Returns a set of cookies sent by this object
	 * 
	 * 	foreach($session->getSentCookies() as $item){
	 * 		list($name,$value,$expiration) = $item;
	 * 	}
	 *
	 * @return array
	 */
	function getSentCookies(){
		$this->_initialize();
		return $this->_SentCookies;
	}

	function _getRequest(){
		if($this->_ExtraRequest){ return $this->_ExtraRequest; }
		return $GLOBALS["HTTP_REQUEST"];
	}

	/**
	 * Generates a random string
	 *
	 * @param integer $length
	 * @access protected
	 */
	static function _RandomString($length = 32){
		return (string)String4::RandomString($length);
	}

	/**
	 * Sets a testing cookie
	 *
	 * The testing cookie has very long expiration.
	 *
	 * @access protected
	 */
	function _setCheckCookieWhenNeeded(){
		if(SESSION_STORER_COOKIE_NAME_CHECK==""){
			// testing cookie is disabled
			return;
		}

		$request = $this->_getRequest();

		if(
			!isset($request) ||
			!$request->defined(SESSION_STORER_COOKIE_NAME_CHECK,"C") ||
			$this->_getCurrentTime()-(int)$request->getCookieVar(SESSION_STORER_COOKIE_NAME_CHECK)>60*60*24*365*2 // the check cookie is older than 2 years
		){
			$this->_setCookie(SESSION_STORER_COOKIE_NAME_CHECK,$this->_getCurrentTime(),$this->_getCurrentTime()+60*60*24*365*5);
		}
	}

	/**
	 * Initializes this session object
   *
	 * Since this method stars to use a database, it`s good idea to call it ALAP.
	 *
	 * @access protected 
	 */
	function _initialize(){
		if($this->_Initialized){ return; }

		$this->_Initialized = true;

		// the data cookies are meant to exist only in a single request
		// so it`s perfectly fine to delete them here
		$this->_clearDataCookies();

		if(
			($pairs = $this->_obtainSessionIdAndSecurityPairs()) &&
			($this->_checkSessionIdAndSecurity($pairs))
		){
			$this->_garbageCollection();
			$this->_readAllValuesFromDatabase();
			return;
		}

		// transfer data from cookie to database
		if($data_ar = $this->_readCookieData()){
			$this->_createNewDatabaseSession();

			foreach($data_ar as $item){
				$key = $item["key"];
				$data = $item["data"];
				if(!isset($data)){
					$this->writeValue($key,null);
					unset($this->_ValuesStore[$key]);
				}else{
					$this->_ValuesStore[$key] = $data;
				}
			}

			// store all data into database
			foreach(array_keys($this->_ValuesStore) as $key){
				$this->_writeDataToDatabase($key);
			}
		}
	}

	/**
	 * Reads data from the session cookies
	 *
	 * @access protected  
	 */
	function _readCookieData(){
		$out = array();
		$request = $this->_getRequest();

		for($i=0;$i<100;$i+=2){
			if($request->getCookie($this->getCookieName().$i)!="check"){ break; }
			// well on the current index there is a check cookie, so the next one must contain a data or something is terribly wrong!

			$item = $request->getCookie($this->getCookieName().($i+1));
			if(!Packer::Unpack($item,$val)){ return array(); }
			if(!is_array($val) || array_keys($val)!=array("key","data")){ return array(); }
			$out[] = $val;
		}
		
		return $out;
	}

	/**
	 * Are data being stored in database?
	 *
	 * @return bool
	 */
	protected function _isSessionInitializedInDatabase(){
		if(defined("TEST") && TEST && !is_null($this->_SessionId)){
			// Since this is a testing environment,
			// there is a big chance that previously saved session could be deleted due to a database rollback
			if(!$this->_dbmole->selectInt("SELECT COUNT(*) FROM sessions WHERE id=:id AND session_name=:session_name",array(":id" => $this->_SessionId, ":session_name" => $this->getSessionName()))){
				$this->_SessionId = null;
				$this->_SessionSecurity = null;
				return false;
			}
		}
		return strlen($this->getSecretToken())>0;
	}

	/**
	 * Extracts all session cookies from the current request
	 *
	 * Normally none or just one record is detected.
	 *
	 * It is also possible to have *multiple* session cookies from different applications on different domains and paths.
	 * So the Cookie HTTP header contains more values with the same name.
	 *
	 * ```
	 *	$pairs = $this->_obtainSessionIdAndSecurityPairs();
	 *	var_dump($pairs);
	 *	// array(
	 *	//	"123.abcdefghijklmopqrstuvwxyz0123456" => array("id" => 123, "security" => "abcdefghijklmopqrstuvwxyz0123456"),
	 *	//	"4881.a13fhJVULIxDlrnp97ogE8K4bmc0twQF" => array("id" => 4881, "security" => "a13fhJVULIxDlrnp97ogE8K4bmc0twQF"),
	 *	//	"14227.J7vPy5fhDVcRd3KEnHeQrsqCSbFO6xal" => array("id" => 14227, "security" => "J7vPy5fhDVcRd3KEnHeQrsqCSbFO6xal"),
	 *	// )
	 *
	 * ```
	 *
	 * @return array
	 */
	function _obtainSessionIdAndSecurityPairs(){
		$request = $this->_getRequest();

		$pairs = array();

		$c_name = $this->getCookieName();

		$this->__addCookieValueToPairs($request->getCookieVar($c_name),$pairs);

		// Cookie: check=1490347093; session=4881.a13fhJVULIxDlrnp97ogE8K4bmc0twQF; session=14227.J7vPy5fhDVcRd3KEnHeQrsqCSbFO6xal; session=650142.AobSw960vtlPzrq8eFH7ODRsVfhUpWMg; session=681433.ExdUe0wl12pTKysc26ShP27IKR93j0vW
		$cookies = $request->getHeader("Cookie");
		foreach(explode(";",$cookies) as $c){
			$ar = explode("=",trim($c));
			if(urlencode($ar[0])==$c_name){
				$this->__addCookieValueToPairs(urlencode($ar[1]),$pairs);
			}
		}

		return $pairs;
	}

	protected function __addCookieValueToPairs($cookie_val,&$pairs){
		$cookie_val = (string)$cookie_val;
		if(strlen($cookie_val) && preg_match('/^([1-9][0-9]{0,20})\.([a-z0-9]{32})$/i',$cookie_val,$matches)){
			if(sizeof($pairs)>20){
				trigger_error(sprintf('Too many cookies named %s, refusing to add "%s"',$this->getCookieName(),$cookie_val));
				return;
			}

			$pairs[$cookie_val] = array(
				"id" => $matches[1],
				"security" => $matches[2]
			);
		}
	}

	/**
	 * Checks whether a given combination od $id and $security is correct
	 *
	 * Returns true on success.
	 *
	 * @return bool
	 */
	protected function _checkSessionIdAndSecurity($pairs){
		if(!$pairs){ return false; }

		$conditions = $bind_ar = array();

		$conditions[] = "session_name=:session_name";
		$bind_ar[":session_name"] = $this->getSessionName();

		$i = 0;
		$_conds = array();
		foreach($pairs as $pair){
			$_conds[] = "(id=:id_$i AND security=:security_$i)";
			$bind_ar[":id_$i"] = $pair["id"];
			$bind_ar[":security_$i"] = $pair["security"];
			$i++;
		}

		$conditions[] = "(".join(") OR (",$_conds).")";
		
		$row = $this->_dbmole->selectRow("
			SELECT
				id,
				security,
				last_access
			FROM
				sessions
			WHERE
				(".join(") AND (",$conditions).")
			ORDER BY last_access DESC LIMIT 1
		",$bind_ar);
		if($row){
			$this->_SessionId = (int)$row["id"];
			$this->_SessionSecurity = $row["security"];

			if($this->_isTimeToUpdateLastAccess($row["last_access"])){
				$this->_dbmole->doQuery("UPDATE sessions SET last_access=:now WHERE id=:id AND last_access=:last_access",array(
					":id" => $this->_SessionId,
					":last_access" => $row["last_access"],
					":now" => $this->_getNow(),
				));
				if($this->getCookieExpiration()>0){
					// send session cookie again when there is some expiration
					$this->_setSessionCookie();
				}
			}

			return true;
		}

		//error_log("non existing session cookie found: $id.$security (".$request->getRemoteAddr().", ".$request->getUserAgent().")");
		//$this->_clearSessionCookie();

		return false;
	}

	function _isTimeToUpdateLastAccess($current_last_access){
		$min_delta = 60 * 4; // 4 minutes
		$max_delta = 60 * 6; // 6 minutes

		$delta = $this->_getCurrentTime()-strtotime($current_last_access);

		if($delta>=$max_delta){
			return true;
		}

		if($delta<$min_delta){
			return false;
		}

		$min = $delta - $min_delta; // min 0, max: ($max_delta-$min_delta-1)
		$max = $max_delta - $min_delta;

		if(rand($min,$max)>($max-10)){
			return true;
		}

		return false;

		// sessions.last_access is being updated once a 5 minutes
		if($this->_getCurrentTime()-strtotime($current_last_access)>=60*5){
			return true;
		}
	}

	/**
	 * Creates a new record in the table sessions
	 *
	 * Also sets $this->_SessionId and $this->_SessionSecurity to their new values.
	 *
	 * @access protected
	 */
	function _createNewDatabaseSession(){
		$id = $this->_dbmole->selectSequenceNextval("seq_sessions");
		$security = SessionStorer::_RandomString();
		$request = $this->_getRequest();

		$stat = $this->_dbmole->doQuery("
			INSERT INTO sessions(
				id,
				session_name,
				security,
				remote_addr,
				last_access,
				created
			) VALUES(
				:id,
				:session_name,
				:security,
				:remote_addr,
				:now,
				:now
			)
		",array(
			":id" => $id,
			":session_name" => $this->getSessionName(),
			":security" => $security,
			":remote_addr" => $request->getRemoteAddr(),
			":now" => $this->_getNow(),
		));

		$this->_garbageCollection();

		$this->_SessionId = $id;
		$this->_SessionSecurity = $security;

		$this->_setSessionCookie();
	}

	/**
	 * Sets the session cookie
	 *
	 * Do nothing when the same session cookie has been already stored in user`s browser.
	 *
	 * Returns false when it is unable to set the cookie.
	 *
	 * @access protected
	 * @return bool
	 */
	function _setSessionCookie(){
		$request = $this->_getRequest();

		$_expire_time = $this->getCookieExpiration()==0 ? 0 : $this->_getCurrentTime() + $this->getCookieExpiration();
		$_cokie_value = $this->getSecretToken();
		$cookie = $request->getCookie($this->getCookieName());
		if(is_string($cookie) && $cookie==$_cokie_value && $this->getCookieExpiration()==0){
			return true;
		}

		if($this->_SslOnly && (!$request || !$request->ssl())){
			return false;
		}

		return $this->_setCookie($this->getCookieName(),$_cokie_value,$_expire_time);
	}

	/**
	 * Sets a cookie
	 *
	 * Possible options
	 *
	 * @access protected
	 * @param string $name
	 * @param string $value
	 * @param integer $time
	 * @param array $options
	 */
	protected function _setCookie($name,$value,$time = 0,$options = array()){
		$options += array(
			"ssl_only" => null,
			"http_only" => true,
			"domain" => $this->_getCookieDomain(),
			"document_root" => $this->_getWebDocumentRoot(),
		);

		if($this->_SslOnly && is_null($options["ssl_only"])){
			$options["ssl_only"] = $this->_SslOnly;
		}

		$request = $this->_getRequest();

		$this->_SentCookies[] = array($name,$value,$time);
		if(strlen($value)>4000){
			error_log("SessionStorer: there is a long cookie! ".strlen($value)." chars, url: ".$request->getRequestAddress().", consider to reduce size of stored data");
		}
		if($value==""){
			// when it`s about to delete a cookie, 2 setcookie() calls are realized -
			// the one is just for sure that the cookie will be deleted
			$this->__setCookie(
				$name,
				$value,
				$time,
				$options["document_root"]
			);
			SESSION_STORER_SHARE_COOKIES_ON_SUBDOMAINS && $this->__setCookie(
				$name,
				$value,
				$time,
				$options["document_root"],
				$request->getHttpHost()
			);
		}
		return $this->__setCookie(
			$name,
			$value,
			$time,
			$options["document_root"], // 
			$options["domain"], // domain
			$options["ssl_only"], // secure
			$options["http_only"] // http only
		);
	}

	/**
	 * @internal
	 *
	 * @param string $name
	 * @param string $value
	 * @param integer $expire
	 * @param string $path
	 * @param string $domain
	 * @param boolean $secure
	 * @param boolean $http_only
	 */
	protected function __setCookie($name , $value, $expire, $path , $domain = null, $secure = null, $httponly = null){

		$options = array(
			"expire" => $expire,
			"path" => $path,
		);

		// use a option if it is set, otherwise the default value is used
		// see HTTPCookie::DefaultOptions()
		if(!is_null($domain)){ $options["domain"] = $domain; }
		if(!is_null($secure)){ $options["secure"] = $secure; }
		if(!is_null($httponly)){ $options["httponly"] = $httponly; }

		$this->_response->addCookie(new HTTPCookie($name,$value,$options));

		/*
		if(defined("TEST") && TEST){
			return @setcookie($name , $value, $expire, $path , $domain, $secure, $httponly);
		}
		return setcookie($name , $value, $expire, $path , $domain, $secure, $httponly); */
	}

	/**
	 * Returns domain on which a new cookie should be set
	 *
	 * A cookie will be set on the given domain and all its subdomains.
	 *
	 * Null is returned when domain attribute is not required or not acceptable.
	 *
	 * @param mixed $share_cookies_on_subdomains boolean or string
	 * @return string
	 */
	function _getCookieDomain($share_cookies_on_subdomains = null){
		if(!isset($share_cookies_on_subdomains)){ $share_cookies_on_subdomains = SESSION_STORER_SHARE_COOKIES_ON_SUBDOMAINS; }

		$request = $this->_getRequest();

		$domain = null;

		if($share_cookies_on_subdomains){

			$domain = $request->getHttpHost();

			if(preg_match('/^[0-9]{1,3}(\.[0-9]{1,3}){3}(|:[0-9]+)/',$domain)){
				// an IPv4 address -> without domain attribute
				return null;
			}

			if(preg_match('/::/',$domain) || preg_match('/(:[A-F0-9]+){2,}/i',$domain)){
				// an IPv6 address -> without domain attribute
				return null;
			}

			$domain = preg_replace('/:\d+$/','',$domain); // www.example.com:8080 -> www.example.com
			$domain = ".$domain"; // www.example.com -> .www.example.com

			if(is_string($share_cookies_on_subdomains)){
				if($share_cookies_on_subdomains!=substr($domain,-strlen($share_cookies_on_subdomains))){
					return null;
				}
				$domain = $share_cookies_on_subdomains;
			}else{
				$domain = preg_replace('/^\.www(\.[^.]+.*\.[a-z]+)$/','\1',$domain); // .www.example.com -> .example.com; !!! Automation only removes www
			}
		}

		return $domain;
	}

	/**
	 * Cleares a cookie with a given name
	 *
	 * @param string $name
	 */
	protected function _clearCookie($name){
		return $this->_setCookie($name,"",- 60 * 60 * 24 * 365);
	}

	/**
	 * Cleares the session cookie
	 *
	 */
	protected function _clearSessionCookie(){
		return $this->_clearCookie($this->getCookieName());
	}

	/**
	 * Reads all session values from database.
	 *
	 */
	protected function _readAllValuesFromDatabase(){
		if(!isset($this->_SessionId)){
			return;
		}
		$rows = $this->_dbmole->SelectRows("
				SELECT
					key,
					value,
					expiration
				FROM
					session_values
				WHERE
					session_id=:session_id AND
					section=:section
			",array(
				":session_id" => $this->_SessionId,
				":section" => $this->getSection(),
		));
		foreach($rows as $row){
			$_expiration = null;
			if(isset($row["expiration"])){
				$_expiration = strtotime($row["expiration"]);
			}
			$this->_ValuesStore[$row["key"]] = array(
				"packed_value" => $row["value"],
				"expiration" => $_expiration
			);
		}
	}

	/**
	 * Garbage collection
	 *
	 * Deletes entries from database which are older than $this->_MaxLifetime.
	 */
	protected function _garbageCollection(){
		if(!SESSION_STORER_AUTO_GARBAGE_COLLECTION){ return; }

		// if(rand(1,10)!=2){ return; } // TODO: HACK to improve speed
		$this->_dbmole->doQuery("
			DELETE FROM sessions WHERE
				last_access<:min_last_access AND
				session_name=:session_name
		",array(
			":min_last_access" => $this->_getIsoDateTime($this->_getCurrentTime() - $this->_MaxLifetime),
			":session_name" => $this->getSessionName(),
		));

		$this->_dbmole->doQuery("
			DELETE FROM session_values WHERE
				expiration<:now
		",array(
			":now" => $this->_getNow()
		));
	}

	/**
	 * Writes data entry to cookie
	 *
	 * Write value to cookie
	 * 	$this->_writeDataToCookie("logged_user_id");
	 *
	 * @param string $key
	 */
	protected function _writeDataToCookie($key){

		$val = array(
			"key" => $key,
			"data" => isset($this->_ValuesStore[$key]) ? $this->_ValuesStore[$key] : null
		);

		$index = &$this->_CookieDataIndex;

		$this->_setCookie($this->getCookieName().$index,"check"); // only a check that a real value is on the next index
		$index++;

		$this->_setCookie($this->getCookieName().$index,Packer::Pack($val)); // _ses_0, _ses_1, _ses_2...
		$index++;
	}

	/**
	 * Writes a data entry into the database
	 *
	 * Write value for a session key into database
	 * 	$this->_writeDataToDatabase("logged_user_id");
	 *
	 * @param string $key
	 */
	protected function _writeDataToDatabase($key){
		if(!isset($this->_ValuesStore[$key])){
			$this->_dbmole->doQuery("
				DELETE FROM session_values WHERE
					session_id=:session_id AND
					section=:section AND
					key=:key
			",array(
				":session_id" => $this->_SessionId,
				":section" => $this->getSection(),
				":key" => $key
			));
		}else{

			# postgresql
			if ($this->_dbmole->getDatabaseType()=="postgresql") {
				$this->_dbmole->doQuery("
					UPDATE
						session_values
					SET
						value=:value,
						expiration=:expiration
					WHERE
						session_id=:session_id AND section=:section AND key=:key", array(
						":session_id" => $this->_SessionId,
						":section" => $this->getSection(),
						":key" => $key,
						":value" => $this->_ValuesStore[$key]["packed_value"],
						":expiration" => $this->_getIsoDateTime($this->_ValuesStore[$key]["expiration"]),
					)
				);

				if($this->_dbmole->getDatabaseServerVersion("as_float")<9.05){
					// for Postgresql < 9.5
					$query = "
						DO $$
						BEGIN
							INSERT INTO session_values (session_id, section, key, value, expiration) VALUES (:session_id, :section, :key, :value, :expiration);
						EXCEPTION WHEN unique_violation THEN
							UPDATE session_values SET value=:value, expiration=:expiration WHERE session_id=:session_id AND section=:section AND key=:key;
						END;
						$$
					";
				}else{
					// for Postgresql >= 9.5
					$query = "
						INSERT INTO session_values (session_id, section, key, value, expiration)
						VALUES (:session_id, :section, :key, :value, :expiration)
						ON CONFLICT (session_id, section, key) DO UPDATE SET value=:value, expiration=:expiration
						RETURNING id
					";
				}
				$this->_dbmole->doQuery($query,array(
					":session_id" => $this->_SessionId,
					":section" => $this->getSection(),
					":key" => $key,
					":value" => $this->_ValuesStore[$key]["packed_value"],
					":expiration" => $this->_getIsoDateTime($this->_ValuesStore[$key]["expiration"]),
				));
			} else {
				$id = $this->_dbmole->selectSingleValue("
					SELECT id FROM session_values WHERE
					session_id=:session_id AND
					section=:section AND
					key=:key
					",array(
						":session_id" => $this->_SessionId,
						":section" => $this->getSection(),
						":key" => $key
					));

				if(isset($id)){ $this->_dbmole->doQuery("DELETE FROM session_values WHERE id=:id",array(":id" => $id)); }

					$options = array();
				if($this->_dbmole->getDatabaseType()=="oracle"){ $options["clobs"] = array("value"); }

					$this->_dbmole->insertIntoTable("session_values",array(
						"id" => 						$this->_dbmole->selectSequenceNextval("seq_session_values"),
						"session_id" => 		$this->_SessionId,
						"section" => 				$this->getSection(),
						"key" => 						$key,
						"value" => 					$this->_ValuesStore[$key]["packed_value"],
						"expiration" => 		$this->_getIsoDateTime($this->_ValuesStore[$key]["expiration"])
					),$options);
			}
		}
	}

	/**
	 * Compress a value into a ascii encoded string
	 *
	 * @param mixed $value
	 * @return string
	 */
	protected function _packValue($value){
		if(!isset($value)){
			return "";
		}
		return chunk_split(base64_encode(serialize($value)),74);
	}

	/**
	 * Decompress a previously compressed.
	 *
	 * @param string $packed_value
	 * @return mixed
	 */
	protected function _unpackValue($packed_value){
		settype($packed_value,"string");
		if(strlen($packed_value)==0){
			return null;
		}
		return unserialize(base64_decode($packed_value));
	}

	/**
	 * Returns the current timestamp.
	 *
	 * If there was passed option current_time while sessino initialization it returns this value.
	 *
	 * @return integer
	 */
	protected function _getCurrentTime(){
		if($this->_ForceCurrentTime){ return $this->_ForceCurrentTime; }
		return defined("CURRENT_TIME") ? CURRENT_TIME : time();
	}

	/**
	 * Returns current date and time in ISO format
	 * 
	 * @return string
	 */
	protected function _getNow(){
		return $this->_getIsoDateTime($this->_getCurrentTime());
	}

	/**
	 * Converts timestamp into time and date in ISO format
	 * 
	 * @param integer $time
	 * @return string
	 */
	protected function _getIsoDateTime($time){
		if(!isset($time)){ return null; }
		return date("Y-m-d H:i:s",$time);
	}

	/**
	 * Determines application`s base href
	 *
	 * On http://eshop.localhost/ it returns /
	 * On http://localhost/eshop/ it returns /eshop/
	 *
	 * @access protected
	 * @return string
	 */
	function _getWebDocumentRoot(){
		global $ATK14_GLOBAL;

		if(isset($ATK14_GLOBAL)){
			return $ATK14_GLOBAL->getBaseHref();
		}
	  if(defined("WEB_DOCUMENT_ROOT")){
			return WEB_DOCUMENT_ROOT;
		}
		return "/";
	}

	/**
	 * Clears all data cookie if there are any
	 * 
	 * @return int Count of deleted cookies 
	 * @access protected
	 */
	function _clearDataCookies(){
		$request = $this->_getRequest();

		$counter = 0;

		// deleting data cookies if there are any
		for($i=0;$i<100;$i++){
			if($request->getCookie($this->getCookieName().$i)){
				$this->_clearCookie($this->getCookieName().$i);
				$counter++;
			}
		}

		// deleting data cookies - the old way
		if(($cookie = $request->getCookie($this->getCookieName())) && is_array($cookie)){
			foreach(array_keys($cookie) as $key){
				$this->_clearCookie($this->getCookieName()."[$key]");
				$counter++;
			}
		}

		return $counter;
	}
}
