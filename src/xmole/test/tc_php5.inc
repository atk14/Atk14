<?php
class tc_php5 extends tc_base{
	function test_to_string(){
		$x = new XMole();
		$this->assertEquals("instance of XMole","$x");
	}
}
?>
