<?php
/**
 * Format the given number according to the current locale
 *
 *	{$number|format_number} -> 1,234.567
 *	{$number|format_number:2} -> 1,234.57
 *	{$number|format_number:0} -> 1,235
 */
function smarty_modifier_format_number($number,$decimal_places = null){
	if(!strlen($number)){ return ""; }
	return Atk14Locale::FormatNumber($number,$decimal_places);
}
