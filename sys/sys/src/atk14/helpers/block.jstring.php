<?
/**
* Eskapuje string pro javascript. Vystup obsahuje i uvozovky!
* 
* <script type="text/javascript">
* 	var message={jstring}Zprava pro tebe: {render partial="message"}{/jstring};
*	</script>
*/
function smarty_block_jstring($params,$content,&$smarty,&$repeat){
	$params = array_merge(array(
		"escsape" => ""
	),$params);
	$content = Atk14Utils::EscapeForJavascript($content);
	if($params["escape"] == "html"){
		$content = str_replace("<","&lt;",$content);
		$content = str_replace(">","&gt;",$content);
		$content = str_replace("&","&amp;",$content);
	}
	return '"'.$content.'"';
}
?>
