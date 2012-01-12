<?php
/**
 * {error_log}Some error notice{/error_log}
 */
function smarty_block_error_log($params, $content, &$smarty, &$repeat)
{
	return '<p style="background-color: white; color: red; font-weight: bold;">'.h($content).'</p>';
}
