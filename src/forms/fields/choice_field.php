<?php
/**
 * Field for choices.
 *
 * @filesource
 */

/**
 * Field for choices.
 *
 * @package Atk14\Forms
 */
class ChoiceField extends Field
{
	/**
	 * List of choices
	 *
	 * @var array
	 */
	protected $choices = array();

	/**
	 * List of disabled choices
	 *
	 * @var array
	 */
	protected $disabled_choices = array();

	/**
	 * Constructor
	 *
	 * This class extends $options with the following. For default options see {@link Field} class.
	 *
	 * ```
	 * new ChoiceField([
	 *  "choices" => ["" => "-- select class --", "a" => "Class A", "b" => "Class B", "c" => "Class C"],
	 *  "disabled_choices" => ["c"]
	 * ]);
	 * ```
	 *
	 * @param array $options 
	 * - choices array list of choices to render as list of options in select input field
	 * - disabled_choices array list of disabled choices
	 * @see Field::__construct()
	 */
	function __construct($options=array()) {
		if (!isset($options["widget"])) {
			$options["widget"] = new Select();
		}
		parent::__construct($options);
		$this->update_messages(array(
			'invalid_choice' => _('Select a valid choice. That choice is not one of the available choices.'),
			'disabled_choice' => _('This choice cannot be selected.'),
			'required' => _('Please, choose the right option.'),
		));
		if (isset($options['choices'])) {
			$this->set_choices($options['choices']);
		}
		if (isset($options['disabled_choices'])) {
			$this->set_disabled_choices($options['disabled_choices']);
		}
	}

	/**
	 * Get list of input choices.
	 *
	 * {@internal V djangu zrealizovano pomoci property.}}
	 * @return array
	 */
	function get_choices() {
		return $this->choices;
	}

	/**
	 * Sets list of choices for option input field.
	 *
	 * The parameter $value has the following form
	 * ```
	 * array(
	 * 	"value_1" => "First option label",
	 * 	"value_2" => "Second option label"
	 * )
	 * ```
	 *
	 *
	 * {@internal V djangu zrealizovano pomoci property (v pripade nastaveni saha i na widget) }}
	 *
	 * @param array $value
	 */
	function set_choices($value) {
		$this->choices = $value;
		$this->widget->choices = $value;
	}

	function set_disabled_choices($disabled_choices) {
		$this->disabled_choices = $disabled_choices;
		$this->widget->disabled_choices = $disabled_choices;
	}

	function clean($value)
	{
		list($error, $value) = parent::clean($value);
		if (!is_null($error)) {
			return array($error, null);
		}
		if ($this->check_empty_value($value)) {
			$value = '';
		}
		if ($value === '') {
			return array(null, null);
			//return array(null, $value);
		}
		$value = (string)$value;
		// zkontrolujeme, jestli je zadana hodnota v poli ocekavanych hodnot
		$found = false;
		foreach ($this->get_choices() as $k => $v) {
			if ((string)$k === $value) {
				$found = true;
				break;
			}
		}
		if (!$found) {
			// neni!
			return array($this->messages['invalid_choice'], null);
		}
		$disabled_choices = array_map(function($item){ return (string)$item; },$this->disabled_choices);
		if (in_array($value,$disabled_choices)) {
			return array($this->messages['disabled_choice'], null);
		}
		return array(null, (string)$value);
	}
}
