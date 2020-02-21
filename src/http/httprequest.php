<?php
/**
 * HTTPRequest class provides all information about HTTP request.
 *
 * @filesource
 */

/**
 * HTTPRequest class provides all information about HTTP request.
 *
 * Instance of the class is available in any {@link Atk14Controller} descendant as $request variable.
 *
 * @package Atk14\Http
 *
 */
class HTTPRequest{

	/**
	 * Document root
	 *
	 * @var string
	 * @ignore
	 */
	protected $_HTTPRequest_documentRoot = "";
	
	/**
	 * Server name as set in httpd.conf.
	 *
	 * @var string
	 * @ignore
	 */
	protected $_ServerName = "";
	
	/**
	 * Server name as used in requested url.
	 *
	 * @var string
	 * @ignore
	 */
	protected $_HttpHost = "";

	/**
	 * HTTP Referer.
	 *
	 * @var string
	 * @ignore
	 */
	protected $_HttpReferer = "";

	/**
	 * Protocol used.
	 *
	 * @var string
	 * @ignore
	 */
	protected $_HTTPRequest_serverProtocol = "";

	/**
	 * Server port
	 *
	 * @var string
	 * @ignore
	 */
	protected $_ServerAddr = null;

	/**
	 * Port used on server side of connection
	 *
	 * @var integer
	 * @ignore
	 */
	protected $_ServerPort = null;

	/**
	 * @var string
	 */
	protected $_HTTPRequest_scriptName = "";

	/**
	 * @var string
	 */
	protected $_HTTPRequest_scriptFilename = "";

	/**
	 * 
	 * TODO: should be protected...
	 * 
	 */
	var $_HTTPRequest_headers = array();

	/**
	 * @var array
	 */
	protected $_SSLPorts = array(443);

	//var $_HTTPRequest_paramsGet = array();
	//var $_HTTPRequest_paramsPost = array();
	//var $_HTTPRequest_paramsCookie = array();

	/**
	 * Username used for basic authentication
	 *
	 * @var string
	 * @ignore
	 */
	protected $_BasicAuthUsername = null;

	/**
	 * Password used for basic authentication
	 *
	 * @var string
	 * @ignore
	 */
	protected $_BasicAuthPassword = null;

	/**
	 * An array to store force values.
	 *
	 * @var array
	 * @ignore
	 */
	protected $_ForceValues = array();


	/**
	 * Constructor
	 *
	 */
	function __construct(){
		$this->_autoInitialize();
	}


	/**
	 *
	 * Add a port which can be used for SSL connection.
	 *
	 * @param int $port port number. possible string is converted to integer.
	 */
	function addSSLPort($port){
		settype($port,"integer");
		$this->_SSLPorts[] = $port;
	}

	/**
	 * Class initialization.
	 *
	 * Does the main part of the class initialization. Sets all parameters of current request.
	 *
	 * @ignore
	 */
	protected function _autoInitialize(){
		global $_SERVER;
		if(function_exists("getallheaders")){ // in CLI there is no function getallheaders()
			$_headers = getallheaders();
		}else{
			$_headers = array();
			foreach($_SERVER as $k => $v){
				if(substr($k,0,5)=="HTTP_"){ // HTTP_HOST, HTTP_USER_AGENT
					$k = substr($k,5);
					$k = str_replace("_","-",$k);
					$_headers[$k] = $v;
				}
			}
		}
		if(is_array($_headers)){
			$this->_HTTPRequest_headers = $_headers;
		}
		
		if(isset($_SERVER['DOCUMENT_ROOT'])){
			$_tmp = $_SERVER['DOCUMENT_ROOT'];
			settype($_tmp,"string");
			$this->_HTTPRequest_documentRoot = $_tmp;
		}

		if(isset($_SERVER['HTTP_HOST'])){
			$_tmp = $_SERVER['HTTP_HOST'];
			settype($_tmp,"string");
			$_tmp = preg_replace('/:\d+$/','',$_tmp); // secure.example.com:444 -> secure.example.com
			$this->_HttpHost = $_tmp;
		}

		if(isset($_SERVER['SERVER_NAME'])){
			$_tmp = $_SERVER['SERVER_NAME'];
			settype($_tmp,"string");
			$this->_ServerName = $_tmp;
		}

		if(isset($_SERVER['HTTP_REFERER'])){
			$this->_HttpReferer = (string)$_SERVER['HTTP_REFERER'];
		}

		if(isset($_SERVER['SERVER_PROTOCOL'])){
			$_tmp = $_SERVER['SERVER_PROTOCOL'];
			settype($_tmp,"string");
			$this->_HTTPRequest_serverProtocol = $_tmp;
		}

		if(isset($_SERVER['SERVER_PORT'])){
			$this->_ServerPort = (integer)$_SERVER['SERVER_PORT'];
		}

		if(isset($_SERVER['SERVER_ADDR'])){
			$this->_ServerAddr = (string)$_SERVER['SERVER_ADDR'];
		}

		if(isset($_SERVER['SCRIPT_NAME'])){
			$_tmp = $_SERVER['SCRIPT_NAME'];
			settype($_tmp,"string");
			$this->_HTTPRequest_scriptName = $_tmp;
		}

		if(isset($_SERVER['SCRIPT_FILENAME'])){
			$_tmp = $_SERVER['SCRIPT_FILENAME'];
			settype($_tmp,"string");
			$this->_HTTPRequest_scriptFilename = $_tmp;
		}

		if(isset($GLOBALS['_SERVER']['PHP_AUTH_USER']) && isset($GLOBALS['_SERVER']['PHP_AUTH_PW'])){
			$_username = $GLOBALS['_SERVER']['PHP_AUTH_USER'];
			$_password = $GLOBALS['_SERVER']['PHP_AUTH_PW'];
			settype($_username,"string");
			settype($_password,"string");

			$this->_BasicAuthUsername = $_username;
			$this->_BasicAuthPassword = $_password;
		}
		
	}

