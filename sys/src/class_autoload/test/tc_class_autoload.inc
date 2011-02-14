<?
class tc_class_autoload extends tc_base{
	function test(){
		$this->assertFalse(class_exists("RedFruit"));
		$this->assertFalse(class_exists("RedDwarf"));
		$this->assertFalse(class_exists("RedColor"));
		$this->assertFalse(class_exists("RedBar"));

		class_autoload("./classes/");

		$this->assertTrue(class_exists("RedFruit"));
		$this->assertTrue(class_exists("RedDwarf"));
		$this->assertTrue(class_exists("RedColor"));
		$this->assertTrue(class_exists("RedBar"));
	}
}
