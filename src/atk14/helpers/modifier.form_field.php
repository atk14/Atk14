<?php
require_once(dirname(__FILE__)."/modifier.field.php");

/**
* Vytahne field (HTML reprezentaci) formularoveho pole.
*/
function smarty_modifier_form_field($form, $field){
	return smarty_modifier_field($form,$field);
}
