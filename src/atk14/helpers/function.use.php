<?php
/**
 * {use layout="two_columns"}
 */
function smarty_function_use($params,$template){
	$smarty = atk14_get_smarty_from_template($template);
	$rendering_component = $smarty->getTemplateVars("rendering_component");

	$params += array(
		"layout" => "", // 
	);

	if(!$rendering_component){
		// layout name cannot be set during rendering component
		$GLOBALS["__explicit_layout_name__"] = $params["layout"]; // TODO: avoid using global variable
	}
}
