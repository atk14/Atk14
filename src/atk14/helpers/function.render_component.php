<?php
/**
* 
* {render_component controller=menu action=menu}
*
* Predani spec. parametru (v controlleru bude source_id dostupne pomoci $this->params->getValue("source_id"))
* 	{render_component controller=article action=overview source_id=123}
* 
*/
function smarty_function_render_component($params,$template){
	$smarty = atk14_get_smarty_from_template($template);

	$params += array(
		"controller" => $smarty->getTemplateVars("controller"),
		"action" => "index",
		"namespace" => $smarty->getTemplateVars("namespace"),
	);

	$controller_params = $params;
	unset($controller_params["controller"]);
	unset($controller_params["action"]);
	unset($controller_params["namespace"]);

	// pokud je parametr objekt, bude preveden volanim getId() na skalarni hodnotu
	foreach($controller_params as $key => $v){
		if(is_object($v)){ $controller_params[$key] = $v->getId(); }
	}

	$response = Atk14Dispatcher::ExecuteAction($params["controller"],$params["action"],array(
		"render_layout" => false,
		"apply_render_component_hacks" => true,
		"params" => $controller_params,
		"namespace" => $params["namespace"],
	));

	$buf = &$response->getOutputBuffer();
	return $buf->toString();
}
