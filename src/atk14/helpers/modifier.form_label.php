<?php
require_once(dirname(__FILE__)."/modifier.label.php");

/**
* Vytahne label formularoveho pole.
*/
function smarty_modifier_form_label($form, $field){
	return smarty_modifier_label($form,$field);
}
