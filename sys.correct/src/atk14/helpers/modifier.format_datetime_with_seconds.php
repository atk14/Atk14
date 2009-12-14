<?
/**
* {$iso_datetime|format_datetime_with_seconds} -> 22.1.2008 13:44:00
*/
function smarty_modifier_format_datetime_with_seconds($iso_datetime){
	return Atk14Locale::FormatDateTimeWithSeconds($iso_datetime);
}
?>
