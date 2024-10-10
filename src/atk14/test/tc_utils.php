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
		if(ATK14_USE_SMARTY4){
			$version = "4";
		}elseif(ATK14_USE_SMARTY3){
			$version = "3";
		}else{
			$version = "2";
		}
		$this->assertEquals("atk14_smarty{$version}___",$smarty->compile_id);

		$smarty = Atk14Utils::GetSmarty("template_path",array(
			"controller_name" => "books",
			"namespace" => "admin",
			"compile_id_salt" => "salt"
		));
		$this->assertEquals("atk14salt_smarty{$version}_admin_books_",$smarty->compile_id);
	}

	function test_normalize_uri(){
		$this->assertEquals('/public/stylesheets/blueprint/screen.css?1305634061',Atk14Utils::NormalizeUri('/public/stylesheets/blueprint/screen.css?1305634061'));

		$this->assertEquals('/public/dist/js/app.js?1384779779',Atk14Utils::NormalizeUri('/public/javascripts/../dist/js/app.js?1384779779'));
		$this->assertEquals('/public/stylesheets/blueprint/screen.css?1305634061',Atk14Utils::NormalizeUri('/public/././stylesheets/blueprint/screen.css?1305634061'));
		$this->assertEquals('/public/dist/css/app.css?1384766775',Atk14Utils::NormalizeUri('/public/stylesheets/../dist/css/app.css?1384766775'));
	}

	function test_ToScalar(){
		$this->assertEquals(5,Atk14Utils::ToScalar(5));
		$this->assertEquals(1.23,Atk14Utils::ToScalar(1.23));
		$this->assertEquals("Text",Atk14Utils::ToScalar("Text"));

		$this->assertEquals(null,Atk14Utils::ToScalar(null));

		$article = Article::CreateNewRecord(array("id" => 345));
		$this->assertEquals(345,Atk14Utils::ToScalar($article));

		$str = new String4("Object with toString() method");
		$this->assertEquals("Object with toString() method",Atk14Utils::ToScalar($str));
	}

	function test_StringToOptions(){
		$this->assertEquals(array(
			"color" => "red",
			"with_border" => true,
			"with_decoration" => false,
			"drop_shadow" => true,
			"class" => "",
			"max_amount" => null,
		),Atk14Utils::StringToOptions('color=red,with_border,with_decoration=false,drop_shadow=true,class=,max_amount=null'));

		$this->assertEquals(array(),Atk14Utils::StringToOptions(""));
		$this->assertEquals(array(),Atk14Utils::StringToOptions(array()));
		$this->assertEquals(array("a" => "b"),Atk14Utils::StringToOptions(array("a" => "b")));
	}

	function test__DetermineEnvironmentByRemoteAddr(){
		$this->assertEquals("DEVELOPMENT",Atk14Utils::_DetermineEnvironmentByRemoteAddr("127.0.0.1"));
		$this->assertEquals("DEVELOPMENT",Atk14Utils::_DetermineEnvironmentByRemoteAddr("::1"));

		$this->assertEquals("PRODUCTION",Atk14Utils::_DetermineEnvironmentByRemoteAddr("8.8.8.8"));
		$this->assertEquals("PRODUCTION",Atk14Utils::_DetermineEnvironmentByRemoteAddr("2001:4860:4860::8888"));

		// The environment PRODUCTION is considered on an IP address from internal areas
		$this->assertEquals("PRODUCTION",Atk14Utils::_DetermineEnvironmentByRemoteAddr("192.168.1.2"));
		$this->assertEquals("PRODUCTION",Atk14Utils::_DetermineEnvironmentByRemoteAddr("10.20.30.40"));
	}

	function test_ShellRemoteAddr(){
		putenv("SSH_CLIENT=8.8.8.8 1234 22");

		$this->assertEquals("8.8.8.8",Atk14Utils::ShellRemoteAddr());
		$this->assertEquals("dns.google",Atk14Utils::ShellRemoteHost());

		putenv("SSH_CLIENT=");

		$this->assertEquals(null,Atk14Utils::ShellRemoteAddr());
		$this->assertEquals(null,Atk14Utils::ShellRemoteHost());
	}

	function test_EscapeForJavascript(){
		//
		$this->assertEquals('hnědá lištička',Atk14Utils::EscapeForJavascript("hnědá lištička"));

		// special ascii chars \x00-\x1F
		$this->assertEquals('\u0001 \u0005 \t \u001F',Atk14Utils::EscapeForJavascript(join(" ",array(chr(1),chr(5),chr(9),chr(31)))));
	}
}
