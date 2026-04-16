StringBuffer
============

[![Tests](https://github.com/atk14/StringBuffer/actions/workflows/tests.yml/badge.svg)](https://github.com/atk14/StringBuffer/actions/workflows/tests.yml)

StringBuffer is a PHP class providing operations for efficient string buffering.
It can hold a mix of plain strings and file contents, and treats them uniformly
as a single continuous buffer.

Installation
------------

    composer require atk14/string-buffer

Basic usage
-----------

    $sb = new StringBuffer();
    $sb->addString("Hello World!\n");
    $sb->addString(" How are you?");
    $sb->addFile("/path/to/file");

    $length = $sb->getLength();
    $sb->printOut();

You can also pass an initial string to the constructor:

    $sb = new StringBuffer("Hello World!");

Combining buffers:

    $sb1 = new StringBuffer("Hello");
    $sb2 = new StringBuffer(" World!");
    $sb1->addStringBuffer($sb2);
    echo $sb1; // "Hello World!"

Converting to string:

    $string = (string)$sb;
    // or
    $string = "$sb";
    // or
    $string = $sb->toString();

Other operations:

    // Replace a substring throughout the buffer
    $sb->replace("World", "PHP");

    // Extract a portion of the buffer (works like PHP's substr())
    $part = $sb->substr(0, 5);
    $last = $sb->substr(-3);

    // Write the whole buffer to a file
    $sb->writeToFile("/path/to/output.dat");

    // Clear the buffer
    $sb->clear();

Memory-efficient temporary buffer
----------------------------------

`StringBufferTemporary` is a drop-in replacement for `StringBuffer` that
automatically offloads content to a temporary file once it exceeds 1 MB.
This keeps memory consumption low when working with large amounts of data.

    $buffer = new StringBufferTemporary();

    $buffer->add($megabyte);
    $buffer->add($megabyte);
    $buffer->add($megabyte);

    $buffer->printOut();
    // or
    $buffer->writeToFile($target_filename);

Temporary files are created automatically and deleted when the buffer object
is destroyed.

The threshold can be adjusted if needed:

    StringBufferTemporary::$FILEIZE_THRESHOLD = 512 * 1024; // 512 kB

The temporary directory defaults to the system temp dir. To override it,
define the `TEMP` constant before using the class:

    define("TEMP", "/path/to/temp/");

Append buffer contents to the file
----------------------------------

The file opening mode can be passed as the second parameter of the `writeToFile()` method.

    // Write at the end of the file
    $buffer->writeToFile("/path/to/output.dat","a"); 

Licence
-------

StringBuffer is free software distributed [under the terms of the MIT license](http://www.opensource.org/licenses/mit-license)

[//]: # ( vim: set ts=2 et: )
