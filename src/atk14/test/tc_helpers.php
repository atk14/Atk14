<?php
class TcHelpers extends TcBase{

	function test_javascript_script_tag(){
		$out = $this->_run_action("helpers/javascript_script_tag");
		$mtime = filemtime("public/javascripts/site.js");
		$this->assertEquals('<script src="/public/javascripts/site.js?v'.$mtime.'"></script>
<script src="/public/javascripts/site.js?v'.$mtime.'" media="screen"></script>
<script src="/public/javascripts/site.v'.$mtime.'.js" media="screen"></script>
<script src="/public/javascripts/nonexisting.js"></script>',trim($out));

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
		$this->assertContains('<li class="red">ADVENTURE: The Adventures of Tom Sawyer by Mark Twain (index=0, 1/2, first)</li>',$out);
		$this->assertContains('<li class="red">ADVENTURE: Swallows and Amazons by Arthur Ransome (index=1, 2/2, last)</li>',$out);

		$this->assertContains('some_value after render: TOP_VALUE',$out);
		$this->assertContains('some_value from the pit: LOWER_VALUE',$out);
		$this->assertContains('some_value from the middle: LOWER_VALUE',$out);
	}

	function test_render_with_forms(){
		$out = $this->_run_action("helpers/render_with_forms");
		$this->assertContains('FirstForm: <form action="/first/" method="post" id="form_helpers_first">',$out);
		$this->assertContains('SecondForm: <form action="/second/" method="post" id="form_helpers_second">',$out);
	}

	function test_render_component(){
		global $ATK14_GLOBAL;
		$out = $this->_run_action("helpers/render_component");

		$this->assertContains('<div id="external_content">Hello World from Mars!</div>',$out);
		$this->assertContains('<div id="external_content_from_other_namespace">Hello from Venus, an planet from the Universe</div>',$out);

		$this->assertEquals("helpers",$ATK14_GLOBAL->getValue("controller"));
		$this->assertEquals("render_component",$ATK14_GLOBAL->getValue("action"));
		$this->assertEquals("",$ATK14_GLOBAL->getValue("namespace"));

		// --

		$content = $this->_run_action("universe/planets/controller_state");
		$content_rp = $this->_run_action("helpers/render_component");

		$this->assertContains('namespace: "universe"
controller: "planets"
action: "controller_state"
prev_namespace: ""
prev_controller: ""
prev_action: ""',$content);

		$this->assertContains('namespace: "universe"
controller: "planets"
action: "controller_state"
prev_namespace: ""
prev_controller: "helpers"
prev_action: "render_component"',$content_rp);

	}

	function test_render_component_with_redirection(){
		$out = $this->_run_action("helpers/render_component_with_redirection",array(),$response);
		$this->assertEquals("http://www.atk14.net/",$response->getLocation());
		$this->assertEquals("",$out);
	}

	function test_a(){
		$out = $this->_run_action("helpers/a");
		$this->assertContains('<a href="/en/books/">List Books</a>',$out);
		$this->assertContains('<a title="Book info" href="/en/books/detail/?id=123">Book#123</a>',$out);
		$this->assertContains('<a href="/en/books/detail/?id=456#detail">Book#456</a>',$out);
		$this->assertContains('<a class="active" href="/en/books/detail/?id=7890#detail">Book#7890</a>',$out);
	}

	function test_link_to(){
		// prerequirement
		global $ATK14_GLOBAL;
		$ATK14_GLOBAL->setValue("lang","en");
		$this->assertEquals("cs",$ATK14_GLOBAL->getDefaultLang());
		$this->assertEquals("en",$ATK14_GLOBAL->getLang());

		$out = $this->_run_action("helpers/link_to");
		$this->assertContains('Book#1200 URL: http://www.testing.cz/en/books/detail/?id=1200&amp;format=xml',$out);
		$this->assertContains('Kniha#600 URL: https://secure.testing.cz/cs/books/detail/?id=600#detail',$out);
	}

	function test_content(){
		$out = $this->_run_action("helpers/content");

		$out = str_replace("<content>A song just for fun\n</content>","<content>A song just for fun</content>",$out); // HACK: In Smarty3 there is an extra new line, dont know why

		$this->assertContains("<content>A song just for fun</content>",$out);
		$this->assertContains('<greeting>Hello</greeting>',$out);
		$this->assertContains('<lyrics>Five little monkeys jumping on the bed. One fell off and bumped his head. Mama called the doctor and the doctor said, "No more monkeys jumping on the bed!"</lyrics>',$out);

		$this->assertContains('<title>La Musique | DEMO</title>',$out);
	}

	function test_cache(){
		$out = $this->_run_action("helpers/cache");
		$out2 = $this->_run_action("helpers/cache");

		$this->assertEquals($out,$out2);
		$this->assertNotContains('uniqid: ""',$out); // just to make sure that a proper $uniqid was assigned to the template
	}
}
