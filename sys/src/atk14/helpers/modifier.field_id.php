<?php
/**
 * Zjisti id policka.
 * <label for="{$form|field_id:"usename"}">....</label>
 */
function smarty_modifier_field_id($form, $field)
{
    $field = $form->get_field($field);
    return $field->id_for_label();
}
