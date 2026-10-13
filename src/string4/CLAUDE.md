# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

String4 is a small PHP library (single composer package `atk14/string4`) providing a `String4` class that wraps a plain PHP string in an immutable-style, chainable object, with full UTF-8 support (case conversion, slugification, truncation, HTML stripping, pluralization/singularization, splitting, etc.). Supports PHP 5.3+ through 8.5.

## Commands

Install dependencies:
```bash
composer update
```

Run the full test suite:
```bash
cd test && ../vendor/bin/run_unit_tests
```

Run a specific test case file (without the `tc_` prefix or `.php` extension works too):
```bash
cd test && ../vendor/bin/run_unit_tests tc_string4
```

Tests run automatically via GitHub Actions (`.github/workflows/tests.yml`) across PHP 5.6–8.5.

## Architecture

- `src/string4.php` — the entire `String4` class (~1300 lines, no namespace). All public methods generally return a **new `String4` instance** rather than mutating in place, so method calls are designed to be chained fluently (e.g. `String4::ToObject($x)->gsub(...)->singularize()->underscore()`).
  - `String4::ToObject($string, $encoding = null)` is the preferred static factory — it returns a copy if given an existing `String4` instance, otherwise wraps a plain string.
  - Internally stores the raw PHP string (`$_String4`) and its encoding (`$_Encoding`); most methods are mbstring-based wrappers made encoding-aware.
  - `_copy()` is the internal helper used by almost every method to produce the new instance returned from a transformation.
- `src/_inflect.php` — defines `_Inflect`, a standalone English inflection helper (pluralize/singularize/camelize/underscore/tableize rules) consumed internally by `String4`'s corresponding methods. Depends on `atk14/translate` (declared in `composer.json`).
- `composer.json` autoloads `src/` as a classmap (no PSR-4), so both classes are loaded by filename, not namespace.

## Testing framework specifics

- Tests use [`atk14/tester`](vendor/atk14/tester), a wrapper around PHPUnit that smooths over PHPUnit API differences across versions — test case classes extend `TcBase` (auto-provided) and use PHPUnit-style assertions.
- `test/tc_string4.php` is the single test case file covering the whole class, organized as one `test_<method_name>` method per `String4` method/behavior being tested.
- `test/initialize.php` is loaded automatically before tests run; it sets `DEFAULT_CHARSET` to UTF-8 and requires the Composer autoloader.
- `test/page_component.php` defines a minimal `PageComponent` stub used by `getId()`-related tests, mimicking how `String4` is used as an ID-like value in other atk14 ecosystem packages.

## Documentation

`README.md` contains the full method reference with examples for every public method — treat it as the source of truth for documented behavior/options when adding or modifying methods, and keep it in sync with code changes.
