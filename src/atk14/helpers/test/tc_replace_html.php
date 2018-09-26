<?php
class TcReplaceHtml extends TcBase {

	function test(){
		$repeat = false;

		$out = smarty_block_replace_html(array("id" => "el_id"), "Some content", null, $repeat);
		$this->assertEquals('$("#el_id").html("Some content");',$out);

		$out = smarty_block_replace_html(array("class" => "el_class"), "Other content", null, $repeat);
		$this->assertEquals('$(".el_class").html("Other content");',$out);

		$out = smarty_block_replace_html(array("selector" => "#box > div"), "Other content", null, $repeat);
		$this->assertEquals('$("#box > div").html("Other content");',$out);
	}
}

