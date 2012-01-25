<?php
/**
 * Tests for Creature class.
 */
class tc_creature extends tc_base{

	function test_has_image(){
		$creature = Creature::CreateNewRecord(array(
			"name" => "A Testing One"
		));
		$this->assertFalse($creature->hasImage());

		$creature->s("image_url","http://example.com/nice_picture.jpg");
		$this->assertTrue($creature->hasImage());
	}
}

