<?php
/**
 * Renders a field (as widget) from the given form
 *
 *	{$form|field:"firstname"}
 *
 * Label can be copied to placeholder (in case of Input* fields)
 *
 *	{$form|field:"search":"label_to_placeholder"}
 */
function smarty_modifier_field($form, $field_name, $options = ""){
	$options = Atk14Utils::StringToOptions($options);
	$options += array(
		"label_to_placeholder" => false,
	);

	$field = $form->get_field($field_name);

	if($options["label_to_placeholder"]){
		$orgin_attrs = $field->widget->attrs;
		if(is_subclass_of($field->widget,"Input") && !array_key_exists("placeholder",$field->widget->attrs)){
			$field->widget->attrs["placeholder"] = $field->label;
		}
	}

	$out = $field->as_widget();

	if($options["label_to_placeholder"]){
		$field->widget->attrs = $orgin_attrs;
	}

	return $out;
}
