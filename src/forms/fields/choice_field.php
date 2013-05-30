<?php
/**
 * Field for choices.
 *
 * @package Atk14
 * @subpackage Forms
 */
class ChoiceField extends Field
{
	var $choices = array();

	function __construct($options=array())
	{
		if (!isset($this->widget)) {
			$this->widget = new Select();
		}
		parent::__construct($options);
		$this->update_messages(array(
			'invalid_choice' => _('Select a valid choice. That choice is not one of the available choices.'),
			'required' => _('Please, choose the right option.'),
		));
		if (isset($options['choices'])) {
			$this->set_choices($options['choices']);
		}
	}

	/**
	* Vrati seznam voleb.
	*
	* NOTE: V djangu zrealizovano pomoci property.
	*/
	function get_choices()
	{
		return $this->choices;
	}

	/**
	* Nastavi seznam voleb.
	*
	* NOTE: V djangu zrealizovano pomoci property (v pripade nastaveni
	* saha i na widget)
	*/
	function set_choices($value)
	{
		$this->choices = $value;
		$this->widget->choices = $value;
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
		return array(null, (string)$value);
	}
}
