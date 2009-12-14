<?
function smarty_block_form_remote($params, $content, &$smarty, &$repeat)
{
	$params = array_merge(array(
		"form" => $smarty->_tpl_vars["form"],
	),$params);

	$form = $params["form"];

	foreach($params as $k => $v){
		if(preg_match("/^_(.+)/",$k,$matches)){
			$form->set_attr($matches[1],$v);
		}
	}

	$form->set_attr("class","remote_form");

	$out = array();
	$out[] = $form->begin();
	$out[] = $content;
	$out[] = $form->end();
	return join("\n",$out);
}
?>
