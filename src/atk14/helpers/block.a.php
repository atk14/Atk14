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
 * Use this helper to create a link.
 *
 * The script path is created by using reserved parameters like 'controller', 'action', 'namespace', and 'lang'.
 * This will create basic link &lt;a href="/en/articles/">Show list of articles&lt;/a>
 * ```
 * {a controller="articles" action="index" lang="en"}Show list of articles{/a}
 * ```
 *
 * More query parameters can be added by adding them to the helper. They will appear in the URL with the name you give them in helper.
 * ```
 * {a controller="articles" action="detail" id=$article}Read the article{/a}
 * ```
 * This will create basic link &lt;a href="/en/articles/detail/?id=32">Read the article</a>
 *
 *
 * You can also define attributes of the tag. Simply add them to the helper with underscore at the beginning. For example parameter <b>_class=heading</b> will generate &lt;a /> tag with attribute class="heading"
 * ```
 * {a controller="articles" action="detail" id=$article _class="heading"}Read the article{/a}
 * ```
 *
 * When a link to other namespace is needed, for example you are in admin and want a link to the main application which is not namespaced.
 * ```
 * {a controller=products action=detail id=$product namespace=""}Show me the detail in shop{/a}
 * ```
 *
 * Sometimes you may want to access an URL with method POST.
 * ```
 * {a controller="articles" action="destroy" id=$action _method="post" _confirm="Are you sure to delete the article?"}Destroy the article{/a}
 * ```
 *
 *
 * @param array $params Parameters to build the &lt;a /> tag
 * Reserved parameters are:
 * - __controller__ - name of controller
 * - __action__ - name of action in the specified controller
 * - __namespace__ - application namespace. Defaults to current namespace.
 * - __lang__ - language. Defaults to ATK14_DEFAULT_LANG
 * - **domain_name** - Generated url will contain this domain_name when used with _with_hostname=true
 * - ___with_hostname__ - see domain_name parameter
 * - ___anchor__ - generates anchor
 * - ___method__ - GET or POST. Defaults to GET.
 * - ___confirm__ - dialog that pops up after the link is clicked
 *
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
