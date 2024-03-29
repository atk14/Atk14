<?php
if(!defined("_ATK14_FUNCTIONS_LOADED_") && !function_exists("EasyReplace")){

/**
 * Searches and replaces in a string
 *
 *	<code>
 *		echo EasyReplace("Hi %who%, how it's %what%?",array("%who%" => "Valda", "%what%" => "going"));
 *	</code>
 *
 *	@param string		$str
 *	@param array		$replaces	associative array
 *	@return	strig
 */
function EasyReplace($str,$replaces){
	$str = (string)$str;
	$replaces = (array)$replaces;
	if(!sizeof($replaces)){ return $str; }
	$keys = array_keys($replaces);
	$values = array_values($replaces);
	$keys = array_map(function($item){ return (string)$item; },$keys);
	$values = array_map(function($item){ return (string)$item; },$values);
	return str_replace($keys,$values,$str);
}

/**
 * Alias for htmlspecialchars()
 */
function h($string, $flags = null, $encoding = null){
	if(!is_string($string)){
		$string = (string)$string;
	}
	if(!isset($flags)){
		$flags =  ENT_COMPAT | ENT_QUOTES;
		if(defined("ENT_HTML401")){ $flags = $flags | ENT_HTML401; }
	}
	if(!isset($encoding)){
		// as of PHP5.4 the default encoding is UTF-8, it causes troubles in non UTF-8 applications,
		// I think that the encoding ISO-8859-1 works well in UTF-8 applications
		$encoding = "ISO-8859-1";
	}
	return htmlspecialchars((string)$string,$flags,$encoding);
}

/**
 * Defines a constant if it hasn't been defined yet
 *
 * Returns actual content of the constant.
 *
 *	<code>
 *		$port = definedef("EXPORT_DB_PORT",1234); // $port may be 1234 or may be not :)
 *	</code>
 */
function definedef($name, $value){
	defined($name) || define($name, $value);
	return constant($name);
}

/**
 * This file is part of the array_column library
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright Copyright (c) 2013 Ben Ramsey <http://benramsey.com>
 * @license http://opensource.org/licenses/MIT MIT
 */

if (!function_exists('array_column')) {

	/**
	 * Returns the values from a single column of the input array, identified by
	 * the $columnKey.
	 *
	 * Optionally, you may provide an $indexKey to index the values in the returned
	 * array by the values from the $indexKey column in the input array.
	 *
	 * @param array $input A multi-dimensional array (record set) from which to pull
	 *                     a column of values.
	 * @param mixed $columnKey The column of values to return. This value may be the
	 *                         integer key of the column you wish to retrieve, or it
	 *                         may be the string key name for an associative array.
	 * @param mixed $indexKey (Optional.) The column to use as the index/keys for
	 *                        the returned array. This value may be the integer key
	 *                        of the column, or it may be the string key name.
	 * @return array
	 */
	function array_column($input = null, $columnKey = null, $indexKey = null)
	{
		// Using func_get_args() in order to check for proper number of
		// parameters and trigger errors exactly as the built-in array_column()
		// does in PHP 5.5.
		$argc = func_num_args();
		$params = func_get_args();

		if ($argc < 2) {
				trigger_error("array_column() expects at least 2 parameters, {$argc} given", E_USER_WARNING);
				return null;
		}

		if (!is_array($params[0])) {
				trigger_error('array_column() expects parameter 1 to be array, ' . gettype($params[0]) . ' given', E_USER_WARNING);
				return null;
		}

		if (!is_int($params[1])
				&& !is_float($params[1])
				&& !is_string($params[1])
				&& $params[1] !== null
				&& !(is_object($params[1]) && method_exists($params[1], '__toString'))
		) {
				trigger_error('array_column(): The column key should be either a string or an integer', E_USER_WARNING);
				return false;
		}

		if (isset($params[2])
				&& !is_int($params[2])
				&& !is_float($params[2])
				&& !is_string($params[2])
				&& !(is_object($params[2]) && method_exists($params[2], '__toString'))
		) {
				trigger_error('array_column(): The index key should be either a string or an integer', E_USER_WARNING);
				return false;
		}

		$paramsInput = $params[0];
		$paramsColumnKey = ($params[1] !== null) ? (string) $params[1] : null;

		$paramsIndexKey = null;
		if (isset($params[2])) {
				if (is_float($params[2]) || is_int($params[2])) {
						$paramsIndexKey = (int) $params[2];
				} else {
						$paramsIndexKey = (string) $params[2];
				}
		}

		$resultArray = array();

		foreach ($paramsInput as $row) {

				$key = $value = null;
				$keySet = $valueSet = false;

				if ($paramsIndexKey !== null && array_key_exists($paramsIndexKey, $row)) {
						$keySet = true;
						$key = (string) $row[$paramsIndexKey];
				}

				if ($paramsColumnKey === null) {
						$valueSet = true;
						$value = $row;
				} elseif (is_array($row) && array_key_exists($paramsColumnKey, $row)) {
						$valueSet = true;
						$value = $row[$paramsColumnKey];
				}

				if ($valueSet) {
						if ($keySet) {
								$resultArray[$key] = $value;
						} else {
								$resultArray[] = $value;
						}
				}

		}

		return $resultArray;
	}
}

/**
 * Replacement for built-in function assert()
 *
 * Function assert() turned to language construct in PHP 7 and has no effect in production environment.
 */
function myAssert($expression,$message = ""){
	if(!$expression){
		$file = $line = "???";
		$ar = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT,1);
		if($ar){
			$file = $ar[0]["file"];
			$line = $ar[0]["line"];
		}

		$message = $message ? $message : "Assertion";
		$msg = sprintf("myAssert(): %s failed in %s on line %d",$message,$file,$line);
		throw new Exception($msg);
	}
}

define("_ATK14_FUNCTIONS_LOADED_",__FILE__);
}