	/**
	 * Gets request URI.
	 *
	 * @return string
	 */
	function getRequestUri(){
		global $_SERVER;
		if($uri = $this->_getForceValue("RequestUri")){ return $uri; }
		return isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : "";
	}

	/**
	 * Gets request URI.
	 *
	 * Alias to {@link getRequestUri()} method
	 *
	 * @return string
	 */
	function getUri(){ return $this->getRequestUri(); }

	function setRequestUri($uri){
		$this->_setForceValue("RequestUri",$uri);
	}
	function setUri($uri){ $this->setRequestUri($uri); }

	/**
	 * Returns Query sting
	 *
	 * ```
	 * // consider URL https:/example.com/articles/list.php?tag=love&offset=20
	 * echo $request->getQueryString(); // "tag=love&offset=20"
	 * echo $request->getQueryString(true); // "?tag=love&offset=20"
	 *
	 * // consider URL https:/example.com/articles/list.php
	 * echo $request->getQueryString(); // ""
	 * echo $request->getQueryString(true); // ""
	 * ```
	 *
	 * @return string
	 */
	function getQueryString($prepend_question_mark = false){
		$uri = $this->getRequestUri();
		$ary = explode('?',$uri);
		array_shift($ary);
		$query_string = join('?',$ary);
		if($prepend_question_mark && strlen($query_string)){
			return "?".$query_string;
		}
		return $query_string;
	}

	/**
	 * Returns URL to the server
	 *
	 * Basically it returns request URL without the URI part.
	 *
	 * ```
	 * $server_url = $request->getServerUrl(); // e.g. "https://www.test.com:444"
	 * ```
	 *
	 * @returns string
	 */
	function getServerUrl(){
		if($url = $this->_getForceValue("RequestAddress")){
			$url = preg_replace('/^(https?:\/\/[^\/]+)(.*)/i','\1',$url);
			return $url;
		}

		$scheme = $this->getScheme();
		$port = $this->isServerOnStandardPort() ? "" : ":".$this->getServerPort();
		$hostname = $this->getHttpHost();
		return "$scheme://$hostname$port";
	}

	/**
	 * Returns complete address for this request
	 *
	 * ```
	 * echo $HTTP_REQUEST->getRequestAddress(); // e.g. "http://www.grand-book-store.com/en/books/detail/?id=123"
	 * ```
	 *
	 * @return string
	 */
	function getRequestAddress(){
		if($url = $this->_getForceValue("RequestAddress")){
			return $url;
		}

		$server_url = $this->getServerUrl();
		$uri = $this->getRequestUri();
		return "$server_url$uri";
	}

	function setRequestAddress($url){
		$this->_setForceValue("RequestAddress",$url);
	}

	/**
	 * Alias for getRequestUri()
	 *
	 * @return string
	 */
	function getUrl(){
		return $this->getRequestAddress();
	}

	/**
	 * Alias for setRequestAddress()
	 */
	function setUrl($url){
		$this->setRequestAddress($url);
	}

	/**
	 * Gets HTTP referer.
	 *
	 * @return string
	 */
	function getHttpReferer(){
		return $this->_getForceValue_or_Value("HttpReferer");
	}

	function setHttpReferer($referer){
		$this->_setForceValue("HttpReferer",$referer);
	}

	/**
	 * Gets name of script executed.
	 *
	 * @return string
	 */
	function getScriptName(){
		return $this->_HTTPRequest_scriptName;
	}

	/**
	 * Gets remote IP address.
	 *
	 * @return string
	 */
	function getRemoteAddr(){
		if($addr = $this->_getForceValue("RemoteAddr")){ return $addr; }
		return isset($GLOBALS["_SERVER"]["REMOTE_ADDR"]) ? $GLOBALS["_SERVER"]["REMOTE_ADDR"] : null;
	}

	function setRemoteAddr($addr){
		$this->_setForceValue("RemoteAddr",$addr);
	}

