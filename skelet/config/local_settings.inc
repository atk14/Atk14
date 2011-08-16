<?
/**
* Either some parts of ATK14 system (i.e. mailing subsystem) or some third party libs
* could be configured by constants or variables.
* 
* This file is the right place to do such configuration.
*
* You can inspect all ATK14 system`s constants in sys/src/default_settings.inc
*/

define("SECRET_TOKEN","put_some_random_string_here");

define("ATK14_DOCUMENT_ROOT",dirname(__FILE__)."/../");
define("ATK14_BASE_HREF","/");

define("ATK14_APPLICATION_NAME","Our Colourful Website");
define("ATK14_HTTP_HOST",PRODUCTION ? "www.myapp.com" : "myapp.localhost");
define("ATK14_ADMIN_EMAIL","your@email");

if(DEVELOPMENT){
	// place for development environment settings

}

if(PRODUCTION){
	// place for production environment settings

}

if(TEST){
	// place for test environment settings

}

