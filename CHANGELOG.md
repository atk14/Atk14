# Change Log
All notable changes to ATK14 Framework will be documented in this file.

## Unreleased

- 9d50989 - UrlFetcher::getContent() returns StringBuffer (actually StringBufferTemporary) and not string. BC BREAK!

## [1.5] - 2018-07-04

### Added (only main features are listed)

- a7d4d85 - [Sendmail] Added 4th optional parameter to sendmail(): $additional_parameters; When omited, the parameter is being configured automatically according to the Return-Path address
- ca2dd43 - [Atk14] Added new method Atk14Form::get_fields()
- d33fbbd - [Atk14] Variables can be used in config/deploy.yml; a deployment stage can extend a different stage than the first one by using "extends" key
- 8f0ef59 - [Files] Added method Files::MkdirForFile()
- 4f0e440 - [Atk14] After filter can be a closure
- 8c9261c - With ```scripts/dump_db*``` its now possible to backup data or schema from a database other than "default"
- f0d732c - [HTTP] Added method HTTPRequest::getRemoteHostname()
- 2588dac - [Atk14] Atk14Mailer handles correctly actions with only HTML template
- 2452292 - [DbMole] Added option "recache" default false
- a7e9021 - [Atk14] Block helper a_destroy accepts a new parameter ```_xhr``` (default true)
- f296a93 - [Files] Added new method Files::GetTempDir()
- 821fca7, 821fca7, 9c8e06f - [DbMole] Added new constant ```DBMOLE_AUTOMATIC_DELAY_TRANSACTION_BEGINNING_AFTER_CONNECTION```; by default it is TRUE in non-test environment

## [1.4] - 2018-04-10

### Added

- 866cf9c - Added constant ATK14_VERSION
- 6b8dfd4 - [TableRecord] Added option preread_data to TableRecord_Lister::getItems()
- e8d3bb7 - [TableRecord] Added method TableRecord_Lister::getIds()
- d1e9fd5 - [TableRecord] Added new static method TableRecord_Lister::ClearCache()
- eb652e7 - Added new function definedef()
- ce37a0c - [Atk14] Added method Atk14Mailer::_after_filter()
- c74a5a9 - [Forms] CheckboxSelectMultiple widget can be instantiated with option "escape_labels" (default true)
- 261fdf5 - [Atk14] Added new method Atk14Client::getResponseHeaders()
- 5cbcc2c - [TableRecord] Added method TableRecord_Finder::getQueryData()
- 0e851d1 - [Atk14] Added method Atk14Utils::AddHttpHostToUri(); Added optional parameter with_hostname to helpers stylesheet_link_tag and javascript_script_tag
- 1023bea - [Atk14] Added configuration constant ATK14_STATIC_FILE_VERSIONS_INDICATED_BY with default value "parameter"

### Changed

- f8b7f5f - [Atk14, Forms] The same form can be validated again and again
- c8f6eeb - [Atk14] Helper {render} sets item automatically, BC BREAK!
- ed04a72 - [Atk14] Method Atk14Controller::_redirect_to_ssl() and Atk14Controller::_redirect_to_no_ssl() fixed, BC BREAK!
- 944c579 - [Atk14] Atk14Client can handle paths starting with '/' (i.e. URIs)
- 08661d5 - [Atk14] Namespace can be redefined as an option in Atk14Router::__construct()

### Security

- 588527b - [Packer, Security] mcrypt_encrypt(), mcrypt_decrypt() replaced with openssl_encrypt(), openssl_decrypt()
- 369fbfc - [Security] Encryption in the Packer library is enabled by default
- e5f8c66 - [Security] SECRET_TOKEN is loaded automatically from config/.secret_token.txt

### Fixed

- There are many fixes :)

## [1.3] - 2017-09-08

### Added

- ./scripts/shell for entering a shell or execution of a program on a deploy stage
- Fixtures for preparing testing data
- 3662784 - [sendmail, Atk14] Added reply_to and reply_to_name to sendmail() and Atk14Mailer
- 4e871f6 - [Atk14] New controller attributes added for better rendering component handling: $prev_namespace, $prev_controller and $prev_action
- 08b8683 - [Forms] New option added to RadioSelect: convert_html_special_chars (default is true)
- 43aa2b0 - [Files] New method added: Files::FindFiles()
- 1e388bd - ./scripts/dump_settings is able to dump data in json
- 9f419b - TableRecord can read table structure when a schema is used in table_name
- [Translate] Added Cyrillic and German transliteration
- 66bf5d2 - [Localization] Gettext messages from the framework are held in directory ./locale/
- 52f0820 - [Atk14, Smarty] Experimental feature for capturing all rendered templates
- a8d74e7 - scripts/run_unit_tests loads locally installed PHPUnit

### Changed

- a9f8984 - [Atk14] The silent redirection from an unknown language to the default language was removed
- Smarty upgraded from 3.1.27 to 3.1.30
- Smarty2 upgraded from 2.6.28 to 2.6.30

### Security

- 4c5ead1 - [security] h() now escapes ' to &#039;
- a6d446d - [Security] By default script destroy_database_objects doesn't do its job in PRODUCTION

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
