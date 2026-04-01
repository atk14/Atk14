# Change Log
All notable changes to StringBuffer will be documented in this file.

## [1.2.5] - 2026-04-01

* 64b9cda - StringBuffer::substr() fixed for negative offsets
* a586890 - Added alias StringBuffer::add() for StringBuffer::addString()
* 97a8da2 - Added static property StringBufferTemporary::$FILEIZE_THRESHOLD
* Some smelly code rewritten

## [1.2.4] - 2021-05-24

- If the output buffering is active it is turned off in StringBufferFileItem::flush()

## [1.2.3] - 2021-04-27

- Fix

## [1.2.2] - 2021-02-7

- Fix - stale file removed

## [1.2.1] - 2021-02-06

- StringBufferTemporary optimized

## [1.2] - 2021-02-06

- Added method StringBuffer::writeToFile()
- Added StringBufferTemporary, which can help with memory optimization

## [1.1.2] - 2021-02-05

- Files are being opened in the binary mode

## [1.1.1] - 2021-02-05

- StringBufferFileItem throws an exception when there is something wrong with the file

## [1.1] - 2021-02-04

- Added method StringBuffer::substr($offset,$length)

## [1.0] - 2017-01-24
