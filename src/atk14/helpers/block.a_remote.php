<?php
/**
 * Smarty block plugin that generates remote link
 *
 *
 * @package Atk14\Helpers
 */

/**
 * Smarty plugin that generates remote link
 *
 * The generated link is handled with javascript and sent as remote. When javascript is not present, click on the link is sent as normal HTTP request.
 *
 *
 * Usage:
 * ```
 * {a_remote action=detail id=$product}Product detail{/a_remote}
 *
 * {a_remote action=detail id=$product _data-type=json}Product detail{/a_remote}
 *
 * {a_remote action=destroy id=$product _method=delete _confirm="Are you sure to delete the product?"}Delete product{/a_remote}
 * ```
 *
 * @param array $params Uses same parameters as {@link smarty_block_a}. Here is description of additional parameters:
 * - <b>_method</b> - method for sending the request. Defaults to GET
 * @param string $content link content
 * @param Smarty_Internal_Template $template
 * @param boolean &$repeat repeat flag
 *
 * @return string html a tag with proper url
 */
function smarty_block_a_remote($params, $content, $template, &$repeat){
	if($repeat){ return; }
	$smarty = atk14_get_smarty_from_template($template);

	$attributes = array();

	$params = array_merge(array(
		"_method" => "get",
		"_confirm" => null, // an confirmation message ('Are you sure?')
		"__be_pretty_ugly__" => false // internal parameter, don't use it outside
	),$params);

	$be_pretty_ugly = $params["__be_pretty_ugly__"];
	unset($params["__be_pretty_ugly__"]);

	$method = strtolower($params["_method"]);
	unset($params["_method"]);

	$url = Atk14Utils::BuildLink($params,$smarty);

	$attrs = array("data-remote" => "true");

	if(isset($params["_confirm"])){
		$attrs["data-confirm"] = $params["_confirm"];
		// automaticky pridame tridu confirm pro zachovani zpetne kompatibility
		$params["_class"] = isset($params["_class"]) ? trim("confirm ".$params["_class"]) : "confirm";
	}
	unset($params["_confirm"]);

	if($method!="get"){ $attrs["data-method"] = $method; }

	Atk14Utils::ExtractAttributes($params,$attrs);
	$attrs["href"] = $url;
	if($be_pretty_ugly){
		// TODO: doesn't check existance of the JS function before_remote_link()
		$_data = "";
		$_type = "GET";
		if($method=="post"){
			$_data = ", data: ''";
			$_type = "POST";
		}
		unset($attrs["data-remote"]);
		$attrs["onclick"] = "$('body').css('cursor','wait'); $.ajax({ type: '$_type', url: $(this).attr('href')$_data, dataType: 'script', complete: function(){ $('body').css('cursor','default'); } }); return false;";
	}else{
		if(!isset($attrs["class"])){ $attrs["class"] = ""; }
		$_class = $method=="post" ? "remote_link post" : "remote_link";
		$attrs["class"] = trim("$attrs[class] $_class");
	}

	$attrs = Atk14Utils::JoinAttributes($attrs);

	return "<a$attrs>$content</a>";
}
