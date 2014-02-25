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
	function value_from_datadict($data, $name)
	{
		global $HTTP_REQUEST;
		return $HTTP_REQUEST->getUploadedFile($name);
	}
}
