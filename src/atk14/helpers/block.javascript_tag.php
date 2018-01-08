<?php
/**
 * Smarty plugin for inserting javascript code.
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
 * @param array $params there are no options for this plugin.
 * @param string $content
 */
function smarty_block_javascript_tag($params, $content, $template, &$repeat){
	if(!$content){ return; }
	$out = array();
	$out[] = '<script>';
	$out[] = '//<![CDATA[';
	$out[] = $content;
	$out[] = '//]]>';
	$out[] = '</script>';

	return join("\n",$out);
}
