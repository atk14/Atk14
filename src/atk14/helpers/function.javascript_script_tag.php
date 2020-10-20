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
 * Other attributes will be placed as attributes of the <script> element
 * <code>
 * 		{javascript_script_tag file="/public/javascripts/script.js" defer="defer" async="async"}
 * </code>
 *
 * It renders
 * <code>
 *		<script src="/public/javascripts/script.js?1313093878" defer="defer" async="async"></script>
 * </code>
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
		"version_indicated_by" => ATK14_STATIC_FILE_VERSIONS_INDICATED_BY, // "parameter", "filename", "none"

		// internal stuff
		// the real file is searched in the following places
		"_places_" => array(
			array($ATK14_GLOBAL->getPublicRoot()."/javascripts/",	$ATK14_GLOBAL->getPublicBaseHref()."/javascripts/"),	// "/public/javascripts/"
			array($ATK14_GLOBAL->getPublicRoot(),									$ATK14_GLOBAL->getPublicBaseHref()),									// "/public/"
			array($ATK14_GLOBAL->getApplicationPath()."/../",			$ATK14_GLOBAL->getBaseHref())													// "/"
		),
		"_snippet_" => '<script src="%uri%"%attribs%></script>'
	);

	$file = $params["file"]; unset($params["file"]);
	$hide_when_file_not_found = $params["hide_when_file_not_found"]; unset($params["hide_when_file_not_found"]);
	$with_hostname = $params["with_hostname"]; unset($params["with_hostname"]);
	$version_indicated_by = $params["version_indicated_by"]; unset($params["version_indicated_by"]);

	$places = $params["_places_"]; unset($params["_places_"]);
	$snippet = $params["_snippet_"]; unset($params["_snippet_"]);

	$file = preg_replace('/\/{2,}/','/',$file); // "/public//dist/styles/vendor.min.css" -> "/public/dist/styles/vendor.min.css"

	if(preg_match('/^\//',$file)){
		// $file starts with "/", so we will search only in the very last place.
		// But first $base_href needs to be removed from the $file.
		$base_href = $ATK14_GLOBAL->getBaseHref();
		if($base_href!=="/" && substr($file,0,strlen($base_href))===$base_href){
			$file = "/".substr($file,strlen($base_href));
		}
		$places = array(
			array_pop($places)
		);
	}

	$filename = $uri = $filename_default = $uri_default = "";
	foreach($places as $place){
		list($root,$base_href) = $place;

		$_filename = Atk14Utils::NormalizeFilepath("$root/$file");

		if(!$filename_default){
			$filename_default = $_filename;
			$uri_default = "$base_href/$file";
		}

		if(file_exists($_filename)){
			$filename = $_filename;
			$uri = "$base_href/$file";
			break;
		}
	}

	if(!$filename){
		$filename = $filename_default;
		$uri = $uri_default;
	}

	$uri = Atk14Utils::NormalizeUri($uri);
	if($with_hostname){
		$uri = Atk14Utils::AddHttpHostToUri($uri);
	}

	if(file_exists($filename)){
		if($version_indicated_by=="parameter"){
			$uri .= "?v".filemtime($filename);
		}elseif($version_indicated_by=="filename"){
			$uri = preg_replace('/(\.[a-zA-Z0-9]{1,10})$/i','.v'.filemtime($filename).'\1',$uri);
		}
	}elseif($hide_when_file_not_found){
		return "";
	}

	$attribs = Atk14Utils::JoinAttributes($params);

	return strtr($snippet,array(
		"%uri%" => $uri,
		"%attribs%" => $attribs,
	));
}
