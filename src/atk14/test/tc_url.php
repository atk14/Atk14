<?php
class TcUrl extends TcBase{
	function test(){
		global $_GET;

		$_GET = array();

		$this->_test_route("/articles/feed.rss",array(
			"namespace" => "",
			"lang" => "en",
			"controller" => "articles",
			"action" => "overview",
			"force_redirect" => null,
			"get_params" => array("format" => "rss"),
		));


		$this->_test_route("/cs/articles/overview/",array(
			"namespace" => "",
			"lang" => "cs",
			"controller" => "articles",
			"action" => "overview",
			"force_redirect" => null
		));

		// format=rss mame v routes.php
		$_GET = array("format" => "rss");
		$this->_test_route("/en/articles/overview/?format=rss",array(
			"namespace" => "",
			"lang" => "en",
			"controller" => "articles",
			"action" => "overview",
			"force_redirect" => "/articles/feed.rss"
		));

		// format=xml nemame v routes.php
		$_GET = array("format" => "xml");
		$this->_test_route("/en/articles/overview/?format=xml",array(
			"namespace" => "",
			"lang" => "en",
			"controller" => "articles",
			"action" => "overview",
			"force_redirect" => null,
		),array("format" => "xml"));
	
		$_GET = array();
	}

	function test_EncodeParams(){
		$this->assertEquals('?format=xml',Atk14Url::EncodeParams(array("format" => "xml")));
		$this->assertEquals('?limit=10&offset=0',Atk14Url::EncodeParams(array("limit" => "10", "offset" => "0")));
		$this->assertEquals('?colors%5B%5D=red&colors%5B%5D=blue',Atk14Url::EncodeParams(array("colors" => array("red","blue"))));
		$this->assertEquals('?colors%5Bc1%5D=red&colors%5Bc2%5D=blue',Atk14Url::EncodeParams(array("colors" => array("c1" => "red", "c2" => "blue"))));
	}

	function test_recognize_route_omit_trailing_slash(){
		$this->_test_route("/invoice/12345.pdf",array(
			"controller" => "invoices",
			"action" => "detail",
			"lang" => "en",
			"get_params" => array("id" => "12345", "format" => "pdf"),
			"force_redirect" => null
		));
		$this->_test_route("/faktura/12345.pdf",array(
			"controller" => "invoices",
			"action" => "detail",
			"lang" => "cs",
			"get_params" => array("id" => "12345", "format" => "pdf"),
			"force_redirect" => null
		));
		$this->_test_route("/invoice/12345.xml",array(
			"controller" => "invoices",
			"action" => "detail",
			"get_params" => array("id" => "12345", "format" => "xml"),
			"force_redirect" => null
		));
		$this->_test_route("/invoice/12345.pdf/",array(
			"controller" => "invoices",
			"action" => "detail",
			"get_params" => array("id" => "12345", "format" => "pdf"),
			"force_redirect" => "/invoice/12345.pdf"
		));
		$this->_test_404_route(array(
			"/invoice/12345.gif", // invalid format
			"/invoice/nonsence.pdf", // invalid id
		));
	}

	function test_recognize_route(){
		global $_GET;
		$_GET = array();

		$this->_test_route("/cs/articles/overview/",array(
			"namespace" => "",
			"lang" => "cs",
			"controller" => "articles",
			"action" => "overview",
			"force_redirect" => null
		));

		// missing slash at the end of URI
		$this->_test_route("/cs/articles/overview",array(
			"namespace" => "",
			"lang" => "cs",
			"controller" => "articles",
			"action" => "overview",
			"force_redirect" => "/cs/articles/overview/"
		));

		$_GET["from"] = "20";
		$r = $this->_test_route("/cs/articles/overview/?from=20",array(
			"namespace" => "",
			"lang" => "cs",
			"controller" => "articles",
			"action" => "overview",
			"force_redirect" => null
		));

		// missing slash
		$r = $this->_test_route("/cs/articles/overview?from=20",array(
			"namespace" => "",
			"lang" => "cs",
			"controller" => "articles",
			"action" => "overview",
			"force_redirect" => "/cs/articles/overview/?from=20"
		));

		$_GET = array();
	}

