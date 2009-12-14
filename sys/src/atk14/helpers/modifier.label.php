<?php
// vim: set et si ts=4 sw=4 enc=utf-8 syntax=php:

/**
* Vytahne label formularoveho pole.
*
* {$form|label:"firstname"}
*/
function smarty_modifier_label($form, $field, $label_suffix = ":")
{
    $field = $form->get_field($field);
    $label = htmlspecialchars($field->label);
    if (!in_array($label[strlen($label)-1], array(':', '?', '.', '!'))) {
        $label .= $label_suffix;
    }
    
    //return $field->label;
    return $field->label_tag(array('contents' => $label));
}
?>
