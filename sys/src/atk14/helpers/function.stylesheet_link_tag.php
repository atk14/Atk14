<?
/**
* Vygeneruje link na stylesheet soubor.
* Link doplni hodnotou modify time souboru, takze odpadne problem s cachovanim css souboru.
*
* Soubory vyhledava v adresari ./public/stylesheets/
* 
* 
* Pouziti:
*
*		{stylesheet_link_tag file="blueprint/screen.css" media="screen, projection"}
*		{stylesheet_link_tag file="blueprint/print.css", media="print"}
*		<!--[if IE]>
*		{stylesheet_link_tag file="blueprint/lib/ie.css", media="screen, projection"}
*		<![endif]-->
*																																									
*		{stylesheet_link_tag file="styles.css"}
*/
function smarty_function_stylesheet_link_tag($params,&$smarty){
	global $ATK14_GLOBAL;
	$href = $ATK14_GLOBAL->getPublicBaseHref()."stylesheets/$params[file]";
	$filename = $ATK14_GLOBAL->getPublicRoot()."stylesheets/$params[file]";


	if(file_exists($filename)){
		$href = $href."?".filemtime($filename);
	}else{
		return "<!-- stylesheet file not found: $params[file] -->";
	}

	unset($params["file"]);

	$attribs = array();
	reset($params);
	while(list($key,$value) = each($params)){
		$attribs[] = htmlspecialchars($key)."=\"".htmlspecialchars($value)."\"";
	}
	if(sizeof($attribs)>0){ $attribs[] = "";}
	
	return "<link rel=\"stylesheet\" href=\"$href\" ".join(" ",$attribs)."/>";
}
?>
