<?php
define("ATK14_USE_SMARTY3", true);
define("ATK14_DOCUMENT_ROOT", __DIR__ );
require_once('../../../../load.php');

class TcARemote extends TcBase {

	function test(){
		$content = "Detail";
		$repeat = false;

		$template = Atk14Utils::GetSmarty();
		$template->assign("controller","products");
		$template->assign("action","index");
		$template->assign("lang","en");

		// the new feature is suppressed by default
		$this->assertFalse(constant("ATK14_AUTO_HIDE_HREF_IN_REMOTE_LINKS"));

		// a plain GET remote link exposes the real url in href
		$this->assertEquals(
			'<a data-remote="true" href="/en/products/detail/?id=42" class="remote_link">Detail</a>',
			smarty_block_a_remote(["action" => "detail", "id" => 42],$content,$template,$repeat)
		);

		// a POST remote link also exposes the real url in href, as ATK14_AUTO_HIDE_HREF_IN_REMOTE_LINKS is off by default
		$this->assertEquals(
			'<a data-remote="true" data-method="post" href="/en/products/destroy/?id=42" class="remote_link post">Detail</a>',
			smarty_block_a_remote(["action" => "destroy", "id" => 42, "_method" => "post"],$content,$template,$repeat)
		);

		// _hide_href=true hides the real url (moved into data-href) even for a GET link
		$this->assertEquals(
			'<a data-remote="true" href="#" data-href="/en/products/detail/?id=42" class="remote_link">Detail</a>',
			smarty_block_a_remote(["action" => "detail", "id" => 42, "_hide_href" => true],$content,$template,$repeat)
		);

		// _hide_href=false keeps the real url exposed even for a POST link
		$this->assertEquals(
			'<a data-remote="true" data-method="post" href="/en/products/destroy/?id=42" class="remote_link post">Detail</a>',
			smarty_block_a_remote(["action" => "destroy", "id" => 42, "_method" => "post", "_hide_href" => false],$content,$template,$repeat)
		);

		// _confirm adds data-confirm and the "confirm" class
		$this->assertEquals(
			'<a data-remote="true" data-confirm="Are you sure?" data-method="post" class="confirm remote_link post" href="/en/products/destroy/?id=42">Detail</a>',
			smarty_block_a_remote(["action" => "destroy", "id" => 42, "_method" => "post", "_confirm" => "Are you sure?"],$content,$template,$repeat)
		);
	}
}
