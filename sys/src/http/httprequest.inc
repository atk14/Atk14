<?php
/**
 * @package Atk14
 * @subpackage InternalLibraries
 * @filesource
 */

if (get_magic_quotes_gpc()) {
	/**
	 * Strips slashes in strings passed in array.
	 *
	 * @param array $array array of strings to strip slashes from
	 * @return array
	 */
	function stripslashes_array($array) {
			return is_array($array) ? array_map('stripslashes_array', $array) : stripslashes($array);
	}

	$_COOKIE = stripslashes_array($_COOKIE);
	//$_FILES = stripslashes_array($_FILES);
	$_GET = stripslashes_array($_GET);
	$_POST = stripslashes_array($_POST);
	//$_REQUEST = stripslashes_array($_REQUEST);
}

/**
 * HTTPRequest class provides all information about HTTP request.
 *
 * Instance of the class is available in any {@link Atk14Controller} descendant as $request variable.
 *
 * @package Atk14
 * @subpackage InternalLibraries
 *
 */
class HTTPRequest{

	/**
	 * @access private
	 */
	var $_HTTPRequest_documentRoot = "";
	
	/**
	 * Server name as set in httpd.conf.
	 *
	 * @access private
	 */
	var $_HTTPRequest_serverName = "";
	
	/**
	 * Jmeno serveru, ktere napise uzivak do url.
	 * @access private
	 */
	var $_HTTPRequest_HTTPHost = "";

	/**
	 * Protocol used.
	 * @access private
	 */
	var $_HTTPRequest_serverProtocol = "";

	/**
	 * @access private
	 */
	var $_HTTPRequest_serverPort = null;

	/**
	 * @access private
	 */
	var $_HTTPRequest_scriptName = "";

	/**
	 * @access private
	 */
	var $_HTTPRequest_scriptFilename = "";

	/**
	 * @access private
	 */
	var $_HTTPRequest_headers = array();

	/**
	 * @access private
	 * @var array
	 */
	var $_SSLPorts = array(443);

	//var $_HTTPRequest_paramsGet = array();
	//var $_HTTPRequest_paramsPost = array();
	//var $_HTTPRequest_paramsCookie = array();

	/**
	 * @var string
	 * @access private
	 */
	var $_HTTPRequest_BasicAuth_Username = null;

	/**
	 * @var string
	 * @access private
	 */
	var $_HTTPRequest_BasicAuth_Password = null;


	/**
	 * @return HTTPRequest
	 */
	function HTTPRequest(){
		$this->_AutoInitialize();
	}


