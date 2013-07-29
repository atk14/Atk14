<?php
/**
 * Sessions lifetime, count of seconds
 */
if(!defined("SESSION_STORER_SESSION_MAX_LIFETIME")){
	define("SESSION_STORER_SESSION_MAX_LIFETIME",60 * 60 * 24 * 1);  // 1 den
}
/**
 * Session cookie name
 */
if(!defined("SESSION_STORER_COOKIE_NAME_SESSION")){
	define("SESSION_STORER_COOKIE_NAME_SESSION","_ses_");
}
/**
 * Checking cookie name
 *
 * Set SESSION_STORER_COOKIE_NAME_CHECK to empty string for disable sending the testing cookie.
 */
if(!defined("SESSION_STORER_COOKIE_NAME_CHECK")){
	define("SESSION_STORER_COOKIE_NAME_CHECK","_chk_");
}

if(!defined("SESSION_STORER_DEFAULT_SESSION_NAME")){
	define("SESSION_STORER_DEFAULT_SESSION_NAME","default");
}

/**
 * When there isn't a correct session cookie but there is a testing cookie,
 * shall be the session initialize in database?
 */
if(!defined("SESSION_STORER_INITIALIZE_DATABASE_SESSION_EARLY")){
	define("SESSION_STORER_INITIALIZE_DATABASE_SESSION_EARLY",true);
}

