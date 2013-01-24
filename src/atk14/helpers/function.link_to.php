<?php
/**
 * Smarty function tag.
 *
 * Smarty function tag {link_to} generates a link
 *
 * Basic usage:
 * <code>
 * {link_to controller=login action=login _ssl=true _with_hostname=true}
 * </code>
 *
 * Reserved parameters:
 * <ul>
 * 	<li>action - </li>
 * 	<li>controller - </li>
 * 	<li>lang - </li>
 * 	<li>_connector -</li>
 * 	<li>_anchor -</li>
 * 	<li>_with_hostname -</li>
 * 	<li>_ssl -</li>
 * </ul>
 *
 * @package Atk14
 * @subpackage Helpers
 * @author Jaromir Tomek
 * @filesource
 */

/**
 * Smarty function that generates url.
 *
 * @package Atk14
 * @author Jaromir Tomek
 */
function smarty_function_link_to($params,$template){
	$smarty = atk14_get_smarty_from_template($template);

	$options = array(
		"connector" => "&amp;",
		"anchor" => null,
		"with_hostname" => false,
		"ssl" => null,
	);
	Atk14Utils::ExtractAttributes($params,$options);

	/*
	if(!isset($params["action"]) && !isset($params["controller"])){ $params["action"] = $smarty->getTemplateVars("action"); }
	if(!isset($params["controller"])){ $params["controller"] = $smarty->getTemplateVars("controller"); }
	if(!isset($params["action"])){ $params["action"] = "index"; }
	if(!isset($params["lang"])){ $params["lang"] = $smarty->getTemplateVars("lang"); }*/

	$url = Atk14Url::BuildLink($params,$options);

	return $url;
}
