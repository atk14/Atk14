<?
/**
*
*/
function smarty_function_form_tag($params,&$smarty){
	$url = Atk14Utils::BuildLink($params,$smarty);

	$attrs = Atk14Utils::ExtractAttributes($params);
	$attrs["action"] = $url;
	if(!isset($attrs["method"])){ $attrs["method"] = "post"; }
	$attrs = Atk14Utils::JoinAttributes($attrs);

  return "<form$attrs>";
}
?>
