<?php
/**
 * Here is some private stuff.
 *
 * @ignore
 */
class _HTTPUtils{

	/**
	 * Strips slashes in strings passed in array.
	 *
	 * @param array $array array of strings to strip slashes from
	 * @return array
	 */
	static function _StripslashesArray($array){
		return is_array($array) ? array_map('_HTTPUtils::_StripslashesArray', $array) : stripslashes($array);
	}

	static function _HandleXAuthorization(){
		if(!function_exists("getallheaders")){ return; }

		$headers = getallheaders();
		if(!isset($headers["X-Authorization"]) || isset($GLOBALS['_SERVER']['PHP_AUTH_USER'])){
			return;
		}

		_HTTPUtils::_SetAuthData($headers["X-Authorization"]);
	}

	/**
	 * _HTTPUtils::_SetAuthData("Basic cHJldmlldzpWdVNlMXk=");
	 */
	static function _SetAuthData($authorization){
		if(!preg_match('/^Basic (.*)/',$authorization,$matches)){
			return;
		}
		$auth = base64_decode($matches[1]);
		if(!is_string($auth)){
			return;
		}

		$ar = explode(":",$auth);

		if(sizeof($ar)<2){
			return;
		}
		
		$GLOBALS['_SERVER']['PHP_AUTH_USER'] = array_shift($ar);
		$GLOBALS['_SERVER']['PHP_AUTH_PW'] = join(':',$ar);
	}

	/**
	 * Preparing environment, i.e. global variables
	 */
	static function PrepareEnvironment(){
		global $_COOKIE, $_FILES, $_GET, $_POST, $_REQUEST, $_SERVER;

		if (function_exists("get_magic_quotes_gpc") && get_magic_quotes_gpc()) {
			$_COOKIE = _HTTPUtils::stripslashes_array($_COOKIE);
			//$_FILES = _HTTPUtils::stripslashes_array($_FILES);
			$_GET = _HTTPUtils::stripslashes_array($_GET);
			$_POST = _HTTPUtils::stripslashes_array($_POST);
			//$_REQUEST = _HTTPUtils::stripslashes_array($_REQUEST);
		}

		/*
		 * If your script is being run as fastcgi, there is no access to to the HTTP Authorization header.
		 * So you can put the following lines into .htaccess
		 * 
		 * <IfModule mod_headers.c>
		 *	RewriteRule (.*) - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
		 *	RequestHeader set X-Authorization %{HTTP_AUTHORIZATION}e
		 * </IfModule>
		 *
		 * After then there is a new HTTP header X-Authorization with the original Authorization value.
		 */
		_HTTPUtils::_HandleXAuthorization();

		// In order to distinguish URLs for normal and XHR requests, atk14.js transparently adds __xhr_request=1 to URL for every XHR GET request.
		// It's ok for us to silently remove __xhr_request parameter.
		// But we need to consider such requests as XHR, see HTTPRequest::xhr().
		if(isset($_GET["__xhr_request"]) && $_GET["__xhr_request"]==="1"){
			unset($_GET["__xhr_request"]);
			$_SERVER["X_ORIGINAL_REQUEST_URI"] = $_SERVER["REQUEST_URI"];
			$_SERVER["REQUEST_URI"] = preg_replace('/__xhr_request=1\&?/','',$_SERVER["REQUEST_URI"]);
			$_SERVER["REQUEST_URI"] = preg_replace('/[?&]$/','',$_SERVER["REQUEST_URI"]);
		}
	}
}

_HTTPUtils::PrepareEnvironment();

