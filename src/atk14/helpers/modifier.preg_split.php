<?php
function smarty_modifier_preg_split($pattern,$subject){
	return preg_split($pattern,$subject);
}
