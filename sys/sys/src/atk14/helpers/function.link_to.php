<?
/**
* {link_to controller=login action=login _ssl=true _with_hostname=true}
*/
function smarty_function_link_to($params,&$smarty){
	$options = array(
		"connector" => "&amp;",
		"anchor" => null,
		"with_hostname" => false,
		"ssl" => null,
	);
	reset($options);
	while(list($_key,$_value) = each($options)){
		if(isset($params["_$_key"])){
			$options[$_key] = $params["_$_key"];
		}
		unset($params["_$_key"]);
	}

	if(!isset($params["action"]) && !isset($params["controller"])){ $params["action"] = $smarty->_tpl_vars["action"]; }
	if(!isset($params["controller"])){ $params["controller"] = $smarty->_tpl_vars["controller"]; }
	if(!isset($params["action"])){ $params["action"] = "index"; }
	if(!isset($params["lang"])){ $params["lang"] = $smarty->_tpl_vars["lang"]; }

	$url = Atk14Url::BuildLink($params,$options);

	return $url;
}
?>
