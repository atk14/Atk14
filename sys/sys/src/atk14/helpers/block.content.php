<?
/**
* {content for="head"}
* 	
*	{/content}
*/
function smarty_block_content($params,$content,&$smarty,&$repeat){
	$id = $params["for"];

	if(!isset($smarty->atk14_contents)){ $smarty->atk14_contents = array(); }
	if(!isset($smarty->atk14_contents[$id])){ $smarty->atk14_contents[$id] = ""; }

	$smarty->atk14_contents[$id] .= $content;

	return "";
}
?>
