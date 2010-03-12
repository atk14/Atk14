<?
if(!function_exists("smarty_block_a_remote")){
	require_once(dirname(__FILE__)."/block.a_remote.php");
}
function smarty_block_a_destroy($params, $content, &$smarty, &$repeat){
	$params["action"] = "destroy";
	$params["_method"] = "post";
	$params["_class"] = isset($params["_class"]) ? $params["_class"]." confirm" : "confirm";
	return smarty_block_a_remote($params,$content,$smarty,$repeat);
}
