<?php
/**
 * Smarty modifier for safe output
 *
 * This modifier escapes string to print out safe string in HTML
 *
 * The modifier does the same operation as {@link smarty_block_h()}
 *
 * Example:
 * <code>
 * {$user->getName()|h}
 * </code>
 *
 * @package Atk14
 * @subpackage Helpers
 *
 */

/**
 * @param string $content content to be escaped
 */
function smarty_modifier_h($content){
	return h($content);
}
