<?php
// In this file there is old way how to define routes

$ATK14_GLOBAL->setValue("routes",[
	"
	cs:clanky/feed.rss
	en:articles/feed.rss
	"	=>																												[
																															"__path__" => "articles/overview",
																															"__omit_trailing_slash__" => true,
																															"format" => "rss",
																															],
	"
	cs:faktura/<id>.<format>
	en:invoice/<id>.<format>
	" => 																												[
																															"__path__" => "invoices/detail",
																															"__omit_trailing_slash__" => true,
																															"id" => "/[1-9][0-9]*/",
																															"format" => "/(pdf|xml)/",
																															],
	"
	cs:clanek/<id>-<slug>
	en:article/<id>-<slug>
	" =>																												[
																															"__page_title__" => "Article detail",
																															"__path__" => "articles/detail",
																															"id" => "/[1-9][0-9]*/",
																															"slug" => "/[a-z0-9-]+/",
																															],

	/*
	// Default routes are in the file config/routers/default_router.php
	"<lang>/<controller>" =>                           [
                                                              "__page_title__" => "",
 																															"__page_description__" => "",
																															"action" => "index",
                                                              ),
	
	"<lang>/<controller>/<action>" =>                           [
                                                              "__page_title__" => "",
 																															"__page_description__" => "",
                                                            ), */
]);
