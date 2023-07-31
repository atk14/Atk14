<?php
defined("URL_FETCHER_VERIFY_PEER") || define("URL_FETCHER_VERIFY_PEER",true);

/**
 * Class providing methods to make http requests
 *
 * @package Atk14\UrlFetcher
 * @filesource
 */

/**
 * UrlFetcher class provides methods to make http requests
 *
 * ## Basic usage
 * ```
 * $fetcher = new UrlFetcher();
 * $fetcher->setAuthorization("username","password");
 * //$fetcher->resetAuthorization();
 * if($fetcher->fetchContent("http://www.root.cz/")){
 * 	echo $fetcher->getContent();
 * }else{
 * 	echo $fetcher->getErrorMessage();
 * }
 * ```
 *
 * ## Make a GET request
 * ```
 * $fetcher = new UrlFetcher("http://username:password@www.root.cz/");
 * if($f->found()){
 * 	echo $f->getContent();
 * }
 * ```
 *
 * ## Make a POST request
 * ```
 * $f = new UrlFetcher("http://www.root.cz/login/");
 * if($f->post("username=madl&password=krtek")){
 * 	echo $f->getContent();
 * }
 * ```
 *
 * ## HTTP headers
 *
 * Some headers can be specified in options:
 * ```
 * $f = new UrlFetcher("http://www.example.com/data_collector.php");
 * $f->post($xml,array("content_type" => "text/xml"));
 * ```
 *
 * Specific headers can be added in additional_headers option
 * ```
 * $f = new UrlFetcher("http://www.example.com/", array("additional_headers" => array("X-App-Version: 1.2")));
 * echo $f->getContent();
 * ```
 */
class UrlFetcher {

	const VERSION = "1.8.1";

	/**
	 * Authentication type
	 *
	 * "" or "basic"
	 *
	 * @var string
	 */
	protected $_AuthType = "";

	/**
	 * Authentication username
	 *
	 * @var string
	 */
	protected $_Username = "";

	/**
	 * Authentication password
	 *
	 * @var string
	 */
	protected $_Password = "";

	/**
	 * Connection timeout in seconds
	 *
	 * @var integer
	 */
	protected $_SocketTimeout = 5;

	/**
	 * Redirections counter
	 *
	 * @var integer
	 */
	protected $_CountOfRedirection = 0;

	/**
	 * Maximum number of redirections to follow
	 *
	 * @var integer
	 */
	protected $_MaxRedirections = 5;

	/**
	 * Headers to be sent with each request made by this instance of UrlFetcher
	 *
	 * @var array
	 */
	protected $_ConstructorAdditionalHeaders = array(); // these headers never disappear

	/**
	 * HTTP headers sent with a single request.
	 *
	 * Can be added during a POST request. They are reset after the request is done.
	 * @var array
	 * @todo more explanation and tests
	 */
	protected $_AdditionalHeaders = array();

	/**
	 * Text to send in User-Agent http header
	 *
	 * @var string
	 */
	protected $_UserAgent = "UrlFetcher";

	/**
	 *
	 * @var boolean
	 */
	protected $_VerifyPeer;

	protected $_Proxy = "";

	protected $_ForceContentLength = null;

	/**
	 * @ignore
	 */
	function _reset(){
		$this->_Fetched = null;
		$this->_RequestMethod = "GET";
		$this->_PostData = new StringBuffer();
		$this->_AdditionalHeaders = array();
		$this->_Url = "";
		$this->_Ssl = false;
		$this->_Port = 80;
		$this->_Server = "";
		$this->_Uri = "";
		$this->_ErrorMessage = "";

		$this->_RequestHeaders = "";
		$this->_ResponseHeaders = "";

		$this->_ForceContentLength = null;

		$this->_Content = null; // StringBufferTemporary
	}

