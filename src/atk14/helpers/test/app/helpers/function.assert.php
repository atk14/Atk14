<?php
function smarty_function_assert($params, $template) {
	$params += [
		'smarty3' => null,
		'var' => null,
		'message' => null,
		'comment' => null,
	];
	if($params['smarty3']) {
		return;
	}
	$var = $template->getTemplateVars($params['var']);
	$template->getTemplateVars('test')->assertEquals($params['value'], $var, 'FAILED TEST: '. $params['message']. " - " . $params['comment']);
}
