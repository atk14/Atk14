Files
=====

[![Tests](https://github.com/atk14/Files/actions/workflows/tests.yml/badge.svg?branch=master)](https://github.com/atk14/Files/actions/workflows/tests.yml)

A PHP class for basic file manipulation.

Basic usage
-----------

Files is just a bunch of static functions.

    $content = Files::GetFileContent("/path/to/a/file");

To recursively delete a directory

    $items_deleted = Files::RecursiveUnlinkDir("/path/to/a/dir");

To determine a file type

    $mime_type = Files::DetermineFileType("/path/to/a/file"); // "image/jpg"

To write a content to a temporary file

    $temp_filename = Files::WriteToTemp($some_content);

And so on.

Usage
-----

### Directory creation

    Files::Mkdir("/path/to/a/directory/"); // creates directory "/path/to/a/directory"
    Files::Mkdir("/path/to/another/directory"); // creates directory /path/to/another/directory

    Files::MkdirForFile("/path/to/a/file"); // creates directory "/path/to/a"

### Finding files

    $all_files_in_a_dir = Files::FindFiles("./dir"); // ["./dir/image.jpg", "./dir/image.png", "./dir/subdir/image2.jpg", "./dir/subdir/readme.txt"]
    $files_right_in_a_dir = Files::FindFiles("./dir", ["maxdepth" => 1]); // ["./dir/image.jpg", "./dir/image.png"]

    // filtering files
    $images = Files::FindFiles("./dir/", ["pattern" => '/\.(png|jpg)$/']); // ["./dir/image.jpg", "./dir/image.png", "./dir/subdir/image2.jpg"]

    // finding recently updated log files
    $log_files = Files::FindFiles("./log/", [
      "pattern" => '/\.log$/', // only *.log files
      "min_mtime" => time() - 30 * 60 // not older than 30 minutes 
     ]);

### Temporary files

    // by the TEMP constant the temporary directory can be specified
    define("TEMP","/path/to/temp/");

    echo Files::GetTempDir(); // "/path/to/temp/";

    $filename = Files::GetTempFilename(); // "/path/to/to/temp/files_tmp_5e060486c4e507.42603767"
    $filename = Files::GetTempFilename("image_scaling"); // "/path/to/temp/image_scaling5e060493067748.51409402";

    $filename = Files::WriteToTemp($content); // "/path/to/temp/files_tmp_5e0602ce153bd8.40620979"

### File types

    Files::DetermineFileType("path/to/a/file"); // "image/jpeg"

    // safe file type determination on just uploaded file
    Files::DetermineFileType($_FILES["file"]["tmp_name"],["original_filename" => $_FILES["file"]["name"]]);

Installation
------------

Use the Composer to install Files.

    cd path/to/your/project/
    composer require atk14/files dev-master

Licence
-------

Files is free software distributed [under the terms of the MIT license](http://www.opensource.org/licenses/mit-license)

[//]: # ( vim: set ts=2 et: )
