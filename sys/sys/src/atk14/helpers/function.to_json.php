<?
function smarty_function_to_json($params,&$smarty){
	// TODO: if $val is an object, the method toJson() should be called (if such method exists)
	$out = json_encode($params["var"]);
	return $out;
}
?>
