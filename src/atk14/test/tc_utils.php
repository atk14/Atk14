<?php
class TcUtils extends TcBase{
	function test_response_produced(){
		$c = new Atk14Controller();

		$c->response = new HTTPResponse();
		$this->assertFalse(Atk14Utils::ResponseProduced($c));
		$c->response->setLocation("/testing/");
		$this->assertTrue(Atk14Utils::ResponseProduced($c));

		$c->response = new HTTPResponse();
		$this->assertFalse(Atk14Utils::ResponseProduced($c));
		$c->response->write("Hello");
		$this->assertTrue(Atk14Utils::ResponseProduced($c));

		$c->response = new HTTPResponse();
		$this->assertFalse(Atk14Utils::ResponseProduced($c));
		$c->response->setStatusCode(404);
		$this->assertTrue(Atk14Utils::ResponseProduced($c));

		$c->response = new HTTPResponse();
		$this->assertFalse(Atk14Utils::ResponseProduced($c));
		$c->action_executed = true;
		$this->assertTrue(Atk14Utils::ResponseProduced($c));
	}

	function test_join_arrays(){
		$this->assertEquals(array(),Atk14Utils::JoinArrays(array()));
		$this->assertEquals(array(),Atk14Utils::JoinArrays());
		$this->assertEquals(array(),Atk14Utils::JoinArrays(array(),array(),array()));

		$this->assertEquals(array("a","b","c","d"),Atk14Utils::JoinArrays(array("a","b"),array("c","d")));
		$this->assertEquals(array("a","b","c","d"),Atk14Utils::JoinArrays(array("a","b"),array("c"),array("d")));
		$this->assertEquals(array("a","b","c","d"),Atk14Utils::JoinArrays(array("a","b","c","d")));

		// prevod skalaru na pole
		$this->assertEquals(array("a","b","c","d"),Atk14Utils::JoinArrays("a","b",array("c","d")));

		// ignorovani null hodnot
		$this->assertEquals(array("a","b","c","d"),Atk14Utils::JoinArrays("a","b",null,null,array("c","d"))); 
	}

	function test_get_smarty(){
		$smarty = Atk14Utils::GetSmarty("template_path");
		$this->assertEquals("atk14_smarty3___",$smarty->compile_id);

		$smarty = Atk14Utils::GetSmarty("template_path",array(
			"controller_name" => "books",
			"namespace" => "admin",
			"compile_id_salt" => "salt"
		));
		$this->assertEquals("atk14salt_smarty3_admin_books_",$smarty->compile_id);
	}
}
