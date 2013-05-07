<?php
/**
 * Field for validation of datetime with seconds.
 *
 * @package Atk14
 * @subpackage Forms
 */
class DateTimeWithSecondsField extends DateField
{
	function DateTimeWithSecondsField($options=array())
	{
		parent::DateField($options);
		$this->update_messages(array(
			'invalid' => _('Enter a valid date, hours, minutes and seconds.')
		));
		$this->_format_function = "FormatDateTimeWithSeconds";
		$this->_parse_function = "ParseDateTimeWithSeconds";
	}
}
