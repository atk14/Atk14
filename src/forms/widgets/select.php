<?php
/**
 * Widget for select input field.
 *
 * Outputs field of this type:
 * <code>
 * <select>
 *   <option value="1">jedna</option>
 * </select>
 * </code>
 *
 * @package Atk14
 * @subpackage Forms
 */
class Select extends Widget
{
	var $input_type = "select";

	function __construct($options=array())
	{
		$options = forms_array_merge(array('attrs'=>null, 'choices'=>array()), $options);
		parent::__construct($options);
		$this->choices = $options['choices'];
	}

	function render($name, $value, $options=array())
	{
		if(!isset($options["attrs"])){ $options["attrs"] = array(); }
		$options["attrs"] = forms_array_merge($this->attrs,$options["attrs"]);
		$options = forms_array_merge(array('attrs'=>null, 'choices'=>array()), $options);
		if (is_null($value)) {
			$value = '';
		}
		$final_attrs = $this->build_attrs(array(
			'name' => $name,
			'class' => 'form-control' // form-control is there for Bootstrap
		),$options['attrs']);
		$output = array('<select'.flatatt($final_attrs).'>');
		// NOTE: puvodne jsem tu mel array_merge, ale ten nejde pouzit
		// protoze se chova nehezky k indexum typu integer a string
		// ('1' a 1 jsou pro nej 2 ruzne veci a v tomto KONKRETNIM miste to vadi,
		// protoze z hlediska hodnot do formularovych prvku se integer prevadi 
		// na string
		$choices = my_array_merge(array($this->choices, $options['choices']));

		foreach ($choices as $option_value => $option_label) {
			if ((string)$option_value === (string)$value) { // yarri: tady pridavam 3. rovnitko: jinak bylo "" to same jako "0"
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
}
