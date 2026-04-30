<?php
class TcFindingRecords extends TcBase{
	function test_magic_queries(){
		$the_true_one = Article::CreateNewRecord([
			"title" => "Foo Bar",
			"body" => "True Foo Bar",
			"created_at" => "1990-01-01",
		]);

		$an_imitation = Article::CreateNewRecord([
			"title" => "Foo Bar",
			"body" => "Just an Imitation",
			"created_at" => "1990-01-02",
		]);

		$non_unique_1 = Article::CreateNewRecord([
			"title" => "Foo Bar",
			"body" => "Non Unique",
			"created_at" => "2001-01-01",
		]);

		$non_unique_2 = Article::CreateNewRecord([
			"title" => "Foo Bar",
			"body" => "Non Unique",
			"created_at" => "2001-01-02",
		]);

		$null_title = Article::CreateNewRecord([
			"title" => null,
			"body" => "Null Title",
			"created_at" => "2001-01-03",
		]);

		//
		$a = Article::FindFirst("body","True Foo Bar");
		$this->assertEquals($the_true_one->getId(),$a->getId());

		$a = Article::FindFirst("body=:body",[":body" => "True Foo Bar"]);
		$this->assertEquals($the_true_one->getId(),$a->getId());

		$a = Article::FindFirst(["conditions" => ["body" => "True Foo Bar"]]);
		$this->assertEquals($the_true_one->getId(),$a->getId());

		$a = Article::FindFirst("body","True Foo Bar","title","Foo Bar");
		$this->assertEquals($the_true_one->getId(),$a->getId());

		$a = Article::FindFirst(["conditions" => [
			"body" => "True Foo Bar",
			"title" => "Foo Bar"]
		]);
		$this->assertEquals($the_true_one->getId(),$a->getId());

		$a = Article::FindFirst("body","True Foo Bar","body","True Foo Bar [X]");
		$this->assertEquals(null,$a);

		$a = Article::FindFirst("body","Just an Imitation");
		$this->assertEquals($an_imitation->getId(),$a->getId());

		$a = Article::FindFirst(["conditions" => ["body" => "Just an Imitation"]]);
		$this->assertEquals($an_imitation->getId(),$a->getId());

		//
		$a = Article::FindFirst("body='True Foo Bar'");
		$this->assertEquals($the_true_one->getId(),$a->getId());

		$a = Article::FindFirst("body='Just an Imitation'");
		$this->assertEquals($an_imitation->getId(),$a->getId());

		//
		$a = Article::FindFirst("title",null);
		$this->assertEquals($null_title->getId(),$a->getId());

		$a = Article::FindFirst("body","Null Title","title",null);
		$this->assertEquals($null_title->getId(),$a->getId());

		$a = Article::FindFirst("body","Null Title [X]","title",null);
		$this->assertEquals(null,$a);

		//
		$a = Article::FindFirst("title","Foo Bar","body","True Foo Bar");
		$this->assertEquals($the_true_one->getId(),$a->getId());

		$a = Article::FindFirst("title","Foo Bar","body","Just an Imitation");
		$this->assertEquals($an_imitation->getId(),$a->getId());

		$a = Article::FindFirst("title","Foo Bar","body", "Non Unique",["order_by" => "created_at"]);
		$this->assertEquals($non_unique_1->getId(),$a->getId());

		$a = Article::FindFirst("title","Foo Bar","body", "Non Unique",["order_by" => "created_at DESC"]);
		$this->assertEquals($non_unique_2->getId(),$a->getId());

		// FindAll

		$ar = Article::FindAll([
			"conditions" => [
				"title" => "Foo Bar",
				"body" => "Non Unique"
			],
			"order_by" => "created_at DESC",
		]);
		$this->assertEquals($non_unique_2->getId(),$ar[0]->getId());
		$this->assertEquals($non_unique_1->getId(),$ar[1]->getId());

		$ar = Article::FindAll([
			"conditions" => [
				"title" => "Foo Bar",
				"body" => "Non Unique"
			],
			"order_by" => "created_at DESC",
		]);
		$this->assertEquals(2,count($ar));
		$this->assertEquals($non_unique_2->getId(),$ar[0]->getId());
		$this->assertEquals($non_unique_1->getId(),$ar[1]->getId());

		$ar = Article::FindAll("title","Foo Bar",["order_by" => "created_at DESC"]);
		$this->assertEquals(4,count($ar));
		$this->assertEquals($non_unique_2->getId(),$ar[0]->getId());
		$this->assertEquals($non_unique_1->getId(),$ar[1]->getId());
		$this->assertEquals($an_imitation->getId(),$ar[2]->getId());
		$this->assertEquals($the_true_one->getId(),$ar[3]->getId());

		$ar = Article::FindAll("title","Foo Bar","body","Just an Imitation",["order_by" => "created_at DESC"]);
		$this->assertEquals(1,count($ar));
		$this->assertEquals($an_imitation->getId(),$ar[0]->getId());

		$ar = Article::FindAll("title=:title AND body=:body",[
			":title" => "Foo Bar",
			":body" => "Just an Imitation"
		],["order_by" => "created_at DESC"]);
		$this->assertEquals(1,count($ar));
		$this->assertEquals($an_imitation->getId(),$ar[0]->getId());
	}

