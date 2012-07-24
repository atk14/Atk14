<?php
/**
* Vytahne field (HTML reprezentaci) formularoveho pole.
*
* {$form|field:"firstname"}
*/
function smarty_modifier_field($form, $field){
	$field = $form->get_field($field);
	return $field->as_widget();
}
