<?php
class TcStripTags extends TcBase {

	function test(){
		$this->assertEquals('Hello World',smarty_modifier_strip_tags('Hello World'));
		$this->assertEquals('Hello World',smarty_modifier_strip_tags('Hello W<span>orl</span>d'));
		$this->assertEquals('Hello World',smarty_modifier_strip_tags('<h1>Hello<div>W<span>orl</span>d</div></h1>'));

		$src = '
			<h1>Lorem<sup>*</sup> <small>Ipsum</small></h1>
			<p>
				Lorem ipsum dolor sit amet, consectetur Adipiscing &amp; Elit. <a href="http://lorem.ipsum.com/">Maecenas hendrerit risus neque</a>, et semper ligula mattis a. Morbi ma<i>lesu</i>ada augue vel massa commodo.
			</p>
		';
		$out = 'Lorem* Ipsum

				Lorem ipsum dolor sit amet, consectetur Adipiscing & Elit. Maecenas hendrerit risus neque, et semper ligula mattis a. Morbi malesuada augue vel massa commodo.';
		$this->assertEquals($out,smarty_modifier_strip_tags($src));
	}

	function test_block(){
		$params = array();
		$template = null;
		$repeat = false;

		$this->assertEquals('Hello World',smarty_block_strip_tags($params,'Hello World',$template,$repeat));
		$this->assertEquals('Hello World',smarty_block_strip_tags($params,'<h1>Hello<div>W<span>orl</span>d</div></h1>',$template,$repeat));
	}
}
