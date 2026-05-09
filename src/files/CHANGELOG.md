# Change Log
All notable changes to this project will be documented in this file.

## [1.6.11] - 2026-05-09

* 563695a - Added public method Files::NormalizeFilename()

## [1.6.10] - 2026-05-05

* Fix for PHP5.6

## [1.6.9] - 2026-05-05

* Broken version 1.6.8 fixed

## [1.6.8] - 2026-05-05

* 1a61513 - Package is compatible with PHP>=5.6
* 3544c93 - Method Files::WriteToCacheFile() fixed
* c0200a8 - Method Files::IsReadableAndWritable() fixed
* Some more fixes and refactoring

## [1.6.7] - 2026-05-04

* 21f0e71 - The backtick operator replaced with shell_exec

## [1.6.6] - 2024-11-01

* 7d469c4 - Added list of mime types with no doubt about the file suffix

## [1.6.5] - 2024-10-30

* 9d7646f - Using Imagick to determine mime type

## [1.6.4] - 2023-09-12

* 3b50949 - Added method Files::WriteToCacheFile()
* bce9189 - Added method Files::CopyToTemp()
* 756088f - Detection of a apk file improved

## [1.6.3] - 2022-09-08

* d6e2ed5 - Added mime type detection for AVIF images

## [1.6.2] - 2021-11-06

- 4f5d6f6 - Proper mime type detection of a jar file (application/java-archive)
- 9f97cd0 - Proper mime type detection of an apk file (application/vnd.android.package-archive)
- 21602d1 - Better SVG files recognition

## [1.6.1] - 2021-03-04

- Method Files::GetImageSize() fixed for PHP8

## [1.6] - 2021-03-04

- Added method Files::GetImageSizeByContent()
- Method Files::GetImageSize() accepts filename as the first parameter, but the obsolete usage is preserved - It gonna be BC BREAK!

## [1.5] - 2021-02-06

- Added methods Files::TouchFile() and Files::EmptyFile()

## [1.4.2] - 2020-03-09

- Constants FILES_DEFAULT_FILE_PERMS and FILES_DEFAULT_DIR_PERMS are not defined to the default values when they are not defined

## [1.4.1] - 2020-03-09

- Added constants FILES_DEFAULT_FILE_PERMS and FILES_DEFAULT_DIR_PERMS

## [1.4] - 2020-02-26

- Added methods for setting and getting default file and directory permissions
  - Files::SetDefaultFilePerms()
  - Files::GetDefaultFilePerms()
  - Files::SetDefaultDirPerms()
  - Files::GetDefaultDirPerms()
- Added method Files::NormalizeFilePerms() (works for a file or directory)
- Method Files::GetFileContent() fixed

## [1.3.1] - 2019-07-03

- Files::GetTempFilename() fixed (filename prefix was not considered)

## [1.3] - 2018-05-29

### Added
- Added new method Files::MkdirForFile()

## [1.2] - 2018-04-19

### Added
- Option "maxdepth" added to Files::FindFiles()
- Added new method Files::GetTempDir()

## [1.1] - 2017-05-02

### Added
- New method added: Files::FindFiles()

### Fixed
- Files::DetermineFileType() uses function mime_content_type()
- Files::RecursiveUnlinkDir fixed

## [1.0] - 2017-01-23

Library "Files" was extracted from the ATK14 Framework.
