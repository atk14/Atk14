<?php
function smarty_modifier_html_entity_decode($string,$flags = ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401){
	return html_entity_decode($string,$flags);
}
