<?php
/**
 * Widget for password input field.
 *
 * Outputs field of this type:
 * <code>
 * <input type="password" />
 * </code>
 *
 * By default the element has attribute class set to "text"
 *
 * @package Atk14
 * @subpackage Forms
 */
class PasswordInput extends Input
{
	var $input_type = 'password';
	var $render_value;

	/**
	 * Constructor.
	 *
	 * @param array $options
	 */
	function __construct($options=array())
	{
		if(!isset($this->attrs["class"])){ // pokud nebylo class definovano v konstruktoru
			!isset($options["attrs"]) && ($options["attrs"] = array());
			$options["attrs"] = forms_array_merge(array(
				"class" => "text"
			),$options["attrs"]);
		} 

		$options = forms_array_merge(array('render_value'=>true), $options);
		parent::__construct($options);
		$this->render_value = $options['render_value'];
	}

	function render($name, $value, $options=array())
	{
		$options = forms_array_merge(array('attrs'=>null), $options);
		if (!$this->render_value) {
			$value = null;
		}
		return parent::render($name, $value, $options);
	}
}
