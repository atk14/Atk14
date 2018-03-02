<?php
class TcSmartyPrefilter extends TcBase{

	function test(){
		$this->assertEquals("Hello",atk14_smarty_prefilter("Hello"));

		$this->assertEquals('{$hello}',atk14_smarty_prefilter('{$hello}'));
		$this->assertEquals('{$hello nofilter}',atk14_smarty_prefilter('{$hello nofilter}'));
		$this->assertEquals('{$hello nofilter}',atk14_smarty_prefilter('{!$hello}'));

		$this->assertEquals('{$hello|modifier nofilter}',atk14_smarty_prefilter('{!$hello|modifier}'));
		$this->assertEquals('{$hello|modifier:"param=1" nofilter}',atk14_smarty_prefilter('{!$hello|modifier:"param=1"}'));
		$this->assertEquals('{$hello|modifier:"param={$value}" nofilter}',atk14_smarty_prefilter('{!$hello|modifier:"param={$value}"}'));
		$this->assertEquals('{$hello|modifier:{$value} nofilter}',atk14_smarty_prefilter('{!$hello|modifier:{$value}}'));

		$this->assertEquals('{literal}{{/literal}Hello{literal}}{/literal}',atk14_smarty_prefilter('\{Hello\}'));

		$this->assertEquals('{a_remote action=detail id=$product _data___type=json}detail{/a_remote}',atk14_smarty_prefilter('{a_remote action=detail id=$product _data-type=json}detail{/a_remote}'));
		$this->assertEquals('{a_destroy _data___type=json id=$product}delete{/a_remote}',atk14_smarty_prefilter('{a_destroy _data-type=json id=$product}delete{/a_remote}'));
	}
}
