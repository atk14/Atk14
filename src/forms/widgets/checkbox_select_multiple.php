<?php
/**
 * Renders checkboxes as unordered list.
 *
 * Each value in $choices renders as <li /> item in <ul /> list.
 *
 * @package Atk14\Forms
 */
class CheckboxSelectMultiple extends SelectMultiple
{
	var $input_type = "select";

	/**
	 * @param array $options
	 * - **escape_labels** - escaping html in checkbox labels [default: true]
	 *
	 */
	function __construct($options = array())
	{
		$options += array(
			"escape_labels" => true,
			"input_attrs" => array(),
			"label_attrs" => array(),
			"wrap_attrs" => array(),
			"bootstrap4" => FORMS_MARKUP_TUNED_FOR_BOOTSTRAP4,
		);
		$options += array(
			"bootstrap4_customized" => $options["bootstrap4"] // whether or not add classes for checkbox customization in bootstrap4 (https://www.w3schools.com/bootstrap4/bootstrap_forms_custom.asp)
		);

		if($options["bootstrap4"]){
			$options["input_attrs"] += array(
				"class" => $options["bootstrap4_customized"] ? "form-check-input custom-control-input" : "form-check-input",
			);
			$options["label_attrs"] += array(
				"class" => $options["bootstrap4_customized"] ? "form-check-label custom-control-label" : "form-check-label",
			);
			$options["wrap_attrs"] += array(
				"class" => $options["bootstrap4_customized"] ? "form-check custom-control custom-checkbox" : "form-check",
			);
		}

		$this->escape_labels = $options["escape_labels"];
		$this->bootstrap4 = $options["bootstrap4"];
		$this->bootstrap4_customized = $options["bootstrap4_customized"];
		$this->input_attrs = $options["input_attrs"];
		$this->label_attrs = $options["label_attrs"];
		$this->wrap_attrs = $options["wrap_attrs"];
		parent::__construct($options);
	}

	function my_check_test($value)
	{
		return in_array($value, $this->_my_str_values);
	}

	function render($name, $value, $options=array())
	{
		$options = forms_array_merge(array('attrs'=>null, 'choices'=>array()), $options);
		if (is_null($value) || $value==="" || !is_array($value)) {
			$value = array();
		}
		$has_id = is_array($options['attrs']) && isset($options['attrs']['id']);
		$final_attrs = $this->build_attrs($this->input_attrs,$options['attrs']);
		$output = array();

		$output[] = $this->bootstrap4 ? '<ul class="list list--checkboxes">' : '<ul class="checkboxes">';

		$choices = my_array_merge(array($this->choices, $options['choices']));
		$str_values = array();
		foreach ($value as $v) {
			if (!in_array((string)$v, $str_values)) {
				$str_values[] = (string)$v;
			}
		}
		$this->_my_str_values = $str_values;

		$i = 0;
		foreach ($choices as $option_value => $option_label) {
			$label_attrs = $this->label_attrs;
			if ($has_id) {
				$final_attrs['id'] = $options['attrs']['id'].'_'.$i;
				$label_attrs["for"] = $final_attrs['id'];
			}
			$cb = new CheckboxInput(array('attrs'=>$final_attrs, 'check_test'=>array($this, 'my_check_test'), 'bootstrap4' => $this->bootstrap4));
			$option_value = (string)$option_value;
			$rendered_cb = $cb->render("{$name}[]", $option_value);
			$label = ($this->escape_labels ? forms_htmlspecialchars($option_label) : $option_label);
			$markup = $this->bootstrap4 ? '<div%wrap_attrs%>%checkbox% <label%label_attrs%>%label%</label></div>' : '<label class="control-label">%checkbox% %label%</label>';
			$output[] = $this->bootstrap4 ? '<li class="list__item">' : '<li class="checkbox">';
			$output[] = strtr($markup,array(
				"%checkbox%" => $rendered_cb,
				"%wrap_attrs%" => flatatt($this->wrap_attrs),
				"%label_attrs%" => flatatt($label_attrs),
				"%label%" => $label
			));
			$output[] = '</li>';
			$i++;
		}

		$output[] = '</ul>';

		return implode("\n", $output);
	}

	function id_for_label($id_)
	{
		if ($id_ && sizeof($this->choices)==1) {
			// if there are more choices than one it is an unwanted feature to toggle checkbox of the first choice by clicking label
			$id_ = $id_.'_0';
		}
		return $id_;
	}
}
