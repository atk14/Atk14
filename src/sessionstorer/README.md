SessionStorer
=============

A library for storing sessions in a database, used in the [ATK14 Framework](https://github.com/atk14).

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

Available options:

| Option         | Default | Description |
|----------------|---------|-------------|
| `dbmole`       | auto    | dbmole instance; auto-detected from globals or singleton if not provided |
| `current_time` | `time()` | Unix timestamp to use as "now" |
| `session_name` | `null`  | if set together with `max_lifetime`, deletes expired sessions by name |
| `max_lifetime` | `null`  | session lifetime in seconds |
| `deep_clean`   | `true`  | if true, also deletes all sessions older than 2 years |

The method returns the total count of deleted records.

License
-------

MIT
