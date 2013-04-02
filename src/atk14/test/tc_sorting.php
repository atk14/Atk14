<?php
class TcSorting extends TcBase{
	function test(){
		$sorting = $this->_get_sorting();
		$this->assertEquals("id ASC",$sorting->getOrder());
		$this->assertEquals("id ASC","$sorting");

		$sorting = $this->_get_sorting("created_at-asc");
		$this->assertEquals("created_at DESC",$sorting->getOrder());

		$sorting = $this->_get_sorting("created_at-desc");
		$this->assertEquals("created_at ASC",$sorting->getOrder());

		$sorting = $this->_get_sorting("title-asc");
		$this->assertEquals("title ASC, id ASC",$sorting->getOrder());

		$sorting = $this->_get_sorting("title-desc");
		$this->assertEquals("title DESC, id DESC",$sorting->getOrder());

		$sorting = $this->_get_sorting("author-asc");
		$this->assertEquals("author ASC, id ASC",$sorting->getOrder());

		$sorting = $this->_get_sorting("author-desc");
		$this->assertEquals("author DESC, id DESC",$sorting->getOrder());

		$sorting = $this->_get_sorting("shelf_mark-asc");
		$this->assertEquals("UPPER(shelf_mark) ASC, title ASC",$sorting->getOrder());

		$sorting = $this->_get_sorting("shelf_mark-desc");
		$this->assertEquals("UPPER(shelf_mark) DESC, title DESC",$sorting->getOrder());
	}

	function _get_sorting($order = null){
		$params = new Dictionary();
		if($order){ $params->s("order",$order); }

		$sorting = new Atk14Sorting($params);
		$sorting->add("id");
		$sorting->add("created_at",array("reverse" => true));
		$sorting->add("title",array(
			"ascending_ordering" => "title ASC, id ASC",
			"descending_ordering" => "title DESC, id DESC",
		));

		$sorting->add("author",array(
			"asc" => "author ASC, id ASC",
			"desc" => "author DESC, id DESC",
		));

		$sorting->add("shelf_mark","UPPER(shelf_mark) ASC, title ASC","UPPER(shelf_mark) DESC, title DESC");

		return $sorting;
	}
}
