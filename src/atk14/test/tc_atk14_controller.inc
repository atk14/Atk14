<?php
class tc_atk14_controller extends tc_base{
	function test_multiple_before_filters(){
		$c = new MultipleBeforeFiltersController();
		$c->atk14__initialize(array());

		$c->user = 1; // simulace prihlaseneho uzivatele
		$this->assertEquals(array(),$c->before_filters);
		$c->atk14__runBeforeFilters();
		$this->assertEquals(array("filter1","filter2","check_user_is_logged","before_filter","filter3","filter4"),$c->before_filters);

		$c->user = null; // simulace odhlaseneho uzivatele
		$c->before_filters = array();
		$this->assertEquals(array(),$c->before_filters);
		$c->atk14__runBeforeFilters();
		$this->assertEquals(array("filter1","filter2","check_user_is_logged"),$c->before_filters);
	}

	function test_multiple_after_filters(){
		$c = new MultipleAfterFiltersController();
		$c->atk14__initialize();

		$this->assertEquals(array(),$c->after_filters);
		$c->atk14__runAfterFilters();
		$this->assertEquals(array("afilter1","afilter2","after_filter","afilter3","afilter4"),$c->after_filters);
	}

	function test_redirect_to_and_link_to(){
		$c = new ApplicationController();
		$c->atk14__initialize();
		$c->lang = "en";
		$c->controller = "books";
		$c->namespace = "";

		foreach(array(
			array("overview","/en/books/overview/"),
			array("users/create_new" , "/en/users/create_new/"),
			array("/public/pricelist.html" , "/public/pricelist.html"),
			array("http://www.domenka.cz/" , "http://www.domenka.cz/"),
			array(array("controller" => "books", "action" => "detail") , "/en/books/detail/"),
			array(array("controller" => "books", "action" => "detail", "id" => "123") , "/en/books/detail/?id=123"),
		) as $i){
			list($params,$result) = $i;
			$c->_redirect_to($params);
			$this->assertEquals($result,$c->response->getLocation());
			$this->assertEquals(302,$c->response->getStatusCode()); // 302 Moved temporarily

			$c->_redirect_to($params,array("moved_permanently" => true));
			$this->assertEquals($result,$c->response->getLocation());
			$this->assertEquals(301,$c->response->getStatusCode()); // 301 Moved permanently

			$url = $c->_link_to($params);
			$this->assertEquals($result,$url);
		}

		$params =	array("controller" => "books", "action" => "detail", "id" => "123", "format" => "xml");

		$c->_redirect_to($params);
		$this->assertEquals("/en/books/detail/?id=123&format=xml",$c->response->getLocation());

		$this->assertEquals("/en/books/detail/?id=123&amp;format=xml",$c->_link_to($params));

		$this->assertEquals("http://www.testing.cz/en/books/detail/?id=123&amp;format=xml",$c->_link_to($params,array("with_hostname" => true)));
		$this->assertEquals("https://www.testing.cz/en/books/detail/?id=123&amp;format=xml",$c->_link_to($params,array("with_hostname" => true, "ssl" => true)));

		$this->assertEquals("https://www.testing.cz/en/books/detail/?id=123&amp;format=xml",$c->_link_to($params,array("ssl" => true)));
		$this->assertEquals("http://www.testing.cz/en/books/detail/?id=123&amp;format=xml",$c->_link_to($params,array("ssl" => false)));
	}

	function test_link_to(){
		global $ATK14_GLOBAL;
		$c = new ApplicationController();
		$c->atk14__initialize();
		$c->lang = "en";
		$c->controller = "books";
		$c->action = "overview";
		$c->namespace = "";

		$this->assertEquals("/en/books/export/",$c->_link_to(array("action" => "export")));
		$this->assertEquals("/en/books/export/",$c->_link_to("export"));

		$this->assertEquals("/en/books/detail/?id=123&amp;format=xml",$c->_link_to(array("action" => "detail", "id" => 123, "format" => "xml")));
		$this->assertEquals("/en/books/detail/?id=123&format=xml",$c->_link_to(array("action" => "detail", "id" => 123, "format" => "xml"),array("connector" => "&")));

		$this->assertEquals("/en/articles/",$c->_link_to(array("controller" => "articles")));
		$this->assertEquals("/en/articles/",$c->_link_to("articles/index"));

		$this->assertEquals("/admin/cs/articles/",$c->_link_to(array("controller" => "articles", "namespace" => "admin", "lang" => "cs")));

		$this->assertEquals("/en/books/overview/",$c->_link_to());

		$c->namespace = "admin";

		$this->assertEquals("/admin/en/books/overview/",$c->_link_to());

		$this->assertEquals("/admin/en/articles/",$c->_link_to(array("controller" => "articles")));
		$this->assertEquals("/admin/en/articles/",$c->_link_to("articles/index"));

		$this->assertEquals("/en/articles/",$c->_link_to(array("controller" => "articles", "namespace" => "")));
	}

	function test_layout(){
		$client = new Atk14Client();
		$controller = $client->get("testing/default_layout");

		$page = new String($controller->response->buffer->toString());
		$this->assertEquals(true,$page->contains("This is a template"));
		$this->assertEquals(true,$page->contains("<!-- default layout -->"));

		$controller = $client->get("testing/custom_layout");
		$page = new String($controller->response->buffer->toString());
		$this->assertEquals(true,$page->contains("This is a template"));
		$this->assertEquals(true,$page->contains("<!-- custom layout -->"));

		$controller = $client->get("testing/no_layout");
		$page = new String($controller->response->buffer->toString());
		$this->assertEquals(true,(bool)$page->match("/^This is a template$/"));
	}
}
