<?php
function smarty_function_assert_consume($params, $template) {
	$test = $template->getTemplateVars('test');
	$array = &$test->array;
	if(current($array)===false) {
		reset($array);
		$test->assertEquals(null, $params['key']);
		$test->assertEquals(null, $params['value']);
	} else {
		$test->assertEquals(key($array), $params['key']);
		$test->assertEquals(current($array), $params['value']);
		next($array);
	}
}
