<?php
/**
 * Field with multiple choices.
 *
 * @package Atk14
 * @subpackage Forms
 *
 * @internal NOTE: tohle asi v PHP nebude fachat, protoze pokud se ve formulari objevi vice poli sdilejici stejny nazev, v $_POST se objevi pouze jeden z nich (posledni)
 * @internal NOTE: v PHP to funguje, pokude se parametr ve formulare nazve takto: <select name="choices[]" multiple="multiple">... (yarri)
 */
class MultipleChoiceField extends ChoiceField
{
	function __construct($options=array())
	{
		$options += array(
			"widget" => new SelectMultiple()
		);
		parent::__construct($options);
		$this->update_messages(array(
			'invalid_choice' => _('Select a valid choice. %(value)s is not one of the available choices.'),
			'invalid_list' => _('Enter a list of values.'),
			'required' => _('Please, choose the right options.'),
		));
	}

	function clean($value)
	{
		if ($this->required && !$value) {
			return array($this->messages['required'], null);
		}
		elseif (!$this->required && !$value) {
			return array(null, array());
		}
		if (!is_array($value)) {
			return array($this->messages['invalid_list'], null);
		}

		$new_value = array();
		foreach ($value as $k => $val) {
			$new_value[$k] = (string)$val;
		}
		$valid_values = array();
		foreach ($this->get_choices() as $k => $v) {
			if (!in_array((string)$k, $valid_values)) {
				$valid_values[] = (string)$k;
			}
		}

		foreach ($new_value as $val) {
			if (!in_array($val, $valid_values)) {
				return array(EasyReplace($this->messages['invalid_choice'], array('%(value)s'=> h($val))), null);
			}
		}
		return array(null, $new_value);
	}
}
