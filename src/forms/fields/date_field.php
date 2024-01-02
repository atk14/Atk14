<?php
/**
 * Field for date validations.
 *
 * @package Atk14
 * @subpackage Forms
 */
class DateField extends CharField
{

	var $max_date;
	var $min_date;
	var $_format_function;
	var $_parse_function;

	function __construct($options=array())
	{
		$options = array_merge(array(
			"null_empty_output" => true,
			"max_date" => null, // e.g. "2021-05-11"
			"min_date" => null,
		),$options);
		$this->max_date = $options['max_date'];
		$this->min_date = $options['min_date'];
		parent::__construct($options);
		$this->update_messages(array(
			'invalid' => _('Enter a valid date.'),
			'max_date' => _('Ensure this date is not newer than %value%.'),
			'min_date' => _('Ensure this date is not older than %value%.'),
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
		$value = call_user_func_array(array("Atk14Locale",$this->_parse_function),array($value));
		if(!$value){
			return array($this->messages['invalid'], null);
		}
		if($this->max_date){
			$date = $this->max_date;
			$date_localized = call_user_func_array(array("Atk14Locale",$this->_format_function),array($date));
			$date = call_user_func_array(array("Atk14Locale",$this->_parse_function),array($date_localized));
			if(strtotime($value)>strtotime($date)){
				return array(str_replace("%value%",$date_localized,$this->messages['max_date']), null);
			}
		}
		if($this->min_date){
			$date = $this->min_date;
			$date_localized = call_user_func_array(array("Atk14Locale",$this->_format_function),array($date));
			$date = call_user_func_array(array("Atk14Locale",$this->_parse_function),array($date_localized));
			if(strtotime($value)<strtotime($date)){
				return array(str_replace("%value%",$date_localized,$this->messages['min_date']), null);
			}
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
