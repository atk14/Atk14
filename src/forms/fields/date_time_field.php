<?php
/**
 * Field for datetime validations.
 *
 * @package Atk14
 * @subpackage Forms
 */
class DateTimeField extends DateField
{
	function DateTimeField($options=array())
	{
		parent::DateField($options);
		$this->update_messages(array(
			'invalid' => _('Enter a valid date, hours and minutes.')
		));
		$this->_format_function = "FormatDateTime";
		$this->_parse_function = "ParseDateTime";
	}
}
