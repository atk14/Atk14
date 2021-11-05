<?php
function smarty_block_strip_tags($params, $content, $template, &$repeat){
	if($repeat){ return; }

	Atk14Require::Helper("modifier.strip_tags");

	return smarty_modifier_strip_tags($content);
}
