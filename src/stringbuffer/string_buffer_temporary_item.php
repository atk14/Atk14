<?php
class StringBufferTemporaryItem extends StringBufferFileItem {

	function __construct($content){
		$this->_String = (string)$content;
	}

	function addString($content){
		$this->_String .= (string)$content;
	}

	function isFileized(){
		return is_null($this->_String);
	}

	function fileize(){
		$filename = Files::GetTempFilename("string_buffer_temporary_");
		Files::WriteToFile($filename,$this->_String,$err,$err_str);
		if($err){
			throw new Exception(get_class($this).": cannot write to temporary file $filename ($err_msg)");
		}
		$this->_String = null;
		$this->_Filename = $filename;
	}

	function __destruct(){
		if($this->_Filename && file_exists($this->_Filename)){
			unlink($this->_Filename);
		}
	}
	
	function __sleep(){
		if(is_null($this->_String)){
			$this->_String = $this->toString();
		}
		if($this->_Filename && file_exists($this->_Filename)){
			unlink($this->_Filename);
		}
		return array_keys(get_object_vars($this));
	}
}
