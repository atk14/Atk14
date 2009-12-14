<?php
// vim: set et si ts=4 sw=4 enc=utf-8 syntax=php:

require_once(dirname(__FILE__)."/modifier.field.php");

/**
* Vytahne field (HTML reprezentaci) formularoveho pole.
*/
function smarty_modifier_form_field($form, $field)
{
    return smarty_modifier_field($form,$field);
}
?>
