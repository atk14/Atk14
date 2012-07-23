<?php
/**
* In a template in an ATK14 project one can escape curly bracets:
*
* <code>
*		function js_alert(message)\{ alert($message); return false; \}
* </code>
* 
* one can define a data-* attribute in a link
*
* <code>
*		{a_remote action=detail id=$product _class=detail _data-type=json}Detail{/a_remote}
* </code>
*
* Such things are not possible in Smarty template engine.
* 
* This function changes a template source in some ways to make this possible.
* Smarty calls it automatically before a template compilation.
*/
function atk14_smarty_prefilter($tpl_source, &$smarty = null){
	$tpl_source = strtr($tpl_source,array(
		"\\{" => "{literal}{{/literal}",
		"\\}" => "{literal}}{/literal}",
	));
	
	$tpl_source = preg_replace("/({(a|a_remote|a_destroy|form|form_remote)\\s+[^}]*\\b_data)-/","\\1___",$tpl_source);
	/*
	if(preg_match("/^([^\s]+)\*\s*Smarty\s*\*([^\s]+)/",$tpl_source,$matches)){
		 $smarty->left_delimiter = $matches[1];
		 $smarty->right_delimiter = $matches[2];
		 $tpl_source = preg_replace("/^([^\s]+)\*\s*Smarty\s*\*([^\s]+)/","",$tpl_source);
	}
	*/
	return $tpl_source;
}

/**
 * Handles some of Smarty errors.
 */
function atk14_error_handler($errno, $errstr, $errfile, $errline){
	global $HTTP_RESPONSE;

	// catching Smarty syntax error
	if($errno==E_USER_ERROR && preg_match("/^Smarty/",$errstr)){
		if(DEVELOPMENT){
			echo "$errstr";
		}else{
			$HTTP_RESPONSE->internalServerError();
			$HTTP_RESPONSE->flushAll();
		}
		error_log($errstr);
	}

	// catching Smarty template missing error
	if($errno==512 && preg_match("/^Smarty/",$errstr)){
		if(DEVELOPMENT){
			echo "$errstr";
		}
		error_log($errstr);
	}

	//echo "$errno: $errstr\n";

	return false;
}
set_error_handler("atk14_error_handler");

/**
 * smarty2 <-> smarty3 compatibility hack
 */
function atk14_get_smarty_from_template($template){
	return isset($template->smarty) ? $template->smarty : $template;
}
