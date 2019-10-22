<?php
/**
 * HTTPResponse class provides methods to control HTTP response.
 *
 * @package Atk14
 * @subpackage Http
 * @filesource
 */

/**
 * HTTPResponse class provides methods to control HTTP response.
 *
 * Instance of the class is available in any {@link Atk14Controller} descendant as $response variable.
 *
 * @package Atk14
 * @subpackage Http
 */
class HTTPResponse{

	/**
	 * @ignore
	 * @access private
	 * @var integer
	 */
	var $_StatusCode = 200;


	/**
	 * @ignore
	 * @access private
	 * @var string
	 */
	var $_StatusMessage = null;

	/**
	 * @ignore
	 * @access private
	 * @var string
	 */
	var $_Location = null;

	/**
	 * @ignore
	 * @access private
	 * @var boolean
	 */
	var $_LocationMovedPermanently = false;

	/**
	 * @access private
	 * @ignore
	 */
	var $_LocationMovedWithStatus = null;

	/**
	 * @ignore
	 * @access private
	 * @var string
	 */
	var $_ContentType = "text/html";

	/**
	 * @access private
	 * @ignore
	 */
	var $_ContentCharset = null;

	/**
	 * @access private
	 * @ignore
	 */
	var $_Headers = array();
	
	/**
	 * @access private
	 * @ignore
	 */
	var $_StatusCode_Redefined = false;

	/**
	 * @access private
	 * @ignore
	 */
	var $_Location_Redefined = false;
	
	/**
	 * @access private
	 * @ignore
	 */
	var $_ContentType_Redefined = false;
	
	/**
	 * @access private
	 * @ignore
	 */
	var $_ContentCharset_Redefined = false;

	/**
	 * @access private
	 * @ignore
	 */
	var $_HTTPCookies = array();

	/**
	 * @access private
	 * @ignore
	 * @var StringBuffer
	 */
	var $_OutputBuffer = null;

	/**
	 * @access private
	 * @ignore
	 */
	var $_OutputBuffer_Flush_Started = false;

	/**
	 * @access private
	 * @ignore
	 * @var StringBuffer
	 */
	var $buffer = null;
	
