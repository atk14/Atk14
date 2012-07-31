<?php
/**
 * Camelize given string.
 * 
 * <code>
 *		{assign var=greeting value="hello_world"}
 *		{$greeting|camelize} {* HelloWorld *}
 *		{$greeting|camelize:"lower"} {* helloWorld *}
 * </code>
 */
function smarty_modifier_camelize($str,$lower_or_upper = "upper"){
	$str = new String($str,array());
	return (string)$str->camelize(array(
		"lower" => $lower_or_upper=="lower"
	));
}