if(!defined("SESSION_STORER_SHARE_COOKIES_ON_SUBDOMAINS")){
	define("SESSION_STORER_SHARE_COOKIES_ON_SUBDOMAINS",false);
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
 * There are two tables needed in a database:
 * <code>
 *			CREATE SEQUENCE seq_sessions;
 *			CREATE TABLE sessions(
 *				id INT NOT NULL PRIMARY KEY DEFAULT NEXTVAL('seq_sessions'),
 *				security VARCHAR(32) NOT NULL CHECK (security ~ '^[a-zA-Z0-9]{32}$'),
 *				--
 *				remote_addr VARCHAR(255) DEFAULT '' NOT NULL,
 *				--
 *				created TIMESTAMP DEFAULT NOW() NOT NULL,
 *				last_access TIMESTAMP DEFAULT NOW() NOT NULL
 *			);
 *			CREATE INDEX in_sessions_lastaccess ON sessions (last_access);
 *
 *			CREATE SEQUENCE seq_session_values;
 *			CREATE TABLE session_values(
 *				id INT NOT NULL PRIMARY KEY DEFAULT NEXTVAL('seq_session_values'),
 *				session_id INT NOT NULL,
 *				session_name VARCHAR(64) NOT NULL CHECK(LENGTH(session_name)>0),
 *				--
 *				key VARCHAR(128) NOT NULL CHECK(LENGTH(key)>0),
 *				value TEXT DEFAULT '' NOT NULL,
 *				expiration TIMESTAMP DEFAULT NULL,
 *				--
 *				CONSTRAINT unq_sessionvalues UNIQUE(session_id,session_name,key),
 *				CONSTRAINT fk_sessionvalues_sessions FOREIGN KEY (session_id) REFERENCES sessions(id) ON DELETE CASCADE
 *			);
 *			CREATE INDEX in_sessionvalues_sessionid ON session_values(session_id);
 *			CREATE INDEX in_sessionvalues_expiration ON session_values(expiration);
 * </code>
 *
 */
class SessionStorer{

	/**
	 * Name of the session
	 * @access protected
	 * @var string
	 */
	var $_SessionName = "default";

	/**
	 * Id of a record from the table sessions
	 * @access protected
	 * @var integer
	 */
	var $_SessionId = null;

	/**
	 * A random string, a security supplement for $_SessionId
	 * @access protected
	 * @var string
	 */
	var $_SessionSecurity = null;

	/**
	 * All stored session values
	 * @access protected
	 * @var array
	 */
	var $_ValuesStore = array();

	/**
	 * A flag that thit object has been already initialized
	 * @access protected
	 */
	var $_Initialized = false;

	/**
	 * A flag that the token has been already changed
	 * @access protected
	 */
	var $_TokenChanged = false;

	/**
	 * Internal index for counting data entries sent by cookies
	 * @see SessionStorer::_writeDataToCookie()
	 * @access protected
	 */
	var $_CookieDataIndex = 0;

	/**
	 * Store for cookies sent by this object
	 * For testing purposes.
	 * @access protected
	 * @var array
	 */
	var $_SentCookies = array();

	var $_request = null;

	var $_dbmole = null;

	/**
	 * Constructor
	 *
	 * Several sessions could exists in a database with different names.
	 * <code>
	 *	 $session = new SessionStorer();
	 *	 $session = new SessionStorer("main_application");
	 *	 $session = new SessionStorer("admin");
	 * </code>
	 */
	function SessionStorer($session_name = "",$options = array()){
		if(is_array($session_name)){
			$options = $session_name;
			$session_name = "";
		}

		$options = array_merge(array(
			"request" => $GLOBALS["HTTP_REQUEST"],
			"dbmole" => null
		),$options);

		if(!$session_name){ $session_name = SESSION_STORER_DEFAULT_SESSION_NAME; }
		$this->_SessionName = (string)$session_name;
		$this->_request = $options["request"];

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
	 * Checks whether client has cookies enabled
	 *
	 * @return bool
	 */
	function cookiesEnabled(){
		return isset($GLOBALS["_COOKIE"]) && sizeof($GLOBALS["_COOKIE"])>0;
	}

	/**
	 * Reads a session value
	 *
	 * <code>
	 *	$session->readValue("fruit"); // null
	 * 	$session->writeValue("fruit","orange");
	 *	$session->readValue("fruit"); // "orange"
	 * </code>
	 *
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

	/**
	 * Returns a secret content of user`s cookie which identifies current session
	 *
	 * Actualy the secret token is the value of the session cookie.
	 *
	 * <code>
	 *	echo $session->getSecretToken(); // 1215.WKN7voIUyCGER4OzkPwl2B3eJ1QM68mL
	 * </code>
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
	 * <code>
	 *	$current_token = $session->getSecretToken();
	 *	$new_token = $session->changeSecretToken();
	 *	assert($new_token!=$current_token);
	 * </code>
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
	 * <code>
	 * 	foreach($session->getSentCookies() as $item){
	 *		list($name,$value,$expiration) = $item;
	 *	}
	 * </code>
	 *
	 * @return array
	 */
	function getSentCookies(){
		return $this->_SentCookies;
	}

	/**
	 * Generates a random string
	 *
	 * @access protected
	 */
	static function _RandomString($length = 32){
		return (string)String::RandomString($length);
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

		if(!isset($this->_request) || !$this->_request->defined(SESSION_STORER_COOKIE_NAME_CHECK,"C")){
			$this->_setCookie(SESSION_STORER_COOKIE_NAME_CHECK,"1",time()+60*60*24*365*5);
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

		// the data cookies are meant to exist only a one request
		// so it`s perfectly fine to delete them here
		$this->_clearDataCookies();

		if(
			$this->_obtainSessionIdAndSecurity($id,$security) &&
			$this->_checkSessionSessionIdAndSecurity($id,$security)
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

		for($i=0;$i<100;$i+=2){
			if($this->_request->getCookie(SESSION_STORER_COOKIE_NAME_SESSION.$i)!="check"){ break; }
			// well on the current index there is a check cookie, so the next one must contain a data or something is terribly wrong!

			$item = $this->_request->getCookie(SESSION_STORER_COOKIE_NAME_SESSION.($i+1));
			if(!Packer::Unpack($item,$val)){ return array(); }
			if(!is_array($val) || array_keys($val)!=array("key","data")){ return array(); }
			$out[] = $val;
		}
		
		return $out;
	}

	/**
	 * Are data being stored in database?
	 *
	 * @access 
	 * @return bool
	 */
	function _isSessionInitializedInDatabase(){
		if(defined("TEST") && TEST && !is_null($this->_SessionId)){
			// Since this is a testing environment,
			// there is a big chance that previously saved session could be deleted due to a database rollback
			if(!$this->_dbmole->selectInt("SELECT COUNT(*) FROM sessions WHERE id=:id",array(":id" => $this->_SessionId))){
				$this->_SessionId = null;
				$this->_SessionSecurity = null;
				return false;
			}
		}
		return strlen($this->getSecretToken())>0;
	}

	/**
	 * Checks whether there is a session cookie
	 *
	 * @access protected
	 * @return bool
	 */
	function _obtainSessionIdAndSecurity(&$id = null,&$security = null){
		$id = null;
		$security = null;

		if(!isset($GLOBALS["_COOKIE"][SESSION_STORER_COOKIE_NAME_SESSION])){ return false; }
		if(!is_string($cookie_val = $GLOBALS["_COOKIE"][SESSION_STORER_COOKIE_NAME_SESSION])){ return false; }

		if(preg_match('/^([1-9][0-9]{0,20})\.([a-z0-9]{32})$/i',$cookie_val,$matches)){
			$id = $matches[1];
			$security = $matches[2];
			return true;
		}

		return false;
	}

	/**
	 * Checks whether a given combination od $id and $security is correct
	 *
	 * Returns true on success.
	 * 
	 * @access protected
	 * @return bool
	 */
	function _checkSessionSessionIdAndSecurity($id,$security){
		settype($id,"integer");
		settype($security,"string");

		if(!$id || !$security){ return false; }

		$rec_security = $this->_dbmole->selectSingleValue("
			SELECT
				security
			FROM
				sessions
			WHERE
				id=:id
		",array(":id" => $id));
		if(isset($rec_security) && $rec_security==$security){
			$this->_dbmole->doQuery("UPDATE sessions SET last_access=:now WHERE id=:id AND last_access<=:before_5_minutes",array(
				":id" => $id,
				":now" => $this->_getNow(),
				":before_5_minutes" => $this->_getIsoDateTime($this->_getCurrentTime()-60*5), // aktualizujeme last_access nejvyse 1x za 5 minut
			));
			$this->_SessionId = $id;
			$this->_SessionSecurity = $security;
			return true;
		}

		//error_log("non existing session cookie found: $id.$security (".$this->_request->getRemoteAddr().", ".$this->_request->getUserAgent().")");

		$this->_clearSessionCookie();
		return false;
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

		$stat = $this->_dbmole->doQuery("
			INSERT INTO sessions(
				id,
				security,
				remote_addr
			) VALUES(
				:id,
				:security,
				:remote_addr
			)
		",array(
			":id" => $id,
			":security" => $security,
			":remote_addr" => $this->_request->getRemoteAddr(),
		));

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
		$_expire_time = 0; // session cookie se nastavi do zavreni prohlizece
		$_cokie_value = $this->getSecretToken();
		$cookie = $this->_request->getCookie(SESSION_STORER_COOKIE_NAME_SESSION);
		if(is_string($cookie) && $cookie==$_cokie_value){
			return true;
		}
		return $this->_setCookie(SESSION_STORER_COOKIE_NAME_SESSION,$_cokie_value,$_expire_time);
	}

	/**
	 * Sets a cookie
	 *
	 * @access protected
	 */
	function _setCookie($name,$value,$time = 0){
		$this->_SentCookies[] = array($name,$value,$time);
		if(strlen($value)>4000){
			error_log("SessionStorer: there is a long cookie! ".strlen($value)." chars, url: ".$this->_request->getRequestAddress().", consider to reduce size of stored data");
		}
		if($value==""){
			// when it`s about to delete a cookie, 2 setcookie() calls are realized -
			// the one is just for sure that the cookie will be deleted
			$this->__setCookie(
				$name,
				$value,
				$time,
				$this->_getWebDocumentRoot()
			);
			SESSION_STORER_SHARE_COOKIES_ON_SUBDOMAINS && $this->__setCookie(
				$name,
				$value,
				$time,
				$this->_getWebDocumentRoot(),
				$this->_request->getHttpHost()
			);
		}
		return $this->__setCookie(
			$name,
			$value,
			$time,
			$this->_getWebDocumentRoot(), // 
			$this->_getCookieDomain(), // domain
			false, // secure
			true // http only
		);
	}

	function __setCookie($name , $value, $expire, $path , $domain = null, $secure = false, $httponly = false){
		if(defined("TEST") && TEST){
			return @setcookie($name , $value, $expire, $path , $domain, $secure, $httponly);
		}
		return setcookie($name , $value, $expire, $path , $domain, $secure, $httponly);
	}

	function _getCookieDomain($share_cookies_on_subdomains = null){
		if(!isset($share_cookies_on_subdomains)){ $share_cookies_on_subdomains = SESSION_STORER_SHARE_COOKIES_ON_SUBDOMAINS; }
		$domain = $this->_request->getHttpHost();
		if($share_cookies_on_subdomains){
			$domain = preg_replace('/^.*\.([^.]+\.[a-z]+)$/','\1',$domain);
		}
		return $domain;
	}

	/**
	 * Cleares a cookie with a given name
	 *
	 * @access protected
	 */
	function _clearCookie($name){
		return $this->_setCookie($name,"",- 60 * 60 * 24 * 365);
	}

	/**
	 * Cleares the session cookie
	 *
	 * @access protected
	 */
	function _clearSessionCookie(){
		return $this->_clearCookie(SESSION_STORER_COOKIE_NAME_SESSION);
	}

	/**
	 * Reads all session values from database.
	 *
	 * @access protected
	 */
	function _readAllValuesFromDatabase(){
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
					session_name=:session_name
			",array(
				":session_id" => $this->_SessionId,
				":session_name" => $this->_SessionName
		));
		while(list(,$row) = each($rows)){
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
	 * Realizes garbage collection
	 *
	 * Deletes old entries from database.
	 *
	 * @access protected 
	 */
	function _garbageCollection(){
		$_seconds = SESSION_STORER_SESSION_MAX_LIFETIME;
		settype($_seconds,"integer");
		$this->_dbmole->doQuery("
			DELETE FROM sessions WHERE
				last_access<:last_access
		",array(
			":last_access" => $this->_getIsoDateTime($this->_getCurrentTime() - $_seconds)
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
	 * <code>
	 * $this->_writeDataToCookie("logged_user_id");
	 * </code>
	 * 
	 * @access protected
	 */
	function _writeDataToCookie($key){

		$val = array(
			"key" => $key,
			"data" => isset($this->_ValuesStore[$key]) ? $this->_ValuesStore[$key] : null
		);

		$index = &$this->_CookieDataIndex;

		$this->_setCookie(SESSION_STORER_COOKIE_NAME_SESSION.$index,"check"); // only a check that a real value is on the next index
		$index++;

		$this->_setCookie(SESSION_STORER_COOKIE_NAME_SESSION.$index,Packer::Pack($val)); // _ses_0, _ses_1, _ses_2...
		$index++;
	}

	/**
	 * Writes a data entry into the database
	 * 
	 * <code>
	 * $this->_writeDataToDatabase("logged_user_id");
	 * </code>
	 * 
	 * @access protected
	 */
	function _writeDataToDatabase($key){
		if(!isset($this->_ValuesStore[$key])){
			$this->_dbmole->doQuery("
				DELETE FROM session_values WHERE
					session_id=:session_id AND
					session_name=:session_name AND
					key=:key
			",array(
				":session_id" => $this->_SessionId,
				":session_name" => $this->_SessionName,
				":key" => $key
			));
		}else{

			$id = $this->_dbmole->selectSingleValue("
				SELECT id FROM session_values WHERE
					session_id=:session_id AND
					session_name=:session_name AND
					key=:key
			",array(
				":session_id" => $this->_SessionId,
				":session_name" => $this->_SessionName,
				":key" => $key
			));

			if(isset($id)){ $this->_dbmole->doQuery("DELETE FROM session_values WHERE id=:id",array(":id" => $id)); }

			$options = array();
			if($this->_dbmole->getDatabaseType()=="oracle"){ $options["clobs"] = array("value"); }

			$this->_dbmole->insertIntoTable("session_values",array(
				"id" => 						$this->_dbmole->selectSequenceNextval("seq_session_values"),
				"session_id" => 		$this->_SessionId,
				"session_name" => 	$this->_SessionName,
				"key" => 						$key,
				"value" => 					$this->_ValuesStore[$key]["packed_value"],
				"expiration" => 		$this->_getIsoDateTime($this->_ValuesStore[$key]["expiration"])
			),$options);
		}
	}

	/**
	 * Compress a value into a ascii encoded string
	 *
	 * @access protected
	 * @param mixed $value
	 * @return string
	 */
	function _packValue($value){
		if(!isset($value)){
			return "";
		}
		return chunk_split(base64_encode(serialize($value)),74);
	}

	/**
	 * Decompress a previously compressed.
	 *
	 * @access protected
	 * @param string $packed_value
	 * @return mixed
	 */
	function _unpackValue($packed_value){
		settype($packed_value,"string");
		if(strlen($packed_value)==0){
			return null;
		}
		return unserialize(base64_decode($packed_value));
	}

	/**
	 * Returns the current timestamp
	 *
	 * @access protected
	 * @return integer
	 */
	function _getCurrentTime(){
		return defined("CURRENT_TIME") ? CURRENT_TIME : time();
	}

	/**
	 * Returns current date and time in ISO format
	 * 
	 * @access protected
	 * @return string
	 */
	function _getNow(){
		return $this->_getIsoDateTime($this->_getCurrentTime());
	}

	/**
	 * Converts timestamp into time and date in ISO format
	 * 
	 * @access protected
	 * @return string
	 */
	function _getIsoDateTime($time){
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
	 * Cleares all data cookie if there are any
	 * 
	 * @access protected
	 * @return int Count of deleted cookies 
	 */
	function _clearDataCookies(){
		$counter = 0;

		// deleting data cookies if there are any
		for($i=0;$i<100;$i++){
			if($this->_request->getCookie(SESSION_STORER_COOKIE_NAME_SESSION.$i)){
				$this->_clearCookie(SESSION_STORER_COOKIE_NAME_SESSION.$i);
				$counter++;
			}
		}

		// deleting data cookies - the old way
		if(($cookie = $this->_request->getCookie(SESSION_STORER_COOKIE_NAME_SESSION)) && is_array($cookie)){
			foreach(array_keys($cookie) as $key){
				$this->_clearCookie(SESSION_STORER_COOKIE_NAME_SESSION."[$key]");
				$counter++;
			}
		}

		return $counter;
	}
}
