<?php
class TcHelpers extends TcBase{
	function test_javascript_script_tag(){
		$out = $this->_run_action("helpers/javascript_script_tag");
		$mtime = filemtime("public/javascripts/site.js");
		$this->assertEquals('<script src="/public/javascripts/site.js?'.$mtime.'" type="text/javascript"></script>
<script src="/public/javascripts/site.js?'.$mtime.'" type="text/javascript" media="screen"></script>
<script src="/public/javascripts/nonexisting.js" type="text/javascript"></script>',trim($out));

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
		$this->assertContains('<li class="red">ADVENTURE: The Adventures of Tom Sawyer by Mark Twain (nr#0)</li>',$out);
		$this->assertContains('<li class="red">ADVENTURE: Swallows and Amazons by Arthur Ransome (nr#1)</li>',$out);

		$this->assertContains('some_value after render: TOP_VALUE',$out);
		$this->assertContains('some_value from the pit: LOWER_VALUE',$out);
		$this->assertContains('some_value from the middle: LOWER_VALUE',$out);
	}

	function test_render_component(){
		global $ATK14_GLOBAL;
		$out = $this->_run_action("helpers/render_component");
		$this->assertEquals('<div id="external_content">Hello World from Mars!</div>',trim($out));

		$this->assertEquals("helpers",$ATK14_GLOBAL->getValue("controller"));
		$this->assertEquals("render_component",$ATK14_GLOBAL->getValue("action"));
	}

	function test_a(){
		$out = $this->_run_action("helpers/a");
		$this->assertContains('<a href="/en/books/">List Books</a>',$out);
		$this->assertContains('<a title="Book info" href="/en/books/detail/?id=123">Book#123</a>',$out);
		$this->assertContains('<a href="/en/books/detail/?id=456#detail">Book#456</a>',$out);
	}

	function test_link_to(){
		// prerequirement
		global $ATK14_GLOBAL;
		$ATK14_GLOBAL->setValue("lang","en");
		$this->assertEquals("cs",$ATK14_GLOBAL->getDefaultLang());
		$this->assertEquals("en",$ATK14_GLOBAL->getLang());

		$out = $this->_run_action("helpers/link_to");
		$this->assertContains('Book#1200 URL: http://www.testing.cz/en/books/detail/?id=1200&amp;format=xml',$out);
		$this->assertContains('Kniha#600 URL: https://www.testing.cz/cs/books/detail/?id=600#detail',$out);
	}
}
