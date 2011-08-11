<?php
/**
* Dumps contant value.
*
* <code>
* {"ATK14_APPLICATION_NAME"|dump_constant}
*	</code>
*/
function smarty_modifier_dump_constant($name){
	return defined($name) ? constant($name) : "";
}
