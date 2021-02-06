<?php
class StringBufferTemporaryFileItem extends StringBufferFileItem {

	function __destruct(){
		if(file_exists($this->_Filename)){
			unlink($this->_Filename);
		}
	}
	
	function __sleep(){
		if(is_null($this->_String)){
			$this->_String = $this->toString();
		}
		if(file_exists($this->_Filename)){
			unlink($this->_Filename);
		}
		return array_keys(get_object_vars($this));
	}
}
