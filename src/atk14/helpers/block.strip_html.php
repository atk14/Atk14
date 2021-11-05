<?php
function smarty_block_strip_html($params, $content, $template, &$repeat){
	if($repeat){ return; }

	Atk14Require::Helper("modifier.strip_html");

	return smarty_modifier_strip_html($content);
}
