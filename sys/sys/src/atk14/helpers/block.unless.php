<?
/**
* Zobrazi obsah parametru content nebo vnitrek blokoveho tagu, pokud je parametr prazdny.
*
*  Jmeno objednavky: {unless content=$title}{t}nepojmenovana{/t}{/unless}
*/
function smarty_block_unless($params,$content,&$smarty,&$repeat){
	if(isset($params["content"]) && strlen($params["content"])>0){
		return $params["content"];
	}
	return $content;
}
?>
