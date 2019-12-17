<?php
/**
 * Field for validation of IP address
 *
 * Extends {@link RegexField}
 */

/**
 * Field for validation of IP address
 *
 * Extends {@link RegexField}
 *
 * Example
 * ```
 * $this->add_field("server_ip", new IpAddressField([
 * 	"label" => "Server address",
 * ]))
 * ```
 *
 * @package Atk14
 * @subpackage Forms
 */
class IpAddressField extends RegexField
{
	/**
	 * Constructor
	 *
	 * For more options see {@see RegexField::__construct()}
	 *
	 * @param array $options
	 * - **null_empty_output** -
	 * - **ipv4_only** -
	 * - **ipv6_only** -
	 */
	function __construct($options = array()){
		$options = array_merge(array(
			"null_empty_output" => true,
			"ipv4_only" => false,
			"ipv6_only" => false,
		),$options);
		$re_ipv4 = '(25[0-5]|2[0-4]\d|[0-1]?\d?\d)(\.(25[0-5]|2[0-4]\d|[0-1]?\d?\d)){3}';
		$re_ipv6 = '[0-9a-fA-F]{0,4}(:[0-9a-fA-F]{0,4}){1,8}'; // TODO: velmi nedokonale!
		$re_exp = "/^(($re_ipv4)|($re_ipv6))$/";
		$options["ipv4_only"] && ($re_exp = "/^$re_ipv4$/");
		$options["ipv6_only"] && ($re_exp = "/^$re_ipv6$/");
		parent::__construct($re_exp,$options);
		$this->update_messages(array(
			"invalid" => _("Enter a valid IP address."),
		));
	}

	/**
	 * Method performing validation.
	 *
	 * @param string $value
	 * @note Perhaps it is not necesary since it does not do other validation than the one used in parent class.
	 */
	function clean($value){
		return parent::clean($value);
	}
}
