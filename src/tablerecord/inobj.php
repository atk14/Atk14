<?php
/**
 * Basic class of all descendant models.
 *
 * @package Atk14
 * @subpackage TableRecord
 * @filesource
 *
 */

/**
 * Basic class for all descendant models.
 *
 * @package Atk14
 * @subpackage TableRecord
 * @filesource
 *
 */
class inobj{

	/**
	 * Constructor
	 */
	function __construct(){
	}

	/**
	 * Converts TableRecord object to its id.
	 *
	 * @param mixed $obj object to be converted
	 * 
	 * @return mixed
	 * @static
	 */
	function _objToId($obj){
		return is_object($obj) ? $obj->getId() : $obj;
	}

	/**
	 * Legacy method
	 *
	 * does nothing
	 * @param string $function_name
	 */
	static function RegisterErrorCallback($function_name){ }
}
