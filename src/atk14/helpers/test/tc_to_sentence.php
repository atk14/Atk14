<?php
class TcToSentence extends TcBase {
	function test() {
		$singers = array("George Michael");
		$this->assertEquals("George Michael", smarty_modifier_to_sentence($singers));
		$singers[] = "Boy George";
		$this->assertEquals("George Michael and Boy George", smarty_modifier_to_sentence($singers));
		$singers[] = "Jimmy Somerville";
		$this->assertEquals("George Michael, Boy George and Jimmy Somerville", smarty_modifier_to_sentence($singers));
	}
}
