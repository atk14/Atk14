<?php
class DefaultRouter extends Atk14Router{

	function setUp(){

		// Two routes only for the default language (cs)
		$this->addRoute("/post-<id>.raw",array(
			"lang" => $this->default_lang,
			"path" => "posts/detail",
			"params" => array(
				"id" => '/\d+/',
				"format" => 'raw'
			)
		));
		$this->addRoute("/post-<id>/",array(
			"lang" => $this->default_lang,
			"controller" => "posts",
			"action" => "detail",
			"params" => array(
				"id" => "/\d+/"
			)
		));

		$this->addRoute("/sitemap.xml","sitemaps/index");
		$this->addRoute("/robots.txt","main/robots_txt");

		// Generic routes
		$this->addRoute("/",array(
			"lang" => $this->default_lang,
			"path" => "main/index",
			"title" => ATK14_APPLICATION_NAME,
			"description" => ATK14_APPLICATION_DESCRIPTION,
		));

		$this->addRoute("/<lang>/",array(
			"path" => "main/index"
		));

		$this->addRoute("/<lang>/<controller>/",array(
			"action" => "index"
		));

		$this->addRoute("/<lang>/<controller>/<action>/");
	}
}
