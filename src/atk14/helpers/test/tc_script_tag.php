<?php
class TcScriptTag extends TcBase {

	function test(){
		global $ATK14_GLOBAL;
		$template = null;
		$repeat = false;

		$this->assertEquals(trim('
<script>
//<![CDATA[
alert("Hello!");
//]]>
</script>
		'),smarty_block_javascript_tag(array(),'alert("Hello!");',null,$repeat));

		$this->assertEquals(trim('
<script type="text/javascript">
//<![CDATA[
alert("Hello!");
//]]>
</script>
		'),smarty_block_javascript_tag(array("type" => "text/javascript"),'alert("Hello!");',null,$repeat));

		$this->assertEquals("",smarty_block_script_tag(array(),"\n\n",null,$repeat));

		// Content-Security-Policy nonce

		$ATK14_GLOBAL->setCspNonce("abcdefgh");

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