	/**
	 * Constructor
	 *
	 * ```
	 * $f = new UrlFetcher();
	 * $f = new UrlFetcher("http://www.example.com/");
	 * $f = new UrlFetcher("http://www.example.com/",array("additional_headers" => array("X-Powered-By: Grizzly Lib 1.2")));
	 * $f = new UrlFetcher(array("additional_headers" => array("X-Powered-By: Grizzly Lib 1.2")));
	 * ```
	 *
	 * @param string $url URL to fetch content from
	 * @param array $options
	 * - **additional_headers** -
	 * - **max_redirections** [default: 5]
	 * - **user_agent** - content of User-Agent http header [default: 'UrlFetcher 1.0']
	 */
	function __construct($url = "", $options = array()){
		$this->_reset();

		if(is_array($url)){
			$options = $url;
			$url = "";
		}

		$options += array(
			"additional_headers" => array(),
			"max_redirections" => $this->_MaxRedirections,
			"user_agent" => "UrlFetcher/".self::VERSION,
			"verify_peer" => URL_FETCHER_VERIFY_PEER,
			"proxy" => "", // e.g. "tcp://127.0.0.1:8118"
		);

		if(strlen($url)>0){
			$this->_setUrl($url);
		}

		$this->_ConstructorAdditionalHeaders = $options["additional_headers"];
		$this->_MaxRedirections = $options["max_redirections"];
		$this->_UserAgent = $options["user_agent"];
		$this->_VerifyPeer = $options["verify_peer"];
		$this->_Proxy = $options["proxy"];
	}
	
	/**
	 * Returns URL
	 *
	 * Normallly it returns initial url.
	 * In case the request was redirected it returns the target url.
	 *
	 * Common situation without redirection
	 * ```
	 * $uf = new UrlFetcher("http://example.com/content.html");
	 * echo $uf->getUrl(); // http://example.com/content.html
	 * ```
	 *
	 * Request with redirection
	 * ```
	 * $uf = new UrlFetcher("http://example.com/to_be_redirected.html");
	 * echo $uf->getUrl(); // http://example.com/to_be_redirected.html
	 * $uf->getContent();
	 * echo $uf->getUrl(); // http://example.com/redirected_address.html
	 * ```
	 * @return string
	 */
	function getUrl(){ return $this->_Url; }

	function getUri(){ return $this->_Uri; }

	/**
	 * Returns method of the most recent request
	 *
	 *
	 * ```
	 * echo $uf->getRequestMethod(); // "GET" or "POST"
	 * ```
	 *
	 * @return string
	 */
	function getRequestMethod(){
		return $this->_RequestMethod;
	}

	/**
	 * Checks if error occured on request
	 *
	 * @return bool
	 */
	function errorOccurred(){ return strlen($this->getErrorMessage())>0; }

	/**
	 * Returns error message about last request.
	 *
	 * @return string
	 */
	function getErrorMessage(){
		return $this->_ErrorMessage;
	}

	/**
	 * Set authorization parameters.
	 *
	 * @param string $username
	 * @param string $password
	 */
	function setAuthorization($username,$password){
		settype($username,"string");
		settype($password,"string");

		$this->_Username = $username;
		$this->_Password = $password;
		$this->_AuthType = "basic";
	}

	/**
	 * Set timeout for connection
	 *
	 * @param int $timeout timeout in seconds
	 */
	function setSocketTimeout($timeout){ $this->_SocketTimeout = $timeout; }

	/**
	 * Resets authentization parameters so it is not used in the request
	 */
	function resetAuthorization(){
		$this->_Username = "";
		$this->_Password = "";
		$this->_AuthType = "";
	}

