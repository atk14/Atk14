<?
/**
* {dump var=$basket->getTotalPrice()}
*/
function smarty_function_dump($params,&$smarty){
	$out = isset($params["var"]) ? print_r($params["var"],true) : "NULL";
	return "<pre>".strtr($out,array("<" => "&lt;",">" => "&gt;"))."</pre>";
}
?>
