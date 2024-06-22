<?php
/**
 * Smarty block plugin
 *
 * Plugin that removes white spaces from beginning and end of a string.
 *
 * Example:
 * <code>
 * <h2>{trim} Welcome Beda {/trim}</h2>
 * </code>
 *
 * outputs:
 * <code><h2>Welcome Beda</h2></code>
 *
 * @package Atk14
 * @subpackage Helpers
 */

/**
 * Smarty block function.
 *
 * Removes white spaces from beginning and end of a string. Does not recognize any parameters.
 */
function smarty_block_trim($params, $content, $template, &$repeat){
	if($repeat){ return; }

	$params += [
		"each_line" => false,
	];

	$content = (string)$content;

	if($params["each_line"]){
		$content = explode("\n",$content);
		$content = array_map(function($line){ return trim($line); },$content);
		$content = join("\n",$content);
	}

	return trim($content);
}
