<?
if (get_magic_quotes_gpc()) {
	function stripslashes_array($array) {
			return is_array($array) ? array_map('stripslashes_array', $array) : stripslashes($array);
	}

	$_COOKIE = stripslashes_array($_COOKIE);
	//$_FILES = stripslashes_array($_FILES);
	$_GET = stripslashes_array($_GET);
	$_POST = stripslashes_array($_POST);
	//$_REQUEST = stripslashes_array($_REQUEST);
}

class HTTPRequest{

	var $_HTTPRequest_documentRoot = "";
	
	/**
	* Server name nastaveny v httpd.conf.
	*/
	var $_HTTPRequest_serverName = "";
	
	/**
	* Jmeno serveru, ktere napise uzivak do url.
	*/
	var $_HTTPRequest_HTTPHost = "";
	var $_HTTPRequest_serverProtocol = "";
	var $_HTTPRequest_serverPort = null;
	var $_HTTPRequest_scriptName = "";
	var $_HTTPRequest_scriptFilename = "";

	var $_HTTPRequest_headers = array();

	var $_SSLPorts = array(443);

	//var $_HTTPRequest_paramsGet = array();
	//var $_HTTPRequest_paramsPost = array();
	//var $_HTTPRequest_paramsCookie = array();


	/**
	* @var string
	*/
	var $_HTTPRequest_BasicAuth_Username = null;
	/**
	* @var string
	*/
	var $_HTTPRequest_BasicAuth_Password = null;


	function HTTPRequest(){
		$this->_AutoInitialize();
	}

	function AddSSLPort($port){
		settype($port,"integer");
		$this->_SSLPorts[] = $port;
	}

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
	function getRequestUri(){
		global $_SERVER;
		if(isset($this->_ForceRequestUri)){ return $this->_ForceRequestUri; }
		return isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : "";
	}
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
	function getHttpReferer(){
		return isset($GLOBALS["_SERVER"]["HTTP_REFERER"]) ? $GLOBALS["_SERVER"]["HTTP_REFERER"] : "";
	}
	function getScriptName(){
		return $this->_HTTPRequest_scriptName;
	}
	function getRemoteAddr(){
		return $GLOBALS["_SERVER"]["REMOTE_ADDR"];
	}
	function getServerName(){
		return $this->_HTTPRequest_serverName;
	}
	function getServerPort(){
		return $this->_HTTPRequest_serverPort;
	}
	function getHTTPHost(){
		return $this->_HTTPRequest_HTTPHost;
	}

	/**
	* Vrati string "username:password".
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

	function getBasicAuthUsername(){
		return $this->_HTTPRequest_BasicAuth_Username;
	}

	function getBasicAuthPassword(){
		return $this->_HTTPRequest_BasicAuth_Password;
	}

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
	function getMethod(){ return $this->getRequestMethod(); }

	function setRequestMethod($method){ $this->_ForceRequestMethod = strtoupper($method); }
	function setMethod($method){ $this->setRequestMethod($method); }

	// is this a POST request 
	function post(){ return $this->getRequestMethod()=="POST"; }

	// is this a GET request 
	function get(){ return $this->getRequestMethod()=="GET"; }

	// is this a PUT request 
	function put(){ return $this->getRequestMethod()=="PUT"; }

	// is this a DELETE request 
	function delete(){ return $this->getRequestMethod()=="DELETE"; }

	/**
	* Je toto XHR request?
	* Funguje s JQuery.
	*/
	function xhr(){
		if(isset($this->_ForceXhr)){ return $this->_ForceXhr; }
		$headers = $this->_HTTPRequest_headers;
		reset($headers);
		while(list($key,$value) = each($headers)){
			$key = strtolower($key);
			$value = strtolower($value);
			if($key=="x-requested-with" && $value=="xmlhttprequest"){
				return true;
			}
		}
		return false;
	}

	function setXhr($value = true){
		if(!isset($value)){ unset($this->_ForceXhr); return; }
		$this->_ForceXhr = $value;
	}

