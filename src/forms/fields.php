<?php
/**
 * This file contains Base classes associated with input form fields classes.
 *
 * @package Atk14
 * @subpackage Forms
 * @filesource
 */


/**
 * Object collecting rules for javascript validator for this field.
 *
 * @package Atk14
 * @subpackage Forms
 */
class JsValidator{
	/**
	 * Constructor
	 *
	 * @ignore
	 */
	function __construct(){
		$this->_messages = array();
		$this->_rules = array();
	}

	/**
	 * Gets error messages associated with a field.
	 *
	 * @return string[]
	 */
	function get_messages(){ return $this->_messages; }
	
	/**
	 * Associate error message with a validation method.
	 *
	 * @param string $key
	 * @param string $message
	 */
	function add_message($key,$message){ $this->_messages[$key] = $message; }

	/**
	 * Gets validation rules associated with a field.
	 *
	 * @return string[]
	 */
	function get_rules(){ return $this->_rules; }

	/**
	 *
	 * Adds a validation rule for javascript validator.
	 *
	 * @param string $rule validation method
	 * @param string $value validation rule
	 */
	function add_rule($rule,$value){ $this->_rules[$rule] = $value; }

	/**
	 * @todo explain
	 */
	function set_field_name($name){
		$this->_field_name = $name;
	}
}


/**
 * Parent class for all input fields.
 *
 * This class provides a way to render input form fields and validate entered values.
 *
 * Here is an example of basic form fields' usage:
 *
 * 	class MessageForm extends ApplicationForm {
 * 		function set_up() {
 * 			$this->add_field("message", new CharField(array(
 * 				"label" => "A word to Atk14 developers",
 * 				"widget" => new TextArea(array(
 * 					"rows" => '5',
 * 					"cols" => '40',
 * 				),
 * 			)));
 * 		}
 * 	}
 *
 *
 * This is the base class for all field types and shouldn't be used directly.
 * You should use its subclass.
 *
 * Atk14 supports following basic field classes.
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
 *
 * You can create new subclass by extending the {@link Field Field class}.
 * To create new subclass for a new field type you need at least two basic methods.
 *
 * - {@link Field::__construct() - declaration of input field. Provides information about field type and its attributes}
 * - {@link Field::clean() provides validation of entered values}
 *
 * Additionally it's usually good to use {@link Field::format_initial_data() format_initial_data method} when specifying special formatting of values.
 *
 * @package Atk14
 * @subpackage Forms
 * @abstract
 * @filesource
 */
class Field
{

	/**
	 *
	 * Widget instance.
	 *
	 * Defines how the input field is rendered (e.g., text area, select)
	 *
	 * @var Widget
	 */
	var $widget = null;

	/**
	 * Array of messages for various types.
	 *
	 * There are basic types 'required' and 'invalid' used by Atk14. They can be extended by {@link update_messages()}
	 *
	 * @var array
	 */
	var $messages = array();

	/**
	 * Constructor.
	 *
	 * Constructor of this class defines only basic set of options. These can be extended by a subclass.
	 *
	 * @param array $options Possible options
	 * <ul>
	 * <li><b>required</b> - boolean</li>
	 * <li><b>widget</b> - {@see Widget}</li>
	 * <li><b>label</b> - </li>
	 * <li><b>initial</b> - </li>
	 * <li><b>help_text</b> - </li>
	 * <li><b>hint</b> - </li>
	 * <li><b>hints</b> array of string </li>
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
				'hint'           => '', // value format hint, like "john.doe@example.com"
				'hints'					 => array(), // array("john.doe","samantha93")
				'error_messages' => null,
				'disabled'       => false,
			),
			$options
		);
		if($options["hint"] && !$options["hints"]){
			$options["hints"] = array($options["hint"]);
		}

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
		$this->hints = $options['hints'];
		$this->hint_in_placeholder = false;
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
				if(sizeof($this->hints)==1 && !preg_match('/</',$this->hints[0])/* no-html */ && !in_array("placeholder",$_attr_keys)){
					$widget->attrs["placeholder"] = $this->hints[0];
					$this->hint_in_placeholder = true;
				}
				if($this->required && !in_array("required",$_attr_keys)){
					$widget->attrs["required"] = "required";
				}
			}
		}

		$this->widget = $widget;
	}

	/**
	 * Updates whole array of messages or adds new ones as specified in $messages array.
	 *
	 * @param array $messages
	 */
	function update_messages($messages)
	{
		$this->messages = forms_array_merge(
			$this->messages,
			$messages
		);
	}

	/** 
	 * Modifies definition of error message
	 *
	 * 		$field->update_messages("invalid","This doesn't look like a reasonable value...");
	 *
	 * @param string $type
	 * @param string $message
	 */
	function update_message($type,$message){
		$this->update_messages(array($type => $message));
	}

	/**
	 * Basic field value validation.
	 *
	 * Checks if the field doesn't contain empty value when it is required. Can be overridden in a subclass.
	 *
	 * list($error,$cleaned_value) = $field->clean($raw_value); // $error may be null, a string or an array of strings; null or empty array means no error
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

	/**
	 * Returns widgets attributes.
	 *
	 * To be overridden in a subclass.
	 *
	 * @param Widget $widget
	 */
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
	 * @param string $data
	 * @todo should be defined as abstract
	 */
	function format_initial_data($data){
		return $data;
	}


	/**
	 * Javascript validation rule.
	 *
	 * @todo more explanation
	 */
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