	function test_recognize_route_nice_url(){
		global $_GET;

		$this->_test_route("/article/123-some-article-title/",array(
			"namespace" => "",
			"lang" => "en",
			"controller" => "articles",
			"action" => "detail",
			"force_redirect" => null,
			"get_params" => array("id" => "123", "slug" => "some-article-title"),
		));

		$this->_test_route("/clanek/123-some-article-title/",array(
			"namespace" => "",
			"lang" => "cs",
			"controller" => "articles",
			"action" => "detail",
			"force_redirect" => null,
			"get_params" => array("id" => "123", "slug" => "some-article-title"),
		));

		$this->_test_route("/article/123-some-article-title",array("force_redirect" => "/article/123-some-article-title/"));	
		$this->_test_route("/clanek/123-some-article-title",array("force_redirect" => "/clanek/123-some-article-title/"));	

		$_GET = array("id" => "124","slug" => "another-article");
		$this->_test_route("/en/articles/detail/?id=124&slug=another-article",array(
			"force_redirect" => "/article/124-another-article/",
		));
		$_GET = array();

		$this->_test_404_route(array(
			"/article/0123-zero-at-the-begining/",
			"/article/12?3-bad-url/",
			"/article/123-bad-slug_!/",
			"/article/123-/",
			"/article/-missing-id/",
		));
	}

