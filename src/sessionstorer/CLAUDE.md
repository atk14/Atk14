# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Overview

`SessionStorer` is a PHP library for storing sessions in a database, used in the ATK14 Framework. It is a single-class library (`src/sessionstorer.php`) backed by `atk14/dbmole`. The primary database is PostgreSQL (which is the primary database for ATK14); MySQL, Oracle, and MS SQL Server are also supported.

## Running Tests

Tests require a PostgreSQL database. The test suite uses `atk14/tester` and runs via:

```bash
cd test && ../vendor/bin/run_unit_tests
```

To run a single test file:
```bash
cd test && ../vendor/bin/run_unit_tests tc_sessionstorer.php
```

The test database connection is configured in `vendor/atk14/dbmole/test/connections_and_handler.php`. CI uses `postgres` user with password `postgres` on localhost:5432, with a `test` database owned by user `test`.

Each test method wraps in a transaction that is rolled back in `_tearDown()` (see `test/tc_base.php`), so tests are isolated.

## Architecture

### Hybrid cookie/database storage

Values are stored in two stages:
1. **Before a valid session cookie exists**: all current values are serialized (via `Packer::Pack`) and written into one or more numbered cookies (`_ses_0`, `_ses_1`, …) as a temporary measure. The first cookie carries a length prefix (`<length>:<data>`); additional cookies continue the data. This is a single-request measure — the cookies are cleared at the start of the next request once a DB session is established.
2. **After a database session is established**: values migrate from cookies to the `session_values` table, and the session is identified by a cookie containing `{session_id}.{security_token}`.

### Cookie-only mode

Passing `"cookie_only" => true` disables the database entirely. All values are stored in numbered cookies for the lifetime of the session. The packed data includes an `extra_salt` derived from the class name and session name to prevent cross-session data reuse. Expired values are automatically removed from cookies on the next read (triggering a cookie rewrite even without an explicit `writeValue` call). Use `"disable_check_cookie" => true` alongside to suppress the check cookie.

### Database schema

Two tables are required (see `src/structures.postgresql.sql`):

- **`sessions`**: one row per session (`id`, `session_name`, `security`, `remote_addr`, `last_access`, `created`)
- **`session_values`**: key-value rows per session (`session_id`, `section`, `key`, `value`, `expiration`), with a unique constraint on `(session_id, section, key)`

Values are serialized with `base64_encode(serialize($value))` before storage.

### Sections

A single session (identified by cookie) can contain multiple logical **sections** (e.g. `"default"`, `"admin"`). The section is passed as the first constructor argument and scopes all reads/writes, allowing different parts of the app to share a session without key collisions.

### Session token format

The session cookie value is `{session_id}.{security_token}` (e.g. `1215.WKN7voIUyCGER4OzkPwl2B3eJ1QM68mL`). Both must match a database row for the session to be valid.

### Key configuration constants

All constants have defaults and should be defined before including the library:

| Constant | Default | Purpose |
|---|---|---|
| `SESSION_STORER_SESSION_MAX_LIFETIME` | 86400 (1 day) | Garbage collection lifetime |
| `SESSION_STORER_DEFAULT_SESSION_NAME` | `"session"` | Cookie name base |
| `SESSION_STORER_COOKIE_NAME_SESSION` | `"_%session_name%_"` | Cookie name template |
| `SESSION_STORER_COOKIE_NAME_CHECK` | `"_chk_"` | Testing/detection cookie name |
| `SESSION_STORER_SHARE_COOKIES_ON_SUBDOMAINS` | `false` | Set `true` or a domain string |
| `SESSION_STORER_AUTO_GARBAGE_COLLECTION` | `true` | Random 1-in-20 GC on write |
| `SESSION_STORER_SET_COOKIES_ONLY_ON_SSL_BY_DEFAULT` | `false` | Force HTTPS cookies |
| `SESSION_STORER_INITIALIZE_DATABASE_SESSION_EARLY` | `false` | Create DB session before first write |

Constructor also accepts instance-level options (not constants): `cookie_only` (bool, default `false`) and `disable_check_cookie` (bool, default `false`).

In tests, `CURRENT_TIME` constant is used to control time (instead of `time()`), allowing time-travel in tests.

### Dependencies

The library depends on ATK14 packages: `atk14/http` (HTTPRequest, HTTPResponse, HTTPCookie), `atk14/dbmole` (PgMole/OracleMole/MySQLMole/SqlSrvMole), `atk14/string4` (String4::RandomString), `atk14/packer` (Packer::Pack/Unpack).

In ATK14 apps, `$GLOBALS["HTTP_REQUEST"]` and `$GLOBALS["HTTP_RESPONSE"]` are auto-detected; a `$dbmole` global or PgMole/OracleMole singletons are used for database access. The `_writeDataToDatabase()` method has database-specific branches for PostgreSQL (upsert via `ON CONFLICT`) and Oracle (sequences, CLOBs); MySQL and MS SQL Server use the generic dbmole path.
