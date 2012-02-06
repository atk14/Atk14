<?php
/**
* Here is the list of routes (URIs) to controllers and their actions. Routes are
* considered in order - first matching route will be used.
* 
* Four generic routes at the end of the list are enough for every application.
*
* Search engine friendly URIs can be also defined here. Consider to setup SEF URIs
* at the end of the development or at least not at the begining.
* 
* In your templates build links always this way
*	
*		{a controller=creatures action=detail id=$creature}Detail of the creature{/a}
*
* According the matching generic route, link will look like
*
*			/en/creatures/detail/?id=123
*
* And then add SEF route definition:
*
*			"creature-<id>" =>	array(
*															 	"__path__" => "creatures/detail",
*															 	"id" => "/[0-9]+/",
*															 	"lang" => "en"
*															 	)
*
* Previous link will change automatically to the following one
*
*			/creature-123/
*
* And even more when a visitor visits previous link directly (i.e. from a bookmarks),
* he will be transparently redicted to the new one.
*/
class DefaultRouter extends Atk14StaticRouter{
	var $namespace = "";

	function setUp(){
		// Here are SEF URI examples.

		// http://myapp.localhost/creatures/ (english)
		// http://myapp.localhost/prisery/ (czech)
		$this->addRoute("/creatures/","en/creatures/index");
		$this->addRoute("/prisery/","cs/creatures/index");

		// http://myapp.localhost/creature-8.json (english)
		// http://myapp.localhost/prisera-8.xml (czech)
		foreach(array(
			"en" => "/creature-<id>.<format>",
			"cs" => "/prisera-<id>.<format>",
		) as $lang => $uri){
			$this->addRoute($uri,"$lang/creatures/detail",
											array(
												"id" => "/[0-9]+/", // note that /^[0-9]+$/ doesn't work here, but actually the pattern /[0-9]+/ acts here precisely like /^[0-9]+$/
												"format" => "/(json|xml)/"
											)
			);
		}

		// http://myapp.localhost/creature-8/ (english)
		// http://myapp.localhost/prisera-8/ (czech)
		foreach(array(
			"en" => "/creature-<id>/",
			"cs" => "/prisera-<id>/"
		) as $lang => $uri){
			$this->addRoute($uri,"$lang/creatures/detail",
											array(
												"id" => "/[0-9]+/"
											)
			);
		}

		// Generic routes follow.
		// Keep them on the end of the list.

		// This is the front page route.
		// The front page will be served in the default language.
		$this->addRoute("/",array(
			"lang" => $this->default_lang,
			"path" => "main/index",
			"title" => ATK14_APPLICATION_NAME,
			"description" => _("my beautiful application"),
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
