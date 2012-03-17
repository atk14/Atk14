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

	function &_GetDbmole(){
		return PgMole::GetInstance();
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
