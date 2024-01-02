<?php
/**
 * Class for checking value from input field against regular expression.
 *
 * @package Atk14
 * @subpackage Forms
 */
/**
 * Class for checking value from input field against regular expression.
 *
 * It's basically {@link CharField}. CharField only checks if the input value contains a string but RegexField also checks the value using a regular expression.
 */
class RegexField extends CharField
{

	var $regex;

	/**
	 * Constructor
	 *
	 * @param string $regex
	 * @param array $options {@see CharField}
	 */
	function __construct($regex, $options=array())
	{
		parent::__construct($options);
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
	 *
	 * E.g. add default protocol to url field if missing (check is performed by regex like /((?:http://)?)...../
	 *
	 * ```
	 * if($catches[$1]=='')
	 *		return array(null, "http://$value");
	 * else
	 *		return array(null, $value);
	 * ```
	 *
	 * @param string recieved value
	 * @param array  array of matches from preg_match
	 * @return string modified result value
	 */
	function processResult($value, $catches)
	{
			return array(null,$value);
	}

	/**
	 * Method cheking the input value
	 *
	 * @param string $value
	 * @return array
	 */
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

	/**
	 * Return validation rules for javascript.
	 *
	 * @todo needs some explanation
	 *
	 */ 
	function js_validator()
	{
		$js_validator = parent::js_validator();
		$js_validator->add_rule("regex",$this->regex);
		$js_validator->add_message("regex",$this->messages["invalid"]);
		return $js_validator;
	}
}
