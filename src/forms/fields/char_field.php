<?php
/**
 * Field for strings.
 *
 * @package Atk14
 * @subpackage Forms
 */
class CharField extends Field
{
	function __construct($options=array())
	{
		$charset = defined('DEFAULT_CHARSET')?DEFAULT_CHARSET:'utf8';
		$options = forms_array_merge(array(
				'max_length' => null,
				'min_length' => null,
				'trim_value' => true,
				'null_empty_output' => false,
				'charset' => $charset
			),
			$options
		);
		$this->max_length = $options['max_length'];
		$this->min_length = $options['min_length'];
		$this->charset = $options['charset'];
		parent::__construct($options);
		$this->update_messages(array(
			'max_length' => _('Ensure this value has at most %max% characters (it has %length%).'),
			'min_length' => _('Ensure this value has at least %min% characters (it has %length%).'),
			'js_validator_maxlength' => _('Ensure this value has at most %max% characters.'),
			'js_validator_minlength' => _('Ensure this value has at least %min% characters.'),
			'js_validator_rangelength' => _('Ensure this value has between %min% and %max% characters.'),
			'charset' => _('Invalid byte sequence for charset %charset%.'),
		));

		$this->trim_value = $options['trim_value'];
		$this->null_empty_output = $options['null_empty_output'];
	}

	function clean($value)
	{
		if (is_array($value)) {
			$value = var_export($value, true);
		}
		$value = (string)$value;
		$this->trim_value && ($value = trim($value)); // Char by se mel defaultne trimnout; pridal yarri 2008-06-25

		list($error, $value) = parent::clean($value);
		if (!is_null($error)) {
			return array($error, null);
		}

		if ($this->check_empty_value($value)) {
			$value = $this->null_empty_output ? null : '';
			return array(null, $value);
		}

		$value_length = String4::ToObject($value,$this->charset)->length();
		if ((!is_null($this->max_length)) && ($value_length > $this->max_length)) {
			return array(EasyReplace($this->messages['max_length'], array('%max%'=>$this->max_length, '%length%'=>$value_length)), null);
		}
		if ((!is_null($this->min_length)) && ($value_length < $this->min_length)) {
			return array(EasyReplace($this->messages['min_length'], array('%min%'=>$this->min_length, '%length%'=>$value_length)), null);
		}

		if($this->charset && !mb_check_encoding($value, $this->charset)) {
			return array(EasyReplace($this->messages['charset'], array('%charset%'=>$this->charset)), null);
		}

		return array(null, (string)$value);
	}

	function widget_attrs($widget)
	{
		if (!is_null($this->max_length) && in_array(strtolower(get_class($widget)), array('textinput', 'passwordinput'))) {
			return array('maxlength' => (string)$this->max_length);
		}
		return array();
	}

	function js_validator(){
		$js_validator = parent::js_validator();

		if(isset($this->min_length) && ($this->max_length)){
			$js_validator->add_rule("rangelength",array($this->min_length,$this->max_length));
			$js_validator->add_message("rangelength",strtr($this->messages["js_validator_rangelength"],array("%min%" => $this->min_length,"%max%" => $this->max_length)));
		}elseif(isset($this->min_length)){
			$js_validator->add_rule("minlength",$this->min_length);
			$js_validator->add_message("minlength",str_replace("%min%",$this->min_length,$this->messages["js_validator_minlength"]));
		}elseif(isset($this->max_length)){
			$js_validator->add_rule("maxlength",$this->max_length);
			$js_validator->add_message("maxlength",str_replace("%max%",$this->max_length,$this->messages["js_validator_maxlength"]));
		}

		return $js_validator;
	}
}
