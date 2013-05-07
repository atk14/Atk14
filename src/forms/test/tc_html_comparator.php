<?php
class TcHtmlComparator extends TcBase{
	function test(){
		$this->assertTrue($this->_compare_html('<input type="text" name="login" />','<input type="text" name="login" />'));
		$this->assertTrue($this->_compare_html('<input type="text" name="login" />','<input name="login" type="text" />'));
		$this->assertFalse($this->_compare_html('<input type="text" name="login" />','<input type="text" name="password" />'));
		$this->assertFalse($this->_compare_html('<input type="text" name="login" />','<input type="text" name="login" value="admin" />'));

		$this->assertTrue($this->_compare_html('<a href="http://www.google.cz/" title="Search engine">Google</a>','<a href="http://www.google.cz/" title="Search engine">Google</a>'));
		$this->assertFalse($this->_compare_html('<a href="http://www.google.cz/" title="Search engine">Google</a>','<a href="http://www.google.cz/" title="Search engine">La Google</a>'));
		$this->assertTrue($this->_compare_html('<a href="http://www.google.cz/" title="Search engine">Google</a>','<a title="Search engine" href="http://www.google.cz/">Google</a>'));

		$this->assertTrue($this->_compare_html('Hello <strong>World</strong>, nice to meet you!','Hello <strong>World</strong>, nice to meet you!'));
		$this->assertFalse($this->_compare_html('Hellx <strong>World</strong>, nice to meet you!','Hello <strong>World</strong>, nice to meet you!'));
		$this->assertFalse($this->_compare_html('Hello <strong>World</strong>, nice to meet you!!','Hello <strong>World</strong>, nice to meet you!'));
		$this->assertFalse($this->_compare_html('Hello <strong>world</strong>, nice to meet you!','Hello <strong>World</strong>, nice to meet you!'));
	}
}
