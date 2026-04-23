# Change Log
All notable changes to Packer will be documented in this file.

## [1.2] - 2026-04-23

* 0da30ec - Added different constant prefixes for encryption and signing
* 24923dd - Decode accepts a third optional parameter $options
* 3e09a29 - [Security] Concatenation of the HMAC key with a separator
* fbd9f37 - [Security] Function hash_equals() used for signature comparison
* 6726684 - The length of the signature is defined in the PACKER_SIGNATURE_LENGTH constant; default is 16
* abf9317 - A LogicException is thrown when PACKER_SIGNATURE_LENGTH is not between 8 and 43
* cac33eb - Used Base64URL encoding / decoding

## [1.1] - 2026-04-22

* 0e3e13a - Signature calculating improved
* 2af2ef4 - The value set by the Packer::SetSalt() method affects encryption
* 30efd4b - An InvalidArgumentException is thrown when the variable cannot be JSON-encoded
* f725ea1 - [Security] Raw binary key

## [1.0] - 2026-04-21

First release as a standalone package.