	function getRemotePort(){
		if($addr = $this->_getForceValue("RemotePort")){ return $addr; }
		return isset($GLOBALS["_SERVER"]["REMOTE_PORT"]) ? $GLOBALS["_SERVER"]["REMOTE_PORT"] : null;
	}

	function getRemoteHostname(){
		if($addr = $this->getRemoteAddr()){
			return gethostbyaddr($addr);
		}
	}

	/**
	 * Gets server name.
	 *
	 * Returns server name as set in server configuration (httpd.conf)
	 *
	 * @return string
	 */
	function getServerName(){
		return $this->_getForceValue_or_Value("ServerName");
	}

	function setServerName($name){
		$this->_setForceValue("ServerName",$name);
	}

	/**
	 * Gets server IP address
	 *
	 * @return integer
	 */
	function getServerAddr(){
		return $this->_getForceValue_or_Value("ServerAddr");
	}

	function setServerAddr($addr){
		return $this->_setForceValue("ServerAddr",$addr);
	}

	/**
	 * Gets port number.
	 *
	 * @return integer
	 */
	function getServerPort(){
		return $this->_getForceValue_or_Value("ServerPort");
	}

	function setServerPort($port){
		$this->_setForceValue("ServerPort",$port);
	}

	/**
	 * Checks whether the server is running on standard port
	 * 
	 * @return bool
	 */
	function isServerOnStandardPort(){
		$port = $this->getServerPort();
		if(strlen($port)==0){ return true; } // Apparently we're in a shell
		if($this->sslActive()){
			return $this->getServerPort()==443 ||
				$this->getServerPort()==80; // It's quite common that Apache is running on non-ssl port 80 and ssl is provided by Nginx in reverse proxy mode.
		}
		return $this->getServerPort()==80;
	}

	/**
	 * Gets HTTP host.
	 *
	 * Returns server name as given by user in request`s headers
	 *
	 * @return string
	 */
	function getHttpHost(){
		return $this->_getForceValue_or_Value("HttpHost");
	}

	function setHttpHost($host){
		$this->_setForceValue("HttpHost",$host);
	}

	/**
	 * Returns scheme
	 *
	 *	$scheme = $request->getScheme(); // "https" or "http"
	 *
	 * @return string
	 */
	function getScheme(){
		return $this->sslActive() ? "https" : "http";
	}

	/**
	 * Returns authentication string.
	 *
	 * Returns authentication string in the form "username:password".
	 *
	 *	if($request->getBasicAuthString()!="john:magic"){
	 *		$response->authorizationRequired();
	 *		$response->flushAll();
	 *		exit;
	 *	}
	 *
	 * @return string
	 */
	function getBasicAuthString(){
		$username = $this->getBasicAuthUsername();
		$password = $this->getBasicAuthPassword();
		if(strlen($username)>0 || strlen($password)>0){
			return "$username:$password";
		}
	}

	/**
	 * Sets the username and password by the given authentication string
	 *
	 * 	$request->setBasicAuthString("john:magic");
	 *
	 *
	 * 	echo $request->getBasicAuthUsername();
	 * returns "john"
	 *
	 * 	echo $request->getBasicAuthPassword();
	 * returns "magic"
	 *
	 *
	 * 	$request->setBasicAuthString("");
	 *
	 * 	print_r($request->getBasicAuthUsername());
	 * returns null
	 *
	 * 	print_r($request->getBasicAuthPassword());
	 * returns null
	 *
	 * @param string $string authentication string in form username:password
	 */
	function setBasicAuthString($string){
		if(preg_match('/^(.*?):(.*)/',$string,$matches)){
			$this->setBasicAuthUsername($matches[1]);
			$this->setBasicAuthPassword($matches[2]);
			return;
		}

		$this->setBasicAuthUsername(null);
		$this->setBasicAuthPassword(null);
	}

	/**
	 * Returns username from basic authentication string.
	 *
	 * @return string
	 */
	function getBasicAuthUsername(){
		return $this->_getForceValue_or_Value("BasicAuthUsername");
	}

	/**
	 * Sets username for basic authentication.
	 *
	 * @param string $username
	 */
	function setBasicAuthUsername($username){ $this->_setForceValue("BasicAuthUsername",$username); }

	/**
	 * Returns password from basic authentication string.
	 *
	 * @return string
	 */
	function getBasicAuthPassword(){
		return $this->_getForceValue_or_Value("BasicAuthPassword");
	}

	/**
	 * Sets password for basic authentication.
	 */
	function setBasicAuthPassword($password){
		$this->_setForceValue("BasicAuthPassword",$password);
	}

	/**
	 * Returns request method.
	 * 
	 * @return string
	 */
	function getRequestMethod(){
		if($method = $this->_getForceValue("RequestMethod")){ return $method; }

		if(isset($GLOBALS["_SERVER"]["REQUEST_METHOD"])){
			$out = $GLOBALS["_SERVER"]["REQUEST_METHOD"];
			if($out == "POST" && ($_method = strtoupper($this->getVar("_method","PG")))){
				$out = in_array($_method,array("DELETE","PUT")) ? $_method : $out;
			}
			return $out;
		}
	}

