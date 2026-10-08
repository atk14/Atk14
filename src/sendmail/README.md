Sendmail
========

[![Tests](https://github.com/atk14/Sendmail/actions/workflows/tests.yml/badge.svg)](https://github.com/atk14/Sendmail/actions/workflows/tests.yml)

Sendmail is a drop-in replacement for PHP's built-in `mail()` function, with MIME support, attachments, HTML e-mails with embedded images, and a custom sending hook.

Installation
------------

Just use Composer:

    composer require atk14/sendmail

Basic usage
-----------

`sendmail()` can be called exactly like the built-in `mail()` function:

    sendmail(string $to , string $subject , string $message [, mixed $additional_headers [, string $additional_parameters ]])

So in a legacy project every `mail()` occurrence can be replaced with `sendmail()`:

    sendmail("john@doe.com","Thank you for registration","Dear John\n\nthank you for your registration...");

Note: when `$additional_headers` is passed, the message is treated as already pre-built — `sendmail()` uses the given headers as-is and skips its own header construction (so options like `from_name`, `cc`, `bcc`, `extra_headers`, or attachments have no effect in that case).

Advanced usage
---------------

Sendmail can also be called with an associative array, which offers many more options:

    $mail_ar = sendmail([
      "from" => "info@snakeoil.com",
      "from_name" => "Snake Oil",
      // or "from" => "Snake Oil <info@snakeoil.com>",

      // "return_path" => "info@snakeoil.com",

      // "reply_to" => "reply@snakeoil.com",
      // "reply_to_name" => "Snake Oil",

      "to" => "John Doe <john@doe.com>",
      // "to_name" => "John Doe", // alternative to "John Doe <...>" above
      // "cc" => "",
      // "bcc" => "",

      "subject" => "Thank you for registration",
      "body" => "Dear John\n\nthank you for registration...",
      // "mime_type" => "text/plain",
      // "charset" => "UTF-8",
      // "transfer_encoding" => "8bit", // or "quoted-printable"

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
      // a single attachment can also be passed as "attachment" => [...]

      // "extra_headers" => ["Message-Id: <...>", "In-Reply-To: <...>"],

      // "build_message_only" => false,
    ]);

### Parameters

| Key | Description |
| --- | --- |
| `from` | Sender address. Can be a plain e-mail (`"info@snakeoil.com"`) or already contain a name (`"Snake Oil <info@snakeoil.com>"`). Defaults to `SENDMAIL_DEFAULT_FROM`. |
| `from_name` | Sender name, used when `from` is a plain e-mail address. Defaults to `SENDMAIL_DEFAULT_FROM_NAME`. |
| `to` | Recipient address(es). Accepts a single string (optionally `"Name <email>"`) or an array of addresses — duplicates and empty entries are removed automatically. |
| `to_name` | Recipient name, used when `to` is a plain e-mail address. |
| `cc` / `bcc` | Carbon-copy / blind-carbon-copy address(es), same format as `to`. Every message is additionally delivered to `SENDMAIL_BCC_TO` (or `BCC_EMAIL`), if set — see [Configuration constants](#configuration-constants). |
| `return_path` | Envelope sender used in the `Return-Path:` header and, unless `additional_parameters`/`SENDMAIL_MAIL_ADDITIONAL_PARAMETERS` says otherwise, to derive the `-f` bounce argument passed to `mail()`. Defaults to `from`. |
| `reply_to` / `reply_to_name` | `Reply-To:` address and name. Defaults to `from` / `from_name`. |
| `date` | Value of the `Date:` header. Defaults to the current time. |
| `subject` | Message subject. Non-ASCII subjects are automatically encoded per RFC 2047. |
| `body` | Message body. |
| `mime_type` | Body MIME type, e.g. `text/plain` or `text/html`. Defaults to `SENDMAIL_DEFAULT_BODY_MIME_TYPE`. |
| `charset` | Body charset, e.g. `UTF-8`. Defaults to `SENDMAIL_DEFAULT_BODY_CHARSET`. Pass an empty string to omit the charset from `Content-Type:` entirely. |
| `transfer_encoding` | Body transfer encoding: `8bit` (default, see `SENDMAIL_DEFAULT_TRANSFER_ENCODING`) or `quoted-printable`. Does not affect attachments, which are always base64-encoded. |
| `attachments` | Array of attachments, each `["body" => ..., "filename" => ..., "mime_type" => ...]`. As soon as there is at least one attachment, the message is assembled as a `multipart/mixed` MIME message. |
| `attachment` | Shortcut for a single attachment, equivalent to a one-item `attachments` array. |
| `extra_headers` | Array of raw header lines (e.g. `["Message-Id: <...>", "In-Reply-To: <...>"]`), appended as-is after all other headers. Ignored when `headers` is set (see below). |
| `build_message_only` | When `true`, the message is assembled and returned but not sent — see [Building without sending](#building-without-sending). |
| `headers` | Legacy 4th positional `mail()` argument. When set, `sendmail()` sends the given headers/body as-is instead of building its own — most other options above are ignored on this path. |
| `additional_parameters` | Extra parameters passed to PHP's `mail()` (the `-f...` bounce address, typically). Defaults to `SENDMAIL_MAIL_ADDITIONAL_PARAMETERS`, or an address derived from `return_path`/`from` when that constant is left at its default `null`. |

Two legacy aliases are still accepted for backward compatibility: `body_charset` (for `charset`) and `body_mime_type` (for `mime_type`).

### Return value

The returned value is an associative array containing the complete assembled message (`to`, `from`, `cc`, `bcc`, `return_path`, `subject`, `headers`, `body`, `additional_parameters`) plus `accepted_for_delivery`, which is:

* `true` / `false` — the message was handed to / rejected by `mail()`,
* `null` — sending was suppressed, either because `build_message_only` was used or because `SENDMAIL_DO_NOT_SEND_MAILS` is in effect.

#### Building without sending

The returned array can be fed back into another `sendmail()` call — handy for sending the same pre-built message to several recipients:

    $mail_ar = sendmail([
      ...
      "build_message_only" => true
    ]);

    $mail_ar["to"] = "john@doe.com";
    sendmail($mail_ar);

    $mail_ar["to"] = "samantha@doe.com";
    sendmail($mail_ar);

    // and so on

Sending HTML e-mails with images
---------------------------------

For sending HTML e-mails there is another function, `sendhtmlmail()`:

    $mail_ar = sendhtmlmail([
      "from" => "info@snakeoil.com",
      "to" => "john@doe.com",

      "subject" => "Sample HTML email",
      "plain" => "Plain text version",
      "html" => "<html>Html version<img src=\"cid:c8792dkQW\"><br><img src=\"cid:tytdk2392981\"></html>",

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
      ],

      // "extra_headers" => ["Message-Id: <...>"],
    ]);

It builds a multipart (plain + HTML + inline images) body and then sends it through `sendmail()`, so `extra_headers`, `build_message_only`, and the return value all behave the same way as described above.

Configuration constants
------------------------

There are several constants that affect the default behavior of Sendmail. They must be defined **before** Sendmail is loaded (each one only takes effect if not already defined, so these are safe defaults rather than hard overrides).

| Constant | Default | Description |
| --- | --- | --- |
| `SENDMAIL_DEFAULT_FROM` | `"sendmail"` | Default `from` address when none is given. |
| `SENDMAIL_DEFAULT_FROM_NAME` | `""` | Default `from_name`. |
| `SENDMAIL_DEFAULT_BODY_CHARSET` | `DEFAULT_CHARSET` if defined, else `"UTF-8"` | Default `charset`. |
| `SENDMAIL_DEFAULT_BODY_MIME_TYPE` | `"text/plain"` | Default `mime_type`. |
| `SENDMAIL_BODY_AUTO_PREFIX` | `""` | Text prepended to every message body, e.g. `"This is a testing message\nIgnore it. Do not reply!\n\n\n"`. Not applied when `build_message_only` is used. |
| `SENDMAIL_USE_TESTING_ADDRESS_TO` | `""` | When set to an e-mail address, every message is redirected there instead of its real `to`/`cc`/`bcc`; the original addresses are preserved in `X-Original-To:`, `X-Original-Cc:` and `X-Original-Bcc:` headers. |
| `SENDMAIL_DO_NOT_SEND_MAILS` | `true` when `DEVELOPMENT` or `TEST` is defined and truthy, `false` otherwise | When `true`, messages are assembled and returned but never actually sent (`accepted_for_delivery` is `null`). |
| `SENDMAIL_EMPTY_TO_REPLACE` | `""` | Fallback recipient used when `to` is left empty. |
| `SENDMAIL_DEFAULT_TRANSFER_ENCODING` | `"8bit"` | Default `transfer_encoding`: `"8bit"` or `"quoted-printable"`. |
| `SENDMAIL_MAIL_ADDITIONAL_PARAMETERS` | `null` | Extra parameters passed to `mail()`. When left `null`, a `-f` bounce address is derived automatically from `return_path`/`from`; set it to an explicit string (e.g. `"-fbounce@snakeoil.com"`) to use a fixed bounce address, or to `""` to pass no extra parameters at all. |
| `SENDMAIL_BCC_TO` | `""` | Every message is additionally sent as a blind copy to this address. If this constant is not set, a legacy constant `BCC_EMAIL` is used instead, when defined. |

Custom sending hook
--------------------

If a function `sendmail_hook_send()` is defined, it is called instead of the built-in `mail()`. This allows custom transports, logging, or queueing, and takes priority over `SENDMAIL_DO_NOT_SEND_MAILS`.

    function sendmail_hook_send(array $mail_ar, array $orig_params): array {
      // $mail_ar contains: to, from, subject, headers, body, accepted_for_delivery, ...
      // $orig_params contains the original parameters passed to sendmail()

      // custom sending logic here, e.g.:
      MyMailQueue::push($mail_ar);

      $mail_ar["accepted_for_delivery"] = true;
      return $mail_ar;
    }

License
-------

Sendmail is free software distributed [under the terms of the MIT license](http://www.opensource.org/licenses/mit-license)

[//]: # ( vim: set ts=2 et: )
