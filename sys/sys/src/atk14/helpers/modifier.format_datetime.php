<?
/**
* {$iso_datetime|format_datetime} -> 22.1.2008 13:44
*/
function smarty_modifier_format_datetime($iso_datetime){
	return Atk14Locale::FormatDateTime($iso_datetime);
}
?>
