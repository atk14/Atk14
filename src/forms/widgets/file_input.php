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
		if(!isset($this->attrs["class"])){ // if a class was not defined in the constructor
			!isset($options["attrs"]) && ($options["attrs"] = array());
			$class = (FORMS_MARKUP_TUNED_FOR_BOOTSTRAP3 || FORMS_MARKUP_TUNED_FOR_BOOTSTRAP4 || FORMS_MARKUP_TUNED_FOR_BOOTSTRAP5) ? "form-control-file" : "form-control";
			$options["attrs"] = forms_array_merge(array(
				"class" => $class,
			),$options["attrs"]);
		}
		// here, the $value is a HTTPUploadedFile object -> for the rendering it turns to the empty string
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
			$error = $HTTP_REQUEST->getUploadedFileError($name);
			if($error>0 && $error!=4){ // 4 means "No file was uploaded" which is OK for us
				$out = $error;
			}
		}
		return $out;
	}
}
