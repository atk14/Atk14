String4
=======

[![Tests](https://github.com/atk14/String4/actions/workflows/tests.yml/badge.svg)](https://github.com/atk14/String4/actions/workflows/tests.yml)

String4 is a PHP class for comfortable, chainable string manipulation with full UTF-8 support. It wraps a plain PHP string in an object and exposes a rich set of methods — case conversion, slugification, truncation, HTML stripping, pluralization, and more — all designed to be chained fluently.

```php
echo String4::ToObject("CookieConsentsController")
    ->gsub('/Controller$/', '')
    ->singularize()
    ->underscore(); // "cookie_consent"
```

- [Installation](#installation)
- [Instantiation](#instantiation)
- [Method reference](#method-reference)
  - [Basics](#basics)
  - [Case](#case)
  - [Search & replace](#search--replace)
  - [Substrings & slicing](#substrings--slicing)
  - [Splitting & characters](#splitting--characters)
  - [Whitespace](#whitespace)
  - [HTML](#html)
  - [Conversion & formatting](#conversion--formatting)
  - [Line operations](#line-operations)
  - [Random generation](#random-generation)
  - [Encoding](#encoding)
  - [Utilities](#utilities)
- [Testing](#testing)
- [Licence](#licence)


Installation
------------

```bash
composer require atk14/string4
```


Instantiation
-------------

**Constructor**

```php
$s = new String4("Hello World");
$s = new String4("Héllo", "UTF-8");    // explicit encoding
$s = new String4($anotherString4);     // copy from another instance
```

**`String4::ToObject($string, $encoding = null)`** — static factory; returns a copy if the argument is already a `String4` instance, otherwise wraps it.

```php
$s = String4::ToObject("Hello");
$s = String4::ToObject($s);            // returns a copy, safe to pass around
$s = String4::ToObject("Héllo", "ISO-8859-2");
```


Method reference
----------------

Most methods return a **new `String4` instance** and leave the original unchanged, so calls can be chained freely.

---

### Basics

#### `length()` → `int`

Returns the number of characters (not bytes) in the string. Multibyte-safe.

```php
(new String4("Héllo"))->length(); // 5
(new String4(""))->length();      // 0
```

---

#### `toString()` → `string`

Returns the underlying plain PHP string. Calling `echo` or interpolating `"$s"` works too thanks to `__toString()`.

```php
$s = new String4("Hello");
$s->toString(); // "Hello"
echo $s;        // "Hello"
echo "$s";      // "Hello"
```

---

#### `copy()` → `String4`

Returns an independent copy of the object.

```php
$a = new String4("Hello");
$b = $a->copy();
```

---

#### `getEncoding($normalize = false)` → `string`

Returns the encoding the string was constructed with. Pass `true` to get a normalized lowercase form (e.g. `"utf8"` instead of `"UTF-8"`).

```php
$s = new String4("Hello", "UTF-8");
$s->getEncoding();      // "UTF-8"
$s->getEncoding(true);  // "utf8"
```

---

### Case

#### `upcase()` / `upper()` → `String4`

Converts all characters to uppercase. Both names are equivalent aliases.

```php
(new String4("hello"))->upcase();  // "HELLO"
(new String4("héllo"))->upper();   // "HÉLLO"
```

---

#### `downcase()` / `lower()` → `String4`

Converts all characters to lowercase.

```php
(new String4("HÉLLO"))->lower();   // "héllo"
```

---

#### `isUpper()` → `bool`

Returns `true` when all characters are uppercase. Returns `false` for an empty string.

```php
(new String4("HELLO"))->isUpper();  // true
(new String4("Hello"))->isUpper();  // false
(new String4(""))->isUpper();       // false
```

---

#### `isLower()` → `bool`

Returns `true` when all characters are lowercase. Returns `false` for an empty string.

```php
(new String4("hello"))->isLower();  // true
(new String4("Hello"))->isLower();  // false
```

---

#### `capitalize()` → `String4`

Uppercases the first character, leaves the rest unchanged.

```php
(new String4("hello world"))->capitalize();  // "Hello world"
```

---

#### `uncapitalize()` → `String4`

Lowercases the first character, leaves the rest unchanged.

```php
(new String4("Hello World"))->uncapitalize();  // "hello World"
```

---

#### `camelize($options = [])` → `String4`

Converts an underscored string to CamelCase. By default the first letter is uppercased; pass `["lower" => true]` for lowerCamelCase.

```php
(new String4("hello_world"))->camelize();                   // "HelloWorld"
(new String4("hello_world"))->camelize(["lower" => true]);  // "helloWorld"
(new String4("some_xml_parser"))->camelize();               // "SomeXmlParser"
```

---

#### `underscore()` → `String4`

Converts a CamelCase string to `snake_case`.

```php
(new String4("HelloWorld"))->underscore();    // "hello_world"
(new String4("BlogPost"))->underscore();      // "blog_post"
```

---

#### `titleize($options = [])` → `String4`

Capitalizes every word and replaces underscores and hyphens with spaces. Strips a trailing `_id` suffix by default; pass `["keep_id_suffix" => true]` to keep it.

```php
(new String4("x-men: the last stand"))->titleize();  // "X Men: The Last Stand"
(new String4("author_id"))->titleize();               // "Author"
(new String4("author_id"))->titleize(["keep_id_suffix" => true]); // "Author Id"
```

---

### Search & replace

#### `contains($needle)` → `bool`

Returns `true` if the string contains the given substring. When an array is passed, all elements must be present.

```php
$s = new String4("Hello World");
$s->contains("Hello");             // true
$s->contains("hello");             // false  (case-sensitive)
$s->contains(["Hello", "World"]);  // true   (all must match)
$s->contains(["Hello", "Nope"]);   // false
$s->contains([]);                  // false
```

---

#### `containsOneOf(...$needles)` → `bool`

Returns `true` if the string contains at least one of the given substrings. Accepts individual arguments or a single array.

```php
$s = new String4("Hello World");
$s->containsOneOf("Hi", "Hello", "Hey");   // true
$s->containsOneOf(["Hi", "Ciao", "Hey"]);  // false
```

---

#### `match($pattern, &$matches = null)` → `int|false`

Wrapper around `preg_match()`. Match groups are returned in `$matches` as `String4` instances.

```php
$s = new String4("2024-03-26");
$count = $s->match('/(\d{4})-(\d{2})-(\d{2})/', $matches);
// $count   = 1
// $matches[1] is String4("2024")
// $matches[2] is String4("03")
// $matches[3] is String4("26")
```

---

#### `replace($search, $replace = null)` → `String4`

Replaces occurrences of `$search` with `$replace`. When `$search` is an associative array, each key is replaced with its corresponding value.

```php
$s = new String4("Hello World");

$s->replace("World", "PHP");
// "Hello PHP"

$s->replace([
    "Hello" => "Hi",
    "World" => "PHP",
]);
// "Hi PHP"
```

---

#### `gsub($pattern, $replaceOrCallable)` → `String4`

Replaces all matches of a regular expression with a string or the return value of a callback. Wrapper around `preg_replace()` / `preg_replace_callback()`.

```php
$s = new String4("Hello World");

$s->gsub('/[aeiou]/', '*');
// "H*ll* W*rld"

$s->gsub('/\b\w/', function($m) {
    return strtoupper($m[0]);
});
// "Hello World"  (capitalize first letter of each word)
```

---

#### `prepend($content)` → `String4`

Inserts a string at the beginning.

```php
(new String4("World"))->prepend("Hello ");  // "Hello World"
```

---

#### `append($content)` → `String4`

Appends a string to the end.

```php
(new String4("Hello"))->append(" World");  // "Hello World"
```

---

### Substrings & slicing

#### `substr($start, $length = null)` → `String4`

Returns a substring. Multibyte-safe. Negative `$start` counts from the end.

```php
$s = new String4("Lorem Ipsum");
$s->substr(0, 5);  // "Lorem"
$s->substr(6);     // "Ipsum"
$s->substr(-5);    // "Ipsum"
```

---

#### `first($limit = 1)` → `String4`

Returns the first `$limit` characters.

```php
(new String4("Hello"))->first();    // "H"
(new String4("Hello"))->first(3);   // "Hel"
```

---

#### `at($position)` → `String4`

Returns the character at the given zero-based position.

```php
(new String4("Hello"))->at(0);   // "H"
(new String4("Hello"))->at(1);   // "e"
(new String4("Hello"))->at(-1);  // "o"
```

---

#### `truncate($length, $options = [])` → `String4`

Shortens the string to `$length` characters. By default appends `"..."` to indicate truncation.

Options:
- **`omission`** — string appended when truncated (default: `"..."`)
- **`separator`** — if set, the string is truncated at the last occurrence of this character before the cutoff, avoiding mid-word cuts

```php
$s = new String4("Hello World, how are you?");

$s->truncate(14);
// "Hello World..."

$s->truncate(14, ["omission" => " →"]);
// "Hello World →"

$s->truncate(14, ["omission" => "", "separator" => " "]);
// "Hello World,"
```

---

### Splitting & characters

#### `chars($options = [])` → `array`

Returns an array of individual characters as `String4` instances. Pass `["stringify" => true]` to get plain PHP strings instead.

```php
(new String4("Hi!"))->chars();
// [String4("H"), String4("i"), String4("!")]

(new String4("Hi!"))->chars(["stringify" => true]);
// ["H", "i", "!"]
```

---

#### `split($separator, $options = [])` → `array`

Splits the string by a plain-string separator and returns an array of `String4` instances. Pass `["stringify" => true]` for plain strings.

```php
$s = new String4("one two three");
$s->split(" ");
// [String4("one"), String4("two"), String4("three")]
```

---

#### `pregSplit($separator, $options = [])` → `array`

Like `split()` but treats the separator as a regular expression. Shorthand for `split($separator, ["preg_split" => true])`.

```php
$s = new String4("one  two   three");
$s->pregSplit('/\s+/');
// [String4("one"), String4("two"), String4("three")]
```

---

### Whitespace

#### `trim($removeHiddenCharacters = false)` → `String4`

Strips whitespace from both ends of the string. For UTF-8 strings this covers all Unicode space characters (NBSP, Em Space, Thin Space, Ideographic Space, etc.), not just ASCII whitespace.

Pass `true` to also strip invisible/control characters (zero-width spaces, BOM, directional marks, soft hyphens, etc.).

```php
$s = new String4("  Hello World  ");
$s->trim();       // "Hello World"
$s->trim(true);   // also removes zero-width spaces, BOM, etc.
```

---

#### `squish()` → `String4`

Trims the string (including invisible characters) and collapses all internal whitespace sequences to a single space.

```php
(new String4("  Hello   World  "))->squish();  // "Hello World"
```

---

### HTML

#### `stripTags()` → `String4`

Removes all HTML tags (thin wrapper around PHP's `strip_tags()`).

```php
(new String4("<b>Hello</b> <i>World</i>"))->stripTags();  // "Hello World"
```

---

#### `stripHtml()` → `String4`

A smarter HTML-to-text converter. Compared to `stripTags()` it:

- removes block-level tags (`<p>`, `<div>`, `<br>`, headings, etc.) and inserts spaces so words don't run together
- removes entire content of `<script>`, `<style>`, `<head>`, `<noscript>`, and similar non-visible tags
- decodes HTML entities (`&amp;` → `&`, `&nbsp;` → ` `, etc.)
- normalizes whitespace in the result

```php
$html = "<h1>Welcome at our <em>web</em><small>site</small>!</h1>"
      . "<p>We are here to help you.<br>Write us.</p>";

(new String4($html))->stripHtml();
// "Welcome at our website! We are here to help you. Write us."
```

---

### Conversion & formatting

#### `toAscii()` → `String4`

Transliterates the string to ASCII, replacing accented and special characters with their closest ASCII equivalents.

```php
(new String4("Héllo Wörld"))->toAscii();
// "Hello World"

(new String4("příliš žluťoučký kůň"))->toAscii();
// "prilis zlutoucky kun"
```

---

#### `toSlug($options = [])` → `String4`

Converts the string to a URL-friendly slug: transliterates to ASCII, lowercases, replaces non-alphanumeric characters with hyphens, and strips leading/trailing hyphens.

An integer can be passed directly as `$options` to set `max_length`.

Options:
- **`max_length`** — maximum length of the resulting slug
- **`suffix`** — string appended to the slug (also slugified itself), useful for appending IDs

```php
$s = new String4("Amazing facts about foxes!");

$s->toSlug();                                            // "amazing-facts-about-foxes"
$s->toSlug(10);                                          // "amazing-fa"
$s->toSlug(["max_length" => 10]);                        // "amazing-fa"
$s->toSlug(["suffix" => "123"]);                         // "amazing-facts-about-foxes-123"
$s->toSlug(["max_length" => 20, "suffix" => "123"]);     // "amazing-facts-abc-123"
```

---

#### `toBoolean()` → `bool`

Converts the string to a boolean. Returns `false` for an empty string and for the values `"false"`, `"off"`, `"no"`, `"n"`, `"f"` (all case-insensitive). Returns `true` for everything else.

```php
String4::ToObject("yes")->toBoolean();    // true
String4::ToObject("TRUE")->toBoolean();   // true
String4::ToObject("1")->toBoolean();      // true
String4::ToObject("on")->toBoolean();     // true

String4::ToObject("no")->toBoolean();     // false
String4::ToObject("false")->toBoolean();  // false
String4::ToObject("off")->toBoolean();    // false
String4::ToObject("0")->toBoolean();      // false
String4::ToObject("")->toBoolean();       // false
```

---

#### `pluralize()` → `String4`

Returns the plural form of the last word in the string (English only).

```php
(new String4("apple"))->pluralize();      // "apples"
(new String4("Sad man"))->pluralize();    // "Sad men"
```

---

#### `singularize()` → `String4`

Returns the singular form of the last word in the string (English only).

```php
(new String4("apples"))->singularize();        // "apple"
(new String4("Happy people"))->singularize();  // "Happy person"
```

---

#### `tableize()` → `String4`

Converts a CamelCase class name to the corresponding database table name: underscores and pluralizes. Handy for ORM-style conventions.

```php
(new String4("Book"))->tableize();           // "books"
(new String4("BlogPost"))->tableize();       // "blog_posts"
(new String4("CookieConsent"))->tableize();  // "cookie_consents"
```

---

### Line operations

#### `removeEmptyLines($options = [])` → `String4`

Removes empty lines from the string. Handles all common line endings (`\n`, `\r\n`, `\r`).

Options:
- **`max_empty_lines`** — how many consecutive empty lines to allow (default: `0` — all removed)
- **`trim_empty_lines`** — whether to trim whitespace-only lines before deciding if they are empty (default: `true`)

```php
$s = new String4("line one\n\n\nline two\n\nline three");

$s->removeEmptyLines();
// "line one\nline two\nline three"

$s->removeEmptyLines(["max_empty_lines" => 1]);
// "line one\n\nline two\n\nline three"
```

---

#### `eachLineMap($callback)` → `String4`

Applies a callback to every line and returns a new string with the mapped lines. Line endings are preserved.

```php
// Trim every line
$result = $s->eachLineMap(function($line) {
    return $line->trim();
});

// Add a quote prefix to every line
$result = $s->eachLineMap(function($line) {
    return $line->prepend("> ");
});
```

---

#### `eachLineFilter($callback = null)` → `String4`

Keeps only the lines for which the callback returns `true`. When no callback is provided, empty lines are filtered out.

```php
// Remove empty lines
$result = $s->eachLineFilter();

// Keep only lines that start with "#"
$result = $s->eachLineFilter(function($line) {
    return $line->match('/^#/');
});
```

---

### Random generation

#### `String4::RandomString($length = 32, $options = [])` → `String4`

Generates a random alphanumeric string (`[A-Za-z0-9]`). Uses `random_int()` for cryptographically secure output, suitable for tokens and API keys.

`$length` can also be passed as part of `$options`.

Options:
- **`length`** — length of the generated string (default: `32`)
- **`extra_chars`** — additional characters to draw from

```php
echo String4::RandomString();     // 32-character random string
echo String4::RandomString(16);   // 16-character random string

echo String4::RandomString([
    "length"      => 20,
    "extra_chars" => "#!&@",
]);
// e.g. "@vIxpVo!qD4A#n5Rb2E&"
```

---

#### `String4::RandomPassword($length = 10)` → `String4`

Generates a human-friendly random password. Visually ambiguous characters (`0`, `O`, `1`, `l`, `I`) are excluded to reduce transcription errors. The result mixes letters and digits in a pronounceable pattern.

Also works well as a voucher or coupon code generator.

```php
echo String4::RandomPassword();      // e.g. "68ynedeSA6"
echo String4::RandomPassword(12);    // e.g. "68ynedeSA634"
echo String4::RandomPassword(10)->upper(); // e.g. "EVUH923244"
```

---

### Encoding

#### `fixEncoding($options = [])` → `String4`

Replaces invalid UTF-8 byte sequences with a replacement character. Only has effect on UTF-8 strings. Accepts a string argument directly as a shorthand for `["replacement" => ...]`.

The default replacement is `U+FFFD` (the standard Unicode replacement character `▒`).

```php
$s->fixEncoding();
// invalid bytes replaced with "▒"

$s->fixEncoding("?");
// invalid bytes replaced with "?"

$s->fixEncoding(["replacement" => "_"]);
// invalid bytes replaced with "_"
```

---

### Utilities

#### `getId()` → `string`

Returns the string value. Exists for compatibility with the ATK14 framework, which sometimes calls `getId()` on objects to obtain their scalar representation.


Method chaining
---------------

All transformation methods return a new `String4` instance, so calls can be chained in any order:

```php
echo String4::ToObject("  Hello World!  ")
    ->trim()
    ->lower()
    ->replace("!", "")
    ->toSlug();
// "hello-world"

echo String4::ToObject("CookieConsentsController")
    ->gsub('/Controller$/', '')
    ->singularize()
    ->underscore();
// "cookie_consent"

echo String4::ToObject("  lots  of   whitespace  \n\n\n and   newlines  ")
    ->squish();
// "lots of whitespace and newlines"
```

Testing
-------

String4 is tested automatically via GitHub Actions across PHP 5.6 to PHP 8.5.

Tests use the [atk14/tester](https://packagist.org/packages/atk14/tester) wrapper for [phpunit/phpunit](https://packagist.org/packages/phpunit/phpunit).

Install development dependencies:

```bash
composer update --dev
```

Run the test suite:

```bash
cd test
../vendor/bin/run_unit_tests
```

Licence
-------

String4 is free software distributed [under the terms of the MIT license](http://www.opensource.org/licenses/mit-license).

[//]: # ( vim: set ts=2 et: )
