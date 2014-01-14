<?php
/**
 * Smarty block plugin.
 *
 * Displays content of parameter content or content of the block when the parameter is empty.
 *
 * <code>
 *  Jmeno objednavky: {unless content=$title}{t}nepojmenovana{/t}{/unless}
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
 *  <li>content - name of tested variable</li>
 * </ul>
 *
 * @param array $params
 * @param string $content
 * @param string $template smarty specific
 * @param string $repeat smarty specific
 * @package Atk14
 * @subpackage Helpers
 */
function smarty_block_unless($params,$content,$template,&$repeat){
	if($repeat){ return; }
	if(isset($params["content"]) && strlen($params["content"])>0){
		return $params["content"];
	}
	return $content;
}
