<?
/**
* {javascript_tag}
*		alert('Hello World')
*	{/javascript_tag}
*/
function smarty_block_javascript_tag($params, $content, &$smarty, &$repeat){
	$out = array();
	$out[] = '<script type="text/javascript">';
	$out[] = '//<![CDATA[';
	$out[] = $content;
	$out[] = '//]]>';
	$out[] = '</script>';

	return join("\n",$out);
}
?>
