<?php
/**
 * Widget for text area input field.
 *
 * Outputs field of this type:
 * <code>
 * <textarea></textarea>
 * </code>
 *
 * @package Atk14
 * @subpackage Forms
 */
class TextArea extends Widget
{
	var $input_type = "textarea";

	function __construct($options=array())
	{
		$options = forms_array_merge(array('attrs'=>null), $options);
		$this->attrs = array(
			'cols' => '40',
			'rows' => '10',
			'class' => 'form-control', // form-control is there for Bootstrap
		);
		if (!is_null($options['attrs'])) {
			$this->attrs = forms_array_merge($this->attrs, $options['attrs']);
		}
	}

	function render($name, $value, $options=array())
	{
		if (!isset($options['attrs'])) { $options['attrs'] = array(); }
		if (is_null($value)) { $value = ''; }
		$final_attrs = forms_array_merge($this->attrs,$options['attrs'],array(
			'name' => $name,
		));
		
		// note that there is an extra lf character
		return '<textarea'.flatatt($final_attrs).">\n".forms_htmlspecialchars($value).'</textarea>';
	}
}
