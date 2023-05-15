<?php
function smarty_modifier_constant($name){
	return constant($name);
}
