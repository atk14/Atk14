<?php
/**
 * Smarty plugin for creating forms submiting requests asynchronously.
 *
 * Basic usage:
 * <code>
 * {form_remote}
 * {render partial=shared/form_field fields=first_name,last_name,email}
 * {/form_remote}
 * </code>
 *
 * Attributes of the <form> tag can be set by passing name of an attribute as the parameter name prefixed with '_' character.
 * <code>
 * {form_remote _id="my_form" _class="admin nice"}
 * {render partial=shared/form_field fields=first_name,last_name,email}
 * {/form_remote}
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
function smarty_block_form_remote($params, $content, &$smarty, &$repeat)
{
	$params = array_merge(array(
		"form" => $smarty->_tpl_vars["form"],
	),$params);

	$form = $params["form"];

	$form->set_attr("class","remote_form");
	$form->set_attr("data-remote","true");

	$form->set_attr(Atk14Utils::ExtractAttributes($params));

	$out = array();
	$out[] = $form->begin();
	$out[] = $content;
	$out[] = $form->end();
	return join("\n",$out);
}
