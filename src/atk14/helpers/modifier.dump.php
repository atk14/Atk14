<?php
/**
 * {$object|dump}
 * {$object|dump nofilter}
 * {!$object|dump}
 */
function smarty_modifier_dump($var){
	return smarty_function_dump(array("var" => $var),null);
}

if(!function_exists("smarty_function_dump")){
	require_once(dirname(__FILE__)."/function.dump.php");
}

