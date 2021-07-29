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
 *
 * {no_spam class="btn btn-primary"}Contact us at email info@domena.cz.{/no_spam}
 * {no_spam title="our email address"}Contact us at email info@domena.cz.{/no_spam}
 * {no_spam text="email"}Contact us at info@domena.cz.{/no_spam}
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
	return __no_spam_filter__($content,$params);
}

/**
 * @ignore
 */
function __no_spam_filter__($content,$options){
	$options += array(
		"text" => "",

		// attrs; see $attrs_keys below
	);

	$text = $options["text"];

	$attrs_keys = array(
		"class",
		"title",
	);
	$attrs = array();
	foreach($attrs_keys as $k){
		if(isset($options[$k]) && strlen($options[$k])>0){
			$attrs[$k] = $options[$k];
		}
	}

	$data_text = strlen($text) ? ' data-text="'.h($text).'"' : '';
	$data_attrs = $attrs ? ' data-attrs="'.h(json_encode($attrs)).'"' : '';
	return preg_replace("/([^\\s]+)@([^\\s]+)\\.([a-z]{2,14})/i","<span class=\"atk14_no_spam\"$data_text$data_attrs>\\1[at-sign]\\2[dot-sign]\\3</span>",$content);
}
