<?php
/**
 * Here are listed all application setting constants and their default values.
 * Values should be redefined in ./config/settings.php
 */

$__CONFIG_CONSTANTS__ = array();

__defaults__(array(
	"SECRET_TOKEN" => "make_sure_you_define_strong_secret_phrase_in_SECRET_TOKEN_constant",
	"TEMP" => TEST ? dirname(__FILE__)."/../tmp/test/" : dirname(__FILE__)."/../tmp/",
));

__defaults__(array(
	"DEFAULT_EMAIL" => "info@example.com",
	"BCC_EMAIL" => "",
	"DEFAULT_CHARSET" => "UTF-8",
));

__defaults__(array(
	"ATK14_APPLICATION_NAME" => "Our Website",
	"ATK14_APPLICATION_DESCRIPTION" => "Yet another application running on ATK14 Framework", // default description
	"ATK14_DOCUMENT_ROOT" => dirname(__FILE__)."/../", // where is the folder containing app/, config/, dispatcher.php...
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

	// By default ATK14 is able to redirect transparently from an old URL form to a new URL form.
	// This helps to keep every page on it's current URL form.
	//
	// Also you can disable redirecting only in a particular namespace (e.g. api) by setting the following constant
	//  define("ATK14_ENABLE_AUTO_REDIRECTING_IN_API",false);
	"ATK14_ENABLE_AUTO_REDIRECTING" => true,
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
));

__defaults__(array(
	"LOGGER_DEFAULT_LOG_FILE" => TEST ? dirname(__FILE__)."/../log/test.log" : dirname(__FILE__)."/../log/application.log",
	"LOGGER_DEFAULT_NOTIFY_EMAIL" => ATK14_ADMIN_EMAIL,
	"LOGGER_MIN_LEVEL_FOR_EMAIL_NOTIFICATION" => PRODUCTION ? 4 : 30, // 4 -> error, we don't want to receive emails with something less important than error
	"LOGGER_NO_LOG_LEVEL" => PRODUCTION ? -1 : -30, // -1 -> debug, we don't want to log debug messages on PRODUCTION

	"LOCK_DIR" => ATK14_DOCUMENT_ROOT . "robots/lock/",
));

__defaults__(array(
	"TABLERECORD_CACHES_STRUCTURES" => PRODUCTION ? 60*60 : 0, // caches table structures for the given amount of seconds

));

__defaults__(array(
	"DBMOLE_COLLECT_STATICTICS" => DEVELOPMENT, // DbMole is able to collect query statistics and then report it (echo $dbmole->getStatistics())
));

__defaults__(array(
	"PACKER_CONSTANT_SECRET_SALT" => SECRET_TOKEN,
	"PACKER_USE_COMPRESS" => function_exists('gzcompress'),
	"PACKER_ENABLE_ENCRYPTION" => function_exists('mcrypt_encrypt') && defined('MCRYPT_RIJNDAEL_256'), // TODO: provide an explanation
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

function __defaults__($defaults){
	global $__CONFIG_CONSTANTS__;
	foreach($defaults as $key => $value){
		!defined($key) && define($key,$value);
		$__CONFIG_CONSTANTS__["$key"] = constant($key);
	}
}
