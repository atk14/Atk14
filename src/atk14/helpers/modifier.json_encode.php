<?php
function smarty_modifier_json_encode($value,$flags = 0,$depth = 512){
	return json_encode($value,(int)$flags,(int)$depth);
}