	/**
	 * Constructor.
	 */
	function __construct(){
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
	 * ```
	 *	 $response->setStatusCode(400);
	 *	 $response->setStatusCode(404,"Not Found Dude");
	 *	 $response->setStatusCode("200 Found");
	 * ```
	 *
	 * @param integer $code
	 * @param string $message string associated with the status code
	 */
	function setStatusCode($code,$message = null){
		if(preg_match('/^([0-9]{3}) (.+)/',$code,$matches)){
			$code = $matches[1];
			$message = $matches[2];
		}

		settype($code,"integer");
		$this->_StatusCode_Redefined = true;
		$this->_StatusCode = $code;
		$this->_StatusMessage = $message;
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
		if($this->_StatusMessage){ return $this->_StatusMessage; }

		//cerpano z
		//http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html
		$status = array(
			// Successful 2xx
			"200" => "OK",
			"201" => "Created",
			"202" => "Accepted",
			"203" => "Non-Authoritative Information",
			"204" => "No Content",
			"205" => "Reset Content",
			"206" => "Partial Content",
			// Redirection 3xx
			"300" => "Multiple Choices",
			"301" => "Moved Permanently",
			"302" => "Found",
			"303" => "See Other",
			"304" => "Not Modified",
			"305" => "Use Proxy",
			// (306 Unused)
			"307" => "Temporary Redirect",
			// Client Error 4xx
			"400" => "Bad Request",
			"401" => "Unauthorized",
			"402" => "Payment Required",
			"403" => "Forbidden",
			"404" => "Not Found",
			"405" => "Method Not Allowed",
			"406" => "Not Acceptable",
			"407" => "Proxy Authentication Required",
			"408" => "Request Timeout",
			"409" => "Conflict",
			"410" => "Gone",
			"411" => "Length Required",
			"412" => "Precondition Failed",
			"413" => "Request Entity Too Large",
			"414" => "Request-URI Too Long",
			"415" => "Unsupported Media Type",
			"416" => "Requested Range Not Satisfiable",
			"417" => "Expectation Failed",
			// Server Error 5xx
			"500" => "Internal Server Error",
			"501" => "Not Implemented",
			"502" => "Bad Gateway",
			"503" => "Service Unavailable",
			"504" => "Gateway Timeout",
			"505" => "HTTP Version Not Supported",
			"506" => "Variant Also Negotiates",
			"507" => "Insufficient Storage",
		);
		return isset($status["$this->_StatusCode"]) ? $status["$this->_StatusCode"] : "Unknown";
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
	 * ```
	 *	$response->setLocation("/?redirected=1");
	 * ```
	 *
	 * Redirects client with status code '301 Moved Permanently':
	 * ```
	 * $response->setLocation("/?redirected=1",array("moved_permanently" => true));
	 * ```
	 *
	 * @param string $url
	 * @param array $options
	 * - **status** - explicitly set status code
	 * - **moved_permanently** - causes status code to be set to 301
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
	 * ```
	 * $response->setContentType("text/plain");
	 * $response->setContentType("text/plain; charset=UTF-8");
	 * ```
	 *
	 * @param string $content_type
	 */
	function setContentType($content_type){
		settype($content_type,"string");
		if(preg_match('/^([^;]+); charset=["\']?([^\s;]+)["\']?$/',$content_type,$matches)){
			$content_type = $matches[1];
			$this->setContentCharset($matches[2]);
		}
		$this->_ContentType_Redefined = true;
		$this->_ContentType = $content_type;
	}

	/**
	 * Gets content of ContentType response header.
	 *
	 * @return string
	 */
	function getContentType(){ return $this->_ContentType;}

	/**
	 * Sets ContentType response header.
	 *
	 * @param string $content_charset
	 */
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
	 * ```
	 * $gmdate = gmdate("D, d M Y H:i:s \G\M\T");
	 * $response->setHeader("Last-Modified",$gmdate");
	 * $response->setHeader("Last-Modified: $gmdate");
	 * ```
	 *
	 * @param string $name
	 * @param string $value
	 */
	function setHeader($name,$value = ""){
		settype($name,"string");
		settype($value,"string");

		if($value=="" && preg_match('/^([a-z0-9-]+):\s?(.+)/i',$name,$matches)){
			$name = $matches[1];
			$value = $matches[2];
		}

		if(strtolower($name)=="content-type"){
			$this->setContentType($value);
			return;
		}

		// pokud uz tato header existuje, smazeme ji
		foreach(array_keys($this->_Headers) as $_key){
			if(strtoupper($_key)== strtoupper($name)){
				unset($this->_Headers[$_key]);
				break;
			}
		}

		if(strlen($value)==0){
			return;
		}

		$this->_Headers[$name] = $value;
	}

	/**
	 * Bulk HTTP header assignment
	 *
	 * ```
	 * $response->setHeader([
	 *   "X-Powered-By" => "ATK14 Framework",
	 *   "X-Content-Type-Options" => "nosniff",
	 * ]);
	 * ```
	 */
	function setHeaders($headers){
		foreach($headers as $name => $value){
			$this->setHeader($name,$value);
		}
	}

	/**
	 * Returns value of the given HTTP header previously set in this response
	 *
	 * Returns null when there is no such header
	 *
	 * ```
	 *	$response->setHeader("Last-Modified: Mon, 26 Aug 2013 09:41:51 GMT");
	 *
	 *	echo $response->getHeader("Last-Modified"); // Mon, 26 Aug 2013 09:41:51 GMT
	 *	echo $response->getHeader("last-modified"); // Mon, 26 Aug 2013 09:41:51 GMT
	 * ```
	 *
	 * @param string $name
	 * @return string|null
	 */
	function getHeader($name){
		$name = strtoupper($name);
		foreach(array_keys($this->_Headers) as $_key){
			if(strtoupper($_key)== strtoupper($name)){
				return $this->_Headers[$_key];
			}
		}
	}

	/**
	 * Clears previously set header.
	 *
	 * Unset header is not sent.
	 *
	 * ```
	 * $response->setHeader("X-Frame-Option","SAMEORIGIN");
	 * $response->header("X-Frame-Option");
	 * ```
	 *
	 * @param string $name
	 * @return undefined
	 */
	function clearHeader($name){
		return $this->setHeader($name,"");
	}

	/**
	 * Forces client to authentize itself.
	 *
	 * Sends client www-authenticate header for basic authentization
	 * so the browser displays dialog asking username and password.
	 *
	 * Check the authentization params with {@link HTTPRequest::getBasicAuthUsername} and {@link HTTPRequest::getBasicAuthPassword()}
	 *
	 * @param string $realm
	 */
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

	/**
	 * Sets the 403 status code.
	 *
	 * The associated message to be displayed by client can be set.
	 *
	 * @param string $message
	 */
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
	 * Renders a 'Bad request' page
	 *
	 * @param string $message Message to be written out
	 */
	function badRequest($message = null){
		$this->setStatusCode(400);
		$this->clearOutputBuffer();
		if(!isset($message)){
			$message = "Your browser sent a request that this server could not understand.";
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
	protected function _writeStatusMessage($message,$title = ""){

		if($title==""){ $title = $this->getStatusMessage(); }
		
		$this->write("<!DOCTYPE HTML PUBLIC \"-//IETF//DTD HTML 2.0//EN\">
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
	 * ```
	 * $response->addCookie(new HTTPCookie("last_login","bob",array("secure" => true)));
	 * ```
	 * or
	 * ```
	 * $response->addCookie("last_login","bob",array("secure" => true)));
	 * ```
	 *
	 * @param mixed $cookie_or_name
	 * @param string $value
	 * @param array $options
	 */
	function addCookie($cookie_or_name,$value = null,$options = array()){
		if(is_a($cookie_or_name,"HTTPCookie")){
			$cookie = $cookie_or_name;
		}else{
			$cookie = new HTTPCookie((string)$cookie_or_name,(string)$value,$options);
		}

		if(!isset($cookie)){ return; }

		$this->_HTTPCookies[] = $cookie;
	}

	/**
	 * Alias for addCookie()
	 *
	 * @param mixed $cookie_or_name
	 * @param string $value
	 * @param array $options
	 */
	function setCookie($cookie_or_name,$value = null,$options = array()){
		$this->addCookie($cookie_or_name,$value,$options);
	}

	/**
	 * Returns list of cookies
	 *
	 * @return HTTPCookie[]
	 */
	function getCookies(){
		return $this->_HTTPCookies;
	}

	/**
	 * Cleares all previously set cookies.
	 */
	function clearCookies(){
		$this->_HTTPCookies = array();
	}

	/**
	 * Writes a string to output buffer.
	 *
	 * @param string $string_to_write
	 */
	function write($string_to_write){
		settype($string_to_write,"string");
		if(strlen($string_to_write)>0){
			$this->_OutputBuffer->addString($string_to_write);
		}
	}

	/**
	 * Writes a string to output buffer and appends a new line .
	 *
	 * @param string $string_to_write
	 */
	function writeln($string_to_write = ""){
		settype($string_to_write,"string");
		$this->_OutputBuffer->addString($string_to_write."\n");
	}

	/**
	 * Clears output buffer.
	 */
	function clearOutputBuffer(){
		$this->_OutputBuffer->clear();
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
			$this->_OutputBuffer->printOut();
			$this->_OutputBuffer->clear();
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
			$this->_OutputBuffer->printOut();
			$this->_OutputBuffer->clear();
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
	protected function _flushHeaders(){
		$_status_message = $this->getStatusMessage();
		header("HTTP/1.0 $this->_StatusCode $_status_message");
		
		$_content_type_header = "Content-Type: $this->_ContentType";
		if($this->_ContentCharset){ $_content_type_header .= "; charset=$this->_ContentCharset";}
		header($_content_type_header);

		if(strlen($this->_Location)>0){
			header("Location: $this->_Location");
		}

		$headers = $this->getHeaders();
		foreach($headers as $_key => $_value){
			header("$_key: $_value");
		}

		foreach($this->getCookies() as $cookie){
			setcookie($cookie->getName(),$cookie->getValue(),$cookie->getExpire(),$cookie->getPath(),$cookie->getDomain(),$cookie->isSecure(),$cookie->isHttponly());
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
		// Output buffer
		$this->_OutputBuffer->addStringBuffer($http_response->_OutputBuffer);
		if($http_response->_OutputBuffer_Flush_Started){
			$this->_OutputBuffer_Flush_Started = true;
		}

		// Redirection
		$_location = $http_response->getLocation();
		if(isset($_location) && strlen($_location)>0){
			$this->setLocation($_location,array("moved_permanently" => $http_response->_LocationMovedPermanently, "status" => $this->_LocationMovedWithStatus));
		}

		// HTTP status code
		if($http_response->_StatusCode_Redefined){
			$this->setStatusCode($http_response->getStatusCode(),$http_response->_StatusMessage);
		}

		// Content-Type
		if($http_response->_ContentType_Redefined){
			$this->setContentType($http_response->getContentType());
		}

		// Charset
		if($http_response->_ContentCharset_Redefined){
			$this->setContentCharset($http_response->getContentCharset());
		}

		// HTTP headers
		foreach($http_response->getHeaders() as $_key => $_value){
			$this->setHeader($_key,$_value);
		}

		// Cookies
		foreach($http_response->getCookies() as $c){
			$this->addCookie($c);
		}
	}
}
