# Change Log

All notable changes to Sendmail will be documented in this file.

## [1.0.5] - 2026-04-23

* Code cleaned and refactored

## [1.0.4] - 2025-06-06

* 9a2d709 - Removed development information from body when SENDMAIL_USE_TESTING_ADDRESS_TO is set

## [1.0.3] - 2025-06-06

* 6902a53 - When SENDMAIL_USE_TESTING_ADDRESS_TO is set, headers X-Original-To, X-Original-Cc and X-Original-Bcc are being added to the message
* f469bea,7858bce, adabeb6, 50c185a - Added parameter to_name

## [1.0.2] - 2023-04-08

* Sendmail is compatible with PHP >= 5.6
* 94422dd - PHP8.1 compatibility
* 35faa63 - Attachment headers corrected
* c41112e - In headers, "bcc:" changed to "Bcc:" and "cc:" changed to "Cc:"

## [1.0.1] - 2018-07-31

Source code divided into more files

## [1.0] - 2018-07-30

Sendmail was extracted from the ATK14 Framework
