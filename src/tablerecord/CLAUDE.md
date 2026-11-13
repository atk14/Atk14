# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

`atk14/table-record` is a small ORM library mapping database rows onto PHP objects, built for (and used throughout) the ATK14 framework but usable standalone. It depends on `atk14/dbmole` for the actual DB connection/querying layer — `TableRecord` wraps `DbMole` instances, it doesn't replace them.

## Commands

Install dependencies (including dev):

    composer update --dev

Run the full test suite (PHPUnit via ATK14's tester, config in `phpunit.xml.dist`):

    cd test
    ../vendor/bin/run_unit_tests

Run a single test file:

    cd test
    ../vendor/bin/run_unit_tests tc_finder.php

Tests require a running **PostgreSQL** database (primary target) and a **MySQL** database (secondary accessor coverage), both with a `test`/`test` user/db. See `test/README` for exact setup commands, and `test/structures.postgresql.sql` / `test/structures.mysql.sql` for the schema the tests expect. `test/initialize.php` is the PHPUnit bootstrap: it connects via `PgMole`/`MysqlMole` (from `atk14/dbmole`), loads the Postgres structures, and sets up `$GLOBALS["dbmole"]` and `$GLOBALS["dbmole_alternative"]`.

CI (`.github/workflows/tests.yml`) runs the matrix across PHP 7.1–8.5 against Postgres 14 + MySQL 5.7 containers — mirror that setup when debugging cross-version issues.

## Architecture

### Core class: `TableRecord` (src/tablerecord.php)

Every model extends `TableRecord`. The constructor takes either a table name string or an options array (`table_name`, `id_field_name`, `id_field_type`, `sequence_name`, `do_not_read_values`, `dbmole`). Per-class metadata (table name, sequence name, id field, etc.) is computed once per class and cached in the static `self::$_Recipes[$class_name]` array — this is why the constructor checks `isset(self::$_Recipes[$this->_className])` before redoing any of that work.

- If no table name is given, it's derived from the class name via `String4::ToObject($class)->tableize()`.
- If no sequence name is given, it's guessed as `seq_<table>` (schema-qualified tables keep the schema prefix: `public.articles` → `public.seq_articles`).
- Each class gets its own `DbMole` instance, defaulting to `$GLOBALS["dbmole"]` unless overridden via the `dbmole` option — this is how a model can point at a different database (see `test/models/my_test_table.php`). **This global dependency is intentional for ATK14 integration — don't introduce DI here.**
- Table structure (column types) is read from the live DB via a `TableRecord_DatabaseAccessor_*` class and cached statically per class (`self::$_TableStructuresCache`), controlled by `TableRecord::$TableStructuresCacheDuration` / `TABLERECORD_CACHES_STRUCTURES`.

Record lookup/creation entry points: `GetInstanceById()`, `CreateNewRecord()`, `find()`, `getValue()`/`setValue()` (and short aliases `g()`/`s()`), `getValues()`/`setValues()`/`toArray()`.

Magic methods matter a lot here:
- `__call()` resolves `getFoo()` to `getValue("foo")` for any column, and resolves `getAuthor()` (no such column) to `Author::GetInstanceById($this->getValue("author_id"))` when an `author_id` column exists and an `Author`/`inobj_Author` class exists — i.e. belongs-to lookups work for free without declaring them.
- `__callStatic()` resolves `FindByTitle()`/`FindFirstByTitle()`/`FindAllByTitle()` to `FindFirst("title", ...)` / `FindAll("title", ...)`. Only single-column lookups work this way; `FindByXAndY()` is **not** supported (it's parsed as one literal column name).
- `getBelongsTo()` is the explicit (non-magic) version of the belongs-to lookup, with an `attribute_name`/`class_name` override.

### `TableRecord_Finder` (src/tablerecord_finder.php)

Represents a query (conditions/bind params/order/limit/offset) rather than a single record. Implements `ArrayAccess`, `Iterator`, `Countable`, so finders can be `foreach`'d or indexed directly. Obtained via `TableRecord::Finder()`/`FindAll()`/`FindFirst()`.

For large resultsets, `iterateRecords(["batch_size" => N])` returns a generator that reads in bounded batches instead of loading everything via `getRecords()`. This is independent of `TABLERECORD_MAX_NUMBER_OF_RECORDS_READ_AT_ONCE` (default 200), which caps the size of any individual SQL query regardless of batch size — `batch_size` only controls how many records stay resident/identity-mapped at once.

`TableRecord_EmptyFinder` (src/tablerecord_emptyfinder.php) is a null-object finder for the "no results possible" case (e.g. querying `WHERE id IN ()`).

### Caching (src/cache.php)

`Cache` / `ObjectCacher` implement a per-class, per-request identity map, separate from the table-structure cache above:

    Cache::Prepare("Book",[123,124,125]); // queue ids, no query yet
    Cache::Get("Book",123);                // resolves all queued ids in one query
    Cache::Unprepare("Book",123);          // dequeue without reading
    Cache::Clear("Book",123);              // evict a cached instance

`Cache::Unprepare()` only cancels queued-but-unread ids; it leaves already-resolved cached records alone (unlike `Clear()`, which evicts regardless of read state).

### `TableRecord_Lister` (src/tablerecord_lister.php)

Manages ordered M:N associations through a join table with an explicit `rank` column (not a general has-many — it's specifically for sortable associations, e.g. a book's authors in a chosen order). Obtained via `$record->getLister("Subjects")`. The join table/column names are derived from the owner and subject class names (`$book->getLister("Authors")` → `book_authors` table with `book_id`, `author_id`, `rank`), overridable via the `$options` passed to `getLister()`/`TableRecord_Lister::__construct()`. No code is needed on the subject model itself. Implements `ArrayAccess`, `Iterator`, `Countable` like the Finder.

### Database accessors (src/tablerecord_databaseaccessor_*.php, src/itablerecord_databaseaccessor.php)

`iTableRecord_DatabaseAccessor` defines the contract for reading table structure from a live database and translating native DB types to one of TableRecord's internal types (`float`, `integer`, `timestamp`, `boolean`, `string`). There's one implementation per supported database (`TableRecord_DatabaseAccessor_Postgresql`, `_Mysql`, `_Sqlsrv`), selected at runtime from `$this->dbmole->getDatabaseType()`. Adding support for a new database means adding a new accessor class implementing this interface, not touching `TableRecord` itself.

## Test model conventions (test/models/)

Test fixtures double as usage examples for less-common options: schema-qualified table names (`AuthorWithSchema` → `"public.authors"`), overriding `dbmole` to point at a second database/connection (`MyTestTable`, `TestTableAlternative`), skipping columns on read (`Article`'s `do_not_read_values`), and overriding `GetNextId()`.
