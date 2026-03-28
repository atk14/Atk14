Change Log
==========

All notable changes to the DbMole Project will be documented in this file.

## [1.1.6] - 2023-04-10

* e6f7599 - `DbMole::_parseVersion()` fixed for PHP 5.6

## [1.1.5] - 2023-04-09

* b583238 - Added method DbMole::getConnection()

## [1.1.4] - 2022-11-05

* ca29e9c - Method DbMole::getStatistics() outputs data in html or plain text

## [1.1.3] - 2022-07-20

* 39a89e5 - DbMole::selectIntoAssociativeArray() improved for one-field queries
* Recognition of client or server version fixed
* 54db8ba - [SqlsrvMole] Added experimental support for Microsoft SQL Server
* c418488 - [SqlsrvMole] Automatic conversion of DateTime objects into strings was removed in SqlsrvMole - it can be replaced with the connection option ReturnDatesAsStrings=true

## [1.1.2] - 2021-03-15

- Added method DbMole::escapeColumnName4Sql() and used for the proper column names escaping in MySQL

## [1.1.1] - 2020-10-31

- Fixed methods DbMole::getDatabaseClientVersion() and DbMole::getDatabaseServerVersion()
- DbMole is being tested in PHP8

## [1.1] - 2020-10-01

- Added new methods DbMole::getDatabaseClientVersion() and DbMole::getDatabaseServerVersion()
- Better error reporting

## [1.0] - 2019-08-28

First tagged release
