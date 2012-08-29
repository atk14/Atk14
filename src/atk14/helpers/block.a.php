<?php
/**
 * Smarty {a}{/a} block tag.
 *
 * Smarty block tag {a}{/a} generates html <a /> tag
 *
 * @package Atk14
 * @subpackage Helpers
 */

/**
 *
 * @param array $params Parameters to build the <a /> tag
 * Reserved parameters are:
 * <ul>
 * 	<li><b>controller</b> - name of controller</li>
 * 	<li><b>action</b> - name of action in the specified controller</li>
 * 	<li><b>lang</b> - language. Defaults to ??</li>
 * 	<li><b>domain_name</b> - Generated url will contain this domain_name when used with _with_hostname=true</li>
 * 	<li><b>_with_hostname</b> - see domain_name parameter</li>
 * 	<li><b>_anchor</b> - generates anchor</li>
 *	<li><b>_method</b></li>
 *	<li><b>_confirm</b></li>
 * </ul>
 *
 * You can also define attributes of the tag. Simply add them to the helper with underscore at the beginning. For example parameter <b>_class=heading</b> will generate <a /> tag with attribute class="heading"
 * <code>
 * 	{a controller="articles" action="detail" id=$article _class="heading"}Read the article{/a}
 * </code>
 *
 * Sometimes you may want to access an URL with method POST.
 * <code>
 *	{a controller="articles" action="destroy" id=$action _method="post" _confirm="Are you sure to delete the article?"}Destroy the article{/a}
 * </code>
 *
 * More query parameters can be added by adding them to the helper. They will appear in the URL with the name you give them in helper.
 * @param string $content content of the Smarty {a} block tag
 */
function smarty_block_a($params, $content, $template, &$repeat){
	if($repeat){ return; }
	$smarty = atk14_get_smarty_from_template($template);

	$params = array_merge(array(
		"_method" => "get",
		"_confirm" => null,
	),$params);


	Atk14Timer::Start("helper block.a");
	$attrs = array();
	if($params["_method"]!="get"){
		$attrs["data-method"] = $params["_method"];
	}
	if($params["_confirm"]){
		$attrs["data-confirm"] = $params["_confirm"];
	}

	unset($params["_method"]);
	unset($params["_confirm"]);

	$url = Atk14Utils::BuildLink($params,$smarty);

	Atk14Utils::ExtractAttributes($params,$attrs);
	$attrs["href"] = $url;

	$attrs = Atk14Utils::JoinAttributes($attrs);

	Atk14Timer::Stop("helper block.a");
	return "<a$attrs>$content</a>";
}