	function test_find_first(){
		$this->_find_first([
			"conditions" => ["title" => "Creepy Green Light"]
		]);

		$this->_find_first([
			"condition" => ["title" => "Creepy Green Light"]
		]);

		$this->_find_first([
			"conditions" => "title=:title",
			"bind_ar" => [":title" => "Creepy Green Light"],
		]);

		$this->_find_first([
			"condition" => "title=:title",
			"bind" => [":title" => "Creepy Green Light"],
		]);

		$this->_find_first([
			"conditions" => ["title=:title"],
			"bind_ar" => [":title" => "Creepy Green Light"],
		]);

		$this->_find_first([
			"condition" => ["title=:title"],
			"bind" => [":title" => "Creepy Green Light"],
		]);

		$this->_find_first("title=:title",[":title" => "Creepy Green Light"]);
		$this->_find_first("title","Creepy Green Light");
		$this->_find_first("title='Creepy Green Light'");

		// -- old ways, PHP4 compatible

		$this->_find_first_old_way([
			"class_name" => "Article",
			"conditions" => ["title=:title"],
			"bind_ar" => [":title" => "Creepy Green Light"]
		]);

		$this->_find_first_old_way([
			"class" => "Article",
			"condition" => ["title=:title"],
			"bind" => [":title" => "Creepy Green Light"]
		]);

		$this->_find_first_old_way([
			"class_name" => "Article",
			"conditions" => ["title" => "Creepy Green Light"],
		]);
	}

	function test_get_belongs_to(){
		$birdie = Image::CreateNewRecord([
			"url" => "http://www.atk14.net/public/images/atk14.gif",
		]);

		$hacker = Image::CreateNewRecord([
			"url" => "http://www.atk14.net/public/images/easy_to_use.jpg",
		]);

		$article = Article::CreateNewRecord([
			"title" => "Foo Bar",
			"image_id" => null
		]);

		$this->assertNull($article->getBelongsTo("Image"));

		$article->s("image_id",$birdie);
		$i = $article->getBelongsTo("Image");
		$this->assertEquals($birdie->getId(),$i->getId());

		$article->s("image_id",$hacker);
		$i = $article->getBelongsTo("Image");
		$this->assertEquals($hacker->getId(),$i->getId());
	}

	function test_find_by(){
		$green = Article::CreateNewRecord(["title" => "Green"]);
		$red = Article::CreateNewRecord(["title" => "Red"]);

		$a = Article::FindById($red->getId());
		$this->assertEquals($red->getId(),$a->getId());

		$this->assertNull(Article::FindById(-1234));
		$this->assertNull(Article::FindById(null));

		$a = Article::FindByTitle("Green");
		$this->assertEquals($green->getId(),$a->getId());

		$a = Article::FindByTitle("Red");
		$this->assertEquals($red->getId(),$a->getId());

		$this->assertNull(Article::FindByTitle("Orange"));

		// --

		$a = Article::FindFirstByTitle("Green");
		$this->assertEquals($green->getId(),$a->getId());

		$a = Article::FindFirstByTitle("Red");
		$this->assertEquals($red->getId(),$a->getId());

		$this->assertNull(Article::FindFirstByTitle("Orange"));

		// --

		$yello_first = Article::CreateNewRecord(["title" => "Yellow", "created_at" => "2001-01-01"]);
		$yello_middle = Article::CreateNewRecord(["title" => "Yellow", "created_at" => "2001-01-02"]);
		$yello_last = Article::CreateNewRecord(["title" => "Yellow", "created_at" => "2001-01-03"]);

		$a = Article::FindByTitle("Yellow",["order_by" => "created_at"]);
		$this->assertEquals($yello_first->getId(),$a->getId());

		$a = Article::FindByTitle("Yellow",["order_by" => "created_at DESC"]);
		$this->assertEquals($yello_last->getId(),$a->getId());

		$a = Article::FindByTitle("Yellow",["order_by" => "created_at DESC", "offset" => 1]);
		$this->assertEquals($yello_middle->getId(),$a->getId());

		// --

		$ary = Article::FindAllByTitle("Yellow",["order_by" => "created_at"]);
		$this->assertEquals(3,count($ary));
		$this->assertEquals($yello_first->getId(),$ary[0]->getId());
		$this->assertEquals($yello_middle->getId(),$ary[1]->getId());
		$this->assertEquals($yello_last->getId(),$ary[2]->getId());

		$ary = Article::FindAllByTitle("Yellow",["order_by" => "created_at DESC", "limit" => 1]);
		$this->assertEquals(1,count($ary));
		$this->assertEquals($yello_last->getId(),$ary[0]->getId());
	}

	function _find_first($params,$options = []){
		$article = Article::CreateNewRecord([
			"title" => "Creepy Green Light"
		]);

		$a = Article::FindFirst($params,$options);
		$this->assertEquals($article->getId(),$a->getId());

		$article->s("title","Green Red Light");

		$a = Article::FindFirst($params,$options);
		$this->assertNull($a);

		$article->destroy();
	}

	function _find_first_old_way($params,$options = []){
		$article = Article::CreateNewRecord([
			"title" => "Creepy Green Light"
		]);

		$a = TableRecord::FindFirst($params,$options);
		$this->assertEquals($article->getId(),$a->getId());

		$article->s("title","Green Red Light");

		$a = TableRecord::FindFirst($params,$options);
		$this->assertNull($a);

		$article->destroy();	
	}
}
