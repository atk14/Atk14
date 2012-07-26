<?php
/**
* Spocita prvky pole.
*
* 	{$array|@count}
*
* Pozor na ten zavinac! U poli je dulezity
*/
function smarty_modifier_count($ar){
	return sizeof($ar);
}
