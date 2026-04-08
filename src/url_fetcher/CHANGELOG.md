Change Log
==========

All notable changes to this project will be documented in this file.

[1.8.7] - 2026-04-08
--------------------

* a4264a9 - The closing slash in the Location header's value is not mandatory
* 008d9ab - Added read timeout (default 30.0s)
* b80a244 - UrlFetcherViaCommand fixed

[1.8.6] - 2025-09-15
--------------------

* 4c12114 - Added option verify_peer_name (true by default)

[1.8.5] - 2024-05-15
--------------------

* 20aad14 - Fix

[1.8.4] - 2024-04-05
--------------------

* 4be457a - A GET request can contain a request body

[1.8.3] - 2023-08-01
--------------------

* d0f5330 - Method UrlFetcher::setSocketTimeout() returns the previous timeout

[1.8.2] - 2023-07-31
--------------------

* 9235fa0 - Writing to stream improved

[1.8.1] - 2023-05-15
--------------------

* 1077c84 - Fix - Host header corrected in a request to a http server on a non-standard port

[1.8] - 2023-03-13
------------------

* UrlFetcher can communicate through a proxy server

[1.7.4] - 2022-12-13
--------------------

* afcafd4 - In case of communication via SSL, the peer is verified by default + added constant URL_FETCHER_VERIFY_PEER

[1.7.3] - 2022-10-03
--------------------

* 21a5a9b - Content is being transparently gzdecoded when gzip content encoding is in play

[1.7.2] - 2022-09-03
--------------------

* 26a42ef - Method UrlFetcher::_cleanUpUri() fixed
* 93f1e3c - Method UrlFetcher::getContentType() returns null if there is no Content-Type header in the response

[1.7.1] - 2021-12-08
--------------------

* 897b700 - UrlFetcherViaCommand tuned & fixed

[1.7] - 2021-12-03
------------------

* 5033b5f - Added class UrlFetcherViaCommand for fetching data not from network socket but from a command (e.g. scripts/simulate_http_request in an ATK14 project)

[1.6.3] - 2021-07-13
--------------------

- Better error message on an unresolvable domain

[1.6.2] - 2021-02-07
--------------------

- Dependency fixed

[1.6.1] - 2021-02-06
--------------------

- Fix

[1.6] - 2021-02-06
------------------

- UrlFetcher::getContent() returns an instance of StringBufferTemporary

[1.5.1] - 2021-02-05
--------------------

- Memory consumption fixed

[1.5] - 2021-02-05
------------------

- Data is being written to the socket from StringBuffer and not from a string
- Method UrlFetcher::getFilename() improved

[1.4.3] - 2020-12-08
--------------------

- Handled better writing to the socket

[1.4.2] - 2020-02-12
--------------------

- Added jump from the fwrite cycle after reaching a number of errors

[1.4.1] - 2019-10-25
--------------------

- Method UrlFetcher::_cleanUpUri() fixed

[1.4] - 2019-10-21
------------------

- Added cleaning procedure that corrects some URL issues (for example, it converts "http://www.atk14.net/about/../" to "http://www.atk14.net/")

[1.3] - 2018-04-03
------------------

### Added
- PUT and DELETE HTTP requests can be performed by UrlFetcher; added methods put() and delete()

[1.2] - 2018-03-31
------------------

### Added
- Added method UrlFetcher::getStatusMessage()

### Changed
- Method UrlFetcher::getFilename() tries to extract a filename from the Content-Disposition header

[1.1.1] - 2018-03-04
--------------------

### Fixed
- User-Agent format fixed

[1.1] - 2017-11-30
------------------

### Added
- New method added UrlFetcher::getRequestMethod()

[1.0] - 2017-01-24
------------------

- UrlFetcher was extracted from the ATK14 Framework
