<?php
/**
 * Field for email values.
 *
 * @package Atk14
 * @subpackage Forms
 */
class EmailField extends RegexField
{
	function __construct($options=array())
	{
		$options = array_merge(array(
			"null_empty_output" => true,
			"widget" => new EmailInput(),
			"initial" => "@",
		),$options);
		// NOTE: email_pattern je v Djangu slozen ze tri casti: dot-atom, quoted-string, domain
		$email_pattern = "/(^[-!#$%&'*+\\/=?^_`{}|~0-9A-Z]+(\.[-!#$%&'*+\\/=?^_`{}|~0-9A-Z]+)*".'|^"([\001-\010\013\014\016-\037!#-\[\]-\177]|\\[\001-011\013\014\016-\177])*"'.')@(?:[A-Z0-9-]+\.)+[A-Z]{2,14}$/i';
		parent::__construct($email_pattern, $options);
		$this->update_messages(array(
			'invalid' => _('Enter a valid e-mail address.'),
		));
	}

	function clean($value)
	{
		$value = trim($value);
		if($value==="@"){ $value = ""; }

		list($error, $value) = parent::clean($value);
		if (!is_null($error)) {
			return array($error, null);
		}
		if ($value == '') {
			return array(null, $value);
		}
		if (!preg_match($this->regex, $value)) {
			return array($this->messages['invalid'], null);
		}
		return array(null, (string)$value);
	}

	function js_validator(){
		$js_validator = parent::js_validator();
		$js_validator->add_rule("email",true);
		$js_validator->add_message("email",$this->messages["invalid"]);
		return $js_validator;
	}
}
