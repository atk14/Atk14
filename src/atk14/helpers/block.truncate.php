<?php
function smarty_block_truncate($params,$content,$template,&$repeat){
	if($repeat){ return; }

	$params += [
		"length" => 200,
		"omission" => "...",
		"separator" => "",
	];

	$content = new String4($content);
	$content = $content->truncate($params["length"],[
		"omission" => $params["omission"],
		"separator" => $params["separator"],
	]);
	return $content->toString();
}
