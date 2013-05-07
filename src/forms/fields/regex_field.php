<?php
/**
 * Field for strings that must suit to regular expressions.
 *
 * @package Atk14
 * @subpackage Forms
 */
class RegexField extends CharField
{
	function RegexField($regex, $options=array())
	{
		parent::CharField($options);
		$this->update_messages(array(
			'max_length' => _('Ensure this value has at most %max% characters (it has %length%).'),
			'min_length' => _('Ensure this value has at least %min% characters (it has %length%).'),
		));
		if (isset($options['error_message'])) {
			$this->update_messages(array(
				'invalid' => $options['error_message']
			));
		}
		$this->regex = $regex;
	}

	/**
	 * Can be used to postprocess the matched value using results from preg_match
	 * @param string recieved value
	 * @param array  array of matches from preg_match
	 * @return string modified result value
	 *
	 * E.g. add default protocol to url field if missing (check is performed by regex like /((?:http://)?)...../
	 *
	 * if($catches[$1]=='')
	 *		return array(null, "http://$value");
	 * else
	 *		return array(null, $value);
	 */
	function processResult($value, $catches)
	{
			return array(null,$value);
	}

	function clean($value)
	{
		list($error, $value) = parent::clean($value);
		if (!is_null($error)) {
			return array($error, null);
		}
		if ($value == '') {
			return array(null, $value);
		}
		if (!preg_match($this->regex, $value, $catches)) {
			return array($this->messages['invalid'], null);
		}
		return $this->processResult((string)$value, $catches);
	}

	function js_validator()
	{
		$js_validator = parent::js_validator();
		$js_validator->add_rule("regex",$this->regex);
		$js_validator->add_message("regex",$this->messages["invalid"]);
		return $js_validator;
	}
}
