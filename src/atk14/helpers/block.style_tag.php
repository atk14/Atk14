<?php
/**
 *
 * Example
 * ```
 *	{style_tag}
 *		h1 { color: red; }
 *	{style_tag}
 * ```
 */
function smarty_block_style_tag($params, $content, $template, &$repeat){
	global $ATK14_GLOBAL;

	if($repeat){ return; }

	if(CSP_NONCE){
		$params += array(
			"nonce" => CSP_NONCE,
		);
	}

	$attrs = Atk14Utils::JoinAttributes($params);

	return "<style$attrs>$content</style>";
}