	/**
	 * Fetches content from URL.
	 *
	 * Get content with method {@link getContent()}.
	 * When called multiple times, the actual request is made only once.
	 * Can be called from outside.
	 *
	 * When the request is redirected more times than specified by limit, error is returned.
	 *
	 * Recommended usage:
	 * ```
	 * $f = new UrlFetcher("http://www.domemka.cz/file.dat");
	 * if($f->found()){
	 * 	echo $f->getContent();
	 * }
	 * ```
	 *
	 * @param string $url
	 * @return boolean result of the operation
	 * - true => success
	 * - false => some exception occurred
	 */
	function fetchContent($url = "",$options = array()){
		if(is_array($url)){
			$options = $url;
			$url = "";
		}

		if(strlen($url)>0){ $this->_setUrl($url); }

		$options += array(
			"request_method" => null, // "GET", "POST", "PUT", "DELETE"
		);

		if($options["request_method"]){
			$this->_RequestMethod = $options["request_method"];
		}

		if(isset($this->_Fetched)){ return $this->_Fetched; }

		if($this->errorOccurred()){ $this->_Fetched = false; return false; }
	
		$this->_buildRequestHeaders();

		$ret = $this->_makeWritingAndReading();
		if(!is_array($ret)){ return $ret; }

		$this->_ResponseHeaders = $ret[0]; // string
		$this->_Content = $ret[1]; // StringBufferTemporary

		$this->_Fetched = true;

		// this is a nusty hack
		// sometimes it occurs that the content is longer than Content-Length
		//
		// je to hack pro stahovani souboru: http://do-mobilu.respekt.cz/kestazeni-download.php?f_ID=815
		// tam koumaci prilepili za data velikost souboru - pocitaji natvrdo z HTTP/1.1
		if(($length = $this->getContentLength()) && strlen($this->_Content->getLength())>$length){
			$this->_Content = substr($this->_Content,0,$length);
		}

		// !! redirection
		if(in_array($this->getStatusCode(),array(301,302,303)) && ($location = $this->getHeaderValue("Location"))){
			$this->_CountOfRedirection++;
			if($this->_CountOfRedirection>=$this->_MaxRedirections){
				return $this->_setError("maximum redirections reached: $this->_CountOfRedirection");
			}
			if(preg_match('/^\//',$location)){
				// absolute redirection
				$location = preg_replace('/^(https?:\/\/[^\/]+)\/.*/i',"\\1$location",$this->_Url);
			}elseif(!preg_match('/^https?:\/\//',$location)){
				// relative redirection
				if(preg_match('/\?/',$this->_Url)){
					$location = preg_replace('/(^.+\/)[^\/]*\?.*$/',"\\1$location",$this->_Url);
				}else{
					$location = preg_replace('/(^.+\/)[^\/]*$/',"\\1$location",$this->_Url);
				}
			}else{
				// $location contains full URL address
			}
			$this->_Fetched = null;
			return $this->fetchContent($location);
		}

		if($this->getHeaderValue("content-encoding") === "gzip"){
			$_content = gzdecode((string)$this->_Content);
			if($_content === false){
				return $this->_setError("failed to gzdecode content");
			}
			$this->_ForceContentLength = strlen($_content);
			$this->_Content = new StringBufferTemporary($_content);
		}

		if(!preg_match('/^2/',$this->getStatusCode())){
			return $this->_setError("status code is ".$this->getStatusCode());
		}

		return true;
	}

	/**
	 * Performs a POST request
	 *
	 * @param string|array|StringBuffer $data when array it is sent as query parameters
	 * @param array $options
	 * - content_type string - value for Content-Type HTTP header
	 * - additional_headers array - more headers
	 * @return bool result of request
	 */
	function post($data = "",$options = array()){
		if(is_array($data)){
			$d = array();
			foreach($data as $k => $v){
				$d[] = urlencode($k)."=".urlencode((string) $v);
			}
			$data = join("&",$d);
		}

		if(!is_a($data,"StringBuffer")){
			$data = new StringBuffer($data);
		}

		$options = array_merge(array(
			"content_type" => "application/x-www-form-urlencoded",
			"additional_headers" => array(),
		),$options);

		$this->_RequestMethod = "POST";
		$this->_PostData->addStringBuffer($data);
		$this->_AdditionalHeaders = $options["additional_headers"];
		$this->_AdditionalHeaders[] = "Content-Type: $options[content_type]";

		return $this->fetchContent();
	}

	/**
	 * Performs a PUT request
	 */
	function put($url = "",$options = array()){
		if(is_array($url)){
			$options = $url;
			$url = "";
		}
		$options["request_method"] = "PUT";
		return $this->fetchContent($url,$options);
	}

	/**
	 * Performs a DELETE request
	 */
	function delete($url = "",$options = array()){
		if(is_array($url)){
			$options = $url;
			$url = "";
		}
		$options["request_method"] = "DELETE";
		return $this->fetchContent($url,$options);
	}

	/**
	 * Gets request headers.
	 *
	 * @return string
	 */
	function getRequestHeaders(){ return $this->_RequestHeaders; }

