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
}
