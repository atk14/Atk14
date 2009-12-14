<?
function smarty_function_to_sentence($params,&$smarty){
	$params = array_merge(array(
		"var" => array(),
		"words_connector" => ", ",
		"last_word_connector" => " "._("and")." ",
		"two_words_connector" => null,
	),$params);

	if(!isset($params["two_words_connector"])){ $params["two_words_connector"] = $params["last_word_connector"]; }

	$var = $params["var"];

	switch(sizeof($var)){
		case 0:
			return "";
		case 1:	
			return $var[0];
		case 2:
			return join($params["two_words_connector"],$var);
	}
	$last = array_pop($var);
	return join($params["words_connector"],$var).$params["last_word_connector"].$last;
}
?>