	/**
	 * Gets headers returned by the server.
	 *
	 * @param array $options
	 * - <b>as_hash</b> - returns headers as array when set to true [default: false]
	 * - <b>lowerize_keys</b> - convert header names lowercase when set to true [default: false]
	 * @return string|array
	 */
	function getResponseHeaders($options = array()){
		$options = array_merge(array(
			"as_hash" => false,
			"lowerize_keys" => false
		),$options);

		$this->fetchContent();

		$out = $this->_ResponseHeaders;

		if($options["as_hash"]){
			$headers = explode("\n",$out);
			$out = array();
			foreach($headers as $h){
				if(preg_match("/^([^ ]+):(.*)/",trim($h),$matches)){
					$key = $options["lowerize_keys"] ? strtolower($matches[1]) : $matches[1];
					$out[$key] = trim($matches[2]);
				}
			}
		}

		return $out;
	}

	/**
	 * Alias to method getResponseHeaders()
	 *
	 * @param array $options {@see getResponseHeaders()}
	 * @return string|array
	 */
	function getHeaders(){ return $this->getResponseHeaders($options = array()); }

	/**
	 * Return content of called URL.
	 *
	 * @return string
	 */
	function getContent(){ $this->fetchContent(); return $this->_Content; }

	/**
	 * Returns value of given header
	 *
	 * ```
	 * $c_type = $uf->getHeaderValue("Content-Type"); // "text/xml"
	 * ```
	 *
	 * @param string $header
	 * @return string
	 */
	function getHeaderValue($header){
		$header = strtolower($header);
		$headers = $this->getResponseHeaders(array("as_hash" => true, "lowerize_keys" => true));
		if(isset($headers["$header"])){ return $headers["$header"]; }
	}

	/**
	 * Alias for UrlFetcher::getHeaderValue().
	 *
	 * @param string $name Name of header
	 * @return string
	 */
	function getHeader($name){
		return $this->getHeaderValue($name);
	}

	/**
	 * Returns value of Content-type header.
	 *
	 * @return string
	 */
	function getContentType(){
		$c_type = $this->getHeaderValue("content-type");
		if(is_null($c_type)){ return null; }
		$c_type = trim(preg_replace("/(.*?);.*/","\\1",$c_type));
		return $c_type;
	}

	/**
	 * Returns content charset value.
	 *
	 * Value is parsed from the content-type header.
	 *
	 * @return string
	 */
	function getContentCharset(){
		if(preg_match("/;\\s*charset\\s*=([^;]+)/",$this->getHeaderValue("content-type"),$matches)){
			return trim($matches[1]);
		}
	}

	/**
	 * Returns value of Content-Length header.
	 *
	 * @return string
	 */
	function getContentLength(){
		if(!is_null($this->_ForceContentLength)){
			return (string)$this->_ForceContentLength;
		}
		$length = $this->getHeaderValue("content-length");
		if(strlen((string)$length)){
			return $length;
		}
		if($this->_Content){
			return (string)$this->_Content->getLength();
		}
		return "";
	}

	/**
	 * Returns status code of response
	 *
	 *
	 * ```
	 * echo $uf->getStatusCode(); // 200, 404, 403...
	 * ```
	 *
	 * @return int
	 */
	function getStatusCode(){
		if(preg_match("/^HTTP\\/.\\.. ([0-9]{3})/",$this->getResponseHeaders(),$matches)){
			return (int)$matches[1];
		}
	}

	/**
	 * Returns status message of response
	 *
	 *
	 * ```
	 * echo $uf->getStatusMessage(); // "Found", "Not Found", "Forbidden"...
	 * ```
	 *
	 * @return string
	 */
	function getStatusMessage(){
		if(preg_match("/^HTTP\\/.\\.. [0-9]{3} ([A-Za-z ]{1,})/",$this->getResponseHeaders(),$matches)){
			return $matches[1];
		}
	}

	/**
	 * Tests if the request returned content.
	 *
	 * Checks if the returned status code is 200
	 *
	 * @return bool
	 */
	function found(){ return $this->getStatusCode()==200; }

	/**
	 * Returns filename
	 *
	 * It tries to extract a filename from the Content-Disposition header or from the current URL.
	 *
	 * @return string
	 */
	function getFilename(){
		$filename = "";

		if($content_disposition = $this->getHeaderValue("Content-Disposition")){
			if(preg_match('/filename="?([^";]+)"?/',$content_disposition,$matches)){
				$filename = trim($matches[1]);
			}
		}

		if(!strlen($filename) && preg_match("/([^\\/?]+)(\\?.*|)$/",$this->_Uri,$matches)){
			$filename = $matches[1];
			$filename = urldecode($filename);
		}

		// Sanitization
		$filename = strtr($filename,array(
			"/" => "_",
			"\\" => "_",
		));
		if($filename === "."){ $filename = "_"; }
		if($filename === ".."){ $filename = "__"; }

		return $filename;
	}

