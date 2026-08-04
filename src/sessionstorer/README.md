SessionStorer
=============

[![Tests](https://github.com/atk14/SessionStorer/actions/workflows/tests.yml/badge.svg)](https://github.com/atk14/SessionStorer/actions/workflows/tests.yml)

A library for storing sessions in a database or in cookies, used in the [ATK14 Framework](https://github.com/atk14).

Installation
------------

```bash
composer require atk14/session-storer
```

Basic usage
-----------

```php
$session = new SessionStorer();

$session->writeValue("user_id", 42);
$user_id = $session->readValue("user_id"); // 42
```

Available options
----------------

The constructor accepts an optional array of options:

```php
$session = new SessionStorer([
    "session_name" => "cart",
    "cookie_only"  => true,
]);
```

| Option                 | Default     | Description |
|------------------------|-------------|-------------|
| `session_name`         | `"session"` | Name used for the session cookie |
| `section`              | `"default"` | Logical scope within the session; different sections share the cookie but not keys |
| `cookie_only`          | `false`     | Store all values in cookies only, never in the database |
| `disable_check_cookie` | `false`     | Skip the check cookie that detects whether the client accepts cookies |
| `cookie_expiration`    | `0`         | Cookie lifetime in seconds (`0` = until browser is closed) |
| `max_lifetime`         | auto        | Session lifetime in seconds for garbage collection; derived from `cookie_expiration` if not set |

Cookie-only mode
----------------

When a database is not available or not desired, values can be stored exclusively in cookies by passing `cookie_only => true`. The data never touches the database.

```php
$session = new SessionStorer([
    "session_name" => "cart",
    "cookie_only" => true,
]);

$session->writeValue("step", 2);
$step = $session->readValue("step"); // 2
```

Values are packed into one or more numbered cookies (`cart0`, `cart1`, …) with a length prefix in the first cookie. The packed data is encrypted and signed via `Packer::Pack()`, making it resistant to tampering by the client. Expired values are automatically removed from cookies on the next read.

By default, SessionStorer sends a separate check cookie to detect whether the client has cookies enabled. This can be suppressed with `disable_check_cookie => true` — useful when you know cookies are supported because another cookie is already being set elsewhere:

```php
$session = new SessionStorer([
    "disable_check_cookie" => true,
]);
```

Sessions cleanup (cron job)
---------------------------

Old sessions can be cleaned up by calling the static method `DeleteOldSessions()`.
It is suitable for use in a cron job (e.g. `sessions_cleanup` running once a day):

```php
$deleted = SessionStorer::DeleteOldSessions([
    "session_name" => "session",
    "max_lifetime" => 60 * 60 * 24, // 1 day
]);
```

Available options for `DeleteOldSessions()`:

| Option         | Default  | Description |
|----------------|----------|-------------|
| `dbmole`       | auto     | dbmole instance; auto-detected from globals or singleton if not provided |
| `current_time` | `time()` | Unix timestamp to use as "now" |
| `session_name` | `null`   | if set together with `max_lifetime`, deletes expired sessions by name |
| `max_lifetime` | `null`   | session lifetime in seconds |
| `deep_clean`   | `true`   | if true, also deletes all sessions older than 2 years |

The method returns the total count of deleted records.

License
-------

MIT
