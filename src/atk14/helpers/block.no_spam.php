<?php
/**
 * Smarty block plugin
 *
 * @package Atk14\Helpers
 */

/**
 * Smarty block function
 *
 * Obfuscates an email address in a string to protect it from collecting by robots.
 * Does not recognize any parameters.
 *
 * ```
 * {no_spam}Contact us at email info@domena.cz.{/no_spam}
 * ```
 *
 * @param array $params parameters
 * @param string $content string with an email address to be obfuscated
 * @param Smarty_Internal_Template $template
 * @param boolean &$repeat repeat flag
 *
 * @return string obfuscated string
 *
 */
function smarty_block_no_spam($params, $content, $template, &$repeat){
	if($repeat){ return; }
	return __no_spam_filter__($content);
}

/**
 * @ignore
 */
function __no_spam_filter__($content){
	return preg_replace("/([^\\s]+)@([^\\s]+)\\.([a-z]{2,14})/i","<span class=\"atk14_no_spam\">\\1[at-sign]\\2[dot-sign]\\3</span>",$content);
}
