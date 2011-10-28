<?php
$__CONFIG_CONSTANTS__ = array();

__defaults__(array(
	"SECRET_TOKEN" => "make_sure_you_define_strong_secret_phrase_in_SECRET_TOKEN_constant",
	"TEMP" => TEST ? dirname(__FILE__)."/../tmp/test/" : dirname(__FILE__)."/../tmp/",
));

__defaults__(array(
	"DEFAULT_EMAIL" => "info@example.com",
));

__defaults__(array(
	"ATK14_APPLICATION_NAME" => "Our Website",
	"ATK14_DOCUMENT_ROOT" => dirname(__FILE__)."/../", // where is the folder containing app/, config/ dispatcher.php...
	"ATK14_BASE_HREF" => "/",
	"ATK14_HTTP_HOST" => "", // 
	"ATK14_ADMIN_EMAIL" => DEFAULT_EMAIL,
));

__defaults__(array(
	"SESSION_STORER_SESSION_MAX_LIFETIME" => 60 * 60 * 24 * 1, // time in seconds; whole day by default
	"SESSION_STORER_COOKIE_NAME_SESSION" => "session",
	"SESSION_STORER_COOKIE_NAME_CHECK" => "check",
));

__defaults__(array(
	"LOGGER_DEFAULT_LOG_FILE" => TEST ? dirname(__FILE__)."/../log/test.log" : dirname(__FILE__)."/../log/application.log",
));

__defaults__(array(
	"INOBJ_TABLERECORD_CACHES_STRUCTURES" => PRODUCTION ? 60*60 : 0, // caches table structures for given seconds
));

__defaults__(array(
	"DBMOLE_COLLECT_STATICTICS" => false, // DbMole is able to collect query statistics and then report it (echo $dbmole->getStatistics())
));

__defaults__(array(
	"PACKER_CONSTANT_SECRET_SALT" => SECRET_TOKEN,
	"PACKER_USE_COMPRESS" => false,
));

__defaults__(array(
	"SENDMAIL_DEFAULT_FROM" => DEFAULT_EMAIL,
	"SENDMAIL_DEFAULT_BODY_CHARSET" => "UTF-8",
	"SENDMAIL_DEFAULT_BODY_MIME_TYPE" => "text/plain",
	"SENDMAIL_BODY_AUTO_PREFIX" => "", // given text will be prepend to every message
	"SENDMAIL_USE_TESTING_ADDRESS_TO" => false,
	"SENDMAIL_TESTING_ADDRESS_TO" => "", // all emails will be sent to this address; for testing purposes
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


