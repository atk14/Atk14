<?php
function smarty_modifier_uniqid($prefix){
	$prefix = (string)$prefix;
	return uniqid($prefix);
}
