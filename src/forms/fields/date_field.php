<?php
/**
 * Field for date validations.
 *
 * @package Atk14
 * @subpackage Forms
 */
class DateField extends CharField
{
	function __construct($options=array())
	{
		$options = array_merge(array(
			"null_empty_output" => true
		),$options);
		parent::__construct($options);
		$this->update_messages(array(
			'invalid' => _('Enter a valid date.'),
		));
		$this->_format_function = "FormatDate";
		$this->_parse_function = "ParseDate";
	}

	function clean($value)
	{
		list($error, $value) = parent::clean($value);
		if (!is_null($error)) {
			return array($error, null);
		}
		if ($value == '') {
			return array(null, $value);
		}
		eval('$value = Atk14Locale::'.$this->_parse_function.'($value);');
		if(!$value){
			return array($this->messages['invalid'], null);
		}
		return array(null, $value);
	}

	function format_initial_data($data)
	{
		if (is_numeric($data)) {
			// converting timestamp to date in ISO format
			$data = date("Y-m-d H:i:s",$data);
		}
		eval('$out = Atk14Locale::'.$this->_format_function.'($data);');
		return $out;
	}
}
