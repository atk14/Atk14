<?php
// vim: set et si ts=4 sw=4 enc=utf-8 syntax=php:

/**
* Vytahne field (HTML reprezentaci) formularoveho pole.
*
* {$form|field:"firstname"}
*/
function smarty_modifier_field($form, $field)
{
    $field = $form->get_field($field);
    return $field->as_widget();
}
?>
