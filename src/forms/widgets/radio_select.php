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
	var $input_attrs;
	var $label_attrs;
	var $wrap_attrs;


	function __construct($options = array()){
		$options += array(
			"convert_html_special_chars" => true,
			"input_attrs" => array(),
			"label_attrs" => array(),
			"wrap_attrs" => array(),
		);
		$this->convert_html_special_chars = $options["convert_html_special_chars"];
		$this->input_attrs = $options["input_attrs"];
		$this->label_attrs = $options["label_attrs"];
		$this->wrap_attrs = $options["wrap_attrs"];
		parent::__construct($options);
	}

	function _renderer($name, $value, $attrs, $choices)
	{
		$output = array();

		$output[] = ($this->bootstrap4 || $this->bootstrap5) ? '<ul class="list list--radios">' : '<ul class="radios">';

		$i = 0;
		foreach ($choices as $k => $v) {
			$final_attrs = $this->build_attrs($this->input_attrs,$attrs);

			$ch = new RadioInput($name, $value, $final_attrs, array($k=>$v), $i,array("convert_html_special_chars" => $this->convert_html_special_chars, "label_attrs" => $this->label_attrs, "wrap_attrs" => $this->wrap_attrs, "bootstrap3" => $this->bootstrap3, "bootstrap4" => $this->bootstrap4, "bootstrap5" => $this->bootstrap5));
			if($this->bootstrap4 || $this->bootstrap5){
				$output[] = '<li class="list__item">';
				$output[] = $ch->render();
				$output[] = '</li>';
			}else{
				$output[] = "<li>";
				$output[] = $ch->render();
				$output[] = "</li>";
			}
			$i++;
		}

		$output[] = '</ul>';

		return implode("\n", $output);
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
