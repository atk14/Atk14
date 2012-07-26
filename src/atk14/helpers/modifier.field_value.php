<?php
/**
* Vytahne hodnotu formularoveho pole.
*
* {$form|field_value:"firstname"}
*/
function smarty_modifier_field_value($form, $field){
	$value = $form->data[$field];
	return $value;
}