	/**
	 *
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
	 * @access private
	 */
	function _AutoInitialize(){
		global $_SERVER;
		if(function_exists("getallheaders")){ // ve cli PHP funkce getallheaders neexistuje
			$_headers = getallheaders();
		}else{
			$_headers = array();
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
			$this->_HTTPRequest_HTTPHost = $_tmp;
		}

		if(isset($_SERVER['SERVER_NAME'])){
			$_tmp = $_SERVER['SERVER_NAME'];
			settype($_tmp,"string");
			$this->_HTTPRequest_serverName = $_tmp;
		}

		if(isset($_SERVER['SERVER_PROTOCOL'])){
			$_tmp = $_SERVER['SERVER_PROTOCOL'];
			settype($_tmp,"string");
			$this->_HTTPRequest_serverProtocol = $_tmp;
		}

		if(isset($_SERVER['SERVER_PORT'])){
			$_tmp = $_SERVER['SERVER_PORT'];
			settype($_tmp,"integer");
			$this->_HTTPRequest_serverPort = $_tmp;
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

		//if(isset($GLOBALS['_COOKIE'])){
		//	$this->_HTTPRequest_paramsCookie = $GLOBALS['_COOKIE'];
		//}
		//if(isset($GLOBALS['_GET'])){
		//	$this->_HTTPRequest_paramsGet = $GLOBALS['_GET'];
		//}
		//if(isset($GLOBALS['_POST'])){
		//	$this->_HTTPRequest_paramsPost = $GLOBALS['_POST'];
		//}

		if(isset($GLOBALS['_SERVER']['PHP_AUTH_USER']) && isset($GLOBALS['_SERVER']['PHP_AUTH_PW'])){
			$_username = $GLOBALS['_SERVER']['PHP_AUTH_USER'];
			$_password = $GLOBALS['_SERVER']['PHP_AUTH_PW'];
			settype($_username,"string");
			settype($_password,"string");

			$this->_HTTPRequest_BasicAuth_Username = $_username;
			$this->_HTTPRequest_BasicAuth_Password = $_password;

		}
		
	}

	/**
	 * Gets request URI.
	 *
	 * @return string
	 */
	function getRequestUri(){
		global $_SERVER;
		if(isset($this->_ForceRequestUri)){ return $this->_ForceRequestUri; }
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

	function setRequestUri($uri){ $this->_ForceRequestUri = $uri; }
	function setUri($uri){ $this->setRequestUri($uri); }

	function getRequestAddress(){
		$proto = $this->SSLActive() ? "https" : "http";
		$port = "";
		if($this->SSLActive() && $this->getServerPort()!=443){
			$port = ":".$this->getServerPort();
		}
		if(!$this->SSLActive() && $this->getServerPort()!=80){
			$port = ":".$this->getServerPort();
		}
		$hostname = $this->getHTTPHost();
		$uri = $this->getRequestUri();
		return "$proto://$hostname$port$uri";
	}

	/**
	 * Gets HTTP referer.
	 *
	 * @return string
	 */
	function getHttpReferer(){
		return isset($GLOBALS["_SERVER"]["HTTP_REFERER"]) ? $GLOBALS["_SERVER"]["HTTP_REFERER"] : "";
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
		return isset($GLOBALS["_SERVER"]["REMOTE_ADDR"]) ? $GLOBALS["_SERVER"]["REMOTE_ADDR"] : null;
	}

	/**
	 * Gets server name.
	 *
	 * Returns server name as set in server configuration (httpd.conf)
	 *
	 * @return string
	 */
	function getServerName(){
		return $this->_HTTPRequest_serverName;
	}

	/**
	 * Gets port number.
	 *
	 * @return integer
	 */
	function getServerPort(){
		return $this->_HTTPRequest_serverPort;
	}

	/**
	 * Gets HTTP host.
	 *
	 * Returns server name as given by user in URI
	 *
	 * @return string
	 */
	function getHTTPHost(){
		return $this->_HTTPRequest_HTTPHost;
	}

	/**
	 * Returns authentication string.
	 *
	 * Returns string in the form "username:password".
	 *
	 * @access public
	 * @return string
	 */
	function getBasicAuthString(){
		if(strlen($this->_HTTPRequest_BasicAuth_Username)>0 || strlen($this->_HTTPRequest_BasicAuth_Password)>0){
			return "$this->_HTTPRequest_BasicAuth_Username:$this->_HTTPRequest_BasicAuth_Password";
		}
		return "";
	}

	/**
	 * Returns username from basic authentication string.
	 *
	 * @return string
	 */
	function getBasicAuthUsername(){
		return $this->_HTTPRequest_BasicAuth_Username;
	}

	/**
	 * Returns password from basic authentication string.
	 *
	 * @return string
	 */
	function getBasicAuthPassword(){
		return $this->_HTTPRequest_BasicAuth_Password;
	}

	/**
	 * Returns request method.
	 * 
	 * @return string
	 */
	function getRequestMethod(){
		if(isset($this->_ForceRequestMethod)){ return $this->_ForceRequestMethod; }

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

	function setRequestMethod($method){ $this->_ForceRequestMethod = strtoupper($method); }
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
		if(isset($this->_ForceXhr)){ return $this->_ForceXhr; }
		return strtolower($this->getHeader("X-Requested-With"))=="xmlhttprequest";
	}

	function setXhr($value = true){
		if(!isset($value)){ unset($this->_ForceXhr); return; }
		$this->_ForceXhr = $value;
	}

	/**
	 * Returns content of ContentType HTTP header.
	 *
	 * @return string    napr. "text/plain"; null when content-type is not set
	 */
	function getContentType(){
		if(isset($GLOBALS["HTTP_SERVER_VARS"]["CONTENT_TYPE"])){
			$_content_type = $GLOBALS["HTTP_SERVER_VARS"]["CONTENT_TYPE"];	
			if(preg_match("/^([^;]+);/",$_content_type,$matches)){
				$_content_type = trim($matches[1]);
			}
			return $_content_type;
		}
		return null;
	}

	/**
	 * Returns content of Accept-Charset HTTP header.
	 *
	 * @return string	charset	specification or null when charset is not set
	 */
	function getContentCharset(){
		if(isset($GLOBALS["HTTP_SERVER_VARS"]["CONTENT_TYPE"])){
			$_content_type = $GLOBALS["HTTP_SERVER_VARS"]["CONTENT_TYPE"];	
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
	function getUserAgent(){ return $GLOBALS["_SERVER"]["HTTP_USER_AGENT"]; }

	/**
	 * Return raw post data.
	 *
	 *
	 * @return string null when no raw data exist
	 */
	function getRawPostData(){
		if(isset($GLOBALS["HTTP_RAW_POST_DATA"])){
			return $GLOBALS["HTTP_RAW_POST_DATA"];
		}
		return null;
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
		//TODO: https by se melo spis zjistovat: $GLOBALS["_SERVER"]["HTTPS"]=="on" || isset($GLOBALS["_SERVER"]["SSL_PROTOCOL"])
		if(!isset($this->_HTTPRequest_serverPort)){
			return false;
		}
		if(in_array($this->_HTTPRequest_serverPort,$this->_SSLPorts)){
			return true;
		}
		return false;
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
	 * @return bool
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
	function isPostVarDefined($var_name){ return $this->IsVarDefined($var_name,"P"); }

	/**
	 * Checks if a GET parameter is defined.
	 *
	 * @param string $var_name name of GET parameter
	 * @return bool
	 */
	function isGetVarDefined($var_name){ return $this->IsVarDefined($var_name,"G"); }

	/**
	 * Checks if a cookie variable is defined.
	 *
	 * @param string $var_name name of cookie variable
	 * @return bool
	 */
	function isCookieVarDefined($var_name){ return $this->IsVarDefined($var_name,"C"); }

	/**
	 * Returns value of GET parameter.
	 *
	 * @param string $var_name
	 * @return string
	 */
	function getGetVar($var_name){
		settype($var_name,"string");
		$out = null;
		if(isset($GLOBALS["_GET"][$var_name])){
			$out = $GLOBALS["_GET"][$var_name];
		}
		return $out;
	}

	/**
	 * Returns all parameters sent in query string.
	 *
	 * @return array
	 */
	function getAllGetVars(){
		if(isset($this->_ForceGetVars)){ return $this->_ForceGetVars; }
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

	function setGetVars($vars){ $this->_ForceGetVars = $vars; }


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
		if(isset($this->_ForcePostVars)){ return $this->_ForcePostVars; }
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

	function setPostVars($vars){ $this->_ForcePostVars = $vars; }

	/**
	 * Returns value of a cookie $var_name.
	 *
	 * @param string $var_name
	 * @return string value of cookie $var_name
	 */
	function getCookieVar($var_name){
		settype($var_name,"string");
		$out = null;
		if(isset($GLOBALS["_COOKIE"][$var_name])){
			$out = $GLOBALS["_COOKIE"][$var_name];
		}
		return $out;
	}

	/**
	 * Returns value of a cookie $var_name.
	 *
	 * Alias to {@link getCookieVar()} method.
	 *
	 * @param string $var_name
	 * @return string value of cookie $var_name
	 */
	function getCookie($name){ return $this->getCookieVar($name); }

	/**
	 * Returns all variables in cookies.
	 *
	 * @return array
	 */
	function getCookieVars(){ return $GLOBALS["_COOKIE"]; }

	/**
	 * Returns all variables in cookies.
	 *
	 * Alias to {@link getCookieVars()} method.
	 *
	 * @return array
	 */
	function getCookies(){ return $this->getCookieVars(); }

	/**
	 * Returns all variables of specified type from request.
	 *
	 *
	 * You specify type of variables in $order param. 
	 * It also specifies order in which are the types searched.
	 * <ul>
	 * 	<li><b>G</b> - GET parameters</li>
	 * 	<li><b>P</b> - POST parameters</li>
	 * 	<li><b>C</b> - Cookies</li>
	 * </ul>
	 *
	 * If there is parameter specified in more than one types then value from the later specified type overrides the first one.
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
	 * <code>
	 * $file = $request->getUploadedFile("userfile");
	 * </code>
	 *
	 *
	 * When no $name is passed it returns first uploaded file:
	 * <code>
	 * $file = $request->getUploadedFile();
	 * </code>
	 *
	 *
	 * You can perform various operations on the returned object
	 * <code>
	 * echo "filename: ".$file->getFileName()."\n";
	 * echo "size: ".$file->getFileSize()."\n";
	 * echo $file->getContent();
	 * $file->moveTo("data/store/path/");
	 * $file->moveTo("data/store/path/data.txt");
	 * </code>
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
		reset($files);
		while(list(,$file) = each($files)){
			if(!isset($name) || $file->getName()==$name){
				$out = $file;
				break;
			}
		}

		if(!$out){ $out = HTTPXFile::GetInstance(array("name" => $name)); }

		return $out;
	}

	/**
	* if($request->getUploadedFileError("userfile")>0){
	*		echo "doslo k chybe pri nahravani souboru na server";
	*		// pois chyb zde: http://cz.php.net/manual/en/features.file-upload.errors.php
	*	}
	*/
	function getUploadedFileError($name){
		global $_FILES;
		if(isset($_FILES["$name"])){ return $_FILES["$name"]["error"]; }
		return 0;
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
		reset($headers);
		while(list($key,$value) = each($headers)){
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
				reset($static_ar);
				while(list($key,$value) = each($static_ar)){
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
		return $this->_HTTPRequest_headers;
	}

	/**
	 * Returns a  particular request header.
	 *
	 * Method is case insensitive.
	 *
	 *
	 * These calls return same value:
	 * <code>
	 * $val = $request->getHeader("X-File-Name");
	 * $val = $request->getHeader("x-file-name");
	 * </code>
	 *
	 * @return string content of the header.
	 */
	function getHeader($header){
		$header = strtoupper($header);
		foreach($this->getHeaders() as $k => $v){
			if(strtoupper($k)==$header){ return $v; }
		}
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

		// check if the user agent value claims to be windows but not windows mobile
		if(isset($_SERVER['HTTP_USER_AGENT']) && stristr($_SERVER['HTTP_USER_AGENT'],'windows') && !stristr($_SERVER['HTTP_USER_AGENT'],'windows ce')){
			return false;
		}
		// check if the user agent gives away any tell tale signs it's a mobile browser
		if(isset($_SERVER['HTTP_USER_AGENT']) && eregi('up.browser|up.link|windows ce|iemobile|mini|mmp|symbian|midp|wap|phone|pocket|mobile|pda|psp',$_SERVER['HTTP_USER_AGENT'])){
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
		if(isset($_SERVER['HTTP_USER_AGENT']) && isset($a[substr($_SERVER['HTTP_USER_AGENT'],0,4)])){
			return true;
		}	
	}

	/**
	 * Checks if the request comes from IPhone or IPad device
	 *
	 * @return bool
	 */
	function iphone(){
		return preg_match('/iphone/',$this->getUserAgent()) || preg_match('/ipod/',$this->getUserAgent());
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

}
