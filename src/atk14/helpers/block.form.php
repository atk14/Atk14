<?php
/**
 * Smarty plugin for creating forms.
 *
 * Basic usage:
 * <code>
 * {form}
 * {render partial="shared/form_field" fields="first_name,last_name,email"}
 * {/form}
 * </code>
 *
 * Attributes of the <form> tag can be set by passing name of an attribute as the parameter name prefixed with '_' character.
 * <code>
 * {form _id="my_form" _class="admin nice"}
 * {render partial="shared/form_field" fields="first_name,last_name,email"}
 * {/form}
 * </code>
 *
 * @package Atk14
 * @subpackage Helpers
 * @author Jaromir Tomek
 */

/**
 * @param array $params
 * @param string $content
 */
function smarty_block_form($params, $content, $template, &$repeat){
	if($repeat){ return; }
	$smarty = atk14_get_smarty_from_template($template);

	$params = array_merge(array(
		"form" => $smarty->getTemplateVars("form"),
	),$params);

	$form = $params["form"];

	$form->set_attr(Atk14Utils::ExtractAttributes($params));

	$out = array();
	$out[] = $form->begin();
	$out[] = $content;
	$out[] = $form->end();
	return join("\n",$out);
}
