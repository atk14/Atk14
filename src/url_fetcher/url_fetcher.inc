<?
define("URL_FETCHER_VERSION","0.2");
/**
* $fetcher = new UrlFetcher();
*	$fetcher->setAuthorization("username","password");
*	//$fetcher->ressetAuthorization();
*	if($fetcher->fetchContent("http://www.root.cz/")){
*		echo $fetcher->getContent();
*	}else{
*		echo $fetcher->getErrorMessage();
*	}
*
* // GET
* $fetcher = new UrlFetcher("http://username:password@www.root.cz/");
* if($f->found()){
* 	echo $f->getContent();
* }
*
* // POST
* $f = new UrlFetcher("http://www.root.cz/login/");
* if($f->post("username=madl&password=krtek")){
* 	echo $f->getContent();
* }
*	
* $f = new UrlFetcher("http://www.example.com/data_collector.php");
* $f->post($xml,array("content_type" => "text/xml"));
*
* // 
* $f = new UrlFetcher("http://www.example.com/", array("additional_headers" => array("X-App-Version: 1.2")));
* echo $f->getContent();
*/
class UrlFetcher {
	var $_AuthType = ""; // "" nebo "basic"
	var $_Username = "";
	var $_Password = "";

	var $_SocketTimeout = 5;

	function _reset(){
		$this->_Fetched = null;
		$this->_RequestMethod = "GET";
		$this->_PostData = "";
		$this->_AdditionalHeaders = array();
		$this->_Url = "";
		$this->_Ssl = false;
		$this->_Port = 80;
		$this->_Server = "";
		$this->_Uri = "";
		$this->_ErrorMessage = "";

		$this->_RequestHeaders = "";
		$this->_ResponseHeaders = "";

		$this->_Content = null;
	}

	/**
	* 
	*/
	function UrlFetcher($url = "", $options = array()){
		$this->_reset();

		$options = array_merge(array(
			"additional_headers" => array(),
		),$options);

		if(strlen($url)>0){
			$this->_setUrl($url);
		}

		$this->_AdditionalHeaders = $options["additional_headers"];
	}

	function errorOccurred(){ return strlen($this->getErrorMessage())>0; }

	function getErrorMessage(){
		return $this->_ErrorMessage;
	}

	function setAuthorization($username,$password){
		settype($username,"string");
		settype($password,"string");

		$this->_Username = $username;
		$this->_Password = $password;
		$this->_AuthType = "basic";
	}

	function setSocketTimeout($timeout){ $this->_SocketTimeout = $timeout; }

	function resetAuthorization(){
		$this->_Username = "";
		$this->_Password = "";
		$this->_AuthType = "";
	}

	/**
	* Stahne obsah URL.
	* Sam sebe hlida, aby byl volan pouze 1x.
	* Nemusi byt volano vne objektu.
	*
	* Doporuceny postup:
	*		$f = new UrlFetcher("http://www.domemka.cz/file.dat");
	*		if($f->found()){
	*			echo $f->getContent();
	*		}
	*/
	function fetchContent($url = ""){
		if(strlen($url)>0){ $this->_setUrl($url); }

		if(isset($this->_Fetched)){ return $this->_Fetched; }

		if($this->errorOccurred()){ $this->_Fetched = false; return false; }
	
		$this->_buildRequestHeaders();

		$errno = null;
		$errstr = "";
		$_ssl = $this->_Ssl ? "ssl://" : "";
		$f = fsockopen("$_ssl$this->_Server",$this->_Port,$errno,$errstr,$this->_SocketTimeout);
		stream_set_blocking($f,0);
		if(!$f){
			return $this->_setError("failed to open socket: $errstr [$errno]");
		}
		$_data = $this->_RequestHeaders;
		if($this->_RequestMethod=="POST"){ $_data .= $this->_PostData; }
		$stat = fwrite($f,$_data,strlen($_data));

		if(!$stat || $stat!=strlen($_data)){
			fclose($f);
			return $this->_setError("cannot write to socket");
		}

		$content = "";
		$_buffer_ar = array();
		while(!feof($f) && $f){
			$_b = fread($f,4096);
			(strlen($_b)>0) && ($_buffer_ar[] = $_b);
			usleep(20000);
		}
		$content = join("",$_buffer_ar);
		fclose($f);

		if(strlen($content)==0){
			return $this->_setError("failed to read from socket");
		}

		//echo $content;

		if(preg_match("/^(.*?)\\r?\\n\\r?\\n(.*)$/s",$content,$matches)){
			$this->_ResponseHeaders = $matches[1];
			$this->_Content = $matches[2];
		}else{
			return $this->_setError("can't find response headers");
		}

		$this->_Fetched = true;

		if($this->getStatusCode()!=200){
			return $this->_setError("status code is ".$this->getStatusCode());
		}

		// toto je osklivy hack
		// nekdy dostaneme obsah delsi nez je Content-Length
		//
		// je to hack pro stahovani souboru: http://do-mobilu.respekt.cz/kestazeni-download.php?f_ID=815
		// tam koumaci prilepili za data velikost souboru - pocitaji natvrdo z HTTP/1.1
		if(($length = $this->getContentLength()) && strlen($this->_Content)>$length){
			$this->_Content = substr($this->_Content,0,$length);
		}


		return true;
	}

