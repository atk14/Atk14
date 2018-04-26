<?php
/**
 * Smarty plugin that generates link for destroy action.
 *
 * The generated link will lead to destroy action. Thanks to 'post' class the link will be handled with javascript and request sent as remote with POST method.
 *
 * @package Atk14\Helpers
 */


/**
 * Smarty plugin that generates link for destroy action.
 *
 * The generated link will lead to destroy action. Thanks to 'post' class the link will be handled with javascript and request sent as remote with POST method.
 *
 * Usage:
 * ```
 * {a_destroy id=$book}Delete the book entry{/a_destroy}
 *
 * {* When the default action is not enough... *}
 * {a_destroy action="books/destroy" id=$book}Delete the book entry{/a_destroy}
 *
 * {* A custom confirmation message *}
 * {a_destroy id=$book _confirm="Are you sure to delete this book?"}Delete the book entry{/a_destroy}
 *
 * {* In special cases you might not want to use XHR requests for deletions *}
 * {a_destroy id=$book _xhr=false}Delete the book entry{/a_destroy}
 * ```
 *
 * @param string $content Content of the {a_destroy}{/a_destroy} block.
 * @param array $params params
 * @param Smarty_Internal_Template $template
 * @param boolean &$repeat  repeat flag
 * @uses smarty_block_a_remote
 */
function smarty_block_a_destroy($params, $content, $template, &$repeat){
	if($repeat){ return; }
	$smarty = atk14_get_smarty_from_template($template);

	$params = array_merge(array(
		"action" => "destroy",
		"_confirm" => _("Are you sure?"),
		"_method" => "post",
		"_xhr" => true,
	),$params);

	$xhr = $params["_xhr"];
	unset($params["_xhr"]);

	$xhr = is_string($xhr) ? String4::ToObject($xhr)->toBoolean() : $xhr;

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

	if($xhr){
		Atk14Require::Helper("block.a_remote");
		return smarty_block_a_remote($params,$content,$template,$repeat);
	}

	Atk14Require::Helper("block.a");
	return smarty_block_a($params,$content,$template,$repeat);
}
