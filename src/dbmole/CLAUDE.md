# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

DbMole (`atk14/dbmole`) is a lightweight PHP database abstraction layer supporting PostgreSQL, MySQL, Oracle, and SQL Server through a unified interface. It is distributed as a Composer library.

## Commands

```bash
# Install dependencies (including dev)
composer update --dev

# Run tests (from project root)
cd test && ../vendor/bin/run_unit_tests

# Run tests with prepared statements (PostgreSQL only)
cd test && DBMOLE_USE_PREPARED_STATEMENTS=true ../vendor/bin/run_unit_tests
```

Test databases (PostgreSQL and MySQL) must be available at `localhost` with `user=test password=test database=test`.

## Architecture

### Class Hierarchy

`DbMole` (abstract base, `src/dbmole.php`) → concrete drivers:
- `PgMole` (`src/pgmole.php`) — PostgreSQL via `pg_*` functions
- `MysqlMole` (`src/mysqlmole.php`) — MySQL via `mysqli_*` functions
- `OracleMole` (`src/oraclemole.php`) — Oracle via `oci_*` functions
- `SqlsrvMole` (`src/sqlsrvmole.php`) — SQL Server via `sqlsrv_*` functions

`DbMoleException` (`src/dbmole_exception.php`) is a thin exception subclass.

### Instantiation Pattern

`DbMole::GetInstance($configuration_name)` returns a singleton per configuration name. The caller must define a global function `dbmole_connection($dbmole)` that returns the actual DB connection resource based on `$dbmole->getDatabaseType()` and `$dbmole->getConfigurationName()`.

### Query Parameter Binding

All query methods accept a `$bind_ar` array using named `:parameter` placeholders:

```php
$db->selectRows("SELECT * FROM articles WHERE id=:id", [":id" => 42]);
```

- Array values in bindings are automatically expanded to `(:p0,:p1,:p2,...)`.
- Objects with a `getId()` method are automatically dereferenced.
- Binding format is validated by default (`DBMOLE_CHECK_BIND_AR_FORMAT`).

### Key Method Groups in DbMole

| Group | Methods |
|-------|---------|
| Fetching | `selectRows()`, `selectRow()` / `selectFirstRow()`, `selectValue()` / `selectSingleValue()`, `selectInt()`, `selectFloat()`, `selectBool()`, `selectString()` |
| Collections | `selectIntoArray()`, `selectIntoAssociativeArray()`, `iterateRows()` (generator, memory-efficient) |
| Mutation | `doQuery()`, `insertIntoTable()`, `insertOrUpdateRecord()` |
| Transactions | `begin()`, `commit()`, `rollback()` |
| Escaping | `escapeString4Sql()`, `escapeColumnName4Sql()`, `escapeTableName4Sql()` |
| Sequences | `selectSequenceNextval()`, `selectSequenceCurrval()` (PostgreSQL/Oracle/SQL Server) |
| Diagnostics | `getErrorMessage()`, `getQuery()`, `getBindAr()`, `getStatistics()` |

### Configurable Constants (define before first use)

| Constant | Default | Effect |
|----------|---------|--------|
| `DBMOLE_USE_PREPARED_STATEMENTS` | `false` | PostgreSQL prepared statements (performance) |
| `DBMOLE_COLLECT_STATISTICS` | off | Enable query statistics via `getStatistics()` |
| `DBMOLE_CHECK_BIND_AR_FORMAT` | `true` | Validate `:name` format of bind keys |
| `DBMOLE_AUTOMATIC_DELAY_TRANSACTION_BEGINNING_AFTER_CONNECTION` | `true` | Defer `BEGIN` until first actual DML |
| `DBMOLE_DEFAULT_CACHE_EXPIRATION` | `600` | Cache TTL in seconds |
| `DBMOLE_ORACLE_TRUE` / `DBMOLE_ORACLE_FALSE` | `"Y"` / `"N"` | Oracle boolean representation |

### Error Handling

Register a global handler with `DbMole::RegisterErrorHandler($fn)` or per-instance with `$dbmole->setErrorHandler($fn)`. Handlers receive the `$dbmole` instance; call `getErrorMessage()`, `getQuery()`, `getBindAr()` inside.

### Test Layout

Tests live in `test/`. The test framework is `atk14/tester` (`vendor/bin/run_unit_tests`). `tc_dbmole.php` is the main test class; `tc_base.php` provides shared setup. SQL schemas for each DB engine are in `test/structures.<engine>.sql`.
