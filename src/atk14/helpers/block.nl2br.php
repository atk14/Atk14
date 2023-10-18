<?php
function smarty_block_nl2br($params,$content,$template,&$repeat){
	if($repeat){ return; }

	return nl2br((string)$content,true);
}
