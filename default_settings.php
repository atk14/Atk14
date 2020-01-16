<?php
/**
 * Here are listed all application setting constants and their default values.
 * Values should be redefined in ./config/settings.php
 */

$__CONFIG_CONSTANTS__ = array();

// Make sure you have strong secret phrase in SECRET_TOKEN constant in PRODUCTION.
// Place the secret phrase into file config/.secret_token.txt
if(!defined("SECRET_TOKEN")){
	if(file_exists(__DIR__ . "/../config/.secret_token.txt")){
		define("SECRET_TOKEN",Files::GetFileContent(__DIR__ ."/../config/.secret_token.txt"));
	}elseif(file_exists(__DIR__ . "/../config/secret_token.txt")){ // legacy filename, not recommended
		define("SECRET_TOKEN",Files::GetFileContent(__DIR__ ."/../config/secret_token.txt"));
	}
}

__defaults__(array(
	"SECRET_TOKEN" => "DANGER!!! WEAK SECRET_TOKEN!!!",
	"TEMP" => __realpath__( TEST ? __DIR__."/../tmp/test" : __DIR__."/../tmp" )."/",
));

if(trim(SECRET_TOKEN)=="" || (PRODUCTION && SECRET_TOKEN=="DANGER!!! WEAK SECRET_TOKEN!!!")){
	throw new Exception("A propper SECRET_TOKEN is missing.  Perhaps file config/.secret_token.txt is missing or is empty.");
}

__defaults__(array(
	"DEFAULT_EMAIL" => "info@example.com",
	"BCC_EMAIL" => "",
	"DEFAULT_CHARSET" => "UTF-8",
));

__defaults__(array(
	"ATK14_APPLICATION_NAME" => "Our Website",
	"ATK14_APPLICATION_DESCRIPTION" => "Yet another application running on ATK14 Framework", // default description
	"ATK14_DOCUMENT_ROOT" => __realpath__(__DIR__."/..")."/", // where is the folder containing app/, config/, dispatcher.php...
	"ATK14_BASE_HREF" => "/",
	"ATK14_HTTP_HOST" => "www.our-awesome-website.com", //
	"ATK14_ADMIN_EMAIL" => DEFAULT_EMAIL, // an address for sending DbMole's error reports...

	"ATK14_PAGINATOR_OFFSET_PARAM_NAME" => "offset", // you may want to use "from" or something else
	"ATK14_SORTING_PARAM_NAME" => "order",
	"ATK14_DEFAULT_LANG" => "auto", // "en", "cs"... when it is "auto", the default lang will be determined by config/locale.yml

	"ATK14_USE_SMARTY3" => true,
	"ATK14_SMARTY_DEFAULT_MODIFIER" => 'h', // 'h' is a goog one, see http://www.smarty.net/docs/en/variable.default.modifiers.tpl
	"ATK14_SMARTY_DIR_PERMS" => 0771, # default Smartys directory permissions
	"ATK14_SMARTY_FILE_PERMS" => 0644, # default Smartys file permissions
	"ATK14_SMARTY_FORCE_COMPILE" => !PRODUCTION, // It may be desirable that in DEVELOPMENT $smarty->force_compile is set to true, see https://www.smarty.net/docs/en/variable.force.compile.tpl

	// For "filename" it is required to have proper rewrite rules in the .htaccess
	//
	//	RewriteCond %{REQUEST_URI} ^\/public\/[^?]+\.v[0-9a-f]{1,64}\.[a-zA-Z0-9]{1,10}(|\?.*)$
	//	RewriteCond %{REQUEST_FILENAME} !-f
	//	RewriteRule ^(public\/.+)\.v[0-9a-f]{1,64}(\.[a-zA-Z0-9]{1,10})$ $1$2 [L]
	// 
	"ATK14_STATIC_FILE_VERSIONS_INDICATED_BY" => "parameter", // "parameter", "filename", "none", see helpers javascript_script_tag and stylesheet_link_tag

	// By default ATK14 is able to redirect transparently from an old URL form to a new URL form.
	// This helps to keep every page on it's current URL form.
	//
	// Also you can disable redirecting only in a particular namespace (e.g. api) by setting the following constant
	//  define("ATK14_ENABLE_AUTO_REDIRECTING_IN_API",false);
	"ATK14_ENABLE_AUTO_REDIRECTING" => true,

	"ATK14_ENABLE_DESTROY_DATABASE_OBJECTS_IN_PRODUCTION" => false, // Can be the script ./scripts/destroy_database_objects executed executed in PRODUCTION?

	"ATK14_LOAD_AFTER_INITIALIZE_SETTINGS" => true, // Load config/after_initialize.php if the file exists?
));

