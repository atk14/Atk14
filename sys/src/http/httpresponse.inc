<?
/**
* 
* $response->buffer->Replace("toto nahradit","timto");
*/
class HTTPResponse{
	var $_StatusCode = 200;

	var $_Location = null;
	var $_LocationMovedPermanently = false;
	var $_LocationMovedWithStatus = null;

	var $_ContentType = "text/html";
	var $_ContentCharset = null;
	var $_Headers = array();
	
	var $_StatusCode_Redefined = false;
	var $_Location_Redefined = false;
	var $_ContentType_Redefined = false;
	var $_ContentCharset_Redefined = false;

	var $_HTTPCookies = array();

	var $_OutputBuffer = null;
	var $_OutputBuffer_Flush_Started = false;
	
	/**
	* Konstruktor.
	*
	*/
	function HTTPResponse(){
		$this->_OutputBuffer = new StringBuffer();
		$this->buffer = &$this->_OutputBuffer;
	}

	function getStatusCode(){ return $this->_StatusCode; }
	function setStatusCode($code){
		settype($code,"integer");
		$this->_StatusCode_Redefined = true;
		$this->_StatusCode = $code;
	}


	function getStatusMessage(){
		$_status_message = "Unknown";
		//cerpano z
		//http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html
		switch($this->_StatusCode){
			// Successful 2xx
			case 200: $_status_message = "OK"; break;
			case 201: $_status_message = "Created"; break;
			case 202: $_status_message = "Accepted"; break;
			case 203: $_status_message = "Non-Authoritative Information"; break;
			case 204: $_status_message = "No Content"; break;
			case 205: $_status_message = "Reset Content"; break;
			case 206: $_status_message = "Partial Content"; break;
			// Redirection 3xx
			case 300: $_status_message = "Multiple Choices"; break;
			case 301: $_status_message = "Moved Permanently"; break;
			case 302: $_status_message = "Found"; break;
			case 303: $_status_message = "See Other"; break;
			case 304: $_status_message = "Not Modified"; break;
			case 305: $_status_message = "Use Proxy"; break;
			// (306 Unused)
			case 307: $_status_message = "Temporary Redirect"; break;
			// Client Error 4xx
			case 400: $_status_message = "Bad Request"; break;
			case 401: $_status_message = "Unauthorized"; break;
			case 402: $_status_message = "Payment Required"; break;
			case 403: $_status_message = "Forbidden"; break;
			case 404: $_status_message = "Not Found"; break;
			case 405: $_status_message = "Method Not Allowed"; break;
			case 406: $_status_message = "Not Acceptable"; break;
			case 407: $_status_message = "Proxy Authentication Required"; break;
			case 408: $_status_message = "Request Timeout"; break;
			case 409: $_status_message = "Conflict"; break;
			case 410: $_status_message = "Gone"; break;
			case 411: $_status_message = "Length Required"; break;
			case 412: $_status_message = "Precondition Failed"; break;
			case 413: $_status_message = "Request Entity Too Large"; break;
			case 414: $_status_message = "Request-URI Too Long"; break;
			case 415: $_status_message = "Unsupported Media Type"; break;
			case 416: $_status_message = "Requested Range Not Satisfiable"; break;
			case 417: $_status_message = "Expectation Failed"; break;
			// Server Error 5xx
			case 500: $_status_message = "Internal Server Error"; break;
			case 501: $_status_message = "Not Implemented"; break;
			case 502: $_status_message = "Bad Gateway"; break;
			case 503: $_status_message = "Service Unavailable"; break;
			case 504: $_status_message = "Gateway Timeout"; break;
			case 505: $_status_message = "HTTP Version Not Supported"; break;
			case 506: $_status_message = "Variant Also Negotiates"; break;
			case 507: $_status_message = "Insufficient Storage"; break;
		}
		return $_status_message;
	}
	
	function getLocation(){ return $this->_Location;}

