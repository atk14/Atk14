<?php
/**
 * Provides access to uploaded file.
 *
 * Uploaded file is accessible as {@link HTTPUploadedFile}
 *
 * @package Atk14
 * @subpackage Forms
 */
class FileField extends Field{
	function FileField($options = array()){
		$options = array_merge(array(
			"widget" => new FileInput(),
		),$options);
		parent::Field($options);
	}
	function clean($value){
		list($err,$value) = parent::clean($value);
		if(isset($err)){ return array($err,null); }
		return array(null,$value);
	}
}
