<?php
/**
 * Smarty tag for inserting a preload link tag.
 *
 * Usage in a template:
 * ```
 * {preload_link_tag file="screen.css" as="style"}
 * {preload_link_tag file="/public/scripts/app.js", as="script"}
 * ```
 *
 * The code above renders something like this:
 * ```
 * <link rel="preload" href="/public/stylesheets/screen.css?1313101533" as="style />
 * <link rel="preload" href="/public/scripts/app.js?1313101533" as="script" />
 * ```
 *
 * It is also possible to link a stylesheet absolutely:
 * ```
 * {preload_link_tag file="/public/themes/retro/styles.css"}
 * ```
 *
 *
 * @package Atk14\Helpers
 * @filesource
 */

/**
 * Smarty tag for inserting a stylesheet file in a page.
 */
function smarty_function_preload_link_tag($params,$template){
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
		"_snippet_" => '<link rel="preload" href="%uri%"%attribs% />'
	);

	Atk14Require::Helper("function.javascript_script_tag");
	return smarty_function_javascript_script_tag($params,$template);
}
