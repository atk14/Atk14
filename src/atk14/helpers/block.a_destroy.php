<?php
/**
 * Smarty plugin that generates link for destroy action.
 *
 * The generated link will lead to destroy action. Thanks to 'post' class the link will be handled with javascript and request sent as remote with POST method.
 *
 * @package Atk14\Helpers
 */

if(!function_exists("smarty_block_a_remote")){
	require_once(dirname(__FILE__)."/block.a_remote.php");
}

/**
 * Smarty plugin that generates link for destroy action.
 *
 * The generated link will lead to destroy action. Thanks to 'post' class the link will be handled with javascript and request sent as remote with POST method.
 *
 * @param string $content Content of the {a_destroy}{/a_destroy} block.
 * @param array $params params
 * @param Smarty_Internal_Template $template
 * @param boolean &$repeat  repeat flag
 * @uses smarty_block_a_remote
 *
 *
 */
function smarty_block_a_destroy($params, $content, $template, &$repeat){
	if($repeat){ return; }
	$smarty = atk14_get_smarty_from_template($template);

	$params = array_merge(array(
		"action" => "destroy",
		"_confirm" => _("Are you sure?"),
		"_method" => "post",
	),$params);

	// building attribute data-destroying_object
	$object_id = is_object($params["id"]) ? $params["id"]->getId() : $params["id"];
	if(is_object($params["id"])){
		$object_class = String4::ToObject(get_class($params["id"]))->underscore()->toString();
	}else{
		$object_class = String4::ToObject($smarty->getTemplateVars("controller"))->singularize()->toString();
	}
	$destroying_object = array(
		"class" => $object_class,
		"id" => $object_id
	);
	$params["_data___destroying_object"] = json_encode($destroying_object);

	return smarty_block_a_remote($params,$content,$template,$repeat);
}