	/**
	 * @ignore
	 */
	protected function _setError($error_message){
		$this->_ErrorMessage = $error_message;
		$this->_Fetched = false;
		return false;
	}

	/**
	 * @ignore
	 */
	protected function _setUrl($url){
		settype($url,"string");
	
		$this->_reset();

		if(!preg_match("/^http(s{0,1}):\\/\\/([^\\/]+)(\\/.*)$/",$url,$matches)){
			return $this->_setError("invalid url format");
		}

		$this->_Url = $url;
		$this->_Ssl = strlen($matches[1])>0;
		$_server = $matches[2];
		$_port = null;
		$_username = "";
		$_password = "";
		$this->_Uri = $this->_cleanUpUri($matches[3]);
		unset($matches);

		//rozpoznani cisla TCP portu, defaultne je to 80 resp. 443 na ssl
		if(preg_match("/^(.+):([0-9]{1,})$/",$_server,$matches)){
			$_server = $matches[1];
			$this->_Port = (integer)$matches[2];
		}else{
			$this->_Port = $this->_Ssl ? 443 : 80;
		}
		
		if(preg_match("/^(.+):(.+)@(.+?)$/",$_server,$matches)){
			$_username = $matches[1];
			$_password = $matches[2];
			$_server = $matches[3];
		}
		if(strlen($_username)>0){ $this->setAuthorization($_username,$_password); }
		$this->_Server = $_server;
		
		return true;
	}

	protected function _cleanUpUri($uri){
		if(preg_match('/^(.*?)(\?.*|)$/',$uri,$matches)){
			$file = $matches[1];
			$query_string = $matches[2];
			$file = preg_replace('/(\/\.){1,}\//','/',$file); // "/././file.dat" -> "/file.dat"
			$file = preg_replace('/^(\/+\.\.){1,}\//','/',$file); // "/../about/" -> "/about/"
			while(1){
				$_file = preg_replace('/^(.*?)\/([^\/]+\/+\.\.)\//','\1/',$file); // "/about/../" -> "/"
				if($_file==$file){ break; }
				$file = $_file;
			}
			$file = preg_replace('/\/[^\/]+\/+\.\.$/','/',$file); // "/about/.." => "/"
			$file = preg_replace('/^(\/+)\.\.$/','\1',$file); // "/.." => "/"
			$uri = "$file$query_string";
		}
		return $uri;
	}

	/**
	 * @ignore
	 */
	protected function _buildRequestHeaders(){
		$out = array();
		$out[] = "$this->_RequestMethod $this->_Uri HTTP/1.0";
		$_server = $this->_Server;
		if((!$this->_Ssl && $this->_Port!=80) || ($this->_Ssl && $this->_Port!=443)){ $_server .= ":$this->_Port"; }
		$out[] = "Host: $_server";
		$out[] = "Connection: close";
		$out[] = "User-Agent: $this->_UserAgent";
		if($this->_AuthType=="basic"){
			$out[] = "Authorization: Basic ".base64_encode("$this->_Username:$this->_Password");
		}
		if($this->_RequestMethod=="POST"){
			$out[] = "Content-Length: ".$this->_PostData->getLength();
		}
		foreach($this->_ConstructorAdditionalHeaders as $h){
			$out[] = $h;
		}
		foreach($this->_AdditionalHeaders as $h){
			$out[] = $h;
		}
		$out[] = "";
		$out[] = "";
		$this->_RequestHeaders = join("\r\n",$out);
	}

