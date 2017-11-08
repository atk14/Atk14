<?php
/**
 * Widget for multiple select input field.
 *
 * Outputs field of this type:
 * ```
 * <select multiple="multiple">
 *   <option value="1">one</option>
 *   <option value="2">two</option>
 *   ...
 * </select>
 * ```
 *
 * @package Atk14\Forms
 */
class SelectMultiple extends Widget
{
	var $input_type = "select";

	/**
	 * Constructor
	 *
	 * @param array $options
	 */
	function __construct($options=array()) {
		$options = forms_array_merge(array('attrs'=>null, 'choices'=>array()), $options);
		parent::__construct($options);
		$this->choices = $options['choices'];
	}

	function render($name, $value, $options=array())
	{
		$options = forms_array_merge(array('attrs'=>null, 'choices'=>array()), $options);
		if (is_null($value)) {
			$value = array();
		}
		$final_attrs = $this->build_attrs($options['attrs'], array(
			'name' => $name.'[]',
			'class' => 'form-control' // form-control is there for Bootstrap
		));
		$output = array('<select multiple="multiple"'.flatatt($final_attrs).'>');
		$choices = my_array_merge(array($this->choices, $options['choices']));
		$str_values = my_array_merge(array($value));

		foreach ($choices as $option_value => $option_label) {
			if (in_array("$option_value", $str_values)) { // uvozovky jsou zde, protoze 0 fungovala spatne
				$selected = ' selected="selected"';
			}
			else {
				$selected = '';
			}
			$output[] = '<option value="'.forms_htmlspecialchars($option_value).'"'.$selected.'>'.forms_htmlspecialchars($option_label).'</option>';
		}
		$output[] = '</select>';
		return implode("\n", $output);
	}

	function value_from_datadict($data, $name)
	{
		if (isset($data[$name])) {
			return $data[$name];
		}
		return null;
	}
}
