<?php

/**
 * Field for validation of IP address
 *
 * Extends {@link RegexField}
 *
 * @package Atk14
 * @subpackage Forms
 */
class IpAddressField extends RegexField
{
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
	function clean($value){
		return parent::clean($value);
	}
}
