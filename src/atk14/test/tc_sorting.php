<?php
class TcSorting extends TcBase{

	function test(){
		$sorting = new Atk14Sorting();
		$this->assertEquals("","$sorting");
		$this->assertEquals(count($sorting), 0);

		// -- default
		$sorting = $this->_get_sorting();
		$this->assertEquals(count($sorting), 7);
		$this->assertEquals("id ASC",$sorting->getOrder());
		$this->assertEquals("id ASC","$sorting");

		$sorting = $this->_get_sorting("id-desc");
		$this->assertEquals("id DESC",$sorting->getOrder());

		// --
		$sorting = $this->_get_sorting("created_at");
		$this->assertEquals("created_at DESC",$sorting->getOrder());

		$sorting = $this->_get_sorting("created_at-asc"); // obsolete key format
		$this->assertEquals("created_at DESC",$sorting->getOrder());

		$sorting = $this->_get_sorting("created_at-desc");
		$this->assertEquals("created_at ASC",$sorting->getOrder());

		// --
		$sorting = $this->_get_sorting("title");
		$this->assertEquals("title ASC, id ASC",$sorting->getOrder());

		$sorting = $this->_get_sorting("title-asc"); // obsolete key format
		$this->assertEquals("title ASC, id ASC",$sorting->getOrder());

		$sorting = $this->_get_sorting("title-desc");
		$this->assertEquals("title DESC, id DESC",$sorting->getOrder());

		// --
		$sorting = $this->_get_sorting("author");
		$this->assertEquals("author ASC, id ASC",$sorting->getOrder());

		$sorting = $this->_get_sorting("author-asc"); // obsolete key format
		$this->assertEquals("author ASC, id ASC",$sorting->getOrder());

		$sorting = $this->_get_sorting("author-desc");
		$this->assertEquals("author DESC, id DESC",$sorting->getOrder());

		// --
		$sorting = $this->_get_sorting("shelf_mark");
		$this->assertEquals("UPPER(shelf_mark) ASC, title ASC",$sorting->getOrder());

		$sorting = $this->_get_sorting("shelf_mark-asc"); // obsolete key format
		$this->assertEquals("UPPER(shelf_mark) ASC, title ASC",$sorting->getOrder());

		$sorting = $this->_get_sorting("shelf_mark-desc");
		$this->assertEquals("UPPER(shelf_mark) DESC, title DESC",$sorting->getOrder());

		// --
		$sorting = $this->_get_sorting("url");
		$this->assertEquals("articles.url",$sorting->getOrder());

		$sorting = $this->_get_sorting("url-asc"); // obsolete key format
		$this->assertEquals("articles.url",$sorting->getOrder());

		$sorting = $this->_get_sorting("url-desc");
		$this->assertEquals("articles.url DESC",$sorting->getOrder());

		// --
		$sorting = $this->_get_sorting("subtitle");
		$this->assertEquals("articles.subtitle ASC",$sorting->getOrder());

		$sorting = $this->_get_sorting("subtitle-asc"); // obsolete key format
		$this->assertEquals("articles.subtitle ASC",$sorting->getOrder());

		$sorting = $this->_get_sorting("subtitle-desc");
		$this->assertEquals("articles.subtitle DESC",$sorting->getOrder());
	}

	function test_ArrayAccess(){
		$sorting = $this->_get_sorting();

		$this->assertEquals(null,$sorting["rank"]);

		$sorting["rank"] = "rank";
		$this->assertEquals(array("rank","rank DESC"),$sorting["rank"]);
		//
		$this->assertEquals("rank",$sorting->getOrder("rank"));
		$this->assertEquals("rank",$sorting->getOrder("rank-asc"));
		$this->assertEquals("rank DESC",$sorting->getOrder("rank-desc"));

		$sorting["rank"] = array("rank ASC, id ASC", "rank DESC, id DESC");
		$this->assertEquals(array("rank ASC, id ASC","rank DESC, id DESC"),$sorting["rank"]);
		//
		$this->assertEquals("rank ASC, id ASC",$sorting->getOrder("rank"));
		$this->assertEquals("rank ASC, id ASC",$sorting->getOrder("rank-asc"));
		$this->assertEquals("rank DESC, id DESC",$sorting->getOrder("rank-desc"));

		$this->assertEquals("id ASC",$sorting->getOrder()); // default
	}

	function test_ArrayIterator(){
		$sorting = $this->_get_sorting();

		$ary = array();
		foreach($sorting as $item){
			$ary[] = $item;
		}

		$this->assertEquals(array(
			"id",
			"created_at",
			"title",
			"author",
			"shelf_mark",
			"url",
			"subtitle",
		),$ary);
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

		$sorting->add("url","articles.url");

		$sorting->add("subtitle","articles.subtitle ASC");

		return $sorting;
	}
}
