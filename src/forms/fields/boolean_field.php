<?php

/**
 * Field for boolean values.
 *
 * @package Atk14
 * @subpackage Forms
 */
class BooleanField extends Field
{
	function BooleanField($options=array())
	{
		$options = array_merge(array(
			"widget" => new CheckboxInput(),
		),$options);
		parent::Field($options);
	}

	function clean($value)
	{
		list($error, $value) = parent::clean($value);
		if (!is_null($error)) {
			return array($error, $value);
		}
		if (is_string($value)){ $value = strtolower($value); }
		if (is_string($value) && in_array($value,array('false','off','no','n','f'))) {
			return array(null, false);
		}
		return array(null, (bool)$value);
	}
}
