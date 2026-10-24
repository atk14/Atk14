<?php
/**
 * Widget for hidden input field.
 *
 * Outputs field of this type:
 * <code>
 * <input type="hidden" />
 * </code>
 *
 * @package Atk14
 * @subpackage Forms
 */
class HiddenInput extends Input
{
	var $input_type = 'hidden';
	var $is_hidden = true;

	function render($name, $value, $options=array())
	{
		$out = parent::render($name, $value, $options);
		// hack: in a hidden field the "required" attribute has no reason
		$out = str_replace(' required="required"','',$out);
		return $out;
	}
}
