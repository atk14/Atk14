<?php
/**
 * Widget for checkbox input field.
 *
 * Outputs field of this type:
 * <code>
 * <input type="checkbox" />
 * </code>
 *
 * @package Atk14\Forms
 */
class CheckboxInput extends Widget
{
	var $input_type = "checkbox";
	var $check_test;

	function __construct($options=array())
	{
		$options += array(
			'attrs' => array(),
			'check_test' => null,

			'bootstrap4' => FORMS_MARKUP_TUNED_FOR_BOOTSTRAP4,
			'bootstrap5' => FORMS_MARKUP_TUNED_FOR_BOOTSTRAP5,
		);

		if($options["bootstrap4"] || $options["bootstrap5"]){
			$options["attrs"] += array(
				"class" => "form-check-input",
			);
		}

		parent::__construct($options);
		$this->check_test = $options['check_test'];
	}

	function render($name, $value, $options=array())
	{
		$options = forms_array_merge(array('attrs'=>null), $options);
		$final_attrs = $this->build_attrs($options['attrs'], array(
			'type' => $this->input_type, 
			'name' => $name)
		);
		if ((!is_null($this->check_test)) && ((is_array($this->check_test) && method_exists($this->check_test[0], $this->check_test[1])) || (function_exists($this->check_test)))) {
			$fn = $this->check_test;
			$result = call_user_func($fn, $value);
		}
		else {
			$result = (bool)$value;
		}
		if ($result) {
			$final_attrs['checked'] = 'checked';
		}
		if (!(is_bool($value) || (is_string($value) && ($value == '')) || is_null($value))) {
			$final_attrs['value'] = $value;
		}
		return '<input'.flatatt($final_attrs).' />';
	}

	function value_from_datadict($data, $name)
	{
		if (!isset($data[$name])) {
			// pokud hodnota v poli chybi, vratime false
			// formulare s nezaskrnutymi checkboxy se po odeslani formiku v datech neobjevuji
			return false;
		}
		return parent::value_from_datadict($data, $name);
	}
}
