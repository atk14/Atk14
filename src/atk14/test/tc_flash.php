<?php
class TcFlash extends TcBase{
	
	function test(){
		$flash = new Atk14Flash();

		$this->assertNull($flash->getMessage("notice"));
		$this->assertNull($flash->getMessage());

		$flash->setMessage("Changes have been saved");

		$this->assertEquals("Changes have been saved",(string)$flash->getMessage());
		$this->assertEquals("Changes have been saved",(string)$flash->getMessage("notice"));
		$this->assertNull($flash->getMessage("error"));

		$message = $flash->getMessage();

		$this->assertEquals("Changes have been saved",$message->getMessage());
		$this->assertEquals("Changes have been saved",(string)$message);
		$this->assertEquals("notice",$message->getType());

		$flash->setMessage("");

		$this->assertNull($flash->getMessage("notice"));
		$this->assertNull($flash->getMessage());
	}

	function test_set_read_state(){
		$flash = new Atk14Flash();

		$flash->setMessage("success","La_Success");
		$this->assertEquals(false,$flash->_FlashRead);

		$this->assertEquals("La_Success",$flash->getMessage("success"));
		$this->assertEquals(true,$flash->_FlashRead);

		//
		$flash2 = new Atk14Flash();

		$flash2->setMessage("success","Another_Success");
		$this->assertEquals(false,$flash2->_FlashRead);

		$this->assertEquals("Another_Success",$flash2->getMessage("success",array("set_read_state" => false)));
		$this->assertEquals(false,$flash2->_FlashRead);

		$this->assertEquals("Another_Success",$flash2->getMessage("success",array("set_read_state" => true)));
		$this->assertEquals(true,$flash2->_FlashRead);

		$this->assertEquals("Another_Success",$flash2->getMessage("success",array("set_read_state" => false)));
		$this->assertEquals(true,$flash2->_FlashRead); // set from the previous call
	}
}
