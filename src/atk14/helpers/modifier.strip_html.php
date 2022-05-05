<?php
function smarty_modifier_strip_html($content){
	return String4::ToObject($content)->stripHtml()->toString();
}
