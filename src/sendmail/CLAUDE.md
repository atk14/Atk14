# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

Sendmail is a small PHP library (`atk14/sendmail`) that replaces PHP's built-in `mail()` function, adding MIME support, attachments, HTML emails with inline images, and a custom sending hook. It's a drop-in replacement: `sendmail()` can be called exactly like `mail()`, or with a richer associative-array signature for more control.

## Commands

Install dependencies:

    composer update

Run the full test suite (uses `atk14/tester`, not PHPUnit directly, despite `phpunit` also being present in `vendor/bin`):

    cd test && ../vendor/bin/run_unit_tests

Run a single test file:

    cd test && ../vendor/bin/run_unit_tests tc_sendmail.php

Tests run against PHP 5.6 through 8.5 in CI (see `.github/workflows/tests.yml`), so avoid syntax/features newer than PHP 5.6 unless guarded.

## Architecture

The library is three files in `src/`, loaded via Composer's `files` autoload entry (`src/sendmail.php` is always included) plus `classmap` for `_CMailFile`:

- **`src/constants.php`** — defines all `SENDMAIL_*` configuration constants (via `defined(...) || define(...)` so user-defined values in the host app always win). Required lazily (`require_once`) from inside the functions that need it, not at load time, so host apps can define constants *after* Composer autoloads the library but before calling `sendmail()`.
- **`src/sendmail.php`** — the entire public API and all logic:
  - `sendmail($params, $subject, $message, $additional_headers, $additional_parameters)` — the main entry point. Accepts either the legacy positional `mail()`-style signature (first arg is a string `$to`) or a single associative array of options. Builds headers/body, optionally delegates attachment/MIME assembly to `_CMailFile`, and either returns the built message (`build_message_only`) or sends it.
  - `sendhtmlmail($options)` — builds a `multipart/related` + `multipart/alternative` MIME body (plain + HTML + inline images via `cid:`) and delegates to `sendmail()`.
  - A family of `_sendmail_*()` private helper functions (address parsing/rendering, subject RFC 2047 encoding, quoted-printable encoding, boundary generation, base64 chunking, CRLF normalization). These are implementation details, not part of the public API.
  - `_sendmail_mail()` is the final call site that invokes PHP's real `mail()` — this is the only place real mail is sent, which is why `SENDMAIL_DO_NOT_SEND_MAILS` and the `sendmail_hook_send()` hook can both short-circuit before it.
- **`src/_CMailFile.php`** — legacy class (`_CMailFile`) that assembles a `multipart/mixed` MIME message with one or more file attachments. Only used by `sendmail()` when `attachments` is non-empty; its `getfile()` method returns body/headers which `sendmail()` then merges with its own headers (From/Reply-To/Bcc/Cc/etc.) rather than using `_CMailFile`'s own header generation. `sendfile()` on this class is dead code (noted in the source) — `sendmail()` always goes through its own `_sendmail_mail()` path instead.

### Key control-flow branches in `sendmail()`

1. **Legacy `$headers` param path**: if `$params["headers"]` (the old 4th positional `mail()` arg) is set, the function takes a "pre-built email" shortcut — it skips all header construction and attachment handling (marked as a known dirty/incomplete solution in the code). `extra_headers` is ignored on this path.
2. **No attachments**: headers are built by hand (From, Reply-To, Bcc, Cc, MIME-Version, Content-Type, Content-Transfer-Encoding, Return-Path, Date), then `extra_headers` lines are appended.
3. **With attachments**: a `_CMailFile` instance builds the MIME-multipart body/headers instead; `extra_headers` lines are still appended afterward by `sendmail()`.
4. **Testing/suppression**: `SENDMAIL_USE_TESTING_ADDRESS_TO` reroutes `to`/`cc`/`bcc` to a single testing address (preserving the originals in `X-Original-To/Cc/Bcc` headers), and `SENDMAIL_DO_NOT_SEND_MAILS` suppresses the actual `mail()` call while still returning the assembled message (`accepted_for_delivery` is `null` in that case vs. `true`/`false` when actually sent).
5. **Custom transport**: if the host app defines `sendmail_hook_send(array $mail_ar, array $orig_params): array`, it's called instead of `_sendmail_mail()`, receiving the fully assembled message plus the original call params — this is the extension point for queues/logging/alternate transports, and it takes priority over `SENDMAIL_DO_NOT_SEND_MAILS`.

Both `sendmail()` and `sendhtmlmail()` can return early (`build_message_only: true`) with the fully assembled message without sending — the returned array can be fed back into `sendmail()` later (e.g. to send the same built message to several different recipients).

## Dependencies

- `atk14/translate` (runtime) — used for `Translate::CheckEncoding()` in subject-line RFC 2047 encoding.
- `atk14/tester` (dev) — the test runner/assertion framework used by `vendor/bin/run_unit_tests`; test cases extend `tc_base` (not PHPUnit's `TestCase`).
- `atk14/files` (dev) — used by test fixtures.

Test bootstrap is `test/initialize.php`, which defines constants like `SENDMAIL_DO_NOT_SEND_MAILS` and `SENDMAIL_DEFAULT_FROM` before loading the autoloader, so tests never actually send mail.
