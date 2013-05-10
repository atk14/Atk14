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
	function __construct($options = array()){
		$options = array_merge(array(
			"widget" => new FileInput(),
		),$options);
		parent::__construct($options);
	}
	function clean($value){
		list($err,$value) = parent::clean($value);
		if(isset($err)){ return array($err,null); }
		return array(null,$value);
	}
}
