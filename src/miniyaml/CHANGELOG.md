# Change Log
All notable changes to miniYAML will be documented in this file.

## [1.1] - 2026-05-17

* 16fe33d - Added support for multirows scalars
* 1a29461 - miniYAML::Load() throws an enxception on unexpected multi-line scalar value
* e92e25e - Variable names are being checked in method InterpretPHP()
* cd82a91 - Package is compatible with PHP>=5.6.0
* d7e8899 - miniYAML::Dump() dumps NULLs properly according to option "nullable" (it is true by default)
* f63cfeb - miniYAML::Load() has new option "nullable" (it is true by default) to consider strings null and NULL as true NULL

## [1.0] - 2021-01-04
