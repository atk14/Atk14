<?php
define("ATK14_USE_SMARTY3", false);
define("ATK14_DOCUMENT_ROOT", __DIR__ );
require_once('../../../../load.php');

class TcSmarty2Render extends TcBase{

	function test() {
		$smarty = Atk14Utils::GetSmarty(array(__DIR__."/templates/"));
		$this->array = array('keyboard' => 8, 3 => 'chicken');
		$smarty->assign(array(
			'a' => 1,
			'e' => 1,
			'f' => 1,
			'one' => array(1),
			'test' => $this,
			#copy array
			'array' => array_slice($this->array,0,2, true)
		));

		$this->counter=0;
		end($this->array);
		next($this->array);

		$smarty->register_function('increment_counter', 'smarty_function_increment_counter', false);
		$smarty->register_function('assert', 'smarty_function_assert', false);
		$smarty->register_function('die', 'smarty_function_die', false);
		$smarty->register_modifier('dump', 'var_dump', false);
		$smarty->register_function('assert_consume', 'smarty_function_assert_consume', false);
		$smarty->fetch('tc_smarty_render.tpl');
		$this->assertEquals($this->counter, 6);

		$smarty = Atk14Utils::GetSmarty(array(__DIR__."/templates/"));
		$smarty->assign("token","EXTERNAL");
		$tokens = $smarty->fetch("tokens.tpl");
		$this->assertEquals('tokens: EXTERNAL | INTERNAL | ASSIGNED | INTERNAL_AGAIN',trim($tokens));
	}
}

function smarty_function_assert($params, $template) {
	if($params['smarty3']) {
		return;
	}
	$var = $template->getTemplateVars($params['var']);
	$template->getTemplateVars('test')->assertEquals($params['value'], $var, 'FAILED TEST: '. $params['message']. " - " . $params['comment']);
}

function smarty_function_die() {
	die();
}

function smarty_function_increment_counter($params, $template) {
	$template->getTemplateVars('test')->counter++;
}

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
