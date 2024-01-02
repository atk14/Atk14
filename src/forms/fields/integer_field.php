<?php
/**
 * Field for integers.
 *
 * @package Atk14
 * @subpackage Forms
 */
class IntegerField extends Field
{

	var $max_value;
	var $min_value;

	function __construct($options=array())
	{
		$options = forms_array_merge(array(
				'max_value' => null,
				'min_value' => null,
			), $options
		);
		$attrs = array();
		if (isset($options["min_value"])) {
			$attrs["min"] = $options["min_value"];
		}
		if (isset($options["max_value"])) {
			$attrs["max"] = $options["max_value"];
		}
		$options = forms_array_merge(array(
				'widget' => new NumberInput(array("attrs" => $attrs)),
			), $options
		);
		$this->max_value = $options['max_value'];
		$this->min_value = $options['min_value'];
		parent::__construct($options);
		$this->update_messages(array(
			'invalid' => _('Enter a whole number.'),
			'max_value' => _('Ensure this value is less than or equal to %value%.'),
			'min_value' => _('Ensure this value is greater than or equal to %value%.'),
		));
	}

	function clean($value)
	{
		$value = trim((string)$value);
		list($error, $value) = parent::clean($value);
		if (!is_null($error)) {
			return array($error, $value);
		}
		if ($this->check_empty_value($value)) {
			return array(null, null);
		}

		$value = preg_replace("/ +/","",$value);
		if (!preg_match("/^(0|[+-]?[1-9][0-9]*)$/",$value)) {
			return array($this->messages['invalid'], null);
		}
		$value = (int)$value;

		if ((!is_null($this->max_value)) && ($value > $this->max_value)) {
			return array(EasyReplace($this->messages['max_value'], array('%value%'=>$this->max_value)), null);
		}
		if ((!is_null($this->min_value)) && ($value < $this->min_value)) {
			return array(EasyReplace($this->messages['min_value'], array('%value%'=>$this->min_value)), null);
		}
		return array(null, $value);
	}
}
