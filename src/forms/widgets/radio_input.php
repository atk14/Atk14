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

	function RadioInput($name, $value, $attrs, $choice, $index)
	{
		$this->name = $name;
		$this->value = $value;
		$this->attrs = $attrs;
		$this->index = $index;
		list($this->choice_value, $this->choice_label) = each($choice);
	}

	function is_checked()
	{
		return $this->value == $this->choice_value;
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
		return '<label>'.$this->tag().' '.forms_htmlspecialchars($this->choice_label).'</label>';
	}
}
