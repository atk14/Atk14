<?php
if(!defined("PACKER_CONSTANT_SECRET_SALT")){
	/**
	 * Secret salt for signing and encrypting packed data.
	 */
	define("PACKER_CONSTANT_SECRET_SALT",defined("SECRET_TOKEN") ? constant("SECRET_TOKEN") : "Put_Some_Secret_Text_Here");
}


if(!defined("PACKER_USE_COMPRESS")){
	/**
	 * Whether to compress packed data with gzip.
	 */
	define("PACKER_USE_COMPRESS",false);
}

if(!defined("PACKER_ENABLE_ENCRYPTION")){
	/**
	 * Whether to encrypt packed data with AES-256-CBC by default.
	 */
	define("PACKER_ENABLE_ENCRYPTION",false);
}

if(!defined("PACKER_USE_JSON_SERIALIZATION")){
	/**
	 * Whether to serialize data as JSON (true) or using PHP native serialize() (false).
	 */
	define("PACKER_USE_JSON_SERIALIZATION",true);
}

if(!defined("PACKER_SIGNATURE_LENGTH")){
	/**
	 * Number of Base64URL characters used as the HMAC-SHA256 signature prefix.
	 * Each character carries ~6 bits of entropy; 16 characters = ~96 bits.
	 * Must be between 8 and 43 (the full SHA-256 Base64URL-encoded length).
	 */
	define("PACKER_SIGNATURE_LENGTH",16);
}

if(PACKER_SIGNATURE_LENGTH < 8 || PACKER_SIGNATURE_LENGTH > 43){
	throw new \LogicException("PACKER_SIGNATURE_LENGTH must be between 8 and 43, ".PACKER_SIGNATURE_LENGTH." given.");
}

/**
 * Packs any PHP variable into a URL-safe ASCII string and unpacks it back.
 * Suitable for use in hidden form fields or as a URL parameter.
 *
 * Packing schema:
 * variable -> serialize -> base64_encoding -> HMAC signature
 *
 *	$var = array(
 *		"key1" => "value1",
 *		"key2" => "value2",
 *		"key3" => array(
 *			"key4" => "value4"
 *		)
 *	);
 *
 *	Packer::SetSalt("secret salt 48392134 fiejfiefj");
 *
 *	$packed_var = Packer::Pack($var);
 *
 *	echo $packed_var;
 *
 *	$new_var = null;
 *	if(!Packer::Unpack($packed_var,$new_var)){
 *		echo "unpacking failed";
 *	}else{
 *		var_dump($new_var);
 *	}
 */
class Packer{

	/**
	 * A map for replacing Base64 characters that are not URL-safe.
	 */
	protected static $BASE64_REPLACES = [
		"+" => "-",
		"/" => "_",
	];

	protected function __construct(){
	}

	/**
	 * Sets the runtime salt used for signing and encryption.
	 * The default salt is taken from the constant PACKER_CONSTANT_SECRET_SALT.
	 * Returns the previous salt.
	 *
	 * @access public
	 * @static
	 * @param string  	$salt new salt
	 * @return string  previous salt
	 */
	static function SetSalt($salt){
		$salt = (string)$salt;
		$current_salt = Packer::_GetSetSalt(true,$salt);

		return $current_salt;
	}

