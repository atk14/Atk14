<?php
class TcBase extends TcSuperBase{
	function _compare_html($expected,$actual){
		$expected = new XMole("<xml>$expected</xml>");
		$actual = new XMole("<xml>$actual</xml>");
		return XMole::AreSame($expected,$actual);
	}

	function assertHtmlEquals($expected,$actual){
		$this->assertTrue($this->_compare_html($expected,$actual),"\n\n### expected ###\n$expected\n\n### actual ###\n$actual\n\n");
	}

	function _get_uploaded_jpeg(){
		return HTTPUploadedFile::GetInstance(array(
			"tmp_name" => dirname(__FILE__)."/../../http/test/hlava.jpg", // just borrowing a testing image :)
			"name" => "hlava.jpg",
			"error" => 0,
		),
		"image",
		array("testing_mode" => true));
	}
}
