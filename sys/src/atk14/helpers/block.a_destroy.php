<?php
/**
 * Smarty plugin that generates link for destroy action.
 *
 * The generated link will lead to destroy action. Thanks to 'post' class the link will be handled with javascript and request sent as remote with POST method.
 *
 * @package Atk14
 * @subpackage Helpers
 */

/**
 * @param string $content Content of the {a_destroy}{/a_destroy} block.
 * @param array $params params
 * @uses smarty_block_a_remote
 *
 *
 */
if(!function_exists("smarty_block_a_remote")){
	require_once(dirname(__FILE__)."/block.a_remote.php");
}
function smarty_block_a_destroy($params, $content, &$smarty, &$repeat){
	$params["action"] = "destroy";
	$params["_method"] = "post";
	$params["_confirm"] = _("Are you sure?");
	$params["_class"] = isset($params["_class"]) ? $params["_class"]." confirm" : "confirm";
	return smarty_block_a_remote($params,$content,$smarty,$repeat);
}
