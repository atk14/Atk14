<?php
/**
 * Widget for radio button input field.
 *
 * @package Atk14
 * @subpackage Forms
 */
class RadioInput
{
	var $input_type = "radio";
	var $convert_html_special_chars = true;

	function __construct($name, $value, $attrs, $choice, $index, $options = array())
	{
		$options += array(
			"convert_html_special_chars" => true,
			"label_attrs" => array(),
			"wrap_attrs" => array(),

			"bootstrap4" => FORMS_MARKUP_TUNED_FOR_BOOTSTRAP4,
		);
		if($options["bootstrap4"]){
			$attrs += array(
				"class" => "form-check-input",
			);
			$options["label_attrs"] += array(
				"class" => "form-check-label",
			);
			if(isset($attrs["id"])){
				$options["label_attrs"] += array(
					"for" => $attrs["id"]."_".$index,
				);
			}
			$options["wrap_attrs"] += array(
				"class" => "form-check",
			);
		}
		$this->name = $name;
		$this->value = $value;
		$this->attrs = $attrs;
		$this->index = $index;
		$this->convert_html_special_chars = $options["convert_html_special_chars"];
		$this->label_attrs = $options["label_attrs"];
		$this->wrap_attrs = $options["wrap_attrs"];
		$this->bootstrap4 = $options["bootstrap4"];

		// A replacement for list($this->choice_value, $this->choice_label) = each($choice);
		$this->choice_value = $this->choice_label = null;
		foreach($choice as $this->choice_value => $this->choice_label){
			break;
		}
	}

	function is_checked()
	{
		return (string)$this->value === (string)$this->choice_value;
	}

	function tag()
	{
		if (isset($this->attrs['id'])) {
			$this->attrs['id'] = $this->attrs['id'].'_'.$this->index;
		}
		$final_attrs = forms_array_merge($this->attrs, array(
			'type' => $this->input_type,
			'name' => $this->name,
			'value' => $this->choice_value
		));
		if ($this->is_checked()) {
			$final_attrs['checked'] = 'checked';
		}
		return '<input'.flatatt($final_attrs).' />';
	}

	function render()
	{
		$label = $this->choice_label;
		if($this->convert_html_special_chars){
			$label = forms_htmlspecialchars($label);
		}

		if($this->bootstrap4){
			return strtr('<div%wrap_attrs%>%tag% <label%label_attrs%>%label%</label></div>',array(
				"%tag%" => $this->tag(),
				"%wrap_attrs%" => flatatt($this->wrap_attrs),
				"%label_attrs%" => flatatt($this->label_attrs),
				"%label%" => $label,
			));
		}

		return '<label'.flatatt($this->label_attrs).'>'.$this->tag().' '.$label.'</label>';
	}
}
