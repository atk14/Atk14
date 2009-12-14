<?
/**
* {replace_html id="login_div"}Jste prihlasen jako <em>admin</em>.{/replace_html}
* {replace_html selector="login_form div"}Jste prihlasen jako <em>admin</em>.{/replace_html}
*/
function smarty_block_replace_html($params, $content, &$smarty, &$repeat){
	$content = Atk14Utils::EscapeForJavascript($content);
	
	$selector = isset($params["id"]) ? "#$params[id]" : $params["selector"];

	return "$('$selector').html(\"$content\");";
}
?>
