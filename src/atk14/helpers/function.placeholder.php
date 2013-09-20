<?php
/**
 * Smarty {placeholder} tag to place stored content.
 *
 * The tag places content stored by {content}{/content} tag.
 * The tag takes one parameter 'for' which identifies the stored content. Default value of the parameter is 'main'.
 *
 * Also see {@link smarty_block_content()}
 *
 * <code>
 * <html>
 *  <head>
 *    <title>My Shiny Web Application</title>
 *    {placeholder for="head"}
 *  </head>
 *  <body>
 *    {placeholder} {* stands for {placeholder for="main"} *}
 *  </body>
 * </html>
 * </code>
 *
 * @package Atk14
 * @subpackage Helpers
 *
 */

/**
 *
 * @param array $params the only parameter is <b>for</b> which identifies the stored block
 * @param array $content
 *
 */
function smarty_function_placeholder($params,$template){
	$params += array(
		"for" => "main"
	);
	$id = $params["for"];

	$smarty = atk14_get_smarty_from_template($template);
	$smarty->addAtk14Content($id,"",array(
		"strategy" => "_place_initial_content_"
	));

	return "<%atk14_content[$id]%>"; // returns an internal sign, which will be replaced later within controller
}
