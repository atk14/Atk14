<?php
/**
 * Smarty block plugin
 *
 * Escapes string for use in javascript.
 * Output string is surrounded with quotes.
 *
 * <code>
 * <script type="text/javascript">
 * 	var message={jstring}Zprava pro tebe: {render partial="message"}{/jstring};
 * </script>
 * </code>
 *
 * @package Atk14
 * @subpackage Helpers
 */

/**
 * Smarty block function
 *
 * Reserved parameters:
 * <ul>
 * 	<li>escape  escaping mode
 * 	<ul>
 * 		<li>html - escapes some html code</li>
 * 	</ul>
 * 	</li>
 * </ul>
 *
 * @param array $params some options
 * @param string $content string to be escaped
 */
function smarty_block_jstring($params,$content,$template,&$repeat){
	if($repeat){ return; }
	$params = array_merge(array(
		"escsape" => ""
	),$params);
	$content = Atk14Utils::EscapeForJavascript($content);
	if($params["escape"] == "html"){
		$content = str_replace("<","&lt;",$content);
		$content = str_replace(">","&gt;",$content);
		$content = str_replace("&","&amp;",$content);
	}
	return '"'.$content.'"';
}
