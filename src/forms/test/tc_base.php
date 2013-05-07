<?php
class TcBase extends TcSuperBase{
	function _compare_html($expected,$actual){
		$expected = new XMole("<xml>$expected</xml>");
		$actual = new XMole("<xml>$actual</xml>");
		return XMole::AreSame($expected,$actual);
	}
}
