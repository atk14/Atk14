<?php
function smarty_modifier_sizeof($ar){
	if(is_null($ar)){ return 0; }
	return sizeof($ar);
}
