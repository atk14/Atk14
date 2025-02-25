<?php
define("ATK14_USE_SMARTY3", false);
define("ATK14_USE_SMARTY4", false);
define("ATK14_USE_SMARTY5", false);
define("ATK14_DOCUMENT_ROOT", __DIR__ );
require_once('../../../../load.php');

// Why do we need theese requires? Don't know...
require_once(__DIR__ . "/app/helpers/function.assert_consume.php");
require_once(__DIR__ . "/app/helpers/function.assert.php");
require_once(__DIR__ . "/app/helpers/function.die.php");
require_once(__DIR__ . "/app/helpers/function.increment_counter.php");

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
		@$smarty->fetch('tc_smarty_render.tpl'); // this produces enormous error log in PHP 8.1
		$this->assertEquals($this->counter, 6);

		$smarty = Atk14Utils::GetSmarty(array(__DIR__."/templates/"));
		$smarty->assign("token","EXTERNAL");
		$tokens = @$smarty->fetch("tokens.tpl");
		$this->assertEquals('tokens: EXTERNAL | INTERNAL | ASSIGNED | INTERNAL_AGAIN',trim($tokens));
	}
}
