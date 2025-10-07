<?php
/**
 * Smarty plugin for inserting javascript code.
 *
 * This is an alias for the block helper script_tag.
 *
 * {javascript_tag} plugin inserts javascript code enclosed by appropriate tag
 *
 * Basic usage:
 * <code>
 * {javascript_tag}
 *		alert('Hello World')
 *	{/javascript_tag}
 * </code>
 * @package Atk14
 * @subpackage Helpers
 * @author Jaromir Tomek
 *
 */

/**
 *
 * @param array $params
 * @param string $content
 */
function smarty_block_javascript_tag($params, $content, $template, &$repeat){
	return smarty_block_script_tag ($params, $content, $template, $repeat);
}

require_once(__DIR__ . "/block.script_tag.php");
// Atk14Require::Helper("block.script_tag");
