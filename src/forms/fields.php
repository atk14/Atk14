<?php
/**
 * Input field classes.
 *
 * Basic field types supported by Atk14.
 *
 * String based fields:
 *
 * - CharField
 * - IntegerField
 * - FloatField
 * - RegexField
 * - EmailField
 * - IpAddressField
 * - DateField
 * - DateTimeField
 * - DateTimeWithSecondsField
 *
 * Checkbox based fields:
 * - BooleanField
 *
 * Select based fields:
 *
 * - ChoiceField
 * - MultipleChoiceField
 *
 * File based fields:
 *
 * - FileField
 * - ImageField
 *
 * @package Atk14
 * @subpackage Forms
 * @filesource
 */


/**
 * Objekt pro sber pravidel pro JS validator a jedno pole.
 *
 * @package Atk14
 * @subpackage Forms
 */
class JsValidator{
	function __constructor(){
		$this->_messages = array();
		$this->_rules = array();
	}

	function get_messages(){ return $this->_messages; }
	function add_message($key,$message){ $this->_messages[$key] = $message; }

	function get_rules(){ return $this->_rules; }
	function add_rule($rule,$value){ $this->_rules[$rule] = $value; }

	function set_field_name($name){
		$this->_field_name = $name;
	}
}


/**
 * Parent class for validation of input fields.
 *
 * This is the base class for all field types and shouldn't be used directly.
 * It should be used through its descendants.
 *
 * When you develop a new validation class for new field type there are three basic methods available
 * during a lifecycle of a Field:
 * - {@link Field::Field() __constructor()} - declaration of input field. Provides information about field type and its attributes.
 * - {@link Field::format_initial_data()} provides presentation of values
 * - {@link Field::clean()} provides validation of values
 *
 * @package Atk14
 * @subpackage Forms
 * @abstract
 */
class Field
{

	/**
	 *
	 * Widget instance.
	 *
	 * Defines how the input field is rendered.
	 *
	 * @var Widget
	 */
	var $widget = null;

	/**
	 * Several messages for various states.
	 *
	 * @var array
	 */
	var $messages = array();

	/**
	 * Constructor.
	 *
	 * @param array $options Possible options
	 * <ul>
	 * <li><b>required</b> - boolean</li>
	 * <li><b>widget</b> - {@see Widget}</li>
	 * <li><b>label</b> - </li>
	 * <li><b>initial</b> - </li>
	 * <li><b>help_text</b> - </li>
	 * <li><b>hint</b> - </li>
	 * <li><b>error_messages</b> - </li>
	 * <li><b>disabled boolean</b> - </li>
	 * </ul>
	 */
	function __construct($options=array())
	{
		// default data
		$options = forms_array_merge(array(
				'required'       => true,
				'widget'         => null,
				'label'          => null,
				'initial'        => null,
				'help_text'      => '', // like "In this field you can write down your favourite numbers"
				'hint'           => '', // value format hint, like "1,3,7"
				'error_messages' => null,
				'disabled'       => false,
			),
			$options
		);
		if (!isset($this->widget)) {
			$this->widget = new TextInput();
		}
		if (!isset($this->hidden_widget)) {
			$this->hidden_widget = new HiddenInput();
		}
		$this->messages = array();
		$this->update_messages(array(
			'required' => _('This field is required.'),
			'invalid' => _('Enter a valid value.'),
		));

		// inicializace podle parametru konstruktoru
		$this->required = $options['required'];
		$this->label = $options['label'];
		$this->initial = $options['initial'];
		$this->help_text = $options['help_text'];
		$this->hint = $options['hint'];
		$this->disabled = $options['disabled'];
		if (is_null($options['widget'])) {
			$widget = $this->widget;
		} else {
			$widget = $options['widget'];
		}
		$extra_attrs = $this->widget_attrs($widget);
		if (count($extra_attrs) > 0) {
			$widget->attrs = forms_array_merge($widget->attrs, $extra_attrs);
		}

		if(FORMS_ENABLE_EXPERIMENTAL_HTML5_FEATURES){
			// this automatically adds placeholder and required to the attributes
			if(is_subclass_of($widget,"Input")){
				$_attr_keys = array_keys($widget->attrs);
				if(strlen($this->hint) && !preg_match('/</',$this->hint)/* no-html */ && !in_array("placeholder",$_attr_keys)){
					$widget->attrs["placeholder"] = $this->hint;
				}
				if($this->required && !in_array("required",$_attr_keys)){
					$widget->attrs["required"] = "required";
				}
			}
		}

		$this->widget = $widget;
	}

	/**
	* Prida do $this->messages dalsi error hlasky.
	* NOTE: muj vymysl
	*/
	function update_messages($messages)
	{
		$this->messages = forms_array_merge(
			$this->messages,
			$messages
		);
	}

	/** 
	 * <code>
	 *     $field->update_messages("invalid","This doesn't look like a reasonable value...");
	 * </code>
	 */
	function update_message($type,$message){
		$this->update_messages(array($type => $message));
	}

	/**
	 * Field value validation.
	 *
	 * Checks if the field doesn't contain empty value.
	 *
	 * @param mixed $value
	 * @return array
	 * @see check_empty_value()
	 */
	function clean($value)
	{
		if ($this->required && $this->check_empty_value($value)) {
			return array($this->messages['required'], null);
		}
		return array(null, $value);
	}

	function widget_attrs($widget)
	{
		return array();
	}

	/**
	 * Checks if the entered value is "empty".
	 *
	 * Checks for null, empty string "", empty array values.
	 *
	 * @param mixed $value
	 * @return bool true if field contains empty value
	 */
	function check_empty_value($value) {
		return
			is_null($value) ||
			(is_string($value) && $value=='') ||
			(is_array($value) && sizeof($value)==0);
	}

	/**
	 * This method provides value presentation.
	 *
	 */
	function format_initial_data($data){
		return $data;
	}

	function js_validator(){
		$js_validator = new JsValidator();

		if($this->required){
			$js_validator->add_rule("required",true);
			$js_validator->add_message("required",$this->messages["required"]);
		}

		return $js_validator;
	}
}

// Every other field class is placed in ./fields/ directory
