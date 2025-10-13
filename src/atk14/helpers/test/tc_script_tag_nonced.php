<?php
class TcScriptTagNonced extends TcBase {

	function test(){
		$template = null;
		$repeat = false;

		// Content-Security-Policy nonce

		$this->assertEquals(trim('
<script nonce="abcdefgh">
//<![CDATA[
alert("Hello!");
//]]>
</script>
		'),smarty_block_javascript_tag(array(),'alert("Hello!");',null,$repeat));

		$this->assertEquals(trim('
<script type="text/javascript" nonce="abcdefgh">
//<![CDATA[
alert("Hello!");
//]]>
</script>
		'),smarty_block_javascript_tag(array("type" => "text/javascript"),'alert("Hello!");',null,$repeat));

	}
}