	/**
	 * Packs a variable into a URL-safe ASCII string.
	 *
	 * $p = Packer::Pack("hello!");
	 * $p = Packer::Pack(array("a","b","c"));
	 *
	 * @throws InvalidArgumentException when the variable cannot be JSON-encoded (only in JSON serialization mode)
	 */
	static function Pack($variable,$options = array()){
		$options += array(
			"use_compress" => PACKER_USE_COMPRESS,
			"enable_encryption" => PACKER_ENABLE_ENCRYPTION,
			"extra_salt" => "", // for signing and encryption
			"use_json_serialization" => PACKER_USE_JSON_SERIALIZATION,
		);

		if($options["use_json_serialization"]){
			$out = json_encode($variable);
			if($out===false){
				throw new InvalidArgumentException("Packer::Pack(): variable cannot be JSON-encoded: ".json_last_error_msg());
			}
		}else{
			$out = serialize($variable);
		}

		if($options["use_compress"]){
			$out = gzcompress($out,5);
			$prefix = "g";
		}else{
			$prefix = "p";
		}
		if($options["enable_encryption"]){
			$out = Packer::_EncryptDataString($out,$options["extra_salt"]);
			$prefix = strtoupper($prefix);
		}

		$out = $prefix.$out;

		$out = Packer::_Base64UrlEncode($out);
		$sign = Packer::_CalculateSignature($out,$options["extra_salt"]);
		$out = $sign.$out;

		return $out;
	}

	/**
	 * Unpack previously packed data.
	 *
	 * <code>
	 * $packed_value = Packer::Pack(array("hello" => "world"));
	 * if(Packer::Unpack($packed_value,$output_value)){
	 *		// ok
	 *		print_r($output_value);
	 *	}
	 * </code>
	 */
	static function Unpack($packed,&$out,$options = array()){
		$options += array(
			"enable_encryption" => PACKER_ENABLE_ENCRYPTION,
			"extra_salt" => "", // for signing and encryption
			"use_json_serialization" => PACKER_USE_JSON_SERIALIZATION,
		);
		$packed = (string)$packed;
		$out = null;
		if(strlen($packed)<=PACKER_SIGNATURE_LENGTH){
			return false;
		}
		$sign = substr($packed,0,PACKER_SIGNATURE_LENGTH);
		$data = substr($packed,PACKER_SIGNATURE_LENGTH);
		$expected_sign = Packer::_CalculateSignature($data,$options["extra_salt"]);
		if(!hash_equals($expected_sign,$sign)){
			return false;
		}
		$serialized = Packer::_Base64UrlDecode($data);
		if(strlen($serialized)<=1){
			return false;
		}
		$prefix = $serialized[0];
		$serialized = substr($serialized,1);
		if(!in_array($prefix,array("p","g","P","G"))){
			return false;
		}
		if($prefix==strtoupper($prefix)){
			$serialized = Packer::_DecryptDataString($serialized,$options["extra_salt"]);
			if($serialized === ""){
				return false;
			}
			$prefix = strtolower($prefix);
		}elseif($options["enable_encryption"]){
			// encryption is enabled, but there isn't encrypted data
			return false;
		}
		if($prefix=="g"){
			$serialized = gzuncompress($serialized);
			if($serialized === false){
				return false;
			}
		}

		if($options["use_json_serialization"]){
			$out = json_decode($serialized,true);
			if($out === null && $serialized !== 'null' && json_last_error() !== JSON_ERROR_NONE){                                                                                                                                                                                                                                      
				return false;                                                                                                                                                                                                                                                                                                            
			}   
		}else{
			$out = version_compare(PHP_VERSION,"7.0.0")>=0 ? unserialize($serialized,["allowed_classes" => false]) : unserialize($serialized);
		}
		return true;
	}

	/**
	 * Unpacks a variable and returns its value directly.
	 * Unlike Unpack(), does not return a boolean.
	 *
	 * Returns null if $packed is tampered or invalid.
	 * Also returns null if the packed value was originally null.
	 */
	static function Decode($packed,&$decoded = false,$options = []){
		$decoded = false;
		if(Packer::Unpack($packed,$out,$options)){
			$decoded = true;
			return $out;
		}
		return null;
	}


