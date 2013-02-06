<?php
class TcMergingArrays extends TcBase{

	/**
	 * Testing whether array()+array() works like array_merge()
	 */
	function test(){
		$options = array();
		$this->assertEquals(array("color" => "red", "direction" => "north"),$options += array("color" => "red", "direction" => "north"));

		$options = array("color" => "blue");
		$this->assertEquals(array("color" => "blue", "direction" => "north"),$options += array("color" => "red", "direction" => "north"));

		$options = array("color" => "blue", "highlight" => true);
		$this->assertEquals(array("color" => "blue", "direction" => "north", "highlight" => true),$options += array("color" => "red", "direction" => "north"));
	}
}
