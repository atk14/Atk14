XMole
=====

[![Tests](https://github.com/atk14/XMole/actions/workflows/tests.yml/badge.svg?branch=master)](https://github.com/atk14/XMole/actions/workflows/tests.yml)

XMole is a simple PHP XML parser that turns XML into a traversable object tree.
It wraps PHP's built-in expat parser and adds a convenient path-based API for
reading element data, attributes, and subtrees.

Installation
------------

```bash
composer require atk14/xmole
```

Basic usage
-----------

Pass XML directly to the constructor, or call `parse()` separately:

```php
$xm = new XMole('
  <order id="42" status="pending">
    <customer>John Doe</customer>
    <item sku="ABC">Widget</item>
    <item sku="XYZ">Gadget</item>
  </order>
');

$xm->get_data("customer");            // "John Doe"
$xm->get_attribute("order", "id");    // "42"
$xm->get_root_name();                 // "order"
```

Path syntax
-----------

Paths work similarly to XPath but use a simpler `/`-separated format.

```php
// Absolute path — must match from the root
$xm->get_data("/order/customer");

// Relative path — matches anywhere in the tree
$xm->get_data("customer");

// Root element shorthand
$xm->get_data("/");           // data of the root element
$xm->get_attributes();        // attributes of the root element
$xm->get_attribute("id");     // attribute of the root element (path can be omitted)
```

Trailing slashes are ignored: `"order/"` and `"order"` are equivalent.

Reading data and attributes
---------------------------

```php
// Element text content
$xm->get_data("customer");                    // "John Doe"

// Single attribute
$xm->get_attribute("order", "status");        // "pending"
$xm->get_attribute("status");                 // same — root element attribute shorthand

// All attributes as an associative array
$xm->get_attributes("order");                 // ["id" => "42", "status" => "pending"]
$xm->get_root_attributes();                   // same for the root element

// Returns null when the element or attribute is not found
$xm->get_data("nonexistent");                 // null
$xm->get_attribute("order", "nonexistent");   // null
```

Whitespace at the beginning and end of element data is trimmed by default.
To disable trimming:

```php
$xm = new XMole($xml, ["trim_data" => false]);
// or
$xm->set_trim_data(false);
```

Navigating subtrees
-------------------

Use `get_xmole()` to get a subtree as a new XMole instance:

```php
$item = $xm->get_xmole("item");       // first matching <item>
$item->get_data();                    // "Widget"  (root element data)
$item->get_attribute("sku");          // "ABC"
```

Use `get_xmoles()` to get all matching elements:

```php
$items = $xm->get_xmoles("item");     // array of XMole instances

$items[0]->get_data();                // "Widget"
$items[1]->get_data();                // "Gadget"
```

Iterating over children
-----------------------

```php
// Access children by index
$first  = $xm->get_child(0);
$second = $xm->get_child(1);

// Get all direct children at once
$children = $xm->get_children();
foreach ($children as $child) {
    echo $child->get_root_name() . ": " . $child->get_data() . "\n";
}

// Iterate sequentially with get_next_child()
while ($child = $xm->get_next_child()) {
    echo $child->get_attribute("sku") . "\n";
}

// Reset the internal pointer to start over
$xm->reset_next_child_index();
```

Error handling
--------------

`parse()` returns `false` on failure. Error details are available via
`get_error_message()` or the optional reference parameters:

```php
$xm = new XMole();
if (!$xm->parse($xml, $err_code, $err_message)) {
    echo $err_message;  // e.g. "XML parser error (76): Mismatched tag on line 3"
}
```

`error()` returns `true` if the last parse failed:

```php
$xm = new XMole($possibly_invalid_xml);
if ($xm->error()) {
    echo $xm->get_error_message();
}
```

Encoding conversion
-------------------

XMole can transparently convert character encoding while parsing,
using the [atk14/translate](https://github.com/atk14/Translate) library.
Input encoding is auto-detected from the XML declaration if not set explicitly.

```php
$xm = new XMole();
$xm->set_input_encoding("UTF-8");
$xm->set_output_encoding("WINDOWS-1250");
$xm->parse($xml);
```

Set both at once with `set_encoding()`:

```php
$xm->set_encoding("ISO-8859-2");
```

Comparing XML
-------------

`is_same_like()` and the static `AreSame()` compare two XML documents
structurally. Attribute order is ignored.

```php
$xml1 = '<item id="1" name="foo" />';
$xml2 = '<item name="foo" id="1" />';   // same attributes, different order

XMole::AreSame($xml1, $xml2);           // true

$xm = new XMole($xml1);
$xm->is_same_like($xml2);               // true
$xm->is_same_like('<item id="2" />');   // false

// Returns null if either document is invalid
XMole::AreSame($xml1, '<bad>');         // null
```

Building XML safely
-------------------

Two static helpers encode values for safe embedding in XML:

```php
// For text content
$xml = "<description>" . XMole::ToXML($user_input) . "</description>";

// For attribute values
$xml = '<item name="' . XMole::ToAttribsValue($name) . '" />';
```

`ToXML()` encodes `& < > " '` and strips control characters invalid in XML 1.0.
`ToAttribsValue()` encodes the same characters and also converts newlines to spaces.

Testing
-------

```bash
composer install
cd test && ../vendor/bin/run_unit_tests
```

License
-------

XMole is free software distributed [under the terms of the MIT license](http://www.opensource.org/licenses/mit-license).

[//]: # ( vim: set ts=2 et: )
