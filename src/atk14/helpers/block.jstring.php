<?php
/**
 * Smarty block plugin
 *
 * @package Atk14\Helpers
 */

/**
 * Smarty block plugin
 *
 * Escapes string for use in javascript.
 * Output string is surrounded with quotes.
 *
 * ```
 * <script type="text/javascript">
 *	var message={jstring}Zprava pro tebe: {render partial="message"}{/jstring};
 * </script>
 * ```
 *
 * ```
 *	json = {
 *		"content": {jstring escape=json}{render partial="content"}{/jstring}
 *	}
 * ```
 *
 * @param array $params parameters
 * - escape string escaping mode
 *  - html escape some html code
 *  - json escape string for json
 * @param string $content string to be escaped
 * @param Smarty_Internal_Template $template
 * @param boolean &$repeat repeat flag
 *
 * @return string escaped string
 */
function smarty_block_jstring($params,$content,$template,&$repeat){
	if($repeat){ return; }
	$params = array_merge(array(
		"escape" => ""
	),$params);
	$content = Atk14Utils::EscapeForJavascript($content);
	if($params["escape"] == "html"){
		$content = str_replace("<","&lt;",$content);
		$content = str_replace(">","&gt;",$content);
		$content = str_replace("&","&amp;",$content);
	}
	if($params["escape"] == "json"){
		// "<scr" + "ipt>" -> "<script>"
		$content = str_replace('" + "','',$content);
	}

	return '"'.$content.'"';
}
