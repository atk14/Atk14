<?php
/**
 * Widget for rendering file input field.
 *
 * @package Atk14\Forms
 */
class FileInput extends Input{
	var $input_type = "file";
	var $multipart_encoding_required = true;

	function render($name, $value, $options=array())
	{
		// zde je $value objekt tridy HTTPUploadedFile -> pro rendering z toho udelame prazdny string
		return parent::render($name, "", $options);
	}

	/**
	 *
	 * Return an HTTPUploadedFile object when the file was uploaded successfully.
	 * Returns null when no file was uploaded.
	 * Return an integer (error code) when an upload error occurred (see http://php.net/manual/en/features.file-upload.errors.php)
	 */
	function value_from_datadict($data, $name)
	{
		global $HTTP_REQUEST,$_FILES;
		$out = $HTTP_REQUEST->getUploadedFile($name); // HTTPUploadedFile
		if(!$out){
			if(isset($_FILES[$name]) && isset($_FILES[$name]["error"])){
				$error = $_FILES[$name]["error"];
				$error = (int)$error;
				if($error>0 && $error!=4){ // 4 means "No file was uploaded" which is OK for us
					$out = $error;
				}
			}
		}
		return $out;
	}
}
