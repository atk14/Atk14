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
		'),smarty_block_javascript_tag([],'alert("Hello!");',null,$repeat));

		$this->assertEquals(trim('
<script type="text/javascript">
//<![CDATA[
alert("Hello!");
//]]>
</script>
		'),smarty_block_javascript_tag(["type" => "text/javascript"],'alert("Hello!");',null,$repeat));

		$this->assertEquals("",smarty_block_script_tag([],"\n\n",null,$repeat));
	}
}
