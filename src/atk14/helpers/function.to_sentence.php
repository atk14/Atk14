<?php
/**
 * Converts an array to a sentence.
 * 
 * {to_sentence var=$singers words_connector=", " last_word_connector=" and "} -> George Michael, Boy George and Jimmy Somerville
 * 
 * There is also smarty`s modifier with the same name.
 */
function smarty_function_to_sentence($params,$template = null){
	$params = array_merge([
		"var" => [],
		"words_connector" => ", ",
		"last_word_connector" => " "._("and")." ",
		"two_words_connector" => null,
	],$params);

	if(!isset($params["two_words_connector"])){ $params["two_words_connector"] = $params["last_word_connector"]; }

	$var = $params["var"];

	switch(count($var)){
		case 0:
			return "";
		case 1:	
			return $var[0];
		case 2:
			return implode($params["two_words_connector"],$var);
	}
	$last = array_pop($var);
	return implode($params["words_connector"],$var).$params["last_word_connector"].$last;
}
