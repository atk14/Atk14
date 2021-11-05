<?php
class TcStripHtml extends TcBase {

	function test(){
		$this->assertEquals('Hello World',smarty_modifier_strip_html('Hello World'));
		$this->assertEquals('Hello World',smarty_modifier_strip_html('Hello W<span>orl</span>d'));
		$this->assertEquals('Hello World',smarty_modifier_strip_html('<h1>Hello<div>W<span>orl</span>d</div></h1>'));

		$src = '
			<style>
				h1. {
					color: red;
				}
			</style>
			<!--
				<em>Inside a comment...</em>
			-->
			<h1>Lorem<sup>*</sup> <small>Ipsum</small></h1>
			<noframes>Sorry no frames!</noframes>
			<p>
				Lorem ipsum dolor sit amet, consectetur Adipiscing &amp; Elit. <a href="http://lorem.ipsum.com/">Maecenas hendrerit risus neque</a>, et semper ligula mattis a. Morbi ma<i>lesu</i>ada augue vel massa commodo.
			</p>
		';
		$out = 'Lorem* Ipsum Lorem ipsum dolor sit amet, consectetur Adipiscing & Elit. Maecenas hendrerit risus neque, et semper ligula mattis a. Morbi malesuada augue vel massa commodo.';
		$this->assertEquals($out,smarty_modifier_strip_html($src));
	}

	function test_block(){
		$params = array();
		$template = null;
		$repeat = false;

		$this->assertEquals('Hello World',smarty_block_strip_html($params,'Hello World',$template,$repeat));
		$this->assertEquals('Hello World',smarty_block_strip_html($params,'<h1>Hello<div>W<span>orl</span>d</div></h1>',$template,$repeat));
	}
}
