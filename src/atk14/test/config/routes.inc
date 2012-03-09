<?php
$ATK14_GLOBAL->setValue("routes",array(
	"
	cs:clanky/feed.rss
	en:articles/feed.rss
	"	=>																												array(
																															"__path__" => "articles/overview",
																															"__omit_trailing_slash__" => true,
																															"format" => "rss",
																															),
	"
	cs:faktura/<id>.<format>
	en:invoice/<id>.<format>
	" => 																												array(
																															"__path__" => "invoices/detail",
																															"__omit_trailing_slash__" => true,
																															"id" => "/[1-9][0-9]*/",
																															"format" => "/(pdf|xml)/",
																															),
	"
	cs:clanek/<id>-<slug>
	en:article/<id>-<slug>
	" =>																												array(
																															"__page_title__" => "Article detail",
																															"__path__" => "articles/detail",
																															"id" => "/[1-9][0-9]*/",
																															"slug" => "/[a-z0-9-]+/",
																															),

	"<lang>/<controller>" =>                           array(
                                                              "__page_title__" => "",
 																															"__page_description__" => "",
																															"action" => "index",
                                                              ),
	
	"<lang>/<controller>/<action>" =>                           array(
                                                              "__page_title__" => "",
 																															"__page_description__" => "",
                                                              ),
));
