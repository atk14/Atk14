# Change Log
All notable changes to this project will be documented in this file.

## [Unreleased]

## [1.2] - 2016-11-10

### Added

- [Akt14] Added Atk14Robot::locking_enabled, Atk14Robot::execute_robot
- ./scripts/dbconsole can run pgadmin3 (with -g|--gui parameter)
- The create_command key in config/database.yml may contain an array of SQL commands
- Connecting to a postgresql database through a local unix socket enabled
- The interactive console acts like non-interactive console when STDIN is attached to a pipe

### Changed

- [Testing] The script run_unit_tests uses PHPUnit instead of PHPUnit2. PHPUnit could be installed eitheir using Composer or using a package system.
- [Forms] Added class number to NumberInput; added class email to EmailInput; on every <li> in the widget CheckboxSelectMultiple there is class="checkbox"
- [UrlFetcher] On ssl the peer verification was disabled
- [TableRecord] TableRecord::getValues() has now option return_id set to true by default

### Fixed

- There are many fixes

## [1.1] - 2016-05-22

### Added
- [Atk14] New method added Atk14Url::ParseParamsFromUri()
- [Atk14] script/deploy improved: added ability to dump a deployment recipe
- [Atk14] Layout name can be set in a template by using {use layout="name"}
- [Atk14] Atk14Sorting implements ArrayAccess which helps to simplify the configuration
- [TableRecord] new constant added: TABLERECORD_USE_CACHE_BY_DEFAULT (false by default)
- [TableRecord] Lister can prefetch data for given set of objects, data are automatically prefetched for all cached owners
- [TableRecord] Cache rewritten; ObjectCacher introduced

### Changed
- [TableRecord] Constant INOBJ_TABLERECORD_CACHES_STRUCTURES renamed to TABLERECORD_CACHES_STRUCTURES
- [Atk14] In Atk14Sorting a key for ascendant sorting has no suffix "-asc"
- [Atk14] Atk14Mailer is being set to the default state just before sending of every single message

### Fixed
- Wrong sql query normalization <https://github.com/atk14/Atk14/issues/3>
- Only owner can drop his tables in ./scripts/destroy_database_objects

### Security
- [DbMole] Prevented logging sensitive data after a SQL error

## [1.0] - 2016-03-19

### Added
- First tag added to the ATK14 framework

### Changed
- So let's take the ATK14 more seriously
