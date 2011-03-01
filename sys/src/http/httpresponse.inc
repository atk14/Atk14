<?php
/**
 * @package Atk14
 * @subpackage InternalLibraries
 * @filesource
 */

/**
 * HTTPResponse class provides methods to control HTTP response.
 *
 * Instance of the class is available in any {@link Atk14Controller} descendant as $response variable.
 *
 * @package Atk14
 * @subpackage InternalLibraries
 */
class HTTPResponse{

	/**
	 * @access private
	 * @var integer
	 */
	var $_StatusCode = 200;

	/**
	 * @access private
	 * @var string
	 */
	var $_Location = null;

	/**
	 * @access private
	 */
	var $_LocationMovedPermanently = false;

	/**
	 * @access private
	 */
	var $_LocationMovedWithStatus = null;

	/**
	 * @access private
	 * @var string
	 */
	var $_ContentType = "text/html";

	/**
	 * @access private
	 */
	var $_ContentCharset = null;

	/**
	 * @access private
	 */
	var $_Headers = array();
	
	/**
	 * @access private
	 */
	var $_StatusCode_Redefined = false;

	/**
	 * @access private
	 */
	var $_Location_Redefined = false;
	
	/**
	 * @access private
	 */
	var $_ContentType_Redefined = false;
	
	/**
	 * @access private
	 */
	var $_ContentCharset_Redefined = false;

	/**
	 * @access private
	 */
	var $_HTTPCookies = array();

	/**
	 * @access private
	 * @var StringBuffer
	 */
	var $_OutputBuffer = null;

	/**
	 * @access private
	 */
	var $_OutputBuffer_Flush_Started = false;

	/**
	 * @var StringBuffer
	 */
	var $buffer = null;
	
	function HTTPResponse(){
		$this->_OutputBuffer = new StringBuffer();
		$this->buffer = &$this->_OutputBuffer;
	}

	/**
	 * Gets status code.
	 *
	 * @return integer
	 */
	function getStatusCode(){ return $this->_StatusCode; }

	/**
	 * Sets status code of the response.
	 *
	 * @param integer $code
	 */
	function setStatusCode($code){
		settype($code,"integer");
		$this->_StatusCode_Redefined = true;
		$this->_StatusCode = $code;
	}


	/**
	 * Gets status message.
	 *
	 * Gets status message as stated in RFC 2616.
	 * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html
	 *
	 * @return string
	 */
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
	
	/**
	 * Gets location.
	 *
	 * Returns value that will be set in Location HTTP header.
	 *
	 * @return string
	 */
	function getLocation(){ return $this->_Location;}

	/**
	 * Forces client to redirection.
	 *
	 * Forces client to redirection by setting Location HTTP header.
	 * Option <b>moved_permanently</b> sets HTTP status code to 301.
	 *
	 * Redirects client with status code '302 Found':
	 * <code>
	 *	$response->setLocation("/?redirected=1");
	 * </code>
	 *
	 * Redirects client with status code '301 Moved Permanently':
	 * </code>
	 *	$response->setLocation("/?redirected=1",array("moved_permanently" => true));
	 * </code>
	 *
	 * @param string $url
	 * @param array $options
	 * <ul>
	 * 	<li><b>status</b> - explicitly set status code</li>
	 * 	<li><b>moved_permanently</b> - </li>
	 * </ul>
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
	 * Checks if the response redirects or not.
	 *
	 * @return bool
	 */
	function redirected(){ return strlen($this->getLocation())>0; }

	/**
	 * Sets Content-Type response header.
	 *
	 * @param string $content_type
	 */
	function setContentType($content_type){
		settype($content_type,"string");
		$this->_ContentType_Redefined = true;
		$this->_ContentType = $content_type;
	}

	/**
	 * Gets content of ContentType response header.
	 *
	 * @return string
	 */
	function getContentType(){ return $this->_ContentType;}

	function setContentCharset($content_charset){
		settype($content_charset,"string");
		$this->_ContentCharset_Redefined = true;
		$this->_ContentCharset = $content_charset;
	}

