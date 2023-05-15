<?php
function smarty_modifier_join($separator,$array){
	return implode($separator,$array);
}
