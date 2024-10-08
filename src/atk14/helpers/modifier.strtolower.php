<?php
function smarty_modifier_strtolower($content){
	return String4::ToObject($content)->lower()->toString();
}
