# Change Log
All notable changes to this project will be documented in this file.

## [Unreleased]

## [0.8.8] - 2026-02-15

* e685a83 - Added parameter $error_str to HTTPUploadedFile::moveTo($filename,&$error_str = null)

## [0.8.7] - 2026-01-26

* e3b8cc4 - HTTPRequest::getRemoteHostname() improved

## [0.8.6] - 2025-12-13

* 760c666 - HTTPRequest::getServerUri() fixed

## [0.8.5] - 2025-03-25

* 1b4829c - The HTTPRequest::getRequestUri() and HTTPRequest::getUrl() methods return NULL if there is nothing to return

## [0.8.4] - 2025-01-27

* c595dae - List of response codes fixed

## [0.8.3] - 2025-01-27

* 041b3c5 - Added some new HTTP status codes

## [0.8.2] - 2024-08-14

* 8b8e9ff - Atk14Client can simulate file uploads

## [0.8.1] - 2024-03-14

* 9d1cc40 - Added method HTTPResponse::temporarilyUnavailable($message = null)
* 8e032de - Added description "I'm a teapot" for response code 418
* PHP 8.1 compatibilities
* b0208fd - `HTTPResponse::_flushHeaders()` prints headers to stdout in cli

## [0.8] - 2021-05-19

- HTTPRequest::sslActive() improved - better detection using $_SERVER["HTTP_X_FORWARDED_PROTO"] and $_SERVER["HTTP_X_FORWARDED_SSL"]

## [0.7] - 2020-03-04

- Files::GetImageSize() is used to determine geometry of an uploaded image
- Package atk14/files is required in version >=1.6.1

## [0.6] - 2020-01-29

- Added method HTTPUploadedFile::getTotalFileSize() which is useful for chunked uploads

## [0.5.2] - 2020-11-01

- HTTPRequest::sslActive() fixed

## [0.5.1] - 2020-11-01

- HTTPRequest::sslActive() fixed

## [0.5] - 2020-10-31

- Added support for samesite option for cookies
- Added static method HTTPCookie::DefaultOptions()

## [0.4] - 2020-10-31

- Added method HTTPRequest::setSslActive()
- Project is being tested in PHP8

## [0.3.1] - 2020-02-12

- Content-Length header is being checked in HTTPXFile::GetInstance()

## [0.3] - 2019-10-22

### Added
- Added method HTTPResponse::setHeaders() for bulk HTTP header assignment
- Added methods HTTPRequest::getScheme() and getServerUrl()

### Fixed
- HTTPRequest::isServerOnStandardPort() fixed for usage in the shell
- method isServerOnStandardPort() tuned

## [0.2] - 2019-03-15

### Methods added
- HTTPRequest::setRequestAddress()
- HTTPRequest::getRemoteHostname()
- HTTPRequest::getQueryString()
- HTTPRequest::isServerOnStandardPort()

### Changed
- HTTPUploadedFile::moveToTemp() returns the new temporary filename

## [0.1] - 2017-11-30

