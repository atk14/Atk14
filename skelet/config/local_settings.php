<?php
/**
 * Either some parts of ATK14 system (i.e. mailing subsystem) or some third party libs
 * could be configured by constants or variables.
 * 
 * This file is the right place to do such configuration.
 *
 * You can inspect all ATK14 application`s constants in atk14/default_settings.php
 * 
 * All the application constants should be inspected by calling:
 *	$ ./scripts/dump_settings
 * 
 * A certain constant should be inspected this way:
 *	$ ./scripts/dump_settings DEFAULT_EMAIL
 */

define("SECRET_TOKEN","put_some_random_string_here");

define("ATK14_DOCUMENT_ROOT",dirname(__FILE__)."/../");
define("ATK14_BASE_HREF","/");

define("DEFAULT_EMAIL","your@email");

define("ATK14_APPLICATION_NAME","ATK14 Project");
define("ATK14_HTTP_HOST",PRODUCTION ? "www.myapp.com" : "myapp.localhost");

if(DEVELOPMENT){
	// a place for development environment settings

}

if(PRODUCTION){
	// a place for production environment settings

}

if(TEST){
	// a place for test environment settings

}


