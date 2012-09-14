<?php
/**
 * Smarty tag for inserting a javascript file in a page.
 *
 * Includes HTML tag <script /> to output.
 * Generated file paths are relative to ./public/javascripts/
 *
 * Udage in a template:
 * <code>
 * 		{javascript_script_tag file="script.js"}
 * </code>
 *
 * This will produce the following output:
 * <code>
 *		<script src="/public/javascripts/script.js?1313093878" type="text/javascript"></script>
 * </code>
 * 
 * It is also possible to link a javascript file absolutely:
 * <code>
 * 		{javascript_script_tag file="/public/themes/retro/script.js"}
 * </code>
 *
 * 
 * @package Atk14
 * @subpackage Helpers
 * @filesource
 */

/**
 * Smarty tag for inserting a javascript file in a page.
 */
function smarty_function_javascript_script_tag($params,$template){
	global $ATK14_GLOBAL;

	$file = $params["file"];
	unset($params["file"]);

	if(preg_match('/^\//',$file)){
		$src = $ATK14_GLOBAL->getBaseHref().preg_replace('/^\//','',$file);
		$filename = $ATK14_GLOBAL->getApplicationPath()."/../".$file;
	}else{
		$src = $ATK14_GLOBAL->getPublicBaseHref()."javascripts/$file";
		$filename = $ATK14_GLOBAL->getPublicRoot()."javascripts/$file";
	}

	if(file_exists($filename)){
		$src .= "?".filemtime($filename);
	}

	$attribs = Atk14Utils::JoinAttributes($params);
	
	return "<script src=\"$src\" type=\"text/javascript\"$attribs></script>";
}
