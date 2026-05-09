ATK14 Logger
============

[![Tests](https://github.com/atk14/Logger/actions/workflows/tests.yml/badge.svg?branch=master)](https://github.com/atk14/Logger/actions/workflows/tests.yml)

A lightweight logging library for PHP applications. Supports writing to files, stdout, and in-memory buffers, with configurable log levels and optional email notifications on errors.

Installation
------------

```bash
composer require atk14/logger
```

Basic usage
-----------

```php
define("LOGGER_DEFAULT_LOG_FILE", "/path/to/app/log/app.log");

$logger = new Logger("import_articles");
$logger->start();

$logger->info("Starting import");
$logger->info("Total articles to import: " . count($articles));

// ...

$logger->warn("Article #42 is missing a thumbnail");
$logger->error("Database connection lost");

// Writes buffered messages to the log file and sends an email notification
// if a message at or above the notify_level was logged.
$logger->stop();
```

Output format:

```
2024-05-15 14:23:00 import_articles[12345]: START
2024-05-15 14:23:01 import_articles[12345]: Starting import
2024-05-15 14:23:01 import_articles[12345]: Total articles to import: 100
2024-05-15 14:23:02 import_articles[12345]: WARN: Article #42 is missing a thumbnail
2024-05-15 14:23:03 import_articles[12345]: ERROR: Database connection lost
2024-05-15 14:23:03 import_articles[12345]: STOP, running time: 0 min 3.00 sec
```

Log levels
----------

| Method | Level | Int |
|---|---|---|
| `$logger->debug($msg)` | debug | -1 |
| `$logger->info($msg)` | info | 0 |
| `$logger->warn($msg)` | warn | 2 |
| `$logger->error($msg)` | error | 4 |
| `$logger->security($msg)` | security | 5 |

For a custom level use `put_log()`:

```php
$logger->put_log("Custom message", 3); // warn++
```

Output destinations
-------------------

By default, messages are written to a file. Multiple destinations can be combined:

```php
// File only (default)
$logger = new Logger("my_app");

// Stdout only
$logger = new Logger("my_app", ["log_to_stdout" => true]);

// Both file and stdout
$logger = new Logger("my_app", ["log_to_file" => true, "log_to_stdout" => true]);

// In-memory buffer (accessible via $logger->buffer)
$logger = new Logger("my_app", ["log_to_buffer" => true]);
$logger->info("Hello");
$logger->flush();
echo $logger->buffer; // "2024-05-15 14:23:01 my_app[12345]: Hello\n"

// Also output to stdout when running in a terminal
$logger = new Logger("my_app", ["automatically_log_to_stdout_on_terminal" => true]);
```

Email notifications
-------------------

When `flush_all()` (or `stop()`) is called and at least one message at or above `notify_level` was logged, an email with the full log is sent.

```php
define("LOGGER_DEFAULT_NOTIFY_EMAIL", "ops@example.com");

// Trigger email on warn (level 2) and above:
define("LOGGER_MIN_LEVEL_FOR_EMAIL_NOTIFICATION", 2);
```

Or configure per prefix (see [Prefix-based configuration](#prefix-based-configuration)).

Prefix-based configuration
---------------------------

Different scripts or tasks can be routed to separate log files with individual settings via the global `$LOGGER_CONFIGURATION` array. Wildcard patterns are supported.

```php
$LOGGER_CONFIGURATION = [
    // Exact match
    "cache_remover" => [
        "log_file"    => "/var/log/app/cache_remover.log",
        "notify_email" => "ops@example.com",
        "notify_level" => "error",   // send email on error and above
        "no_log_level" => "debug",   // suppress debug messages from output
    ],

    // Wildcard: matches "import_articles", "import_data", etc.
    "import_*" => [
        "log_file"    => "/var/log/app/import.log",
        "notify_email" => "imports@example.com",
        "notify_level" => "warn",
    ],
];
```

More specific keys take precedence over wildcards.

Constructor options
-------------------

```php
$logger = new Logger($prefix, $options);
```

| Option | Default | Description |
|---|---|---|
| `prefix` | `""` | Application mark shown in every log line |
| `default_log_file` | `LOGGER_DEFAULT_LOG_FILE` | Path to the log file |
| `log_to_file` | `true` (unless `log_to_stdout` is true) | Write to log file |
| `log_to_stdout` | `false` | Write to stdout |
| `log_to_buffer` | `false` | Write to `$logger->buffer` (StringBuffer) |
| `automatically_log_to_stdout_on_terminal` | `false` | Also write to stdout when running in a terminal |
| `disable_start_and_stop_marks` | `false` | Suppress START/STOP lines |
| `default_notify_email` | `LOGGER_DEFAULT_NOTIFY_EMAIL` | Recipient for email notifications |

Constants
---------

| Constant | Default | Description |
|---|---|---|
| `LOGGER_DEFAULT_LOG_FILE` | `/tmp/logger.log` | Default log file path |
| `LOGGER_DEFAULT_NOTIFY_EMAIL` | `""` | Default notification email (empty = disabled) |
| `LOGGER_MIN_LEVEL_FOR_EMAIL_NOTIFICATION` | `99` | Minimum level to trigger email (99 = disabled) |
| `LOGGER_NO_LOG_LEVEL` | `-99` | Messages at or below this level are suppressed |

License
-------

MIT