	function test_routers(){
		global $_GET;
		$_GET = array();

		$this->_test_route("/fable/green-eggs-and-ham-1",array(
			"namespace" => "",
			"lang" => "en",
			"controller" => "fables",
			"action" => "detail",
			"force_redirect" => null
		));

		$this->_test_route("/fable/green-eggs-and-ham-1",array(
			"namespace" => "",
			"lang" => "en",
			"controller" => "fables",
			"action" => "detail",
			"force_redirect" => null
		),array("id" => "1"));

		$this->_test_route("/universe/fable/green-eggs-and-ham-1",array(
			"namespace" => "universe",
			"lang" => "en",
			"controller" => "fables",
			"action" => "detail",
			"force_redirect" => null
		),array("id" => "1"));

		$this->_test_route("/fable/the-dog-in-the-hat-3",array( // ! dog
			"namespace" => "",
			"lang" => "en",
			"controller" => "fables",
			"action" => "detail",
			"force_redirect" => "/fable/the-cat-in-the-hat-3" // ! cat
		),array("id" => "3"));

		$_GET["id"] = "5";
		$this->_test_route("/admin/en/fables/detail/?id=5",array(
			"namespace" => "admin",
			"lang" => "en",
			"controller" => "fables",
			"action" => "detail",
			"force_redirect" => null
		),array("id" => "5"));

		$_GET = array("format" => "xml");
		$this->_test_route("/fable/a-very-good-fable-2?format=xml",array(
			"namespace" => "",
			"lang" => "en",
			"controller" => "fables",
			"action" => "detail",
			"force_redirect" => null
		),array("id" => "2", "format" => "xml"));

		$_GET = array();

		$this->assertEquals("/fable/where-the-wild-things-are-5",$this->_build_link(array("controller" => "fables", "action" => "detail", "id" => 5)));
		$this->assertEquals("/fable/where-the-wild-things-are-5?format=xml&print=1",$this->_build_link(array("controller" => "fables", "action" => "detail", "id" => 5, "format" => "xml", "print" => 1)));

		// The router is for namespace "" and "universe", but not for namespace "admin",
		// see config/routers/load.php
		$this->assertEquals("/admin/en/fables/detail/?id=5",$this->_build_link(array("namespace" => "admin", "controller" => "fables", "action" => "detail", "id" => 5)));
		$this->assertEquals("/universe/fable/where-the-wild-things-are-5",$this->_build_link(array("namespace" => "universe", "controller" => "fables", "action" => "detail", "id" => 5)));


		$this->assertEquals("/fable/a-very-good-fable-22",$this->_build_link(array("controller" => "fables", "action" => "detail", "id" => 22)));
		$this->assertEquals("/bajka/a-very-good-fable-22",$this->_build_link(array("controller" => "fables", "action" => "detail", "id" => 22, "lang" => "cs")));
		$this->assertEquals("/sk/fables/detail/?id=22",$this->_build_link(array("controller" => "fables", "action" => "detail", "id" => 22, "lang" => "sk"))); // in the router there is no support for sk

		//
		$this->_test_route("/en/",array(
			"namespace" => "",
			"lang" => "en",
			"controller" => "main",
			"action" => "index",
		));
		$this->_test_route("/universe/en/",array(
			"namespace" => "universe",
			"lang" => "en",
			"controller" => "main",
			"action" => "index",
		));

		// In the config/locale.yml there is no Hungarian language
		$this->_test_404_route("/hu/");
		$this->_test_404_route("/universe/hu/");
	
		// Routes only for the default language (cs)
		$this->assertEquals("/post-123/",$this->_build_link(array("namespace" => "", "controller" => "posts", "action" => "detail", "id" => "123", "lang" => "cs")));
		$this->assertEquals("/cs/posts/detail/?id=xyz",$this->_build_link(array("namespace" => "", "controller" => "posts", "action" => "detail", "id" => "xyz", "lang" => "cs"))); // id is not number
		$this->assertEquals("/en/posts/detail/?id=123",$this->_build_link(array("namespace" => "", "controller" => "posts", "action" => "detail", "id" => "123", "lang" => "en"))); // not default language
		//
		$this->assertEquals("/post-123.raw",$this->_build_link(array("namespace" => "", "controller" => "posts", "action" => "detail", "id" => "123", "format" => "raw", "lang" => "cs")));
		$this->assertEquals("/post-123/?format=pdf",$this->_build_link(array("namespace" => "", "controller" => "posts", "action" => "detail", "id" => "123", "format" => "pdf", "lang" => "cs"))); // format is not raw
		$this->assertEquals("/en/posts/detail/?id=123&format=raw",$this->_build_link(array("namespace" => "", "controller" => "posts", "action" => "detail", "id" => "123", "format" => "raw", "lang" => "en"))); // not default language
		//
		$this->_test_route("/post-123/",array(
			"lang" => "cs",
			"controller" => "posts",
			"action" => "detail",
			"force_redirect" => null
		),array(
			"id" => "123"
		));
		$this->_test_route("/post-123.raw",array(
			"lang" => "cs",
			"controller" => "posts",
			"action" => "detail",
			"force_redirect" => null
		),array(
			"id" => "123",
			"format" => "raw"
		));
		$this->_test_404_route("/post-123.pdf");
	}

	function test_ParseParamsFromUri(){
		$this->assertEquals(array(),Atk14Url::ParseParamsFromUri('/'));
		$this->assertEquals(array(),Atk14Url::ParseParamsFromUri('/cs/main/'));

		$this->assertEquals(array("id" => "123", "format" => "xml"),Atk14Url::ParseParamsFromUri('/cs/articles/detail/?id=123&format=xml'));
		$this->assertEquals(array("q" => "klobouček", "offset" => "20"),Atk14Url::ParseParamsFromUri('/cs/articles/?q=klobou%C4%8Dek&offset=20'));

		//recognize both escaped and unescaped arrays
		$this->assertEquals(array("a" => "1", "b" => array( "3", 4=>"6")),Atk14Url::ParseParamsFromUri('/cs/main/?a=1&b%5B%5D=3&b%5B4%5D=6'));
		$this->assertEquals(array("a" => "1", "b" => array( "3", 4=>"6")),Atk14Url::ParseParamsFromUri('/cs/main/?a=1&b[]=3&b[4]=6'));
	}

