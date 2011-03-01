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
	 * @access protected
	 */
	var $_dbmole = null;

	function inobj(){
		$this->_dbmole = &inobj::_GetDbmole();
	}

	function &_GetDbmole(){
		return PgMole::GetInstance();
	}

	/**
	 * Converts inobj_TableRecord object to its id.
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
	function RegisterErrorCallback($function_name){ }
}
?>
