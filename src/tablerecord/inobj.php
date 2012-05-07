<?php
/**
 * Basic class of all descendant models.
 *
 * @package Atk14
 * @subpackage InternalLibraries
 * @filesource
 *
 */

/**
 * Basic class for all descendant models.
 *
 * @package Atk14
 * @subpackage InternalLibraries
 * @filesource
 *
 */
class inobj{

	/**
	 * Database connection.
	 *
	 * @var DbMole
	 */
	var $dbmole = null;

	/**
	 * Database connection.
	 * For backward compatibility
	 *
	 * @var DbMole
	 * @access private
	 */
	var $_dbmole = null;

	function inobj(){
		$this->dbmole = &inobj::_GetDbmole();
		$this->_dbmole = &$this->dbmole;
	}

	static function &_GetDbmole(){
		return PgMole::GetInstance();
	}

	/**
	* Metoda volana automaticky pred serializaci.
	*/
	function __sleep(){
		$vars = get_object_vars($this);
		unset($vars["_dbmole"]);
		unset($vars["dbmole"]);
		return array_keys($vars);
	}
	/**
	* Metoda volana automaticky po unserializaci.
	*/
	function __wakeup(){
		if(class_exists("PgMole")){
			$this->_dbmole = PgMole::GetInstance();
			$this->dbmole = &$this->_dbmole;
		}
	}

	/**
	 * Converts TableRecord object to its id.
	 *
	 * @access private
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
	 */
	static function RegisterErrorCallback($function_name){ }
}
