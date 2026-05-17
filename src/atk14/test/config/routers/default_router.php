<?php
class DefaultRouter extends Atk14Router{

	function setUp(){

		// Two routes only for the default language (cs)
		$this->addRoute("/post-<id>.raw",[
			"lang" => $this->default_lang,
			"path" => "posts/detail",
			"params" => [
				"id" => '/\d+/',
				"format" => 'raw'
			]
		]);
		$this->addRoute("/post-<id>/",[
			"lang" => $this->default_lang,
			"controller" => "posts",
			"action" => "detail",
			"params" => [
				"id" => "/\d+/"
			]
		]);

		$this->addRoute("/sitemap.xml","sitemaps/index");
		$this->addRoute("/robots.txt","main/robots_txt");

		// Generic routes
		$this->addRoute("/",[
			"lang" => $this->default_lang,
			"path" => "main/index",
			"title" => ATK14_APPLICATION_NAME,
			"description" => ATK14_APPLICATION_DESCRIPTION,
		]);

		$this->addRoute("/<lang>/",[
			"path" => "main/index"
		]);

		$this->addRoute("/<lang>/<controller>/",[
			"action" => "index"
		]);

		$this->addRoute("/<lang>/<controller>/<action>/");
	}
}