	/**
	 * Calculates the HMAC signature for the given string.
	 *
	 * @access private
	 * @static
	 * @param string  $str packed data string
	 * @return string signature
	 */
	static function _CalculateSignature($str,$extra_salt = ""){
		$_constant_secret_salt = PACKER_CONSTANT_SECRET_SALT;
		$_user_secret_salt = Packer::_GetSetSalt();
		$signature = hash_hmac("sha256",$str,implode("\x00",["sign",$_constant_secret_salt,$_user_secret_salt,$extra_salt]),true); // raw binary
		$signature = Packer::_Base64UrlEncode($signature);
		return substr($signature,0,PACKER_SIGNATURE_LENGTH);
	}

	/**
	 * Gets or sets the runtime salt used for signing and encryption.
	 *
	 * @access private
	 * @static
	 * @param bool   $set   false = get only; true = set new salt
	 * @param string $salt  new salt value (only used when $set is true)
	 * @return string current or previous salt
	 */
	static function _GetSetSalt($set = false,$salt = ""){
		static $_SALT_;
		$set = (bool)$set;
		$salt = (string)$salt;
		if(!isset($_SALT_)){
			$_SALT_ = "";
		}
		$_current_salt = $_SALT_;
		if($set === true){
			$_SALT_ = $salt;
		}
		return $_current_salt;
	}

	/**
	 * Base64URL-encodes the input string.
	 *
	 * @static
	 * @access private
	 * @param string $data_string
	 * @return string
	 */
	static function _Base64UrlEncode($data_string){
		$data_string = (string)$data_string;
		$out = base64_encode($data_string);
		$out = rtrim($out,"=");
		return strtr($out,self::$BASE64_REPLACES);
	}

	/**
	 * Unescapes the input string and Base64URL-decodes it.
	 *
	 * @static
	 * @access private
	 * @param string $encoded_data_string
	 * @return string
	 */
	static function _Base64UrlDecode($encoded_data_string){
		$encoded_data_string = (string)$encoded_data_string;
		if(strlen($encoded_data_string) === 0){
			return "";
		}
		$base64 = strtr($encoded_data_string,array_flip(self::$BASE64_REPLACES));
		return base64_decode($base64);
	}

	/**
	 * Encrypts a string using AES-256-CBC with a random IV.
	 * The encryption key is derived from the combined secret salts using SHA-256.
	 * Returns the IV prepended to the ciphertext.
	 *
	 * @static
	 * @access private
	 * @param string $data_string  data to encrypt
	 * @param string $extra_salt   additional secret (e.g. per-user token)
	 * @return string              IV (16 bytes) + ciphertext
	 */
	static function _EncryptDataString($data_string,$extra_salt = ""){
		$key = self::_BuildEncryptionKey($extra_salt);
		$iv = function_exists("random_bytes") ? random_bytes(16) : openssl_random_pseudo_bytes(16);
		return $iv.openssl_encrypt($data_string, "AES-256-CBC", $key, OPENSSL_RAW_DATA, $iv);
	}

	/**
	 * Decrypts a string previously encrypted by _EncryptDataString().
	 * Extracts the IV from the first 16 bytes and decrypts the remainder.
	 *
	 * @static
	 * @access private
	 * @param string $encrypted_data_string  IV (16 bytes) + ciphertext
	 * @param string $extra_salt             additional secret (must match the one used for encryption)
	 * @return string                        decrypted data
	 */
	static function _DecryptDataString($encrypted_data_string,$extra_salt = ""){
		$key = self::_BuildEncryptionKey($extra_salt);
		$iv = substr($encrypted_data_string, 0, 16);
		$encrypted_data_string = substr($encrypted_data_string,16);
		$out = openssl_decrypt($encrypted_data_string, "AES-256-CBC", $key, OPENSSL_RAW_DATA, $iv);
		if($out === false){
			return "";
		}
		return $out;
	}

	static private function _BuildEncryptionKey($extra_salt){
		$secret = implode("\x00",["encrypt",PACKER_CONSTANT_SECRET_SALT,Packer::_GetSetSalt(),$extra_salt]);
		$key = hash("sha256", $secret, true); // raw binary key
		return $key;
	}
}
