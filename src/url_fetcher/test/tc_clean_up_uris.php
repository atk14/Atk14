<?php
class TcCleanUpUris extends TcBase {
	
	function test(){
		foreach(array(
			"http://www.atk14.net/" 												=> "/",
			"http://www.atk14.net/./././" 									=> "/",
			"http://www.atk14.net/doc/././00-intro.md" 			=> "/doc/00-intro.md",
			"http://www.atk14.net/././." 										=> "/.",
			"http://www.atk14.net/../" 											=> "/",
			"http://www.atk14.net/.." 											=> "/",
			"http://www.atk14.net/../about/" 								=> "/about/",
			"http://www.atk14.net/../../about/" 						=> "/about/",
			"http://www.atk14.net/..//../about/" 						=> "/about/",
			"http://www.atk14.net/about/../" 								=> "/",
			"http://www.atk14.net/about//../" 							=> "/",
			"http://www.atk14.net/about/about-us/just-us/../../" 	=> "/about/",
			"http://www.atk14.net/../about/../about/about-us/just-us/../../" 	=> "/about/",
			"http://www.atk14.net/about/about-us/just-us/../.." 	=> "/about/",

			"http://www.atk14.net//about/" 									=> "//about/",
			"http://www.atk14.net/about//" 									=> "/about//",
		) as $url => $uri_expected){
			$uf = new UrlFetcher($url);
			$this->assertEquals($uri_expected,$uf->getUri(),"$url -->> $uri_expected");
			//
			$uf = new UrlFetcher($url."?");
			$this->assertEquals($uri_expected."?",$uf->getUri());
			//
			$uf = new UrlFetcher($url."?a=b&c=d");
			$this->assertEquals($uri_expected."?a=b&c=d",$uf->getUri());
		}
	}
}
