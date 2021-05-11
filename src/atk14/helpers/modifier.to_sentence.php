<?php
/**
 * Converts an array to a sentence.
 *
 * {$singers|@to_sentence} -> George Michael, Boy George and Jimmy Somerville
 *
 * You should not to forget the @ symbol in front of the helper`s name.
 * 
 * There is also smarty`s function with the same name.
 */
function smarty_modifier_to_sentence($ar,$connector = null){
	if(is_null($connector)){ $connector = _("and"); }
	$out = '';
	$index = 0;
	$sizeof = sizeof($ar);
	foreach($ar as $item){
		if(($index>0) && ($index==$sizeof-1)){
			$out .= " $connector $item";
		}elseif($index>0){
			$out .= ", $item";
		}else{
			$out .= "$item";
		}
		$index++;
	}
	return $out;
}