__defaults__(array(
	"ATK14_HTTP_HOST_SSL" => ATK14_HTTP_HOST, // sometimes a ssl hostname differs from the non-ssl, like www.project-x.net and secure.project-x.net
	"ATK14_NON_SSL_PORT" => 80,
	"ATK14_SSL_PORT" => 443,
));

// SessionStorer`s constants, a session subsystem
__defaults__(array(
	"SESSION_STORER_SESSION_MAX_LIFETIME" => 60 * 60 * 24 * 1, // time in seconds; whole day by default
	"SESSION_STORER_DEFAULT_SESSION_NAME" => "session",
	"SESSION_STORER_COOKIE_NAME_SESSION" => "%session_name%",
	"SESSION_STORER_COOKIE_NAME_CHECK" => "check", // set this to empty string for disable sending the testing cookie
	"SESSION_STORER_INITIALIZE_DATABASE_SESSION_EARLY" => true,
	"SESSION_STORER_SET_COOKIES_ONLY_ON_SSL_BY_DEFAULT" => false,
));

__defaults__(array(
	"LOGGER_DEFAULT_LOG_FILE" => __realpath__( TEST ? __DIR__."/../log/test.log" : __DIR__."/../log/application.log" ),
	"LOGGER_DEFAULT_NOTIFY_EMAIL" => ATK14_ADMIN_EMAIL,
	"LOGGER_MIN_LEVEL_FOR_EMAIL_NOTIFICATION" => PRODUCTION ? 4 : 30, // 4 -> error, we don't want to receive emails with something less important than error
	"LOGGER_NO_LOG_LEVEL" => PRODUCTION ? -1 : -30, // -1 -> debug, we don't want to log debug messages on PRODUCTION

	"LOCK_DIR" => ATK14_DOCUMENT_ROOT . "robots/lock/",
));

__defaults__(array(
	"TABLERECORD_CACHES_STRUCTURES" => PRODUCTION ? 60*60 : 0, // caches table structures for the given amount of seconds

));

__defaults__(array(
	"DBMOLE_COLLECT_STATICTICS" => DEVELOPMENT, // DbMole is able to collect query statistics and then report it (echo $dbmole->getStatistics()),
	"DBMOLE_AUTOMATIC_DELAY_TRANSACTION_BEGINNING_AFTER_CONNECTION" => !TEST, // In tests do not automatically delay the transaction beginning.
));

__defaults__(array(
	"PACKER_CONSTANT_SECRET_SALT" => SECRET_TOKEN,
	"PACKER_USE_COMPRESS" => function_exists('gzcompress'),
	"PACKER_ENABLE_ENCRYPTION" => true, // function openssl_encrypt() must exists('openssl_encrypt')!
));

__defaults__(array(
	"SENDMAIL_DEFAULT_FROM" => DEFAULT_EMAIL,
	"SENDMAIL_DEFAULT_BODY_CHARSET" => DEFAULT_CHARSET,
	"SENDMAIL_DEFAULT_BODY_MIME_TYPE" => "text/plain",
	"SENDMAIL_BODY_AUTO_PREFIX" => "", // given text will be prepend to every message
	"SENDMAIL_USE_TESTING_ADDRESS_TO" => "", // all emails will be sent to this address; for testing purposes
	"SENDMAIL_DO_NOT_SEND_MAILS" => !PRODUCTION, // do not send emails in DEVELOPMENT or TESTing environment
	"SENDMAIL_EMPTY_TO_REPLACE" => "", // empty to address replacement
	"SENDMAIL_DEFAULT_TRANSFER_ENCODING" => "quoted-printable", // "8bit" or "quoted-printable"
));

__defaults__(array(
	"USING_BOOTSTRAP4" => false, // HTML snippets rendered from some parts of the framework can be tuned for Bootstrap4
	"USING_FONTAWESOME" => false, // Is it OK to decorate internal HTML snippets with something like <span class="fas fa-arrow-up"></span> (see https://fontawesome.com/)
));

__defaults__(array(
	"FORMS_MARKUP_TUNED_FOR_BOOTSTRAP4" => USING_BOOTSTRAP4,
	"FORMS_AUTOMATICALLY_MOVE_HINTS_TO_PLACEHOLDERS" => false, // In some cases on some kinds of fields specific hints can be automatically moved into placeholders. This was standard behavior in older versions of the ATK14 Framework.
));

function __defaults__($defaults){
	global $__CONFIG_CONSTANTS__;
	foreach($defaults as $key => $value){
		!defined($key) && define($key,$value);
		$__CONFIG_CONSTANTS__["$key"] = constant($key);
	}
}

function __realpath__($path){
	$realpath = realpath($path); // realpath() return false when $path doesn't exist
	return $realpath ? $realpath : $path;
}
