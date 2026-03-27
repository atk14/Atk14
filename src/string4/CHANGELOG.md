Change Log
==========

All notable changes to this project will be documented in this file.

[0.5.8] - 2026-03-27
--------------------

* 2a90cf2 - The String4::replace() method no longer performs the in-place replacement! BC BREAK
* 82e3919 - Stronger randomnes in String4::RandomString() and String4::RandomPassword()
* 06a465c - String4::contains() fixed for an empty array
* 537a499 - String4::RandomString() returns a String4 object
* e632741 - String4::ToObject() fixed

[0.5.7] - 2025-07-15
--------------------

* daa7702 - Method `String4::_trim()` fixed

[0.5.6] - 2025-07-13
--------------------

* c247535 - Method String4::removeEmptyLines() fixed

[0.5.5] - 2025-07-12
--------------------

* 30f893b, 79f1895, ae5c810 - Added methods String4::eachLineMap($callback) and String4::eachLineFilter($callback = null)

[0.5.4] - 2025-06-30
--------------------

* 3bf9e93 - Added method String4::removeEmptyLines() + method String4::trim() has new optional parameter $remove_hidden_characters=false

[0.5.3] - 2025-02-07
--------------------

* 839501c - String4::stripHtml() fixed - `<br>` is not an inline tag
* c14c629 - Added methods split, pregSplit and titleize

[0.5.2] - 2023-12-05
--------------------

* e8db05b -  Fix

[0.5.1] - 2022-07-05
--------------------

* 2c25e49 - Method String4::chars() can split an invalid string

[0.5] - 2022-07-04
------------------

* 7b40bd9 - Removed obsolete class String that only worked in PHP 5 (BC BREAK)
* ceeb1c0 - By default, String4::chars() returns array of String4

[0.4] - 2022-01-25
------------------

- Added methods
  * fixEncoding()
  * stripHtml()
  * isLower()
  * isUpper()
  * uncapitalize()

[0.3] - 2020-03-02
------------------
- String4::toSlug() improved, added options max_length and suffix

[0.2.1] - 2020-01-24
--------------------
### Fixed
- String4::gsub() fixed: in some cases string was recognized as callable

[0.2] - 2020-01-17
--------------------
- String4::capitalize()
- String4::gsub() accepts callback
- String4::camelize() and String4::underscore() support unicode

[0.1.1] - 2018-02-16
--------------------

### Fixed
- String4::toBoolean() fixed: it converts empty string to false

[0.1] - 2017-10-30
------------------

- First tagged version of the String4
