<?php
function smarty_modifier_array_filter($array,$callback = null,$mode = 0){
	return array_filter($array,$callback,$mode);
}
