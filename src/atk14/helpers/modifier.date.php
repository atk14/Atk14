<?php
function smarty_modifier_date($pattern,$timestamp = null){
	if(!is_null($timestamp)){
		$timestamp = (int)$timestamp;
	}
	if($timestamp){
		return date($pattern,$timestamp);
	}
	return date($pattern);
}
