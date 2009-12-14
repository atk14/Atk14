<?
/**
* {$iso_date|format_date} -> 22.1.2008
*/
function smarty_modifier_format_date($iso_date){
	return Atk14Locale::FormatDate($iso_date);
}
?>
