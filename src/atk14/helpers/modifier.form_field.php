<?php
/**
 * Renders a field (as widget) from the given form
 *
 * In fact this is just an alias for smarty modifier field.
 */
function smarty_modifier_form_field($form, $field_name, $options = ""){
	Atk14Require::Helper("modifier.field");
	return smarty_modifier_field($form,$field_name,$options);
}