	function test_RecognizeRoute(){
		$data = Atk14Url::RecognizeRoute('/cs/articles/');
		$this->assertEquals("articles",$data["controller"]);
		$this->assertEquals("index",$data["action"]);
		$this->assertEquals(array(),$data["get_params"]);

		$data = Atk14Url::RecognizeRoute('/cs/articles/',array("get_params" => array("q" => "cat", "offset" => "20")));
		$this->assertEquals("articles",$data["controller"]);
		$this->assertEquals("index",$data["action"]);
		$this->assertEquals(array("q" => "cat", "offset" => "20"),$data["get_params"]);

		$data = Atk14Url::RecognizeRoute('/cs/articles/detail/?id=123&format=xml');
		$this->assertEquals("articles",$data["controller"]);
		$this->assertEquals("detail",$data["action"]);
		$this->assertEquals(array("id" => "123", "format" => "xml"),$data["get_params"]);

		$data = Atk14Url::RecognizeRoute('/cs/articles/detail/?id=123&format=xml',array("get_params" => array("id" => "222")));
		$this->assertEquals("articles",$data["controller"]);
		$this->assertEquals("detail",$data["action"]);
		$this->assertEquals(array("id" => "222"),$data["get_params"]);

		$data = Atk14Url::RecognizeRoute('/nonsence/?x1=x2');
		$this->assertEquals("application",$data["controller"]);
		$this->assertEquals("error404",$data["action"]);
		$this->assertEquals(array(),$data["get_params"]);
	}

	function test_BuildLink(){
		$this->assertEquals("https://secure.testing.cz/en/",$this->_build_link(array(),array("ssl" => true)));
		$this->assertEquals("https://ssl.testing.cz/en/",$this->_build_link(array(),array("ssl" => true, "with_hostname" => "ssl.testing.cz")));

		$this->assertEquals("https://ssl.testing.cz/en/",$this->_build_link(array(),array("ssl" => true, "with_hostname" => "ssl.testing.cz", "port" => 443)));
		$this->assertEquals("https://ssl.testing.cz/en/",$this->_build_link(array(),array("ssl" => true, "with_hostname" => "ssl.testing.cz", "port" => 80)));
		$this->assertEquals("https://ssl.testing.cz:444/en/",$this->_build_link(array(),array("ssl" => true, "with_hostname" => "ssl.testing.cz", "port" => 444)));

		$this->assertEquals("http://www.testing.cz/en/",$this->_build_link(array(),array("ssl" => false, "with_hostname" => "www.testing.cz")));
		$this->assertEquals("http://www.testing.cz/en/",$this->_build_link(array(),array("ssl" => false, "with_hostname" => "www.testing.cz", "port" => 80)));
		$this->assertEquals("http://www.testing.cz:81/en/",$this->_build_link(array(),array("ssl" => false, "with_hostname" => "www.testing.cz", "port" => 81)));
		$this->assertEquals("http://www.testing.cz:443/en/",$this->_build_link(array(),array("ssl" => false, "with_hostname" => "www.testing.cz", "port" => 443)));
	}

	function _test_route($request_uri,$expected_ar,$expected_params = array()){
		$route = Atk14Url::RecognizeRoute($request_uri);
		foreach($expected_ar as $k => $v){
			$this->assertequals($v,$route[$k],"testing $k in $request_uri");
		}
		foreach($expected_params as $k => $v){
			$this->assertEquals($route["get_params"][$k],$v,"params $k in $request_uri");
		}
		return $route;
	}

	function _test_404_route($request_uri){
		if(is_array($request_uri)){
			$out = array();
			foreach($request_uri as $r_uri){
				$out[] = $this->_test_404_route($r_uri);
			}
			return $out;
		}
		return $this->_test_route($request_uri,array(
			"controller" => "application",
			"action" => "error404",
			"force_redirect" => null
		));
	}

	function _build_link($params = array(),$options = array()){
		$params += array(
			"namespace" => "",
			"controller" => "main",
			"action" => "index",
			"lang" => "en",
		);

		$options += array(
			"connector" => "&"
		);

		return Atk14Url::BuildLink($params,$options);
	}
}
