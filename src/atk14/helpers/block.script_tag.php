<?php
/**
 * Smarty plugin for inserting javascript code.
 *
 * {script_tag} plugin inserts javascript code enclosed by appropriate tag
 *
 * Basic usage:
 * <code>
 * {script_tag}
 *		alert('Hello World')
 *	{/script_tag}
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
function smarty_block_script_tag($params, $content, $template, &$repeat){
	global $ATK14_GLOBAL;
	if($repeat){ return; }

	if(strlen(trim($content))==0){ return; }

	if(CSP_NONCE){
		$params += array(
			"nonce" => CSP_NONCE,
		);
	}

	$attrs = Atk14Utils::JoinAttributes($params);

	$out = array();
	$out[] = "<script$attrs>";
	$out[] = '//<![CDATA[';
	$out[] = $content;
	$out[] = '//]]>';
	$out[] = '</script>';

	return join("\n",$out);
}