	/**
	* 
	* @access public
	* @return string    napr. "text/plain"; nebo null, pokud content-type neni nastaven
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
	*
	* 
	* @access public
	* @return string			nebo null v pripade, ze charset neni definovan
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

	function getUserAgent(){ return $GLOBALS["_SERVER"]["HTTP_USER_AGENT"]; }

	/**
	* 
	* 
	* @access public
	* @return string			nebo null, pokud zadna data neexistuji
	*/
	function getRawPostData(){
		if(isset($GLOBALS["HTTP_RAW_POST_DATA"])){
			return $GLOBALS["HTTP_RAW_POST_DATA"];
		}
		return null;
	}

	function ssl(){ return $this->sslActive(); }
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
	* Zjisti, zda se parametr nachazi v HTTP requestu.
	* Volitelnym parametrem $order je napr. mozne parametr vyhledavat pouze v POSTU ("P").
	* 
	* @access public
	* @param string $var_name			jmeno parametru
	* @param string $order				kde vstude hledat: "G" GET, "P" POST, "C" Cookies
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

	function defined($var_name,$order = "GPC"){
		return $this->isVarDefined($var_name,$order);
	}

	function isPostVarDefined($var_name){ return $this->IsVarDefined($var_name,"P"); }
	function isGetVarDefined($var_name){ return $this->IsVarDefined($var_name,"G"); }
	function isCookieVarDefined($var_name){ return $this->IsVarDefined($var_name,"C"); }

	function getGetVar($var_name){
		settype($var_name,"string");
		$out = null;
		if(isset($GLOBALS["_GET"][$var_name])){
			$out = $GLOBALS["_GET"][$var_name];
		}
		return $out;
	}

	function getAllGetVars(){ return $GLOBALS["_GET"]; }
	function getGetVars(){ return $this->getAllGetVars(); }


	function getPostVar($var_name){
		settype($var_name,"string");
		$out = null;
		$vars = $this->getAllPostVars();
		if(isset($vars[$var_name])){
			$out = $vars[$var_name];
		}
		return $out;
	}

	function getAllPostVars(){
		if(isset($this->_ForcePostVars)){ return $this->_ForcePostVars; }
		return $GLOBALS["_POST"];
	}
	function getPostVars(){ return $this->getAllPostVars(); }

	function setPostVars($vars){ $this->_ForcePostVars = $vars; }

	function getCookieVar($var_name){
		settype($var_name,"string");
		$out = null;
		if(isset($GLOBALS["_COOKIE"][$var_name])){
			$out = $GLOBALS["_COOKIE"][$var_name];
		}
		return $out;
	}
	function getCookieVars(){ return $GLOBALS["_COOKIE"]; }

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

	function filesUploaded(){
		return sizeof($this->getUploadedFiles())>0;
	}

	/**
	* TODO: Toto nefunguje...
	*/
	function filesUploadedWithNoError(){
		//echo "<pre>";
		//var_dump($GLOBALS["_FILES"]);
		//echo "</pre>";
		return sizeof($this->getUploadedFiles())==sizeof($GLOBALS["_FILES"]);
	}

	function getUploadedFiles($options = array()){
		return HTTPUploadedFile::GetInstances($options);
	}

	/**
	* // vrati prvni uploadnuty soubor
	* $file = $request->getUploadedFile();
	*
	* // vrati uploadnuty soubor podle nazvu
	* $file = $request->getUploadedFile("userfile");
	* echo "filename: ".$file->getFileName()."\n";
	* echo "size: ".$file->getFileSize()."\n";
	*
	* echo $file->getContent();
	* 
	* $file->moveTo("data/store/path/");
	* $file->moveTo("data/store/path/data.txt");
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
	* Vrati "otisk" informaci o klientovi.
	* 
	* @param $output_format   "string", "md5", "array" nebo "serialize"
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
	* Detekce mobilniho zarizeni.
	*
	* Poutzito ze:
	*	PHP to detect mobile phpnes
	*	http://www.andymoore.info/php-to-detect-mobile-phones/
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

	function iphone(){
		return preg_match('/iphone/',$this->getUserAgent()) || preg_match('/ipod/',$this->getUserAgent());
	}

	/**
	* Toto je Valiskova funkce. Neni jasne, k cemu to je....
	*
	* TODO: nejake escapovani podle defaultniho parametru (flagu)?
	*/
	function getAllEncodedGetvars(){
    $output = array();
    foreach ($GLOBALS["_GET"] as $name => $value) {
        $output[] = "$name=$value";
    }
    return implode("&", $output);
	}

}
?>
