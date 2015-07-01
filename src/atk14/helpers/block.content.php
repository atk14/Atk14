<?php
/**
 * Smarty {content}{/content} block tag.
 *
 *
 * Stores a block of code for later use. You can use multiple calls to store more content. The stored block will be rendered by using {placeholder} tag.
 * For more info see {@link smarty_function_placeholder()}
 *
 * <code>
 *	 {content for="javascript"}
 *		 <script language="javascript" type="text/javascript">
 *			function welcome() {
 *				alert('welcome human');
 *			}
 *		 </script>
 * 	 {/content}
 * </code>
 *
 * Consider a block placeholder in a layout like this:
 *
 *	<title>{block_placeholder for=title default_strategy=prepend} | Snake Oil Company{/block_placeholder}</title>
 *
 * Now you can set the page title this way:
 *
 *	 {content for=title}Sitemap{/content} {* or *}
 *	 {content for=title strategy=prepend}Sitemap{/content} {* the same as the previous *}
 *	 {content for=title strategy=replace}Brand new Title{/content}
 *
 * @package Atk14
 * @subpackage Helpers
 */

/**
 * @param array $params the only parameter is <b>for</b> which identifies the place to store the block
 * @param string $content
 *
 */
function smarty_block_content($params, $content, $template, &$repeat){
	if($repeat){ return; }
	$smarty = atk14_get_smarty_from_template($template);

	$params += array(
		"for" => "main",
		"strategy" => null, // default strategy is "append" but it won't be defined here; see Atk14Smarty::getAtk14Content()
	);

	$smarty->addAtk14Content($params["for"],$content,array(
		"strategy" => $params["strategy"],
	));
	return "";
}
