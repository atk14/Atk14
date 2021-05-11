<?php
/**
* Spocita prvky pole.
*
* 	{$array|@count}
*
* Pozor na ten zavinac! U poli je dulezity
*/
function smarty_modifier_count($ar){
	if(is_null($ar)){ return 0; }
	return sizeof($ar);
}
