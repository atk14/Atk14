<?php
/**
 * Field for long strings.
 *
 * TextField renders <textarea> HTML tag
 * and preserves white spaces by default.
 *
 * @package Atk14
 * @subpackage Forms
 */
class TextField extends CharField{

	function __construct($options = array())
	{
		$options = forms_array_merge(array(
			"widget" => new TextArea(),
			"trim_value" => false,
		),$options);
		parent::__construct($options);
	}

	function clean($value){
		$value = (string)$value;
		if($this->required && trim($value)===""){
			// when there are white characters only, the value is considered as empty
			return array($this->messages["required"],null);
		}
		if($this->null_empty_output && trim($value)===""){
			$value = null;
		}
		return parent::clean($value);
	}
}