	/**
	 * Returns request method.
	 *
	 * Alias to {@link getRequestMethod()}
	 *
	 * @return string
	 */
	function getMethod(){ return $this->getRequestMethod(); }

	/**
	 * Sets http request method.
	 *
	 * @param string $method eg. POST, GET
	 */
	function setRequestMethod($method){
		$this->_setForceValue("RequestMethod",strtoupper($method));
	}

	/**
	 * Sets http request method.
	 *
	 * Alias to {@see setRequestMethod}.
	 *
	 * @param string $method eg. POST, GET
	 */
	function setMethod($method){ $this->setRequestMethod($method); }

	/**
	 * Checks if current request was sent by POST method.
	 *
	 * @return bool
	 */
	function post(){ return $this->getRequestMethod()=="POST"; }

	/**
	 * Checks if current request was sent by GET method.
	 *
	 * @return bool
	 */
	function get(){ return $this->getRequestMethod()=="GET"; }

	/**
	 * Checks if current request was sent by PUT method.
	 *
	 * @return bool
	 */
	function put(){ return $this->getRequestMethod()=="PUT"; }

	/**
	 * Checks if current request was sent by DELETE method.
	 *
	 * @return bool
	 */
	function delete(){ return $this->getRequestMethod()=="DELETE"; }

	/**
	 * Checks if current request was sent as asynchronous or XmlHttpRequest.
	 *
	 * Works with JQuery.
	 *
	 * @return bool
	 */
	function xhr(){
		global $_SERVER;

		if(!is_null($xhr = $this->_getForceValue("Xhr"))){ return $xhr; }

		if(isset($_SERVER["X_ORIGINAL_REQUEST_URI"]) && preg_match('/(&|\?)__xhr_request=1(|&.*)$/',$_SERVER["X_ORIGINAL_REQUEST_URI"])){
			return true;
		}
		
		return strtolower($this->getHeader("X-Requested-With"))=="xmlhttprequest";
	}

	/**
	 * $request->setXhr();
	 * $request->setXhr(true);
	 * $request->setXhr(false);
	 * $request->setXhr(null);
	 */
	function setXhr($value = true){
		$this->_setForceValue("Xhr",$value);
	}

	/**
	 * Returns content of ContentType HTTP header.
	 *
	 * @return string    napr. "text/plain"; null when content-type is not set
	 */
	function getContentType(){
		global $_SERVER;

		if($type = $this->_getForceValue("ContentType")){ return $type; }

		if(isset($_SERVER["CONTENT_TYPE"])){
			$_content_type = $_SERVER["CONTENT_TYPE"];
			if(preg_match("/^([^;]+);/",$_content_type,$matches)){
				$_content_type = trim($matches[1]);
			}
			return $_content_type;
		}
		return null;
	}

	function setContentType($content_type){
		$this->_setForceValue("ContentType",$content_type);
	}

	/**
	 * Returns content of Accept-Charset HTTP header.
	 *
	 * @return string	charset	specification or null when charset is not set
	 */
	function getContentCharset(){
		global $_SERVER;

		if(isset($_SERVER["CONTENT_TYPE"])){
			$_content_type = $_SERVER["CONTENT_TYPE"];	
			$_charset = null;
			if(preg_match("/^.*;\\s*charset\\s*=\\s*([^;]+)/",$_content_type,$matches)){
				$_charset = trim($matches[1]);
			}
			return $_charset;
		}
		return null;	
	}

	/**
	 * Returns content of User-Agent HTTP header
	 *
	 * @return string
	 */
	function getUserAgent(){
		if(!is_null($agent = $this->_getForceValue("UserAgent"))){ return $agent; }
		return isset($GLOBALS["_SERVER"]["HTTP_USER_AGENT"]) ? (string)$GLOBALS["_SERVER"]["HTTP_USER_AGENT"] : null;
	}
	function setUserAgent($agent){ $this->_setForceValue("UserAgent",$agent); }

	/**
	 * Return raw post data.
	 *
	 *
	 * @return string null when no raw data exist
	 */
	function getRawPostData(){
		if(!is_null($data = $this->_getForceValue("RawPostData"))){ return $data; }

		// $HTTP_RAW_POST_DATA removed in PHP7.0
		if(isset($GLOBALS["HTTP_RAW_POST_DATA"])){
			return $GLOBALS["HTTP_RAW_POST_DATA"];
		}

		$postdata = file_get_contents("php://input");
		if(is_string($postdata)){
			return $postdata;
		}

		return null;
	}

	function setRawPostData($data){
		$this->_setForceValue("RawPostData",$data);
	}

	/**
	 * Checks if the request comes over SSL.
	 *
	 * Alias to method {@link sslActive()}.
	 *
	 * @return bool
	 */
	function ssl(){ return $this->sslActive(); }
	
