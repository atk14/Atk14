<?php
/**
 * Smarty tag for inserting a javascript file in a page.
 *
 * Includes HTML tag <script /> to output.
 * Generated file paths are relative to ./public/javascripts/
 *
 * Udage in a template:
 * <code>
 * 		{javascript_script_tag file="script.js"} {* or *}
 * 		{javascript_script_tag file="javascripts/script.js"} {* or *}
 * 		{javascript_script_tag file="public/javascripts/script.js"} {* or *}
 * 		{javascript_script_tag file="/public/javascripts/script.js"}
 * </code>
 *
 * This will produce the following output:
 * <code>
 *		<script src="/public/javascripts/script.js?1313093878"></script>
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

	$params += array(
		"file" => "script.css",
		"hide_when_file_not_found" => false,
		"with_hostname" => false,
	);

	$file = $params["file"]; unset($params["file"]);
	$hide_when_file_not_found = $params["hide_when_file_not_found"]; unset($params["hide_when_file_not_found"]);
	$with_hostname = $params["with_hostname"]; unset($params["with_hostname"]);

	// the real file is searched in the following places
	$places = array(
		array($ATK14_GLOBAL->getPublicRoot()."/javascripts/",	$ATK14_GLOBAL->getPublicBaseHref()."/javascripts/"),	// "/public/javascripts/"
		array($ATK14_GLOBAL->getPublicRoot(),									$ATK14_GLOBAL->getPublicBaseHref()),									// "/public/"
		array($ATK14_GLOBAL->getApplicationPath()."/../",			$ATK14_GLOBAL->getBaseHref())													// "/"
	);

	if(preg_match('/^\//',$file)){
		// $file starts with "/", so we will search only in the very last place
		$places = array(
			array_pop($places)
		);
	}

	$filename = $src = $filename_default = $src_default = "";
	foreach($places as $place){
		list($root,$base_href) = $place;

		$_filename = Atk14Utils::NormalizeFilepath("$root/$file");

		if(!$filename_default){
			$filename_default = $_filename;
			$src_default = "$base_href/$file";
		}

		if(file_exists($_filename)){
			$filename = $_filename;
			$src = "$base_href/$file";
			break;
		}
	}

	if(!$filename){
		$filename = $filename_default;
		$src = $src_default;
	}

	$src = Atk14Utils::NormalizeUri($src);
	if($with_hostname){
		$src = Atk14Utils::AddHttpHostToUri($src);
	}

	if(file_exists($filename)){
		$src .= "?".filemtime($filename);
	}elseif($hide_when_file_not_found){
		return "";
	}

	$attribs = Atk14Utils::JoinAttributes($params);
	
	return "<script src=\"$src\"$attribs></script>";
}
