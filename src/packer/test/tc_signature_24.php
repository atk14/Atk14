<?php
class TcSignature24 extends TcBase {

	function test(){
		preg_match('/TcSignature(\d+)/',get_class($this),$matches);
		$expected_signature_length = (int)$matches[1];

		$this->assertEquals($expected_signature_length,PACKER_SIGNATURE_LENGTH);

		$sig1 = Packer::_CalculateSignature("test1");
		$sig2 = Packer::_CalculateSignature("test2");

		$this->assertNotEquals($sig1,$sig2);

		$this->assertEquals($expected_signature_length,strlen($sig1));
		$this->assertEquals($expected_signature_length,strlen($sig2));

		// The basic functions must work

		$packed = Packer::Pack("Test");
		$this->assertEquals("Test",Packer::Decode($packed));
	}
}
