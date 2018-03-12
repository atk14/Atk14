<?php
/**
 * Smarty tag for inserting a stylesheet file in a page.
 *
 * Stylesheets are expected in ./public/stylesheets/ direcotry. 
 * 
 * Usage in a template:
 * <code>
 *		{stylesheet_link_tag file="blueprint/screen.css" media="screen, projection"}
 *		{stylesheet_link_tag file="blueprint/print.css", media="print"}
 *		<!--[if IE]>
 *		{stylesheet_link_tag file="blueprint/lib/ie.css", media="screen, projection"}
 *		<![endif]-->
 *		{stylesheet_link_tag file="styles.css"}
 * </code>
 *
 * The code above renders something like this:
 * <code>
 *		<link rel="stylesheet" href="/public/stylesheets/lib/blueprint-css/blueprint/screen.css?1313101533" media="screen, projection" />
 *		<link rel="stylesheet" href="/public/stylesheets/lib/blueprint-css/blueprint/print.css?1313101533" media="print" />
 *		<!--[if IE]>
 *			<link rel="stylesheet" href="/public/stylesheets/lib/blueprint-css/blueprint/ie.css?1313101533" media="screen, projection" />
 *		<![endif]-->
 *		<link rel="stylesheet" href="/public/stylesheets/styles.css?1330160598" media="screen, projection" />
 * </code>
 *
 * It is also possible to link a stylesheet absolutely:
 * <code>
 *		{stylesheet_link_tag file="/public/themes/retro/styles.css"}
 * </code>
 *
 *
 * @package Atk14
 * @subpackage Helpers
 * @filesource
 */

/**
 * Smarty tag for inserting a stylesheet file in a page.
 */
function smarty_function_stylesheet_link_tag($params,$template){
	global $ATK14_GLOBAL;

	// TODO: Refactore common parts with {javascript_script_tag}

	$params += array(
		"file" => "style.css",
		"hide_when_file_not_found" => false,
		"with_hostname" => false,
	);

	$file = $params["file"]; unset($params["file"]);
	$hide_when_file_not_found = $params["hide_when_file_not_found"]; unset($params["hide_when_file_not_found"]);
	$with_hostname = $params["with_hostname"]; unset($params["with_hostname"]);

	// the real file is searched in the following places
	$places = array(
		array($ATK14_GLOBAL->getPublicRoot()."/stylesheets/",	$ATK14_GLOBAL->getPublicBaseHref()."/stylesheets/"),	// "/public/stylesheets/"
		array($ATK14_GLOBAL->getPublicRoot(),									$ATK14_GLOBAL->getPublicBaseHref()),									// "/public/"
		array($ATK14_GLOBAL->getApplicationPath()."/../",			$ATK14_GLOBAL->getBaseHref())													// "/"
	);

	if(preg_match('/^\//',$file)){
		// $file starts with "/", so we will search only in the very last place
		$places = array(
			array_pop($places)
		);
	}

	$filename = $href = $filename_default = $href_default = "";
	foreach($places as $place){
		list($root,$base_href) = $place;

		$_filename = Atk14Utils::NormalizeFilepath("$root/$file");

		if(!$filename_default){
			$filename_default = $_filename;
			$href_default = "$base_href/$file";
		}

		if(file_exists($_filename)){
			$filename = $_filename;
			$href = "$base_href/$file";
			break;
		}
	}

	if(!$filename){
		$filename = $filename_default;
		$href = $href_default;
	}

	$href = Atk14Utils::NormalizeUri($href);
	if($with_hostname){
		$href = Atk14Utils::AddHttpHostToUri($href);
	}

	if(file_exists($filename)){
		$href .= "?".filemtime($filename);
	}elseif($hide_when_file_not_found){
		return "";
	}

	$attribs = Atk14Utils::JoinAttributes($params);
	return "<link rel=\"stylesheet\" href=\"$href\"$attribs />";
}
