# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project overview

UrlFetcher is a small PHP library (`atk14/url-fetcher`) providing a single class, `UrlFetcher`, for making HTTP/HTTPS requests (GET/POST/PUT/DELETE) using raw PHP streams — no cURL. `UrlFetcherViaCommand` is a subclass that pipes the request through an external command (e.g. `nc`) instead of opening a socket directly, useful for simulating HTTP requests. Supports PHP 5.6 through 8.5 (see the test matrix in `.github/workflows/tests.yml`).

The entire library is two files:
- `src/url_fetcher.php` — the `UrlFetcher` class
- `src/url_fetcher_via_command.php` — `UrlFetcherViaCommand extends UrlFetcher`

Depends on `atk14/string-buffer` (`StringBuffer` / `StringBufferTemporary`) for buffering request/response bodies without holding everything as a single PHP string.

## Commands

Install dependencies:
```
composer update --dev
```

Run the full test suite (from the `test/` directory):
```
cd test && ../vendor/bin/run_unit_tests
```

Run a single test file (by filename or class name, no `.php` needed):
```
cd test && ../vendor/bin/run_unit_tests tc_url_fetcher
cd test && ../vendor/bin/run_unit_tests tc_gzip_encoding tc_proxy
```

`run_unit_tests` (from `atk14/tester`) auto-discovers every `tc_*.php` file in the current directory, expects a matching class (`tc_foo_bar` or `TcFooBar`) extending `tc_base`/`TcBase`, and runs it through PHPUnit. `test/initialize.php` is loaded automatically first and requires the two `src/` files directly (not through Composer autoload), so source changes are picked up without a `composer dump-autoload`.

Proxy tests (`tc_proxy.php`) require a local `privoxy` instance on `127.0.0.1:8118` and are skipped (not failed) if it isn't running.

## Architecture notes

- **No cURL** — sockets are opened manually via `stream_socket_client()` (`tcp://` or `ssl://`), and requests/responses are written/read in chunks (`SOCKET_CHUNK_SIZE` = 256kB) with manual retry/backoff loops (`_fwriteStream()`, the read loop in `_makeWritingAndReading()`). This is the trickiest part of the codebase — timeouts, partial writes, and non-blocking reads are all handled by hand rather than delegated to a stream wrapper.
- **Proxy mode bypasses sockets entirely**: when `proxy` is set, `_makeWritingAndReading()` takes a completely different path using `fopen()` with an HTTP stream context instead of `stream_socket_client()`.
- **Two timeouts**: `socket_timeout` (connection, default 5.0s) vs `read_timeout` (time waiting for data after connecting, default 60.0s) — these are checked separately in `_makeWritingAndReading()`.
- **Lazy, memoized fetching**: `fetchContent()` only performs the actual request once; subsequent calls (including indirectly via `getContent()`, `getResponseHeaders()`, `getStatusCode()`, etc.) return the cached result. Setting a new URL (`_setUrl()`) calls `_reset()` and clears the memoized state.
- **Redirects are handled by recursion**: `fetchContent()` detects 301/302/303 + `Location` header, resolves absolute/relative/protocol-relative redirect targets itself (see the regex logic around line 439 in `url_fetcher.php`), resets `_Fetched`, and recurses into `fetchContent($location)`. `_MaxRedirections` (default 5) guards against loops. `getUrl()` returns the final URL after following redirects.
- **Gzip decoding** happens after the full response body is read, based on the `Content-Encoding` response header — see the `gzdecode()` call in `fetchContent()`.
- **`UrlFetcherViaCommand`** overrides only `_makeWritingAndReading()` — it opens the given shell command via `proc_open()`, writes the built request headers + body to its stdin, and parses headers/content back out of stdout the same way the socket path does. Everything else (URL parsing, redirects, auth, headers) is inherited unchanged from `UrlFetcher`.
- Global constant `URL_FETCHER_VERIFY_PEER` (default `true`) can be defined before the library loads to change the default SSL peer verification behavior; it's read once in the constructor via the `verify_peer` option default.

## Testing caveats

Most tests in `test/tc_url_fetcher.php`, `tc_ssl_certificates.php`, `tc_gzip_encoding.php`, etc. hit a **live remote test server** (`jarek.plovarna.cz/unit-testing/...`) rather than mocking HTTP — there is no local test server or fixture server in this repo. Expect these tests to require network access, and don't assume you can run them fully offline.

## Versioning

`UrlFetcher::VERSION` in `src/url_fetcher.php` must be bumped together with `composer.json`/tags when releasing. `CHANGELOG.md` follows a simple `[version] - date` format listing commit hash + one-line description per change.
