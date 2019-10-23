<?php
class TcSlugify extends TcBase {

	function test(){
		$this->assertEquals("don-t-cross-the-streams",smarty_modifier_slugify("Don't cross the streams"));
		$this->assertEquals("don-t-cr",smarty_modifier_slugify("Don't cross the streams",8));
		$this->assertEquals("don-t-cross",smarty_modifier_slugify("Don't cross the streams",11));
		$this->assertEquals("skakajici-kure",smarty_modifier_slugify("Skákající kuře"));
	}
}
