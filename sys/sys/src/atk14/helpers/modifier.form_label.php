<?php
// vim: set et si ts=4 sw=4 enc=utf-8 syntax=php:

require_once(dirname(__FILE__)."/modifier.label.php");

/**
* Vytahne label formularoveho pole.
*/
function smarty_modifier_form_label($form, $field)
{
    return smarty_modifier_label($form,$field);
}
?>
