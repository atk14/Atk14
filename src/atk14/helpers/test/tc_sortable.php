<?php
define("ATK14_USE_SMARTY3", true);
define("ATK14_DOCUMENT_ROOT", __DIR__ );
require_once('../../../../load.php');

class TcSortable extends TcBase {

	function test(){
		$content = '<td>ID</td>';

		$sorting = new Atk14Sorting();
		$sorting->add("id");

		$template = Atk14Utils::GetSmarty();
		$template->assign("sorting",$sorting);
		$template->assign("params",new Dictionary());

		$repeat = false;

		$this->assertEquals('<td class="sortable active"><a href="/?order=id-desc" title="Sort table by this column" rel="nofollow">ID <span class="arrow-up">&uArr;</span></a></td>',smarty_block_sortable(array("key" => "id"),$content,$template,$repeat));
		$this->assertEquals('<td class="sortable active"><a href="/?order=id-desc" title="Sort by ID" rel="nofollow">ID <span class="arrow-up">&uArr;</span></a></td>',smarty_block_sortable(array("key" => "id", "title" => "Sort by ID"),$content,$template,$repeat));
	}
}