	/**
	*
	*	  // vyvola presmerovani 302 Found
	*		$response->setLocation("/?redirected=1");
	*
	*		// vyvola presmerovani 301 Moved Permanently
	*		$response->setLocation("/?redirected=1",array("moved_permanently" => true));
	*/
	function setLocation($url,$options = array()){
		$options = array_merge(array(
			"moved_permanently" => false,
			"status" => null,
		),$options);
		settype($options["moved_permanently"],"boolean");
		isset($options["status"]) && settype($options["status"],"integer");

		$this->_Location_Redefined = true;
		if(!isset($url)){
			$this->setStatusCode(200);
			// falling back to defaults
			$this->_LocationMovedWithStatus = null;
			$this->_LocationMovedPermanently = false;
			$this->_Location = null;
			return;
		}

		settype($url,"string");
		$this->_Location = $url;
		$this->_LocationMovedPermanently = $options["moved_permanently"];
		$this->_LocationMovedWithStatus = $options["status"];
		$this->setStatusCode(isset($options["status"]) ? $options["status"] : ($options["moved_permanently"] ? 301 : 302));
		return;
	}
	/**
	* Whether response redirects or not.
	*/
	function redirected(){ return strlen($this->getLocation())>0; }

	function setContentType($content_type){
		settype($content_type,"string");
		$this->_ContentType_Redefined = true;
		$this->_ContentType = $content_type;
	}
	function getContentType(){ return $this->_ContentType;}

	function setContentCharset($content_charset){
		settype($content_charset,"string");
		$this->_ContentCharset_Redefined = true;
		$this->_ContentCharset = $content_charset;
	}
	function getContentCharset(){ return $this->_ContentCharset;}

	function getContentLength(){
		return $this->_OutputBuffer->getLength();
	}

	function getHeaders(){ return $this->_Headers; }
	function setHeader($name,$value){
		settype($name,"string");
		settype($value,"string");

		// pokud uz tato header existuje, smazeme ji
		reset($this->_Headers);
		while(list($_key,) = each($this->_Headers)){
			if(strtoupper($_key)== strtoupper($name)){
				unset($this->_Headers[$_key]);
				break;
			}
		}

		if(strlen($value)==""){
			return;
		}

		$this->_Headers[$name] = $value;
	}

