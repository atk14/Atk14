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

	if($nonce = $ATK14_GLOBAL->getCspNonce()){
		$params += array(
			"nonce" => $nonce,
		);
	}

	$attrs = Atk14Utils::JoinAttributes($params);

	return "<style$attrs>$content</style>";
}
