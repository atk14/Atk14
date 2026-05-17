<?php
function smarty_modifier_count($ar){
	if(is_null($ar)){ return 0; }
	return count($ar);
}
