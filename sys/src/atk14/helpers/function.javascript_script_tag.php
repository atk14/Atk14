<?
/**
* Vlozi do vystupu tag <script> pro dany javascriptovy soubor.
* Soubory vyhledava v adresari ./public/javascripts/
*
* Pouziti:
*	{javascript_script_tag file="scripts.js"}
*/
function smarty_function_javascript_script_tag($params,&$smarty){
	global $ATK14_GLOBAL;
	$src = $ATK14_GLOBAL->getPublicBaseHref()."javascripts/$params[file]";
	$filename = $ATK14_GLOBAL->getPublicRoot()."javascripts/$params[file]";


	if(file_exists($filename)){
		$src = $src."?".filemtime($filename);
	}else{
		return "<!-- javascript file not found: $params[file] -->";
	}

	unset($params["file"]);

	$attribs = array();
	reset($params);
	while(list($key,$value) = each($params)){
		$attribs[] = htmlspecialchars($key)."=\"".htmlspecialchars($value)."\"";
	}
	if(sizeof($attribs)>0){
		array_unshift($attribs,"");
	}
	
	return "<script src=\"$src\" type=\"text/javascript\"".join(" ",$attribs)."></script>";
}
?>
