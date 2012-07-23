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

	/**
	 * Constructor
	 */
	function inobj(){
		$this->dbmole = &inobj::_GetDbmole();
		$this->_dbmole = &$this->dbmole;
	}

	/**
	 * Obtains instance of PgMole
	 *
	 */
	static function &_GetDbmole(){
		return PgMole::GetInstance();
	}

	/**
	 * Method called automatically before serialization.
	 *
	 * @ignore
	 */
	function __sleep(){
		$vars = get_object_vars($this);
		unset($vars["_dbmole"]);
		unset($vars["dbmole"]);
		return array_keys($vars);
	}

	/**
	 * Method called automatically after serialization.
	 *
	 * @ignore
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
