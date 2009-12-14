<?
/**
* Escapuje data pro bezpecny tisk do HTML.
*
* {h}{$data}{/h}
*/
function smarty_block_h($params, $content, &$smarty, &$repeat)
{
	return htmlspecialchars($content);
}
?>
