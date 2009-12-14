<?
class inobj{
	function inobj(){
		$this->_dbmole = &inobj::_GetDbmole();
	}

	function &_GetDbmole(){
		return PgMole::GetInstance();
	}

	function _objToId($obj){
		return is_object($obj) ? $obj->getId() : $obj;
	}

	// legacy method; do nothing
	function RegisterErrorCallback($function_name){ }
}
?>
