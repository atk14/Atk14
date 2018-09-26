<?php
/**
 * Smarty block plugin.
 *
 * Replaces HTML element by another content. HTML element is found by its id, class or by css selector.
 * Uses jQuery.
 *
 * <code>
 * {replace_html id="login_div"}You are logged in as <em>admin</em>.{/replace_html}
 * {replace_html class="login"}You are logged in as <em>admin</em>.{/replace_html}
 * {replace_html selector="login_form div"}You are logged in as <em>admin</em>.{/replace_html}
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
 * - class
 * - selector
 *
 * @param array $params
 * @param string $content
 */
function smarty_block_replace_html($params, $content, $template, &$repeat){
	if($repeat){ return false; }

	$params += array(
		"id" => "",
		"class" => "",
		"selector" => ""
	);

	if($params["id"]){
		$selector = "#$params[id]";
	}elseif($params["class"]){
		$selector = ".$params[class]";
	}else{
		$selector = $params["selector"];
	}
	
	$content = Atk14Utils::EscapeForJavascript($content);
	$selector = Atk14Utils::EscapeForJavascript($selector);

	return "$(\"$selector\").html(\"$content\");";
}
