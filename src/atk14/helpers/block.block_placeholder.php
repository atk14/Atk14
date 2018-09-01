<?php
/**
 * This is a block equivalent to smarty the function {placeholder}
 * 
 * Inside the block you can specify a default content for the placeholder
 *
 * Usage:
 *
 *	 {* somewhere in layout *}
 *	 {block_placeholder for="footer"}Default Footer{/block_placeholder}
 *
 *
 *	 {* somewhere in template *}
 *	 {content for=footer strategy=replace}This is new Footer{/content}
 *
 * Note: In Smarty a block and a function can't coexist with the same name.
 * So this is the reason why there are different names: {placeholder} <-> {block_placeholder}{/block_placeholder}
 */
function smarty_block_block_placeholder($params,$content,$smarty,&$repeat){
	if($repeat){ return; }

	$params += array(
		"for" => "main",
		"default_strategy" => "append",
	);
	$id = $params["for"];

	$smarty->addAtk14Content($id,$content,array(
		"strategy" => "_place_initial_content_",
		"default_strategy" => $params["default_strategy"],
	));

	return "<%atk14_content[$id]%>"; // returns an internal sign, which will be replaced later within controller
}