	function authorizationRequired($realm = "private area"){
		$this->setStatusCode(401);
		$this->clearOutputBuffer();
		$this->setHeader("www-authenticate","basic realm=\"$realm\"");
		$this->_writeStatusMessage("
			This server could not verify that you
			are authorized to access the document
			requested.  Either you supplied the wrong
			credentials (e.g., bad password), or your
			browser doesn't understand how to supply
			the credentials required.","Authorization Required
		");
	}

	function forbidden($message = null){
		$this->setStatusCode(403);
		$this->clearOutputBuffer();
		if(!isset($message)){
			$message = "
				You don't have permission to access ".htmlspecialchars($GLOBALS["HTTP_REQUEST"]->getRequestURI())."
				on this server.
			";
		}
		$this->_writeStatusMessage($message);
	}

	function internalServerError($message = null){
		$this->setStatusCode(500);
		$this->clearOutputBuffer();
		if(!isset($message)){
			$message = "Internal server error.";
		}
		$this->_writeStatusMessage($message);
	}

	function notFound($message = null){
		$this->setStatusCode(404);
		$this->clearOutputBuffer();
		if(!isset($message)){
			$message = "The requested URL ".htmlspecialchars($GLOBALS["HTTP_REQUEST"]->getRequestURI())." was not found on this server.";
		}
		$this->_writeStatusMessage($message);
	}

	function _writeStatusMessage($message,$title = ""){

		if($title==""){ $title = $this->getStatusMessage(); }
		
		$this->Write("<!DOCTYPE HTML PUBLIC \"-//IETF//DTD HTML 2.0//EN\">
			<html><head>
			<title>".$this->getStatusCode()." $title</title>
			</head><body>
			<h1>$title</h1>
			<p>$message</p>
			</body></html>
		");
	}

	function &getOutputBuffer(){ return $this->_OutputBuffer; }

	/**
	*  
	* @access public
	* @param HTTPCookie $http_cookie
	* @return null
	*/
	function addCookie($http_cookie){
		if(!isset($http_cookie)){ return; }

		$this->_HTTPCookies[] = $http_cookie;
	}

	function write($string_to_write){
		settype($string_to_write,"string");
		if(strlen($string_to_write)>0){
			$this->_OutputBuffer->AddString($string_to_write);
		}
	}

	function writeln($string_to_write){
		settype($string_to_write,"string");
		$this->_OutputBuffer->AddString($string_to_write."\n");
	}

	function clearOutputBuffer(){
		$this->_OutputBuffer->Clear();
	}

	/**
	* Realizuje tisk na vystup - urceno pro postupny tisk vystupu.
	*
	* Pri prvnim volani vytiskne HTTP hlavicky a obsah vystupniho bufferu.
	* Pri dalsim volani flush() uz nebudou vytisteny hlavicky.
	* Po zavolani je vzdy vystupni buffer vyprazdnen.
	* 
	* @access public
	*/
	function flush(){
		if(!$this->_OutputBuffer_Flush_Started){
			$this->_flushHeaders();
		}

		if($this->getContentLength()>0){
			$this->_OutputBuffer_Flush_Started = true;
			$this->_OutputBuffer->PrintOut();
			$this->_OutputBuffer->Clear();
		}
	}
	
	/**
	* Realizuje tisk na vystup - urceno pro tisk vseho najednou (typicky na konci skriptu).
	*
	* Na rozdil od flush() vytiskne i hlavicku Content-Length,
	* pokud to bude mozne.
	*
	* @access public
	*/
	function flushAll(){
		if(!$this->_OutputBuffer_Flush_Started){
			$this->_flushHeaders();
			Header("Content-Length: ".$this->getContentLength());
		}

		if($this->getContentLength()>0){
			$this->_OutputBuffer_Flush_Started = true;
			$this->_OutputBuffer->PrintOut();
			$this->_OutputBuffer->Clear();
		}
	}

	/**
	* Vytiskne pouze HTTP hlavicky (vcetne pripadnych cookies).
	* Pouzivat jen ve spec. pripadech. Pro vsechny bezne pripady staci pouzivat metody HTTPResponse::flush() nebo HTTPResponse::flushAll().
	*
	* @access public
	*/
	function printHeaders(){
		$this->_flushHeaders();
	}

	/**
	* Vytiskne HTTP hlavicky.
	*
	* @access private
	*/
	function _flushHeaders(){
		$_status_message = $this->getStatusMessage();
		header("HTTP/1.0 $this->_StatusCode $_status_message");
		
		$_content_type_header = "Content-Type: $this->_ContentType";
		if(isset($this->_ContentCharset)){ $_content_type_header .= "; charset=$this->_ContentCharset";}
		header($_content_type_header);

		if(strlen($this->_Location)>0){
			header("Location: $this->_Location");
		}

		$headers = $this->getHeaders();
		reset($headers);
		while(list($_key,$_value) = each($headers)){
			header("$_key: $_value");
		}

		for($i=0;$i<sizeof($this->_HTTPCookies);$i++){
			$cookie = &$this->_HTTPCookies[$i];
			$_secure = 0;
			if($cookie->isSecure()){ $_secure = 1; }
			setcookie($cookie->getName(),$cookie->getValue(),$cookie->getExpire(),$cookie->getPath(),$cookie->getDomain(),$_secure);
		}
	}

	/**
	* Pripoji jiny objekt tridy HTTPResponse.
	* TODO: dodelat hlavicky, cookies...
	*
	* @access public
	* @param HTTPResponse $http_response
	* @return null
	*/
	function concatenate($http_response){
		$this->_OutputBuffer->AddStringBuffer($http_response->_OutputBuffer);

		//kopirovani presmerovani
		$_location = $http_response->getLocation();
		if(isset($_location) && strlen($_location)>0){
			$this->setLocation($_location,array("moved_permanently" => $http_response->_LocationMovedPermanently, "status" => $this->_LocationMovedWithStatus));
		}

		//HTTP status code
		if($http_response->_StatusCode_Redefined){
			$this->setStatusCode($http_response->getStatusCode());
		}

		//Content-Type
		if($http_response->_ContentType_Redefined){
			$this->setContentType($http_response->getContentType());
		}

		//Charset
		if($http_response->_ContentCharset_Redefined){
			$this->setContentCharset($http_response->getContentCharset());
		}

		//HTTP hlavicky
		$headers = $http_response->getHeaders();
		reset($headers);
		while(list($_key,$_value) = each($headers)){
			$this->setHeader($_key,$_value);
		}
	}
}
?>
