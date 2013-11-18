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

	$file = $params["file"];
	unset($params["file"]);

	if(preg_match('/^\//',$file)){
		$href = $ATK14_GLOBAL->getBaseHref().preg_replace('/^\//','',$file);
		$filename = $ATK14_GLOBAL->getApplicationPath()."/../".$file;
	}else{
		$href = $ATK14_GLOBAL->getPublicBaseHref()."stylesheets/$file";
		$filename = $ATK14_GLOBAL->getPublicRoot()."stylesheets/$file";
	}

	$href = Atk14Utils::NormalizeUri($href);

	if(file_exists($filename)){
		$href .= "?".filemtime($filename);
	}


	$attribs = Atk14Utils::JoinAttributes($params);
	return "<link rel=\"stylesheet\" href=\"$href\"$attribs />";
}