	protected function _makeWritingAndReading(){
		$errno = null;
		$errstr = "";
		$_proto = "tcp";
		$context_options = array();
		if($this->_Ssl){
			$_proto = "ssl";
			$context_options["ssl"] = array("verify_peer" => $this->_VerifyPeer);
		}

		$content_buffer = new StringBuffer();
		$response_headers = "";
		$response_buffer = new StringBufferTemporary();
		$f = null;

		if(!$this->_Proxy){

			$context = stream_context_create($context_options);
			$f = stream_socket_client("$_proto://$this->_Server:$this->_Port", $errno, $errstr, $this->_SocketTimeout, STREAM_CLIENT_CONNECT, $context);

			if(!$f){
				if(strpos($errstr,"getaddrinfo failed")){
					$errstr = "could not resolve host: $this->_Server ($errstr)";
				}
				return $this->_setError("failed to open socket: $errstr [$errno]");
			}
			stream_set_blocking($f,0);
			$content_buffer->addString($this->_RequestHeaders);

			if($this->_RequestMethod=="POST"){
				$content_buffer->addStringBuffer($this->_PostData);
			}

		}else{

			// Proxy
			$_header = preg_replace('/^.*?\r\n.*?\r\n/s','',$this->_RequestHeaders);
			$context_options["http"] = array(
				"method" => $this->_RequestMethod,
				"timeout" => $this->_SocketTimeout,
				"ignore_errors" => true,
				"proxy" => $this->_Proxy,
				"request_fulluri"=> !$this->_Ssl,
				"header" => $_header,
				"content" => (string)$this->_PostData,
			);
			$context = stream_context_create($context_options);
			$http_response_header = null;
			$f = @fopen("$this->_Url","r",false,$context);
			if(!$f){
				$err_ar = error_get_last();
				$errstr = "could not connect to proxy server $this->_Proxy";
				return $this->_setError("failed to open socket: $errstr ($err_ar[message])");
			}
			if(!is_null($http_response_header)){
				$response_headers = join("\n\r",$http_response_header);
			}

		}

		if($content_buffer->getLength()){
			$stat = $this->_fwriteStream($f,$content_buffer);

			if(!$stat || $stat!=$content_buffer->getLength()){
				fclose($f);
				return $this->_setError(sprintf("cannot write to socket (bytes written: %s, bytes needed to be written: %s)",$stat,$content_buffer->getLength()));
			}
		}

		while(!feof($f) && $f){
			$_b = fread($f,1024 * 256); // 256kB
			if(strlen($_b)==0){
				usleep(20000);
				continue;
			}
			$response_buffer->addString($_b);

			if(!strlen($response_headers) && preg_match("/^(.*?)\\r?\\n\\r?\\n(.*)$/s",$response_buffer->toString(),$matches)){
				$response_headers = $matches[1];
				$_b = $matches[2];
				$response_buffer = new StringBufferTemporary();
				(strlen($_b)>0) && ($response_buffer->addString($_b));
			}
		}
		fclose($f);

		if(!$response_buffer->getLength() && !strlen($response_headers)){ // content ($response_buffer) may be empty
			return $this->_setError("failed to read from socket");
		}

		if(!strlen($response_headers)){
			return $this->_setError("can't find response response_headers");
		}

		return array($response_headers,$response_buffer);
	}

	/**
	 * Writes string to a network socket
	 *
	 * See http://php.net/fwrite
	 * Note: Writing to a network stream may end before the whole string is written. Return value of fwrite() may be checked:
	 *
	 * @ignore
	 */
	protected function _fwriteStream(&$fp, $buffer) {
		$total_length = $buffer->getLength();
		$fwrite = 0;
		$retries = 0;
		$chunk_size = 1024 * 256; // 256kB
		$chunk = null;
		for($bytes_written = 0; $bytes_written < $total_length; $bytes_written += $fwrite){
			$length = min($chunk_size,$total_length - $bytes_written);
			if(is_null($chunk)){
				$chunk = $buffer->substr($bytes_written,$length);
			}

			// $fwrite = @fwrite($fp, $chunk, $length);
			$max_retries = 5;
			while($max_retries>0){
				$fwrite = @fwrite($fp, $chunk, $length);
				if($fwrite !== false){ break; }
				$max_retries--;
				usleep(10000); // 0.01s
			}

			if($fwrite === false){
				return $bytes_written;
			}

			if(!$fwrite){ // 0 bytes bytes_written; error code  11:  Resource temporarily unavailable
				if($retries>=100){ return $bytes_written; }
				usleep(10000 + $retries * 1000); // 0.01s + 0s .. 0.01s + 0.1s
				$retries++;
				continue;
			}else{
				$retries = 0; // something was bytes_written? -> reset $retries
				$chunk = null;
			}
		}
		return $bytes_written;
	}
}
