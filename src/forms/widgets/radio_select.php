<?php
/**
 * Widget rendering select input as radio buttons.
 *
 * @package Atk14
 * @subpackage Forms
 */
/**
 * Renders radio buttons as unordered list.
 *
 * Usage in a form:
 *
 * A boolean field
 * ```
 * $this->add_field("agreement",new BooleanField(array(
 * 	"label" => "Do you agree?",
 * 	"widget" => new RadioSelect(array(
 * 		"choices" => array(
 * 			"on" => "Yes, I do",
 * 			"off" => "No, I don't"
 * 		)
 * 	))
 * )));
 * // or
 * $this->add_field("agreement",new BooleanField(array(
 * 	"label" => "Do you agree?",
 * 	"widget" => new RadioSelect(array(
 * 		"choices" => array(
 * 			"on" => "<em>Yes</em>, I do",
 * 			"off" => "<em>No</em>, I don't"
 * 		),
 *		"convert_html_special_chars" => false, // by default html special chars are being converted
 * 	))
 * )));
 * ```
 *
 * A choice field
 * ```
 * $choices = array(
 * 	"single" => "Single",
 * 	"married" => "Married",
 * 	"divorced" => "Divorced",
 * 	"widow" => "Widow, widower"
 * );
 * $this->add_field("family_status",new ChoiceField(array(
 * 	"label" => "Family Status",
 * 	"choices" => $choices,
 * 	"widget" => new RadioSelect(array(
 * 		"choices" => $choices
 * 	))
 * )));
 * ```
 */
class RadioSelect extends Select
{
	var $input_type = "radio";
	var $convert_html_special_chars = true;

	function __construct($option = array()){
		$option += array(
			"convert_html_special_chars" => true,
		);
		$this->convert_html_special_chars = $option["convert_html_special_chars"];
		unset($option["convert_html_special_chars"]);
		parent::__construct($option);
	}

	function _renderer($name, $value, $attrs, $choices)
	{
		$output = array();
		$i = 0;
		foreach ($choices as $k => $v) {
			$ch = new RadioInput($name, $value, $attrs, array($k=>$v), $i,array("convert_html_special_chars" => $this->convert_html_special_chars));
			$output[] = "<li>".$ch->render()."</li>";
			$i++;
		}
		return "<ul class=\"radios\">\n".implode("\n", $output)."\n</ul>";
	}

	function render($name, $value, $options=array())
	{
		$options = forms_array_merge(array('attrs'=>null, 'choices'=>array()), $options);
		if (is_null($value)) {
			$value = '';
		}
		$value = (string)$value;
		$final_attrs = $this->build_attrs($options['attrs']);
		$choices = my_array_merge(array($this->choices, $options['choices']));
		return $this->_renderer($name, $value, $final_attrs, $choices);
	}

	function id_for_label($id_)
	{
		if ($id_) {
			$id_ = $id_.'_0';
		}
		return $id_;
	}
}
