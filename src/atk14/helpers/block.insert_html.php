<?php
/**
 * Smarty block plugin
 *
 * <code>
 * {insert_html id="content_div" position="bottom"}<em>Tento text bude pridan.</em>{/insert_html}
 * {insert_html selector="form div" position="bottom"}<em>Tento text bude pridan.</em>{/insert_html}
 * </code>
 *
 *
 * Mozne hodnoty position:
 * - prepend pred
 * - append (default)
 * - before pred
 * - after
 *
 * @package Atk14
 * @subpackage Helpers
 */

/**
 * Smarty block function.
 *
 */
function smarty_block_insert_html($params, $content, $template, &$repeat){
	if($repeat){ return; }
	$content = Atk14Utils::EscapeForJavascript($content);

	$params = array_merge(array(
		"position" => "bottom"
	),$params);

	switch($params["position"]){
		case "append":
		case "prepend":
		case "before":
		case "after":
			$method = $params["position"];
			break;
		default:
			$method = "append";
	}

	$selector = isset($params["id"]) ? "#$params[id]" : $params["selector"];

	return "$(\"$selector\").$method(\"$content\");";
}
