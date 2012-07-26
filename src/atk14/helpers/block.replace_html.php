<?php
/**
 * Smarty block plugin.
 *
 * Replaces HTML element by another content. HTML element is found by its id or by css selector.
 * Uses jQuery.
 *
 * <code>
 * {replace_html id="login_div"}Jste prihlasen jako <em>admin</em>.{/replace_html}
 * {replace_html selector="login_form div"}Jste prihlasen jako <em>admin</em>.{/replace_html}
 * </code>
 *
 * @package Atk14
 * @subpackage Helpers
 */
/**
 * Smarty block function
 *
 * Reserved parameters:
 * - id
 * - selector
 *
 * @param array $params
 * @param string $content
 */
function smarty_block_replace_html($params, $content, $template, &$repeat){
	if($repeat){ return false; }
	$content = Atk14Utils::EscapeForJavascript($content);
	
	$selector = isset($params["id"]) ? "#$params[id]" : $params["selector"];

	return "$('$selector').html(\"$content\");";
}
