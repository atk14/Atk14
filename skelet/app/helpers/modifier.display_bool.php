<?php
/**
* Displays bool value.
* By default display "Yes" on true value and "No" on false value.
*
* Usage in templates:
*
*		{$value|display_bool}
* 	{$value|display_bool:"On":"Off"}
*
*/
function smarty_modifier_display_bool($bool,$msg_yes = null,$msg_no = null){
	if(!isset($msg_yes)){ $msg_yes = _("Yes"); }
	if(!isset($msg_no)){ $msg_no = _("No"); }
	if($bool===true || in_array(strtolower($bool,"true","t","yes","y","1","on"))){
		return $msg_yes;
	}
	return $msg_no;
}
