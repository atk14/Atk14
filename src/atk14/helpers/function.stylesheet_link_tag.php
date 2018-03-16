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

	$params += array(
		"file" => "style.css",

		// see smarty_function_javascript_script_tag() for more default options

		//"hide_when_file_not_found" => false,
		//"with_hostname" => false,

		// internal stuff
		// the real file is searched in the following places
		"_places_" => array(
			array($ATK14_GLOBAL->getPublicRoot()."/stylesheets/",	$ATK14_GLOBAL->getPublicBaseHref()."/stylesheets/"),	// "/public/stylesheets/"
			array($ATK14_GLOBAL->getPublicRoot(),									$ATK14_GLOBAL->getPublicBaseHref()),									// "/public/"
			array($ATK14_GLOBAL->getApplicationPath()."/../",			$ATK14_GLOBAL->getBaseHref())													// "/"
		),
		"_snippet_" => '<link rel="stylesheet" href="%uri%"%attribs% />'
	);

	Atk14Require::Helper("function.javascript_script_tag");
	return smarty_function_javascript_script_tag($params,$template);
}
