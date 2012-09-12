<?php
/**
 * Smarty block plugin
 *
 * Obfuscates email address in a string to protect it from collecting by robots.
 *
 * <code>
 * {no_spam}Napiste nam na adresu info@domena.cz.{/no_spam}
 * </code>
 *
 * @package Atk14
 * @subpackage Helpers
 */

/**
 * Smarty block function
 *
 * Obfuscates an email address in a string to protect it from collecting by robots.
 * Does not recognize any parameters.
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
	return preg_replace("/([^\\s]+)@([^\\s]+)\\.([a-z]{2,5})/i","<span class=\"atk14_no_spam\">\\1[at-sign]\\2[dot-sign]\\3</span>",$content);
}
