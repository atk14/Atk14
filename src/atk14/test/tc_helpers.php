<?php
class TcHelpers extends TcBase{
	function test_javascript_script_tag(){
		$out = $this->_run_action("helpers/javascript_script_tag");
		$mtime = filemtime("public/javascripts/site.js");
		$this->assertEquals('<script src="/public/javascripts/site.js?'.$mtime.'" type="text/javascript"></script>
<script src="/public/javascripts/site.js?'.$mtime.'" type="text/javascript" media="screen"></script>
<!-- javascript file not found: '.ATK14_DOCUMENT_ROOT.'public/javascripts/nonexisting.js -->',trim($out));

	}

	function test_sortable(){
		$out = $this->_run_action("helpers/sortable");
		$xm = $this->_html2xmole($out);
		$theads = $xm->get_xmoles("table/thead/tr/th");
		$this->assertEquals("sortable active",$theads[0]->get_attribute("th","class"));
		$this->assertEquals("name sortable",$theads[1]->get_attribute("th","class")); // trida name je v sablone app/views/helpers/sortable.tpl
	}

	function test_h(){
		$out = $this->_run_action("helpers/h");
		$this->assertContains('escaped value: The book &lt;strong&gt;is mine!&lt;/strong&gt;',$out);
		$this->assertContains('plain value: The book <strong>is mine!</strong>',$out);
		$this->assertContains('escaped value (in a block): The book &lt;strong&gt;is mine!&lt;/strong&gt;',$out);
	}

	function test_render(){
		$out = $this->_run_action("helpers/render");
		$this->assertContains('<li>The Adventures of Tom Sawyer by Mark Twain</li>',$out);
		$this->assertContains('<li>Swallows and Amazons by Arthur Ransome</li>',$out);
	}
}
