<?php
/**
 * Smarty {dump} tag to output value of a variable.
 *
 * Php function print_r is used to output the value.
 * <code>
 * {dump var=$basket->getTotalPrice()}
 * </code>
 *
 * @package Atk14
 * @subpackage Helpers
 * @filesource
 */

/**
 * Smarty {dump} tag to output value of a variable.
 *
 * @param array $params
 * @param array $content
 */
function smarty_function_dump($params,&$smarty){
	$out = isset($params["var"]) ? print_r($params["var"],true) : "NULL";
	return "<pre>".strtr($out,array("<" => "&lt;",">" => "&gt;"))."</pre>";
}
?>
