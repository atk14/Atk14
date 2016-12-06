<?php
require_once("app/controllers/application.php");
require_once("app/controllers/testing_controller.php");
require_once("app/controllers/multiple_before_filters_controller.php");
require_once("app/controllers/multiple_after_filters_controller.php");

class TcController extends TcBase{
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

		$this->assertEquals("/en/books/detail/?id=123&format=xml",$c->_link_to($params));

		$this->assertEquals("http://www.testing.cz/en/books/detail/?id=123&format=xml",$c->_link_to($params,array("with_hostname" => true)));
		$this->assertEquals("https://secure.testing.cz/en/books/detail/?id=123&format=xml",$c->_link_to($params,array("with_hostname" => true, "ssl" => true)));

		$this->assertEquals("https://secure.testing.cz/en/books/detail/?id=123&format=xml",$c->_link_to($params,array("ssl" => true)));
		$this->assertEquals("http://www.testing.cz/en/books/detail/?id=123&format=xml",$c->_link_to($params,array("ssl" => false)));
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

		$this->assertEquals("/en/books/detail/?id=123&format=xml",$c->_link_to(array("action" => "detail", "id" => 123, "format" => "xml")));
		$this->assertEquals("/en/books/detail/?id=123&amp;format=xml",$c->_link_to(array("action" => "detail", "id" => 123, "format" => "xml"),array("connector" => "&amp;")));

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
		$controller = $this->client->get("testing/default_layout");

		$page = new String4($controller->response->buffer->toString());
		$this->assertEquals(true,$page->contains("This is template"));
		$this->assertEquals(true,$page->contains("<!-- default layout -->"));

		$controller = $this->client->get("testing/custom_layout");
		$page = new String4($controller->response->buffer->toString());
		$this->assertEquals(true,$page->contains("This is template"));
		$this->assertEquals(true,$page->contains("<!-- custom layout -->"));

		$controller = $this->client->get("testing/no_layout");
		$page = new String4($controller->response->buffer->toString());
		$this->assertEquals(true,(bool)$page->match("/^This is template$/"));

