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
	}

}
