<?php
/**
 * Smarty modifier plugin
 *
 * Renders tag <label></label> for specified form input.
 *
 * Basic form
 * <code>
 * {$form|label:"firstname"}
 * </code>
 *
 * Extended form with suffix specification:
 * <code>
 * {$form|label:"firstname":"-"}
 * </code>
 *
 * outputs:
 * <code>
 * <label for="firstname">First name</label>
 * </code>
 *
 * @package Atk14
 * @subpackage Helpers
 * @uses smarty_modifier_label
 *
 */

/**
 * Smarty modifier function, renders tag <label></label> for specified form input.
 *
 * @param Atk14Form $form
 * @param string $field
 * @param string $label_suffix
 */
function smarty_modifier_label($form, $field, $label_suffix = ":"){
	$field = $form->get_field($field);
	$label = h($field->label);
	if (!in_array($label[strlen($label)-1], array(':', '?', '.', '!'))) {
		$label .= $label_suffix;
	}
	
	//return $field->label;
	return $field->label_tag(array('contents' => $label));
}