		$controller = $this->client->get("testing/custom_layout_set_from_template");
		$page = new String4($controller->response->buffer->toString());
		$this->assertEquals(true,$page->contains("This is custom_layout_set_from_template"));
		$this->assertEquals(true,$page->contains("<!-- custom layout -->"));
	}

	function test_before_filter(){
		$this->client->get("testing/test");
		$this->assertContains("there_is_a_value_assigned_from_action_method",$this->client->getContent());
		$this->assertContains("there_is_a_value_assigned_usually_from_before_render",$this->client->getContent());
		$this->assertContains("there_is_a_value_assigned_directly_from_before_render",$this->client->getContent());
	}

	function test_render(){
		$controller = $this->client->get("testing/test_render");

		$this->assertContains("John Doe",$controller->snippet);
		$this->assertContains("John Doe",$this->client->getContent());
	}

	function test_error404(){
		$client = &$this->client;
		$controller = $client->get("nonsence/nonsence");
		$this->assertEquals(404,$client->getStatusCode());
		$this->assertEquals("ApplicationController",get_class($controller));
		$this->assertContains("this is views/application/error404.tpl",$client->getContent());

		$controller = $client->get("admin/en/nonsence/nonsence");
		$this->assertEquals(404,$client->getStatusCode());
		$this->assertEquals("AdminController",get_class($controller)); // there is AdminController in file controllers/admin/admin.php
		$this->assertContains("error404 template in views/admin/admin/error404.tpl",$client->getContent());

		$controller = $client->get("universe/en/nonsence/nonsence");
		$this->assertEquals(404,$client->getStatusCode());
		$this->assertEquals("UniverseController",get_class($controller));
	}

	function test_get_form() {
		$client = &$this->client;
		$controller = $client->get("main/hello_world");

		$frm = $controller->_get_form("main/hello_world");
		$this->assertEquals("form_main_hello_world",$frm->atk14_attrs["id"]);
	}

	function test_caching(){
		$client = &$this->client;

		// caching

		$client->get("testing/test_caching");
		$this->assertEquals("text/html",$client->getContentType());
		$this->assertEquals("utf-8",$client->getContentCharset());
		$this->assertEquals(200,$client->getStatusCode());
		$content_1 = $client->getContent();

		$client->get("testing/test_caching");
		$this->assertEquals("text/html",$client->getContentType());
		$this->assertEquals("utf-8",$client->getContentCharset());
		$this->assertEquals(200,$client->getStatusCode());
		$content_2 = $client->getContent();

		$client->get("testing/test_caching",array("alt" => "alt_cache"));
		$this->assertEquals("text/html",$client->getContentType());
		$this->assertEquals("utf-8",$client->getContentCharset());
		$this->assertEquals(200,$client->getStatusCode());
		$content_3 = $client->getContent();

		$client->get("testing/test_caching",array("alt" => "alt_cache"));
		$this->assertEquals("text/html",$client->getContentType());
		$this->assertEquals("utf-8",$client->getContentCharset());
		$this->assertEquals(200,$client->getStatusCode());
		$content_4 = $client->getContent();

		$client->get("testing/test_caching",array("alt" => "////\\////"));
		$this->assertEquals("text/html",$client->getContentType());
		$this->assertEquals("utf-8",$client->getContentCharset());
		$this->assertEquals(200,$client->getStatusCode());
		$content_5 = $client->getContent();

		$client->get("testing/test_caching",array("alt" => "////\\////"));
		$this->assertEquals("text/html",$client->getContentType());
		$this->assertEquals("utf-8",$client->getContentCharset());
		$this->assertEquals(200,$client->getStatusCode());
		$content_6 = $client->getContent();

		$this->assertContains("random_value: ",$content_1);
		$this->assertEquals($content_1,$content_2);
		$this->assertContains("random_value: ",$content_3);
		$this->assertEquals($content_3,$content_4);
		$this->assertContains("random_value: ",$content_5);
		$this->assertEquals($content_5,$content_6);

		$this->assertNotEquals($content_1,$content_3);
		$this->assertNotEquals($content_1,$content_5);
		$this->assertNotEquals($content_3,$content_5);

		$client->get("testing/test_caching",array("disable_cache" => "1"));
		$this->assertEquals("text/html",$client->getContentType());
		$this->assertEquals("utf-8",$client->getContentCharset());
		$this->assertEquals(200,$client->getStatusCode());
		$content_3 = $client->getContent();
		$this->assertContains("random_value: ",$content_3);
		$this->assertNotEquals($content_1,$content_3);

		// no template

		$client->get("testing/test_caching_without_template");
		$this->assertEquals("text/plain",$client->getContentType());
		$this->assertEquals("us-ascii",$client->getContentCharset());
		$this->assertEquals(222,$client->getStatusCode());
		$content_1 = $client->getContent();
		$this->assertTrue(!!preg_match('/^random_value: /',$content_1));

		$client->get("testing/test_caching_without_template");
		$this->assertEquals("text/plain",$client->getContentType());
		$this->assertEquals("us-ascii",$client->getContentCharset());
		$this->assertEquals(222,$client->getStatusCode());
		$content_2 = $client->getContent();
		$this->assertTrue(!!preg_match('/^random_value: /',$content_2));

		$this->assertEquals($content_1,$content_2);

		$client->get("testing/test_caching_without_template",array("disable_cache" => "1"));
		$this->assertEquals("text/plain",$client->getContentType());
		$this->assertEquals("us-ascii",$client->getContentCharset());
		$this->assertEquals(222,$client->getStatusCode());
		$content_3 = $client->getContent();
		$this->assertTrue(!!preg_match('/^random_value: /',$content_3));

		$this->assertNotEquals($content_1,$content_3);
		
		// layout set in action

		$client->get("testing/test_caching_with_layout_set_in_action");
		$content_1 = $client->getContent();
		$this->assertTrue(!!preg_match('/random_value: /',$content_1));
		$this->assertContains("<!-- custom layout -->",$content_1);

		$client->get("testing/test_caching_with_layout_set_in_action");
		$content_2 = $client->getContent();
		$this->assertTrue(!!preg_match('/random_value: /',$content_2));
		$this->assertContains("<!-- custom layout -->",$content_2);

		$this->assertEquals($content_1,$content_2);

		$client->get("testing/test_caching_with_layout_set_in_action",array("disable_cache" => "1"));
		$content_3 = $client->getContent();
		$this->assertTrue(!!preg_match('/random_value: /',$content_3));
		$this->assertContains("<!-- custom layout -->",$content_3);

		$this->assertNotEquals($content_1,$content_3);
	}

	function test_render_template(){
		$client = &$this->client;

		// rendering is disabled in the action
		$client->get("main/hello_from_earth");
		$this->assertEquals("Hello from Earth!",$client->getContent());

		// rendering is disabled in the _before_render
		$client->get("main/hello_from_mars");
		$this->assertEquals("Hello from Mars!",$client->getContent());

		// rendering is disabled in the _before_filter
		$client->get("main/hello_from_venus");
		$this->assertEquals("Hello from Venus!",$client->getContent());
	}

	/**
	 * Otestovani, ze funguje pouzivani datovych sad pomoci anotace @dataProvider
	 *
	 * @dataProvider provideNumbers
	 */
	function testSomething($a, $b) {
		$this->assertTrue(isset($a));
		$this->assertTrue(isset($b));
		$this->assertEquals(2 + $a, $b);
	}

	function provideNumbers() {
		return array(
			"ada" => array(0,2),
			array(1,3),
			array(5,7),
		);
	}
}