	/**
	 * Checks if the request comes over SSL.
	 *
	 * @return bool
	 */
	function sslActive(){
		if(!is_null($var = $this->_getForceValue("SslActive"))){ return $var; }

		if(isset($GLOBALS["_SERVER"]["HTTPS"]) && $GLOBALS["_SERVER"]["HTTPS"]=="on"){
			return true;
		}

		if(is_null($port = $this->getServerPort())){
			return false;
		}
		return in_array($port,$this->_SSLPorts);
	}

	function setSslActive($ssl = true){
		$this->_setForceValue("SslActive",(bool)$ssl);
	}

	/**
	 * Returns value of a parameter sent in the HTTP request.
	 *
	 * It searches all GET ,POST variables and cookies.
	 * Option $order specifies that only parameters of particular type are searched. At also specifies order in which are the types searched.
	 * <ul>
	 * 	<li><b>G</b> - GET parameters</li>
	 * 	<li><b>P</b> - POST parameters</li>
	 * 	<li><b>C</b> - Cookies</li>
	 * </ul>
	 *
	 * @param string $var_name name of variable to check
	 * @param string $order order and types of parameters which are searched
	 * @return bool
	 */
	function getVar($var_name,$order = "GPC"){
		settype($var_name,"string");
		settype($order,"string");

		$out = null;
		
		for($i=0;$i<strlen($order);$i++){
			if($order[$i]=="G"){
				$out = $this->getGetVar($var_name);
				if(isset($out)){ break; }
			}
			if($order[$i]=="P"){
				$out = $this->getPostVar($var_name);
				if(isset($out)){ break; }
			}
			if($order[$i]=="C"){
				$out = $this->getCookieVar($var_name);
				if(isset($out)){ break; }
			}
		}

		return $out;
	}

	/**
	 * Checks if a parameter was sent in the HTTP request.
	 *
	 * Option $order specifies that only parameters of particular type are searched. At also specifies order in which are the types searched.
	 * <ul>
	 * 	<li><b>G</b> - GET parameters</li>
	 * 	<li><b>P</b> - POST parameters</li>
	 * 	<li><b>C</b> - Cookies</li>
	 * </ul>
	 *
	 * @param string $var_name name of variable to check
	 * @param string $order order and types of parameters which are searched
	 * @return bool
	 */
	function isVarDefined($var_name,$order = "GPC"){
		settype($var_name,"string");
		settype($order,"string");

		$out = $this->getVar($var_name,$order);
		if(isset($out)){
			return true;
		}

		return false;
	}

	/**
	 * Checks if a parameter was sent in the HTTP request.
	 *
	 * Alias to {@link isVarDefined} method.
	 *
	 * @param string $var_name
	 * @param string $order
	 * @return bool
	 * @see isVarDefined for description of $order
	 */
	function defined($var_name,$order = "GPC"){
		return $this->isVarDefined($var_name,$order);
	}

	/**
	 * Checks if a POST parameter is defined.
	 *
	 * @param string $var_name name of POST parameter
	 * @return bool
	 */
	function isPostVarDefined($var_name){ return $this->isVarDefined($var_name,"P"); }

	/**
	 * Checks if a GET parameter is defined.
	 *
	 * @param string $var_name name of GET parameter
	 * @return bool
	 */
	function isGetVarDefined($var_name){ return $this->isVarDefined($var_name,"G"); }

	/**
	 * Checks if a cookie variable is defined.
	 *
	 * @param string $var_name name of cookie variable
	 * @return bool
	 */
	function isCookieVarDefined($var_name){ return $this->isVarDefined($var_name,"C"); }

	/**
	 * Returns value of GET parameter.
	 *
	 * @param string $var_name
	 * @return string
	 */
	function getGetVar($var_name){
		settype($var_name,"string");
		$out = null;
		$vars = $this->getAllGetVars();
		if(isset($vars[$var_name])){
			$out = $vars[$var_name];
		}
		return $out;
	}

	/**
	 * Returns all parameters sent in query string.
	 *
	 * @return array
	 */
	function getAllGetVars(){
		if(!is_null($vars = $this->_getForceValue("GetVars"))){ return $vars; }
		return $GLOBALS["_GET"];
	}

	/**
	 * Returns all parameters sent in query string.
	 *
	 * Alias to {@link getAllGetVars()}.
	 *
	 * @return array
	 */
	function getGetVars(){ return $this->getAllGetVars(); }

	function setGetVars($vars){ $this->_setForceValue("GetVars",$vars); }
	function setGetVar($name,$value){
		$vars = $this->getGetVars();
		$vars["$name"] = $value;
		return $this->setGetVars($vars);
	}


	/**
	 * Gets a particular POST variable.
	 *
	 * @param string $var_name
	 * @return string
	 */
	function getPostVar($var_name){
		settype($var_name,"string");
		$out = null;
		$vars = $this->getAllPostVars();
		if(isset($vars[$var_name])){
			$out = $vars[$var_name];
		}
		return $out;
	}

	/**
	 * Returns all parameters sent in POST.
	 *
	 * @return array
	 */
	function getAllPostVars(){
		if(!is_null($vars = $this->_getForceValue("PostVars"))){ return $vars; }
		return $GLOBALS["_POST"];
	}

