<?php
define("ATK14_USE_INTERNAL_SMARTY", false);
define("ATK14_DOCUMENT_ROOT", __DIR__ );
require_once('../../../../load.php');

class TcSmarty5Render extends TcBase{

	function test() {
		$smarty = Atk14Utils::GetSmarty(array(__DIR__."/templates/"));
		$this->array = array('keyboard' => 7, 3 => 'chicken');
		$smarty->assign(array(
			'a' => 1,
			'e' => 1,
			'f' => 1,
			'test' => $this,
			'array' => $this->array
		));
		$this->counter=0;
		end($this->array);
		next($this->array);
		@$smarty->fetch('tc_smarty_render.tpl'); // this produces enormous error log in PHP 8.1
		$this->assertEquals($this->counter, 6);

		$smarty = Atk14Utils::GetSmarty(array(__DIR__."/templates/"));
		$smarty->assign("token","EXTERNAL");
		$tokens = @$smarty->fetch("tokens.tpl");
		$this->assertEquals('tokens: EXTERNAL | INTERNAL | ASSIGNED | INTERNAL_AGAIN',trim($tokens));
	}
}
