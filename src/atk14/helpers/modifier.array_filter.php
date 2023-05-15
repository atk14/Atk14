<?php
function smarty_modifier_array_filter($array,$callback = null){
	if($callback){
		return array_filter($array,$callback);
	}
	return array_filter($array);
}
