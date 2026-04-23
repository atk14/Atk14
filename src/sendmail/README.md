Sendmail
========

[![Tests](https://github.com/atk14/Sendmail/actions/workflows/tests.yml/badge.svg)](https://github.com/atk14/Sendmail/actions/workflows/tests.yml)

Sendmail is a replacement for function mail() in PHP.

Basic usage
-----------

Function sendmail() can be called the same way like built-in function mail().

    sendmail(string $to , string $subject , string $message [, mixed $additional_headers [, string $additional_parameters ]])

So in a legacy project every mail() occurrence can be replaced with sendmail().

    sendmail("john@doe.com","Thank you for registration","Dear John\n\nthank you for your registration...");

Sendmail can also be called with associative array which offers more options.

    $mail_ar = sendmail([
      "from" => "info@snakeoil.com",
      "from_name" => "Snake Oil",
      // or "from" => "Snake Oil <info@snakeoil.com>",

      // "return_path" => "info@snakeoil.com",

      // "reply_to" => "reply@snakeoil.com",
      // "reply_to_name" => "Snake Oil",

      "to" => "John Doe <john@doe.com>",
      // "cc" => "",
      // "bcc" => "",

      "subject" => "Thank you for registration",
      "body" => "Dear John\n\nthank you for registration...",
      // "mime_type" => "text/plain",
      // "charset" => "UTF-8",
      // "transfer_encoding" => "quoted-printable",

      "attachments" => [
        [
          "body" => file_get_contents("/path/to/file"),
          "filename" => "confirmation.pdf",
          "mime_type" => "application/pdf",
        ],[
          "body" => file_get_contents("/path/to/another/file"),
          "filename" => "id_card.png",
          "mime_type" => "image/png"
        ]
      ],

      // "build_message_only" => false,
    ]);

Returned value is an associative array, which contains the complete assembled message and also a status "accepted_for_delivery" (`true` on success, `false` on failure, or `null` when sending is suppressed by the environment). The array can be used as a parameter for another sendmail() call.

    $mail_ar = sendmail([
      ...
      "build_message_only" => true
    ])

    $mail_ar["to"] = "john@doe.com";
    sendmail($mail_ar);

    $mail_ar["to"] = "samantha@doe.com";
    sendmail($mail_ar);

    // and so on

Sending HTML emails with images
-------------------------------

For sending HTML emails there is another function sendhtmlmail().

    $mail_ar = sendhtmlmail([
      "from" => "info@snakeoil.com",
      "to" => "john@doe.com",

      "subject" => "Sample HTML email",
      "plain" => "Plain text version",
      "html" => "<html>Html version<img src="cid:c8792dkQW"><br><img src="cid:tytdk2392981"></html>",

      "images" => [
        [
          "filename" => "sea.gif",
          "content" => $binary_content,
          "cid" => "c8792dkQW",
        ],
        [
          "filename" => "mountain.jpg",
          "content" => $binary_content_2,
          "cid" => "tytdk2392981",
        ]
      ]
    ]);

Configuration constants
-----------------------

There are several constants that affect the default behavior of Sendmail. Please note that they must be defined before Sendmail is being loaded.

    define("SENDMAIL_DEFAULT_FROM","info@snakeoil.com");
    define("SENDMAIL_DEFAULT_FROM_NAME","Snake Oil");
    define("SENDMAIL_DEFAULT_BODY_CHARSET","UTF-8");
    define("SENDMAIL_DEFAULT_BODY_MIME_TYPE","text/plain");
    define("SENDMAIL_BODY_AUTO_PREFIX",""); // "This is a testing message\nIgnore it. Do not reply!\n\n\n"
    define("SENDMAIL_USE_TESTING_ADDRESS_TO","");
    define("SENDMAIL_DO_NOT_SEND_MAILS",((defined("DEVELOPMENT") && DEVELOPMENT) || (defined("TEST") && TEST)));
    define("SENDMAIL_EMPTY_TO_REPLACE","missing.email@snakeoil.com");
    define("SENDMAIL_DEFAULT_TRANSFER_ENCODING","8bit"); // "8bit" or "quoted-printable"
    define("SENDMAIL_MAIL_ADDITIONAL_PARAMETERS","-fbounce@snakeoil.com");
    define("SENDMAIL_BCC_TO","sent.emails@snakeoil.com");

Custom sending hook
-------------------

If a function `sendmail_hook_send()` is defined, it is called instead of the built-in `mail()`. This allows custom transports, logging, or queueing.

    function sendmail_hook_send(array $mail_ar, array $orig_params): array {
      // $mail_ar contains: to, from, subject, headers, body, accepted_for_delivery, ...
      // $orig_params contains the original parameters passed to sendmail()

      // custom sending logic here, e.g.:
      MyMailQueue::push($mail_ar);

      $mail_ar["accepted_for_delivery"] = true;
      return $mail_ar;
    }

Installation
------------

Just use the Composer:

    composer require atk14/sendmail

License
-------

Sendmail is free software distributed [under the terms of the MIT license](http://www.opensource.org/licenses/mit-license)

[//]: # ( vim: set ts=2 et: )
