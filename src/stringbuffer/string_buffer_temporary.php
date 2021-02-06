<?php
/**
 * StringBufferTemporary writes every added content into a temporary file in order to minimize memory consumption
 *
 *	$buffer = new StringBufferTemporary();
 *
 * 	$buffer->add($megabyte);
 * 	$buffer->add($megabyte);
 * 	$buffer->add($megabyte);
 * 	$buffer->add($megabyte);
 *
 *	$buffer->writeToFile($target_filename);
 */
class StringBufferTemporary extends StringBuffer {

	function addString($string_to_add){
		settype($string_to_add,"string");
		if(strlen($string_to_add)>0){
			$filename = Files::GetTempFilename("string_buffer_temporary_");
			Files::WriteToFile($filename,$string_to_add,$err,$err_str);
			if($err){
				throw new Exception(get_class($this).": cannot write to temporary file $filename ($err_msg)");
			}
			$this->_Items[] = new StringBufferTemporaryFileItem($filename);
		}
	}
}
