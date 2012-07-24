<?php
function smarty_function_form_remote_tag($params,$template){
	$smarty = atk14_get_smarty_from_template($template);

	$url = Atk14Utils::BuildLink($params,$smarty);

	$attrs = Atk14Utils::ExtractAttributes($params);
	$attrs["action"] = $url;
	if(!isset($attrs["method"])){ $attrs["method"] = "post"; }
	//$attrs["onsubmit"] = "return remote_form(this);";
	if(!isset($attrs["class"])){ $attrs["class"] = ""; }
	$attrs["class"] = trim("$attrs[class] remote_form");
	$attrs["data-remote"] = "true";
	$attrs = Atk14Utils::JoinAttributes($attrs);

  return "<form$attrs>";
}
