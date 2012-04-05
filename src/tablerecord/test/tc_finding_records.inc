<?
class tc_finding_records extends tc_base{
	function test_find_first(){
		$this->_find_first(array(
			"conditions" => array("title" => "Creepy Green Light")
		));

		$this->_find_first(array(
			"condition" => array("title" => "Creepy Green Light")
		));

		$this->_find_first(array(
			"conditions" => "title=:title",
			"bind_ar" => array(":title" => "Creepy Green Light"),
		));

		$this->_find_first(array(
			"condition" => "title=:title",
			"bind" => array(":title" => "Creepy Green Light"),
		));

		$this->_find_first(array(
			"conditions" => array("title=:title"),
			"bind_ar" => array(":title" => "Creepy Green Light"),
		));

		$this->_find_first(array(
			"condition" => array("title=:title"),
			"bind" => array(":title" => "Creepy Green Light"),
		));

		$this->_find_first("title=:title",array(":title" => "Creepy Green Light"));
		$this->_find_first("title","Creepy Green Light");
		$this->_find_first("title='Creepy Green Light'");

		// -- old ways, PHP4 compatible

		$this->_find_first_old_way(array(
			"class_name" => "Article",
			"conditions" => array("title=:title"),
			"bind_ar" => array(":title" => "Creepy Green Light")
		));

		$this->_find_first_old_way(array(
			"class" => "Article",
			"condition" => array("title=:title"),
			"bind" => array(":title" => "Creepy Green Light")
		));

		$this->_find_first_old_way(array(
			"class_name" => "Article",
			"conditions" => array("title" => "Creepy Green Light"),
		));
	}

	function _find_first($params,$options = array()){
		$article = Article::CreateNewRecord(array(
			"title" => "Creepy Green Light"
		));

		$a = Article::FindFirst($params,$options);
		$this->assertEquals($article->getId(),$a->getId());

		$article->s("title","Green Red Light");

		$a = Article::FindFirst($params,$options);
		$this->assertNull($a);

		$article->destroy();
	}

	function _find_first_old_way($params,$options = array()){
		$article = Article::CreateNewRecord(array(
			"title" => "Creepy Green Light"
		));

		$a = TableRecord::FindFirst($params,$options);
		$this->assertEquals($article->getId(),$a->getId());

		$article->s("title","Green Red Light");

		$a = TableRecord::FindFirst($params,$options);
		$this->assertNull($a);

		$article->destroy();	
	}
}
