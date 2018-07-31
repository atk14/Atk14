<?php
defined("SENDMAIL_DEFAULT_FROM") || define("SENDMAIL_DEFAULT_FROM","sendmail"); // john@doe.com
defined("SENDMAIL_DEFAULT_FROM_NAME") || define("SENDMAIL_DEFAULT_FROM_NAME",""); // "John Doe"
defined("SENDMAIL_DEFAULT_BODY_CHARSET") || define("SENDMAIL_DEFAULT_BODY_CHARSET",defined("DEFAULT_CHARSET") ? constant("DEFAULT_CHARSET") : "UTF-8");
defined("SENDMAIL_DEFAULT_BODY_MIME_TYPE") || define("SENDMAIL_DEFAULT_BODY_MIME_TYPE","text/plain");
defined("SENDMAIL_BODY_AUTO_PREFIX") || define("SENDMAIL_BODY_AUTO_PREFIX","");
defined("SENDMAIL_USE_TESTING_ADDRESS_TO") || define("SENDMAIL_USE_TESTING_ADDRESS_TO","");
defined("SENDMAIL_DO_NOT_SEND_MAILS") || define("SENDMAIL_DO_NOT_SEND_MAILS",((defined("DEVELOPMENT") && DEVELOPMENT) || (defined("TEST") && TEST)));
defined("SENDMAIL_EMPTY_TO_REPLACE") || define("SENDMAIL_EMPTY_TO_REPLACE","");
defined("SENDMAIL_DEFAULT_TRANSFER_ENCODING") || define("SENDMAIL_DEFAULT_TRANSFER_ENCODING","8bit"); // "8bit" or "quoted-printable"
defined("SENDMAIL_BCC_TO") || define("SENDMAIL_BCC_TO",""); // every message will be sent as blind copy to this address

// To define a special bounce address:
//
//  define("SENDMAIL_MAIL_ADDITIONAL_PARAMETERS","-fbounce@domain.com");
//
// If SENDMAIL_MAIL_ADDITIONAL_PARAMETERS is set to null, a bounce address is determined automatically from the From: address.
//
// If extra parameters are not needed, set SENDMAIL_MAIL_ADDITIONAL_PARAMETERS to empty string:
//
//  define("SENDMAIL_MAIL_ADDITIONAL_PARAMETERS","");
//
defined("SENDMAIL_MAIL_ADDITIONAL_PARAMETERS") || define("SENDMAIL_MAIL_ADDITIONAL_PARAMETERS",null);

