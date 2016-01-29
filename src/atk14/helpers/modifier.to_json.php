<?php
/**
 * Smarty modifier plugin
 *
 * @package Atk14\Helpers
 */

/**
 * Outputs php array as json string
 *
 * Example
 * ```
 * {$data|@to_json}
 * ```
 *
 * @param array $var php array
 * @return string json represented as string
 */
function smarty_modifier_to_json($var){
	// TODO: if $var is an object, the method toJson() should be called (if such method exists)
	$out = json_encode($var);
	return $out;
}
