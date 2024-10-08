<?php
function smarty_modifier_strtoupper($content){
	return String4::ToObject($content)->upper()->toString();
}
