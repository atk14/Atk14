<?php
/**
 * Widget for hidden input field.
 *
 * Outputs field of this type:
 * <code>
 * <input type="hidden" />
 * </code>
 *
 * @package Atk14
 * @subpackage Forms
 */
class HiddenInput extends Input
{
	var $input_type = 'hidden';
	var $is_hidden = true;
}
