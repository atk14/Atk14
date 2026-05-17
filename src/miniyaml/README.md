MiniYAML
========

[![Tests](https://github.com/atk14/MiniYaml/actions/workflows/tests.yml/badge.svg)](https://github.com/atk14/MiniYaml/actions/workflows/tests.yml)

MiniYAML is a minimalistic YAML loader and dumper for PHP. It handles the subset of YAML commonly used for configuration files and API responses.

**Why MiniYAML?**

- **Single file, zero dependencies.** Drop `miniyaml.php` into your project and you are done — no Composer, no autoloader, no transitive dependencies.
- **Tiny footprint.** The entire implementation is ~350 lines of straightforward PHP.
- **PHP template evaluation.** The built-in `InterpretPHP()` method lets you embed `<?= $var ?>` tags directly in YAML, making it easy to build environment-specific configuration without a separate templating step. No other mainstream YAML library offers this.
- **Readable API.** Three static methods — `Load()`, `Dump()`, `InterpretPHP()` — cover all common use cases without configuration overhead.
- **Runs on PHP 5.6 and up.** Works on legacy hosting and old codebases where modern libraries have already dropped support.

Installation
------------

    composer require atk14/mini-yaml

Usage
-----

### Load

```php
$ar = miniYAML::Load($yaml_string);
```

Options:

| Option | Type | Default | Description |
|---|---|---|---|
| `nullable` | bool | `true` | Treat `null` and `NULL` as PHP `null` |
| `interpret_php` | bool | `false` | Evaluate PHP tags (`<?php ... ?>`) embedded in the YAML string |
| `values` | array | `[]` | Variables made available when `interpret_php` is enabled |

```php
// Disable null handling — "null" is kept as a plain string
$ar = miniYAML::Load($yaml_string, ["nullable" => false]);

// Evaluate embedded PHP
$yaml = miniYAML::Load($template, [
    "interpret_php" => true,
    "values" => ["domain" => "example.com"],
]);
```

### Dump

```php
$yaml = miniYAML::Dump($array);
```

Options:

| Option | Type | Default | Description |
|---|---|---|---|
| `nullable` | bool | `true` | Dump PHP `null` as `NULL`; when `false`, dumps as empty string `""` |

```php
$yaml = miniYAML::Dump($array, ["nullable" => false]);
```

Supported YAML features
-----------------------

**Load and Dump:**
- Hash (associative) arrays: `key: value`
- Indexed (list) arrays: `- item`
- Nested structures of arbitrary depth
- Empty arrays: `[]`
- Quoted strings: `"value"` and `'value'`
- `null` / `NULL` values (controlled by the `nullable` option)
- Comments: lines starting with `#`

**Load only:**
- Literal block scalars (`|`) — newlines preserved
- Folded block scalars (`>`) — newlines replaced with spaces

**Dump only:**
- Strings containing newlines are automatically serialized as literal block scalars (`|`)
- Strings requiring escaping (colons, special characters, YAML keywords, …) are wrapped in double quotes

Example
-------

```php
$yaml = '
---
status: success
message: Ok
data:
  domain: example.com
  admin:
  - Alice
  - Bob
  description: |
    First line.
    Second line.
';

$ar = miniYAML::Load($yaml);
// [
//   "status"  => "success",
//   "message" => "Ok",
//   "data"    => [
//     "domain"      => "example.com",
//     "admin"       => ["Alice", "Bob"],
//     "description" => "First line.\nSecond line.",
//   ]
// ]

echo miniYAML::Dump($ar);
```

Limitations
-----------

The following YAML features are **not** supported:

- Multiline plain scalars (without `|` or `>`)
- Anchors and aliases (`&`, `*`)
- Explicit type tags (`!!str`, `!!int`, …)
- Flow mappings and sequences (`{…}`, `[…]`) — except empty array `[]`
- Documents with a common base indentation on all lines

MiniYAML is well-suited for YAML you write and control yourself. If the input
comes from an external tool or another language, it may use syntax that MiniYAML
silently misparses or ignores.

Alternatives
------------

If MiniYAML does not cover your use case, consider these alternatives:

| Library | Notes |
|---|---|
| [symfony/yaml](https://github.com/symfony/yaml) | Pure PHP, covers most of YAML 1.2. The standard choice for full YAML support; works standalone without the Symfony framework. |
| [ext-yaml](https://pecl.php.net/package/yaml) | PHP extension wrapping libyaml (C). Fastest option, full YAML 1.1 compliance. Requires server-level installation. |
| [nette/neon](https://github.com/nette/neon) | Pure PHP, implements NEON — a YAML-like format with PHP-native types. Not YAML, but a comfortable alternative if you control both ends. |

Testing
-------

    composer update --dev
    ./vendor/bin/run_unit_tests test

License
-------

MiniYAML is free software distributed [under the terms of the MIT license](http://www.opensource.org/licenses/mit-license)

[//]: # ( vim: set ts=2 et: )
