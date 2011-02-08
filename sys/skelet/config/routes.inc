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
*			"creature-detail-<id>" =>	array(
*															 	"__path__" => "creatures/detail",
*															 	"id" => "/[0-9]+/",
*															 	"lang" => "en"
*															 	)
*
* Previous link will change automatically to the following one
*
*			/creature-detail-123/
*
* And even more when a visitor visits previous link directly (i.e. from a bookmarks),
* he will be transparently redicted to the new one.
*/
$ATK14_GLOBAL->setValue("routes",array(

	// here is a SEF URI sample
	// compressed notation for 2 languages is used
	"
	en:creature-detail-<id>
	cs:detail-prisery-<id>
	" =>																			array(
																						"__path__" => "creatures/detail",
																						"id" => "/[0-9]+/", // note that /^[0-9]+$/ doesn't work here
																						),

	// generic routes follow...
	// keep them on the end of the list
 	"" => 																		array(
																						"lang" => $ATK14_GLOBAL->getDefaultLang(),
																						"__path__" => "main/index",
																						"__page_title__" => "My Application",
																						"__page_description__" => "my beautiful application",
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
