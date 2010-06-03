<?
class tc_atk14_url extends tc_base{
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

		// format=rss mame v routes.inc
		$_GET = array("format" => "rss");
		$this->_test_route("/en/articles/overview/?format=rss",array(
			"namespace" => "",
			"lang" => "en",
			"controller" => "articles",
			"action" => "overview",
			"force_redirect" => "/articles/feed.rss"
		));

		// format=xml nemame v routes.inc
		$_GET = array("format" => "xml");
		$this->_test_route("/en/articles/overview/?format=xml",array(
			"namespace" => "",
			"lang" => "en",
			"controller" => "articles",
			"action" => "overview",
			"force_redirect" => null,
		));
	
		$_GET = array();
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
		// chybejici lomitko na konci request_uri
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
		// chybejici lomitko
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

	function _test_route($request_uri,$expected_ar){
		$route = Atk14Url::RecognizeRoute($request_uri);
		foreach($expected_ar as $k => $v){
			$this->assertEquals($v,$route[$k],"testing $k in $request_uri");
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
}
