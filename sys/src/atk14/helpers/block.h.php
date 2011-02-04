<?
/**
 * Smarty block tag for safe output of strings in HTML.
 *
 * This tag that escapes its content to print out safe strings in HTML.
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
function smarty_block_h($params, $content, &$smarty, &$repeat)
{
	return htmlspecialchars($content);
}
?>
