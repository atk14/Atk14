<?php
class TcJavascriptTag extends TcBase {

	function test(){
		$template = null;
		$repeat = false;

		$this->assertEquals(trim('
<script>
//<![CDATA[
alert("Hello!");
//]]>
</script>
		'),smarty_block_javascript_tag(array(),'alert("Hello!");',null,$repeat));

		$this->assertEquals("",smarty_block_javascript_tag(array(),"\n\n",null,$repeat));
	}
}