	function post($data = "",$options = array()){
		if(is_array($data)){
			$d = array();
			foreach($data as $k => $v){
				$d[] = urlencode($k)."=".urlencode($v);
			}
			$data = join("&",$d);
		}

		$options = array_merge(array(
			"content_type" => "application/x-www-form-urlencoded",
			"additional_headers" => array(),
		),$options);

		$this->_RequestMethod = "POST";
		$this->_PostData = $data;
		$this->_AdditionalHeaders = $options["additional_headers"];
		$this->_AdditionalHeaders[] = "Content-Type: $options[content_type]";

		return $this->found();
	}

	function getRequestHeaders(){ return $this->_RequestHeaders; }
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
	function getHeaders(){ return $this->getResponseHeaders($options = array()); }

	function getContent(){ $this->fetchContent(); return $this->_Content; }
	function getHeaderValue($header){
		$header = strtolower($header);
		$headers = $this->getResponseHeaders(array("as_hash" => true, "lowerize_keys" => true));
		if(isset($headers["$header"])){ return $headers["$header"]; }
	}
	function getContentType(){
		$c_type = $this->getHeaderValue("content-type");
		$c_type = trim(preg_replace("/(.*?);.*/","\\1",$c_type));
		return $c_type;
	}
	function getContentCharset(){
		if(preg_match("/;\\s*charset\\s*=([^;]+)/",$this->getHeaderValue("content-type"),$matches)){
			return trim($matches[1]);
		}
	}
	function getContentLength(){ return $this->getHeaderValue("content-length"); }
	function getStatusCode(){
		if(preg_match("/^HTTP\\/.\\.. ([0-9]{3})/",$this->getResponseHeaders(),$matches)){
			return (int)$matches[1];
		}
	}

	function found(){ return $this->getStatusCode()==200; }

	function getFilename(){
		if(preg_match("/([^\\/?]+)(\\?.*|)$/",$this->_Uri,$matches)){
			return $matches[1];
		}
	}

	function _setError($error_message){
		$this->_ErrorMessage = $error_message;
		$this->_Fetched = false;
		return false;
	}

	function _setUrl($url){
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
		$this->_Uri = $matches[3];
		unset($matches);

		//rozpoznani cisla TCP portu, defaultne je to 80 resp. 443 na ssl
		if(preg_match("/^(.+):([0-9]{1,})$/",$_server,$matches)){
			$_server = $matches[1];
			$this->_Port = (integer)$matches[2];
		}else{
			$this->_Port = $this->_Ssl ? 443 : 80;
		}
		
		//rozpoznani uzivatelskeho jmena a hesla pro HTTP BASIC AUTHentizaci...
		//	predpokladam, ze jmeno a heslo je v URL ve formatu urlencoded
		// 	( - pokud ovsem neni, mohou nastat problemy)
		if(preg_match("/^(.+):(.+)@(.+)$/",$_server,$matches)){
			$_username = urlencode($matches[1]);
			$_password = urlencode($matches[2]);
			$_server = $matches[3];
		}
		if(strlen($_username)>0){ $this->setAuthorization($_username,$_password); }
		$this->_Server = $_server;
		
		return true;
	}

	function _buildRequestHeaders(){
		$out = array();
		$out[] = "$this->_RequestMethod $this->_Uri HTTP/1.0";
		$_server = $this->_Server;
		if((!$this->_Ssl && $this->_Port!=80) || ($this->_Ssl && $this->_Port!=443)){ $_server.":$this->_Port"; }
		$out[] = "Host: $_server";
		$out[] = "Connection: close";
		$out[] = "User-Agent: UrlFetcher ".URL_FETCHER_VERSION;
		if($this->_AuthType=="basic"){
			$out[] = "Authorization: Basic ".base64_encode("$this->_Username:$this->_Password");
		}
		if($this->_RequestMethod=="POST"){
			$out[] = "Content-Length: ".strlen($this->_PostData);
		}
		foreach($this->_AdditionalHeaders as $h){
			$out[] = $h;
		}
		$out[] = "";
		$out[] = "";
		$this->_RequestHeaders = join("\r\n",$out);
	}
}
?>
