<?
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
$ATK14_GLOBAL->setValue("routes",array(

	// Here are SEF URI samples.
	// Compressed notation for 2 languages is used.

	// http://myapp.localhost/creature-8.json
	// http://myapp.localhost/creature-8.xml
	"
	en:creature-<id>.<format>
	cs:prisera-<id>.<format>
	" =>																			array(
																						"__path__" => "creatures/detail",
																						"__omit_trailing_slash__" => true,
																						"id" => "/[0-9]+/", // note that /^[0-9]+$/ doesn't work here
																																// but actually the pattern /[0-9]+/ acts here precisely like /^[0-9]+$/
																						"format" => "/(json|xml)/",
																						),
	
	// http://myapp.localhost/creature-8/
	"
	en:creature-<id>
	cs:prisera-<id>
	" =>																			array(
																						"__path__" => "creatures/detail",
																						"id" => "/[0-9]+/", // note that /^[0-9]+$/ doesn't work here
																																// but actually the pattern /[0-9]+/ acts here precisely like /^[0-9]+$/
																						),

	// Generic routes follow.
	// Keep them on the end of the list.

	// This is the front page route.
	// The front page will be served in the default language.
 	"" => 																		array(
																						"lang" => $ATK14_GLOBAL->getDefaultLang(),
																						"__path__" => "main/index",
																						"__page_title__" => ATK14_APPLICATION_NAME,
																						"__page_description__" => _("my beautiful application"),
																						),

	"<lang>" =>																array(
																						"__path__" => "main/index",
																						),

	"<lang>/<controller>" =>									array(
																						"action" => "index",
																						),
	
	"<lang>/<controller>/<action>" =>					array(
																						),

));
