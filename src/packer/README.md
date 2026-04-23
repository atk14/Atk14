# Packer

[![Tests](https://github.com/atk14/Packer/actions/workflows/tests.yml/badge.svg)](https://github.com/atk14/Packer/actions/workflows/tests.yml)

Converts any PHP variable into a URL-safe string — and back. Supports HMAC signing, AES-256-CBC encryption, and gzip compression.

The Packer is particularly useful for:

* inserting a value into a URL,
* inserting a value into a hidden form field,
* inserting a value into a cookie.

In all cases, the value itself is securely hidden from the user and protected against unauthorized tampering.

## Installation

```bash
composer require atk14/packer
```

## Basic usage

```php
// Pack
$packed = Packer::Pack(["user_id" => 42, "role" => "admin"]);
// e.g. "a1b2c3d4e5f6a1b2peyJ1c2VyX2..."

// Unpack
if (Packer::Unpack($packed, $data)) {
    echo $data["role"]; // "admin"
}
```

Every packed string is **signed with HMAC-SHA256**. Tampered or forged strings are rejected on unpack.

## Configuration

Set a secret before using the library — either via a constant or `SetSalt()`:

```php
// Option A: define a constant (e.g. in bootstrap)
define("PACKER_CONSTANT_SECRET_SALT", "your-secret-here");

// Option B: set at runtime (can be changed per request)
Packer::SetSalt("your-secret-here");
```

> **Important:** Never use the default salt (`"Put_Some_Secret_Text_Here"`) in production. Anyone with access to the source code could forge valid packed strings.

Global defaults can also be set via constants:

| Constant | Default | Description |
|---|---|---|
| `PACKER_CONSTANT_SECRET_SALT` | `"Put_Some_Secret_Text_Here"` | Secret for signing and encryption |
| `PACKER_ENABLE_ENCRYPTION` | `false` | Enable AES-256-CBC encryption |
| `PACKER_USE_COMPRESS` | `false` | Enable gzip compression |
| `PACKER_USE_JSON_SERIALIZATION` | `true` | Use JSON (`true`) or PHP `serialize()` (`false`) |
| `PACKER_SIGNATURE_LENGTH` | `16` | Number of Base64URL characters used as the HMAC-SHA256 signature (8–43) |

## Encryption

```php
define("PACKER_CONSTANT_SECRET_SALT", "your-secret-here");

$packed = Packer::Pack($data, ["enable_encryption" => true]);

if (Packer::Unpack($packed, $data, ["enable_encryption" => true])) {
    // ok
}
```

Each call to `Pack()` with encryption generates a **random IV**, so the same input produces a different output every time.

### Per-call password (`extra_salt`)

Useful when different users or sessions need isolated packed values:

```php
$packed = Packer::Pack($data, [
    "enable_encryption" => true,
    "extra_salt"        => $user_token,
]);

Packer::Unpack($packed, $out, [
    "enable_encryption" => true,
    "extra_salt"        => $user_token, // must match
]);
```

## Compression

```php
$packed = Packer::Pack($large_array, ["use_compress" => true]);
```

Compression is detected automatically on unpack — no need to pass any option.

## Decode() — inline unpacking

`Decode()` is a shorthand for `Unpack()` that returns the value directly instead of using an output parameter:

```php
$data = Packer::Decode($packed);
```

To distinguish a failed unpack from a legitimately packed `null`, use the optional second parameter:

```php
$data = Packer::Decode($packed, $ok);
if (!$ok) {
    // tampered or invalid string
}
```

## Method reference

### `Packer::Pack($variable, $options = []): string`

Packs a variable into a URL-safe string.

| Option | Type | Default | Description |
|---|---|---|---|
| `enable_encryption` | bool | `PACKER_ENABLE_ENCRYPTION` | Encrypt with AES-256-CBC |
| `use_compress` | bool | `PACKER_USE_COMPRESS` | Compress with gzip |
| `use_json_serialization` | bool | `PACKER_USE_JSON_SERIALIZATION` | JSON vs PHP serialize |
| `extra_salt` | string | `""` | Additional secret for signing and encryption |

### `Packer::Unpack($packed, &$out, $options = []): bool`

Unpacks a string. Returns `true` on success, `false` if the string is invalid or tampered.

Accepts the same options as `Pack()`, except `use_compress` (auto-detected from the packed string).

### `Packer::Decode($packed, &$decoded = false): mixed`

Shorthand for `Unpack()`. Returns the unpacked value, or `null` on failure. Sets `$decoded` to `true`/`false`.

### `Packer::SetSalt($salt): string`

Sets the runtime secret salt. Returns the previous salt.

## Security notes

- **Signing:** Every packed string is signed with HMAC-SHA256. Any modification to the string is detected on unpack.
- **Encryption:** AES-256-CBC with a random IV. The encryption key is derived from `PACKER_CONSTANT_SECRET_SALT` + runtime salt (set via `SetSalt()`) + `extra_salt` using SHA-256.
- **Deserialization:** When using PHP `serialize()` mode, objects are never instantiated on unpack (`allowed_classes => false`).

## Requirements

- PHP >= 5.6
- Extensions: `openssl`, `zlib` (only needed if compression is used)

## License

MIT