	/**
	 * Returns all parameters sent in POST.
	 *
	 * Alias to {@link getAllPostVars()}
	 *
	 * @return array
	 */
	function getPostVars(){ return $this->getAllPostVars(); }

	function setPostVars($vars){ $this->_setForceValue("PostVars",$vars); }
	function setPostVar($name,$value){
		$vars = $this->getPostVars();
		$vars["$name"] = $value;
		return $this->setPostVars($vars);
	}


	/**
	 * Gets a particular cookie
	 *
	 * @param string $cookie_name
	 * @return string
	 */
	function getCookieVar($cookie_name){
		settype($cookie_name,"string");
		$out = null;
		$vars = $this->getAllCookieVars();
		if(isset($vars[$cookie_name])){
			$out = $vars[$cookie_name];
		}
		return $out;
	}

	/**
	 * Returns all cookies
	 *
	 * @return array
	 */
	function getAllCookieVars(){
		if(!is_null($vars = $this->_getForceValue("CookieVars"))){ return $vars; }
		return $GLOBALS["_COOKIE"];
	}

	/**
	 * Returns all cookies
	 *
	 * Alias to {@link getAllCookieVars()}
	 *
	 * @return array
	 */
	function getCookieVars(){ return $this->getAllCookieVars(); }

	/**
	 * Sets multiple cookies
	 *
	 * @param array $vars
	 */
	function setCookieVars($vars){ $this->_setForceValue("CookieVars",$vars); }

	/**
	 * Sets a cookie.
	 *
	 * @param string $name
	 * @param mixed $value
	 */
	function setCookieVar($name,$value){
		$vars = $this->getCookieVars();
		$vars["$name"] = $value;
		return $this->setCookieVars($vars);
	}

	/**
	 * Returns value of a cookie $var_name.
	 *
	 * Alias to {@link getCookieVar()} method.
	 *
	 * @param string $name
	 * @return string value of cookie $var_name
	 */
	function getCookie($name){ return $this->getCookieVar($name); }

	/**
	 * Returns all variables in cookies.
	 *
	 * Alias to {@link getCookieVars()} method.
	 *
	 * @return array
	 */
	function getCookies(){ return $this->getCookieVars(); }

	/**
	 * Checks whether cookies are enabled in the browser.
	 *
	 * Technically it just returns true when there is some cookie set.
	 *
	 * ```
	 * if(!$request->cookiesEnabled()){
	 * 	die("Please, enable cookies in your browser");
	 * }
	 * ```
	 *
	 * @return boolean
	 */
	function cookiesEnabled(){ return sizeof($this->getCookieVars())>0; }

	/**
	 * Returns all variables of specified type from request.
	 *
	 *
	 * You specify type of variables in $order param. 
	 * It also specifies order in which are the types searched.
	 * - <b>G</b> - GET parameters
	 * - <b>P</b> - POST parameters
	 * - <b>C</b> - Cookies
	 *
	 * If there is parameter specified in more than one types then value from the later specified type overrides the first one.
	 *
	 * @param string $order
	 * @return array
	 *
	 */
	function getVars($order = "GPC"){
		$out = array();

		$chars = array_reverse(preg_split('//', $order, -1, PREG_SPLIT_NO_EMPTY));
		foreach($chars as $char){
			switch($char){
				case "G":
					$vars = $this->getGetVars();
					break;
				case "P":
					$vars = $this->getPostVars();
					break;
				case "C":
					$vars = $this->getCookieVars();
					break;
				default:
					$vars = array();
			}
			$out = array_merge($out,$vars);
		}

		return $out;
	}

	/**
	 * Checks if there are some uploaded files.
	 *
	 * @return true
	 */
	function filesUploaded(){
		return sizeof($this->getUploadedFiles())>0;
	}

	/**
	 *
	 * @internal Toto nefunguje
	 * @todo spravit
	 */
	function filesUploadedWithNoError(){
		//echo "<pre>";
		//var_dump($GLOBALS["_FILES"]);
		//echo "</pre>";
		return sizeof($this->getUploadedFiles())==sizeof($GLOBALS["_FILES"]);
	}

	/**
	 * Returns all uploaded files.
	 * 
	 * @param array $options 
	 * @return array array of HTTPUploadedFile instances
	 */
	function getUploadedFiles($options = array()){
		return HTTPUploadedFile::GetInstances($options);
	}

