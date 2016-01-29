<?php
/**
 * Smarty plugin
 *
 * @package Atk14\Helpers
 */

/**
 * Outputs php array as json string
 *
 * Example
 * ```
 * {to_json var=$data}
 * ```
 *
 * @param array $var php array
 * @return string json represented as string
 */
function smarty_function_to_json($params,$template){
	// TODO: if $params["var"] is an object, the method toJson() should be called (if such method exists)
	$out = json_encode($params["var"]);
	return $out;
}