	/**
	 * Gets content of charset part of ContentType response header.
	 *
	 * @return string
	 */
	function getContentCharset(){ return $this->_ContentCharset;}

	/**
	 * Gets content of Content-Length response Header.
	 *
	 * @return integer
	 */
	function getContentLength(){
		return $this->_OutputBuffer->getLength();
	}

	/**
	 * Gets all response headers.
	 *
	 * @return array
	 */
	function getHeaders(){ return $this->_Headers; }

	/**
	 * Sets response HTTP header.
	 *
	 * Sets content of HTTP response header to a value. Parameters should be passed as strings but values passed as other types are retyped to string.
	 *
	 * @param string $name
	 * @param string $value
	 */
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

	/**
	 * Writes 'Internal server error' message to output buffer.
	 *
	 * @param string $message own message to send to output. Can be omited and default message will be used
	 * @uses _writeStatusMessage()
	 */
	function internalServerError($message = null){
		$this->setStatusCode(500);
		$this->clearOutputBuffer();
		if(!isset($message)){
			$message = "Internal server error.";
		}
		$this->_writeStatusMessage($message);
	}

	/**
	 * Writes 'URL not found' message to output buffer.
	 *
	 * @param string $message own message to send to output. Can be omited and default message will be used
	 * @uses _writeStatusMessage()
	 */
	function notFound($message = null){
		$this->setStatusCode(404);
		$this->clearOutputBuffer();
		if(!isset($message)){
			$message = "The requested URL ".htmlspecialchars($GLOBALS["HTTP_REQUEST"]->getRequestURI())." was not found on this server.";
		}
		$this->_writeStatusMessage($message);
	}

	/**
	 * Writes status message to output buffer.
	 *
	 * The status message written to buffer is wrapped in HTML code.
	 *
	 * @param string $message a string that is added to the standard message belonging to current status code.
	 * @param string $title optional title. when not passed the status message is used.
	 * @access private
	 */
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

	/**
	 * Returns output buffer.
	 *
	 * @return StringBuffer
	 */
	function &getOutputBuffer(){ return $this->_OutputBuffer; }

	/**
	 * Adds a cookie.
	 *
	 * Adds a cookie to the internal cookies array. They are sent to the output by {@link flush()} or {@link flushAll()} methods.
	 *
	 * @param HTTPCookie $http_cookie
	 */
	function addCookie($http_cookie){
		if(!isset($http_cookie)){ return; }

		$this->_HTTPCookies[] = $http_cookie;
	}

	/**
	 * Writes a string to output buffer.
	 *
	 * @param string $string_to_write
	 */
	function write($string_to_write){
		settype($string_to_write,"string");
		if(strlen($string_to_write)>0){
			$this->_OutputBuffer->AddString($string_to_write);
		}
	}

	/**
	 * Writes a string to output buffer and appends a new line .
	 *
	 * @param string $string_to_write
	 */
	function writeln($string_to_write = ""){
		settype($string_to_write,"string");
		$this->_OutputBuffer->AddString($string_to_write."\n");
	}

	/**
	 * Clears output buffer.
	 */
	function clearOutputBuffer(){
		$this->_OutputBuffer->Clear();
	}

	/**
	 * Sends content to output.
	 *
	 * Intended for sequential output of content.
	 * On first call method outputs HTTP headers and content of buffer. The headers are not output on second call.
	 *
	 * Output buffer is cleared after each call.
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
	 * Sends everything to output.
	 *
	 * Outputs all content of output buffer with headers. This method is typically used at the end of a script.
	 * In contrary to flush() method it also outputs the Content-Length HTTP header if it is possible.
	 *
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
	 * Outputs HTTP headers.
	 *
	 * Only HTTP headers (with possible cookies).
	 * Should be used only in special cases. Commonly used method is {@link HTTPResponse::flush()} or {@link HTTPResponse::flushAll()}
	 *
	 * @access public
	 */
	function printHeaders(){
		$this->_flushHeaders();
	}

	/**
	 * Outputs HTTP headers.
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
	 * Concatenates another HTTPResponse object.
	 *
	 * @todo complete headers, cookies
	 * @access public
	 *
	 * @param HTTPResponse $http_response
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
