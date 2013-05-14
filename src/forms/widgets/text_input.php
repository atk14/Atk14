<?php
/**
 * Widget for text input field.
 *
 * Outputs field of this type:
 * <code>
 * <input type="text" />
 * </code>
 *
 * By default the element has attribute class set to "text"
 *
 * @package Atk14
 * @subpackage Forms
 */
class TextInput extends Input
{
	var $input_type = 'text';

	function render($name, $value, $options = array()) 
	{
		if(!isset($this->attrs["class"])){ // pokud nebylo class definovano v konstruktoru
			!isset($options["attrs"]) && ($options["attrs"] = array());
			$options["attrs"] = forms_array_merge(array(
				"class" => "text"
			),$options["attrs"]);
		}
		return parent::render($name, $value, $options);
	}
}
