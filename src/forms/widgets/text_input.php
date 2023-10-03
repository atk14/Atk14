<?php
/**
 * Widget for text input field.
 *
 * Outputs field of this type:
 *
 * ```
 * <input type="text" />
 * ```
 *
 * By default the element has attribute class set to "text"
 *
 * @package Atk14\Forms
 */
class TextInput extends Input
{
	var $input_type = 'text';

	function render($name, $value, $options = array()) 
	{
		if(!isset($this->attrs["class"])){ // pokud nebylo class definovano v konstruktoru
			!isset($options["attrs"]) && ($options["attrs"] = array());
			$class = "text form-control"; // form-control is there for Bootstrap
			if($this->input_type!="text"){
				$class = "$this->input_type $class"; // "number text form-control"
			}
			$options["attrs"] = forms_array_merge(array(
				"class" => $class,
			),$options["attrs"]);
		}
		return parent::render($name, $value, $options);
	}
}