	/**
	 * Returns a uploaded file.
	 *
	 * Method returns uploaded file specified by $name.
	 *
	 * ```
	 * $file = $request->getUploadedFile("userfile");
	 * ```
	 *
	 * When no $name is passed it returns first uploaded file:
	 * ```
	 * $file = $request->getUploadedFile();
	 * ```
	 *
	 *
	 * You can perform various operations on the returned object
	 * ```
	 * echo "filename: ".$file->getFileName()."\n";
	 * echo "size: ".$file->getFileSize()."\n";
	 * echo $file->getContent();
	 * $file->moveTo("data/store/path/");
	 * $file->moveTo("data/store/path/data.txt");
	 * ```
	 *
	 * Notice: When no file is found it tries to find a file uploaded as XmlHttpRequest.
	 *
	 * @param string $name
	 * @param array $options
	 * @return HTTPUploadedFile|HTTPXFile
	 * @todo various operations komentare presunout do popisu tridy HTTPUploadedFile
	 */
	function getUploadedFile($name = null,$options = array()){
		$out = null;
		$files = $this->getUploadedFiles($options);
		foreach($files as $file){
			if(!isset($name) || $file->getName()==$name){
				$out = $file;
				break;
			}
		}

		if(!$out){ $out = HTTPXFile::GetInstance(array("name" => $name)); }

		return $out;
	}

	/**
	 * Return error code for uploaded file.
	 *
	 * It necessarily means that there was an error during upload.
	 * Value 0 means OK.
	 *
	 * Example
	 * ```
	 * if($request->getUploadedFileError("userfile")>0){
	  *	echo "There was an error during file upload";
	 * }
	 * ```
	 *
	 * @see http://php.net/manual/en/features.file-upload.errors.php Description of error codes
	 * @param string $name name of input field used to upload the file
	 * @return integer
	 */
	function getUploadedFileError($name){
		global $_FILES;
		if(isset($_FILES["$name"])){ return (int)$_FILES["$name"]["error"]; }
		return null;
	}

	/**
	 * Gets a footprint of a client
	 *
	 * @param string $output_format valid options are "string", "md5", "array", "serialize"
	 * @return string|array depends on $output_format. When $output_format is set to array the method returns array, in other cases returns string
	 */
	function getClientFootprint($output_format = "string"){
		if($output_format=="md5"){
			return md5($this->getClientFootprint("string"));
		}

		$headers = $this->_HTTPRequest_headers;
		$static_ar = array();
		foreach($headers as $key => $value){
			switch(strtoupper(trim($key))){
				//case "ACCEPT": --> MSIE CHANGES IT DYNAMICALLY :)
				//case "ACCEPT-CHARSET":
				//case "ACCEPT-ENCODING":
				//case "ACCEPT-LANGUAGE":
				case "USER-AGENT":
					$static_ar[$key] = $value;
					break;
			}
		}
	
		unset ($out);
		switch($output_format){
			case "string":
				$string = "";
				foreach($static_ar as $key => $value){
					$string .= "$key: $value\n";
				}
				return $string;
			case "array":
				return $static_ar;
				break;
			case "serialize":
				return serialize($static_ar);
				break;
		}
	}

	/**
	 * Returns all HTTP headers
	 *
	 * @return array
	 */
	function getHeaders(){
		($headers = $this->_getForceValue("headers")) || ($headers = array());
		return array_merge($this->_HTTPRequest_headers,$headers);
	}

	/**
	 * Returns a  particular request header.
	 *
	 * Method is case insensitive.
	 *
	 *
	 * These calls return same value:
	 * 	$val = $request->getHeader("X-File-Name");
	 * 	$val = $request->getHeader("x-file-name");
	 *
	 * @param string $header
	 * @return string content of the header.
	 */
	function getHeader($header){
		$header = strtoupper($header);
		foreach($this->getHeaders() as $k => $v){
			if(strtoupper($k)==$header){ return $v; }
		}
	}

	/**
	 * Sets a header to a value.
	 *
	 * @param string $header
	 * @param string $value
	 */
	function setHeader($header,$value){
		($headers = $this->_getForceValue("headers")) || ($headers = array());
		$headers[$header] = $value;
		$this->_setForceValue("headers",$headers);
	}



