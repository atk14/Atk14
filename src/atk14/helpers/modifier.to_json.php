<?php
/**
 * In case that you have an array:
 * 
 * {$data|@to_json}
 */
function smarty_modifier_to_json($var){
	// TODO: if $var is an object, the method toJson() should be called (if such method exists)
	$out = json_encode($var);
	return $out;
}
