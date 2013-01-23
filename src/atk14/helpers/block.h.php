<?php
/**
 * Smarty block tag for safe output of strings in HTML.
 *
 * This tag converts special characters to HTML entities.
 *
 * You can alse use {@link smarty_modifier_h()} modifier.
 *
 * Example:
 * <code>
 * {h}{$data}{/h}
 * </code>
 *
 * @package Atk14
 * @subpackage Helpers
 */

/**
 * Smarty block function
 *
 * Converts special characters to HTML entities.
 */
function smarty_block_h($params, $content, $template, &$repeat){
	if($repeat){ return; }
	return h($content);
}
