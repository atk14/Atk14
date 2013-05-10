<?php
// TODO: Do we really need HiddenInput?
/**
 * Widget for hidden input field.
 *
 * Outputs field of this type:
 * <code>
 * <input type="hidden" />
 * </code>
 *
 * @package Atk14
 * @subpackage Forms
 */
class HiddenInput extends Input
{
	var $input_type = 'hidden';
	var $is_hidden = true;
}

// TODO: Wtf? :)
/**
* <input type="hidden" name="pole" />
* <input type="hidden" name="pole" />
* ...
*/
/*
class MultipleHiddenInput extends HiddenInput
{
	function MultipleHiddenInput($options=array())
	{
		$options = forms_array_merge(array('attrs'=>null, 'choices'=>array()), $options);
		parent::HiddenInput($options);
		$this->choices = $options['choices'];
	}

	function render($name, $value, $options=array())
	{
		$options = forms_array_merge(array('attrs'=>null, 'choices'=>array()), $options);
		if (is_null($value)) {
			$value = array();
		}
		$final_attrs = $this->build_attrs($options['attrs'], array(
			'name' => $name,
			'type' => $this->input_type
		));
		$out = array();
		foreach ($value as $v) {
			$_attrs = forms_array_merge($final_attrs, array('value'=>(string)$v));
			$out[] = '<input'.flatatt($_attrs).' />' ;
		}
		return implode("\n", $out);
	}

	function value_from_datadict($data, $name)
	{
		# if isinstance(data, MultiValueDict):
		#     // NOTE: tohle prdim
		#     return data.getlist(name)
		# return data.get(name, None)
		if (isset($data[$name])) {
			return $data[$name];
		}
		return null;
	}
}
*/
