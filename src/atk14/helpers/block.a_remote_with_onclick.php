<?php
/**
* Like block helpek remote_link, but in this case an uglu onclick event is rendered directly into <a> tag.
*/
require_once(dirname(__FILE__)."/block.a_remote.php");
function smarty_block_a_remote_with_onclick($params, $content, $template, &$repeat){
	if($repeat){ return; }
	$params["__be_pretty_ugly__"] = true;
	return smarty_block_a_remote($params,$content,$template,$repeat);
}
