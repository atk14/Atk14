<?php
/**
* {$iso_date|format_date} -> 22.1.2008
* {$iso_date|format_date:"j.n."} -> 22.1.
*/
function smarty_modifier_format_date($iso_date,$pattern = ""){
	return Atk14Locale::FormatDate($iso_date,$pattern);
}
