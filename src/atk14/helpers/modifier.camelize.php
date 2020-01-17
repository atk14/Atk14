<?php
/**
 * Smarty modifier plugin
 *
 * @package Atk14\Helpers
 */

/**
 * Camelize given string.
 *
 * ```
 * {assign var=greeting value="hello_world"}
 * {$greeting|camelize} {* HelloWorld *}
 * {$greeting|camelize:"lower"} {* helloWorld *}
 * ```
 * @param string $str string
 * @param string $first_char_lower_or_upper
 * - upper - default
 * - lower
 */
function smarty_modifier_camelize($str,$first_char_lower_or_upper = "upper"){
	$str = new String4($str);
	return (string)$str->camelize(array(
		"lower" => $first_char_lower_or_upper=="lower"
	));
}