	/**
	 * Detects mobile device.
	 *
	 * This code is inspired by code published in article 'PHP to detect mobile phones'( http://www.andymoore.info/php-to-detect-mobile-phones/ ).
	 *
	 * @return bool true when the request comes from a mobile device, otherwise false
	 */
	function mobileDevice(){
		global $_SERVER;
		$user_agent = $this->getUserAgent();

		// check if the user agent value claims to be windows but not windows mobile
		if(isset($user_agent) && stristr($user_agent,'windows') && !stristr($user_agent,'windows ce')){
			return false;
		}

		if(preg_match('/iPad;/',$user_agent)){
			return false;
		}

		// check if the user agent gives away any tell tale signs it's a mobile browser
		if(isset($user_agent) && preg_match('/up.browser|up.link|windows ce|iemobile|mini|mmp|symbian|midp|wap|phone|pocket|mobile|pda|psp|iPhone;|iPod;/i',$user_agent)){
			return true;
		}
		// check the http accept header to see if wap.wml or wap.xhtml support is claimed
		if(isset($_SERVER['HTTP_ACCEPT']) && (stristr($_SERVER['HTTP_ACCEPT'],'text/vnd.wap.wml')||stristr($_SERVER['HTTP_ACCEPT'],'application/vnd.wap.xhtml+xml'))){
			return true;
		}
		// check if there are any tell tales signs it's a mobile device from the _server headers
		if(isset($_SERVER['HTTP_X_WAP_PROFILE'])||isset($_SERVER['HTTP_PROFILE'])||isset($_SERVER['X-OperaMini-Features'])||isset($_SERVER['UA-pixels'])){
			return true;
		}
		// build an array with the first four characters from the most common mobile user agents
		$a = array(
											'acs-'=>'acs-',
											'alav'=>'alav',
											'alca'=>'alca',
											'amoi'=>'amoi',
											'audi'=>'audi',
											'aste'=>'aste',
											'avan'=>'avan',
											'benq'=>'benq',
											'bird'=>'bird',
											'blac'=>'blac',
											'blaz'=>'blaz',
											'brew'=>'brew',
											'cell'=>'cell',
											'cldc'=>'cldc',
											'cmd-'=>'cmd-',
											'dang'=>'dang',
											'doco'=>'doco',
											'eric'=>'eric',
											'hipt'=>'hipt',
											'inno'=>'inno',
											'ipaq'=>'ipaq',
											'java'=>'java',
											'jigs'=>'jigs',
											'kddi'=>'kddi',
											'keji'=>'keji',
											'leno'=>'leno',
											'lg-c'=>'lg-c',
											'lg-d'=>'lg-d',
											'lg-g'=>'lg-g',
											'lge-'=>'lge-',
											'maui'=>'maui',
											'maxo'=>'maxo',
											'midp'=>'midp',
											'mits'=>'mits',
											'mmef'=>'mmef',
											'mobi'=>'mobi',
											'mot-'=>'mot-',
											'moto'=>'moto',
											'mwbp'=>'mwbp',
											'nec-'=>'nec-',
											'newt'=>'newt',
											'noki'=>'noki',
											'opwv'=>'opwv',
											'palm'=>'palm',
											'pana'=>'pana',
											'pant'=>'pant',
											'pdxg'=>'pdxg',
											'phil'=>'phil',
											'play'=>'play',
											'pluc'=>'pluc',
											'port'=>'port',
											'prox'=>'prox',
											'qtek'=>'qtek',
											'qwap'=>'qwap',
											'sage'=>'sage',
											'sams'=>'sams',
											'sany'=>'sany',
											'sch-'=>'sch-',
											'sec-'=>'sec-',
											'send'=>'send',
											'seri'=>'seri',
											'sgh-'=>'sgh-',
											'shar'=>'shar',
											'sie-'=>'sie-',
											'siem'=>'siem',
											'smal'=>'smal',
											'smar'=>'smar',
											'sony'=>'sony',
											'sph-'=>'sph-',
											'symb'=>'symb',
											't-mo'=>'t-mo',
											'teli'=>'teli',
											'tim-'=>'tim-',
											'tosh'=>'tosh',
											'treo'=>'treo',
											'tsm-'=>'tsm-',
											'upg1'=>'upg1',
											'upsi'=>'upsi',
											'vk-v'=>'vk-v',
											'voda'=>'voda',
											'wap-'=>'wap-',
											'wapa'=>'wapa',
											'wapi'=>'wapi',
											'wapp'=>'wapp',
											'wapr'=>'wapr',
											'webc'=>'webc',
											'winw'=>'winw',
											'winw'=>'winw',
											'xda-'=>'xda-'
										);
		// check if the first four characters of the current user agent are set as a key in the array
		if(isset($user_agent) && isset($a[substr($user_agent,0,4)])){
			return true;
		}	

		return false;
	}

	/**
	 * Checks if the request comes from IPhone or IPad device
	 *
	 * @return bool
	 */
	function iphone(){
		return preg_match('/iPhone;/',$this->getUserAgent()) || preg_match('/iPod;/',$this->getUserAgent());
	}

	/**
	 * Gets encoded GET variables.
	 *
	 * {@internal Toto je Valiskova funkce. Neni jasne, k cemu to je....}}
	 *
	 * @todo nejake escapovani podle defaultniho parametru (flagu)?
	 * @todo zjistit funkci metody
	 *
	 */
	function getAllEncodedGetvars(){
    $output = array();
    foreach ($GLOBALS["_GET"] as $name => $value) {
        $output[] = "$name=$value";
    }
    return implode("&", $output);
	}

	/**
	 * @ignore
	 */
	private function _getForceValue($name){
		if(isset($this->_ForceValues[$name])){
			return $this->_ForceValues[$name];
		}
	}

	/**
	 * @ignore
	 */
	private function _getForceValue_or_Value($name){
		$out = $this->_getForceValue($name);
		if(!is_null($out)){
			return $out;
		}
		$v_name = "_$name";
		return $this->$v_name;
	}

	/**
	 * @ignore
	 */
	private function _setForceValue($name,$value){
		if(!defined("TEST") || !TEST){
			trigger_error("HTTPRequest: setting force value in non testing environment: $name=$value",E_USER_WARNING);
		}
		$this->_ForceValues[$name] = $value;
	}
}
