<?php
/**
 * {use layout="two_columns"}
 */
function smarty_function_use($params,$template){
	$smarty = atk14_get_smarty_from_template($template);

	$params += array(
		"layout" => "", // 
	);

	$GLOBALS["__explicit_layout_name__"] = $params["layout"]; // TODO: avoid using global variable
}
