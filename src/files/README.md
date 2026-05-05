Files
=====

[![Tests](https://github.com/atk14/Files/actions/workflows/tests.yml/badge.svg?branch=master)](https://github.com/atk14/Files/actions/workflows/tests.yml)

A PHP class providing static methods for common file system operations: reading and writing files, copying, moving, deleting, MIME type detection, image size detection, finding files, and managing permissions.

Installation
------------

    composer require atk14/files

Usage
-----

All methods follow the same error-reporting convention — pass `$error` and `$error_str` by reference to check for failures without exceptions:

    $content = Files::GetFileContent("/path/to/file", $error, $error_str);
    if ($error) {
        echo $error_str; // "file.txt is not a file"
    }

Both parameters are optional.

### Reading and writing

    $content = Files::GetFileContent("/path/to/file");

    Files::WriteToFile("/path/to/file", $content);
    Files::AppendToFile("/path/to/file", $content);
    Files::EmptyFile("/path/to/file");
    Files::TouchFile("/path/to/file");

`WriteToCacheFile()` writes atomically via a temporary file and rename, preventing partial reads in concurrent situations:

    Files::WriteToCacheFile("/path/to/cache/file", $content);

### Copying, moving and deleting

    Files::CopyFile("/path/to/source", "/path/to/target");

    // move a file into an existing directory
    Files::MoveFile("/path/to/file.jpg", "/path/to/dir/");
    // move a file to a new name
    Files::MoveFile("/path/to/old.jpg", "/path/to/new.jpg");

    Files::Unlink("/path/to/file");
    Files::RecursiveUnlinkDir("/path/to/dir");

### Directories

    Files::Mkdir("/path/to/new/directory");         // creates all missing parent directories
    Files::MkdirForFile("/path/to/new/file.txt");   // creates "/path/to/new"

### Temporary files

    $tmp = Files::WriteToTemp($content);    // write content to a new temp file
    $tmp = Files::CopyToTemp("/path/to/source");
    // ... work with $tmp ...
    Files::Unlink($tmp);

    $path = Files::GetTempFilename();                   // just reserve a filename
    $path = Files::GetTempFilename("my_prefix_");       // with a custom prefix

    echo Files::GetTempDir();   // e.g. "/tmp"

The temporary directory defaults to `sys_get_temp_dir()`. Define the `TEMP` constant before including the library to override it:

    define("TEMP", "/path/to/temp/");

### MIME type detection

    $mime = Files::DetermineFileType("/path/to/file");  // e.g. "image/jpeg"

    // for uploaded files, pass the original filename to improve detection accuracy
    $mime = Files::DetermineFileType(
        $_FILES["file"]["tmp_name"],
        ["original_filename" => $_FILES["file"]["name"]],
        $preferred_suffix  // e.g. "jpg"
    );

Detection uses `mime_content_type()`, `finfo`, or the `file` shell command as fallbacks. When [Imagick](https://www.php.net/manual/en/book.imagick.php) is available it is used as a last resort.

### Image dimensions

    [$width, $height] = Files::GetImageSize("/path/to/image.jpg");
    [$width, $height] = Files::GetImageSizeByContent($binary_image_data);

### Finding files

    // all files recursively
    $files = Files::FindFiles("./dir");

    // only immediate children
    $files = Files::FindFiles("./dir", ["maxdepth" => 1]);

    // filter by name pattern
    $images = Files::FindFiles("./dir", ["pattern" => '/\.(png|jpg)$/i']);

    // filter by modification time
    $recent = Files::FindFiles("./log", [
        "pattern"  => '/\.log$/',
        "min_mtime" => time() - 30 * 60,    // not older than 30 minutes
        "max_mtime" => time(),
    ]);

### Permissions

New files are created with `0666`, new directories with `0777` (both subject to the process umask). Override the defaults globally:

    define("FILES_DEFAULT_FILE_PERMS", 0640);
    define("FILES_DEFAULT_DIR_PERMS", 0750);

Or at runtime:

    $prev = Files::SetDefaultFilePerms(0640);
    // ... do some work ...
    Files::SetDefaultFilePerms($prev);

Apply the current defaults to an existing file or directory:

    Files::NormalizeFilePerms("/path/to/file");

Check whether a file is both readable and writable by the current process:

    if (!Files::IsReadableAndWritable("/path/to/file")) {
        // ...
    }

Validate an HTTP-uploaded file:

    if (!Files::IsUploadedFile($_FILES["file"]["tmp_name"])) {
        // reject — not a legitimate upload
    }

Licence
-------

Files is free software distributed [under the terms of the MIT license](http://www.opensource.org/licenses/mit-license)

[//]: # ( vim: set ts=2 et: )
