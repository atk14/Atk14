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
class Textarea extends Widget
{
	function __construct($options=array())
	{
		$options = forms_array_merge(array('attrs'=>null), $options);
		$this->attrs = array(
			'cols' => '40',
			'rows' => '10'
		);
		if (!is_null($options['attrs'])) {
			$this->attrs = forms_array_merge($this->attrs, $options['attrs']);
		}
	}

	function render($name, $value, $options=array())
	{
		$options = forms_array_merge(array('attrs'=>null), $options);
		if (is_null($value)) {
			$value = '';
		}
		$final_attrs = $this->build_attrs($options['attrs'], array(
			'name' => $name)
		);
		return '<textarea'.flatatt($final_attrs).'>'.forms_htmlspecialchars($value).'</textarea>';
	}
}
