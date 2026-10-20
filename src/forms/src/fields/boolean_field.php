<?php
/**
 * Field for boolean values.
 *
 * @package Atk14
 * @subpackage Forms
 * @filesource
 */

/**
 * Field for boolean values.
 *
 * Example setup of a form with single BooleanField
 *
 * 		class SetupForm extends ApplicationForm {
 * 			function set_up() {
 * 				$this->add_field("newsletter", new BooleanField(array(
 * 					"label" => "I want to receive Newsletters about Atk14"
 * 					"initial" => true,
 * 				));
 * 			}
 * 		}
 *
 *
 * @package Atk14
 * @subpackage Forms
 * @filesource
 */
class BooleanField extends Field
{
	/**
	 * Constructor
	 *
	 * @param array $options see {@link Field} class
	 */
	function __construct($options=array())
	{
		$options = array_merge(array(
			"widget" => new CheckboxInput(),
		),$options);
		parent::__construct($options);
	}

	/**
	 * Method to validate value from the input.
	 *
	 * @param mixed $value
	 * @return array array with validated value or an error message
	 */
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
