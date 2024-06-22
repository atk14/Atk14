<?php
/**
 *
 *	{$title|trim}
 */
function smarty_modifier_trim($content,$params = []){
	$params = Atk14Utils::StringToOptions($params);

	$params += [
		"each_line" => false,
	];

	$content = (string)$content;

	if($params["each_line"]){
		$content = explode("\n",$content);
		$content = array_map(function($line){ return trim($line); },$content);
		$content = join("\n",$content);
	}

	return trim($content);
}
