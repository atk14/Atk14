<?php
/**
 * Make a string's first character uppercase
 */
function smarty_modifier_ucfirst($content){
	return String4::ToObject($content)->capitalize()->toString();
}
