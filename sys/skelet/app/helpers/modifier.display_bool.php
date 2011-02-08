<?
/**
* Displays bool value.
*
* {$value|display_bool}
*/
function smarty_modifier_display_bool($bool){
	if($bool===true || in_array(strtolower($bool,"true","t","yes","y","1"))){
		return _("Yes");
	}
	return _("No");
}
