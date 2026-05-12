Translate
=========

[![Tests](https://github.com/atk14/Translate/actions/workflows/tests.yml/badge.svg?branch=master)](https://github.com/atk14/Translate/actions/workflows/tests.yml)

A PHP class for converting strings between character encodings, without relying on the `iconv` extension.

Installation
------------

```bash
composer require atk14/translate
```

Usage
-----

### Converting between encodings

```php
$iso = Translate::Trans($utf8_string, "UTF-8", "ISO-8859-2");
$utf8 = Translate::Trans($iso_string, "ISO-8859-2", "UTF-8");
$win  = Translate::Trans($utf8_string, "UTF-8", "Windows-1250");
```

If a conversion is not supported, the original string is returned unchanged.

### Converting arrays

When `$text` is an array, all string values are converted recursively. Non-string values (integers, nulls, ...) are left as-is.

```php
$data = ["name" => "Žofie", "age" => 42];
$converted = Translate::Trans($data, "UTF-8", "ISO-8859-2");
```

To also convert array keys, pass the `recode_array_keys` option:

```php
$converted = Translate::Trans($data, "UTF-8", "ISO-8859-2", ["recode_array_keys" => true]);
```

### Transliteration to ASCII

```php
echo Translate::Trans("Příliš žluťoučký kůň", "UTF-8", "ASCII");
// → "Prilis zlutoucky kun"
```

The `language` option resolves conflicts between transliteration rules for different languages. Default is Slovak (suitable for Czech and Slovak texts).

```php
// Slovak (default): ä → a
echo Translate::Trans("mäso", "UTF-8", "ASCII");
// → "maso"

// German: ä → ae
echo Translate::Trans("Jäger", "UTF-8", "ASCII", ["language" => "de"]);
// → "Jaeger"
```

Accepted language values: `"sk"`, `"sk_SK"`, `"de"`, `"de_DE"`, `"DE"`, `"en"`, etc.

### Case conversion

```php
echo Translate::Lower("PŘÍLIŠ", "UTF-8");        // → "příliš"
echo Translate::Upper("žluťoučký", "UTF-8");     // → "ŽLUŤOUČKÝ"

echo Translate::Lower("PŘÍLIŠ", "ISO-8859-2");   // works for 8-bit encodings too
echo Translate::Upper("žluťoučký", "Windows-1250");
```

### Checking encoding

Returns `true` if the string is valid in the given encoding, `false` otherwise.

```php
Translate::CheckEncoding($string, "UTF-8");   // → true / false
Translate::CheckEncoding($string, "ASCII");   // → true / false
```

Also works with arrays (checks all keys and values recursively):

```php
Translate::CheckEncoding(["key" => "value", ...], "UTF-8");
```

An optional third argument lists forbidden byte sequences:

```php
Translate::CheckEncoding($string, "UTF-8", ["\r\n", "<script"]);
```

### String length

Returns the number of characters (not bytes) for multi-byte encodings:

```php
$len = Translate::Length("Příliš", "UTF-8");   // → 6
$len = Translate::Length("Prilis", "ASCII");   // → 6
```

Supported encodings
-------------------

| Encoding | Aliases |
|---|---|
| UTF-8 | `utf-8` |
| UTF-16 | `utf-16` |
| ISO-8859-1 | `latin1`, `latin-1`, `lat1`, `il1`, `8859_1`, ... |
| ISO-8859-2 | `latin2`, `latin-2`, `lat2`, `il2`, `8859_2`, ... |
| Windows-1250 | `cp1250`, `win-1250`, `1250`, ... |
| Windows-1252 | `cp1252`, `win-1252`, `1252`, ... |
| CP852 | `cp852`, `ibm852`, `cp-852`, ... |
| ASCII | `us-ascii`, `usascii` |
| HTML entities | `html entities` |
| KOI8 | `koi-8`, `cskoi8r` |
| Legacy | `kam`, `mac`, `macce`, `pc2`, `pc2a`, `vga` |

Charset names are case-insensitive.

Using iconv
-----------

By default, the class uses its own conversion tables. If you are confident that `iconv` works correctly on your system (verify by running the test suite), you can enable it for better performance:

```php
define("TRANSLATE_USE_ICONV", true);
```

Testing
-------

Translate is tested automatically via GitHub Actions across PHP 5.6 to PHP 8.5.

Tests use the [atk14/tester](https://packagist.org/packages/atk14/tester) wrapper for [phpunit/phpunit](https://packagist.org/packages/phpunit/phpunit).

Install development dependencies:

```bash
composer update --dev
```

Run the test suite:

```bash
./vendor/bin/run_unit_tests test
```

License
-------

Translate is free software distributed [under the terms of the MIT license](http://www.opensource.org/licenses/mit-license).
