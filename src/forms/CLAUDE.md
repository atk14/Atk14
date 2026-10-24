# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

`atk14/forms` is a PHP form framework (fields, widgets, validation, HTML rendering) ported from Django's old `newforms` API. It is the default form library of the [ATK14 Framework](https://www.atk14.net/) but has no hard dependency on it — only on `atk14/string4`, `atk14/http`, and `atk14/functions`. Supports PHP 7.1 through 8.5.

## Commands

Install dependencies (dev deps needed to run tests):

```
composer update --dev
```

Run the full test suite:

```
cd test && ../vendor/bin/run_unit_tests
```

Run a single test file by name (file or class, with or without `.php`):

```
cd test && ../vendor/bin/run_unit_tests tc_fields
cd test && ../vendor/bin/run_unit_tests tc_fields.php tc_widgets.php
```

Test runner is `atk14/tester`'s `run_unit_tests` — a PHPUnit-version-agnostic wrapper (see `vendor/atk14/tester/CLAUDE.md` for how it works internally). Test methods use ordinary `test_*` naming; there's no PHPUnit CLI `--filter` layer added on top, so to run a single test method either temporarily rename/isolate it or grep for the behavior across the relevant `tc_*.php` file.

CI (`.github/workflows/tests.yml`) runs this exact command across PHP 7.1–8.5 via a matrix with `composer update` (unpinned), so changes should not rely on a specific dependency version.

## Architecture

Three-layer model, mirroring Django's old forms API:

- **Widget** (`src/widgets.php`, `src/widgets/*.php`) — pure HTML rendering. Knows how to turn `(name, value, attrs)` into a tag (`<input>`, `<select>`, `<textarea>`, etc.) and how to pull a submitted value back out of a data dict (`value_from_datadict`). Has no notion of validity.
- **Field** (`src/fields.php`, `src/fields/*.php`) — validation and data cleaning. Each field type owns a default `$widget` and implements `clean($value): [error, cleaned_value]`. Fields don't render themselves directly; they delegate to their widget.
- **Form** (`src/forms.php`) — a named collection of fields (`Form::add_field($name, $field)`), plus the glue: binding submitted `$data`, running `full_clean()` over every field (and an optional `clean_<fieldname>()` / `clean()` hook on the form subclass), collecting `errors`/`cleaned_data`, and rendering (`as_table()`/`as_ul()`/`as_p()`). `BoundField` (also in `forms.php`) is the per-field view used during rendering — it pairs a `Field` with the form's submitted/initial data and exposes `as_widget()`, `label_tag()`, `errors()`, etc.

Loading order matters and is fixed in `src/load.php`: `constants.php` → `functions.php` → `widgets.php` (base `Widget` class) → `fields.php` (base `Field` class) → autoload everything under `widgets/` and `fields/` via `class_autoload()` → `forms.php` (depends on both base classes already being defined).

Adding a new field type means creating `src/fields/<name>_field.php` with a class extending `Field` (see `test/custom_fields/url_field.php` for a minimal example used only by tests), implementing at least `__construct()` (declare options/widget) and `clean()` (validation). Adding a new widget means a class in `src/widgets/` extending `Widget`, implementing `render()`.

**Bootstrap markup variants**: `FORMS_MARKUP_TUNED_FOR_BOOTSTRAP3/4/5` constants (set via `USING_BOOTSTRAP3/4/5` before `load.php` runs, see `src/constants.php`) switch widget-rendered CSS classes (e.g. `Select` emits `form-select` for Bootstrap5, `form-control` otherwise). When touching widget `render()` methods, check whether behavior needs to branch on these flags.

**Error/value contract**: `Field::clean()` and any `clean_<name>()`/`clean()` hook on a `Form` subclass return `array($error, $value)` where exactly one side is meaningful — `$error` is `null`/empty on success, otherwise a string or array of strings. This convention is used pervasively; don't change it to exceptions or similar without touching every field.

### Tests (`test/`)

- `initialize.php` sets up test constants (`TEST`, disables all Bootstrap markup flags by default) and loads `vendor/autoload.php` plus a test-only custom field (`custom_fields/url_field.php`).
- `tc_base.php` defines `TcBase` (extended by all test cases) with `assertHtmlEquals()` — HTML comparison is structural (via `XMole`, from `atk14/xmole`), not string-equal, so attribute order/whitespace differences in expected vs. actual HTML don't fail tests.
- Test files are split by subject: `tc_fields.php` (field validation/cleaning, large), `tc_widgets.php` (widget rendering), `tc_forms.php` (form-level binding/rendering/clean hooks, large), plus focused files per special field (`tc_email_field.php`, `tc_file_field.php`, `tc_image_field.php`) and `tc_form.php`.
