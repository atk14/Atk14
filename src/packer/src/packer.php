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
	define("PACKER_ENABLE_ENCRYPTION",false);
}

if(!defined("PACKER_USE_JSON_SERIALIZATION")){
	define("PACKER_USE_JSON_SERIALIZATION",true);
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
		settype($salt,"string");
		$current_salt = Packer::_GetSetSalt(true,$salt);

		return $current_salt;
	}

	/**
	* Packs a variable into a URL-safe ASCII string.
	*
	* $p = Packer::Pack("hello!");
	* $p = Packer::Pack(array("a","b","c"));
	*/
	static function Pack($variable,$options = array()){
		$options = array_merge(array(
			"use_compress" => PACKER_USE_COMPRESS,
			"enable_encryption" => PACKER_ENABLE_ENCRYPTION,
			"extra_salt" => "", // for signing and encryption
			"use_json_serialization" => PACKER_USE_JSON_SERIALIZATION,
		),$options);

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
			$out = Packer::_EncryptData($out,$options["extra_salt"]);
			$prefix = strtoupper($prefix);
		}

		$out = $prefix.$out;

		$out = Packer::_EncodeDataString($out);
		$sign = Packer::_CalculateSignature($out,$options["extra_salt"]);
		$out = $sign.$out;

		return $out;
	}

	/**
	 * Unpack previously packed data.
	 *
	 * <code>
	 * $packed_value = Packer::Pack(array("hello" => "world"));
	 * if(Packer::Unpack($packed_value,$outpout_value)){
	 *		// ok
	 *		print_r($outpout_value);
	 *	}
	 * </code>
	 */
	static function Unpack($packed,&$out,$options = array()){
		$options += array(
			"enable_encryption" => PACKER_ENABLE_ENCRYPTION,
			"extra_salt" => "", // for signing and encryption
			"use_json_serialization" => PACKER_USE_JSON_SERIALIZATION,
		);
		settype($packed,"string");
		$out = null;
		if(strlen($packed)<=16){
			return false;
		}
		$sign = substr($packed,0,16);
		$data = substr($packed,16);
		$expected_sign = Packer::_CalculateSignature($data,$options["extra_salt"]);
		if($expected_sign!==$sign){
			return false;
		}
		$serialized = Packer::_DecodeDataString($data);
		if(strlen($serialized)<=1){
			return false;
		}
		$prefix = $serialized[0];
		$serialized = substr($serialized,1);
		if(!in_array($prefix,array("p","g","P","G"))){
			return false;
		}
		if($prefix==strtoupper($prefix)){
			$serialized = Packer::_DecryptData($serialized,$options["extra_salt"]);
			$prefix = strtolower($prefix);
		}elseif($options["enable_encryption"]){
			// encryption is enabled, but there isn't encrypted data
			return false;
		}
		if($prefix=="g"){
			$serialized = gzuncompress($serialized);
			if(is_bool($serialized)){
				return false;
			}
		}
		$out = $options["use_json_serialization"] ? json_decode($serialized,true) : (version_compare(PHP_VERSION,"7.0.0")>=0 ? unserialize($serialized,["allowed_classes" => false]) : unserialize($serialized));
		return true;
	}

	/**
	* Unpacks a variable and returns its value directly.
	* Unlike Unpack(), does not return a boolean.
	*
	* Returns null if $packed is tampered or invalid.
	* Also returns null if the packed value was originally null.
	*/
	static function Decode($packed,&$decoded = false){
		$decoded = false;
		if(Packer::Unpack($packed,$out)){
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
		$signature = hash_hmac("sha256",$str,$_constant_secret_salt.$_user_secret_salt.$extra_salt,true); // raw binary
		$signature = Packer::_EncodeDataString($signature);
		return substr($signature,0,16);
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
		settype($set,"boolean");
		settype($salt,"string");
		if(!isset($_SALT_)){
			$_SALT_ = "";
		}
		$_current_salt = $_SALT_;
		if($set==true){
			$_SALT_ = $salt;
		}
		return $_current_salt;
	}

	/**
	* Returns the escape map for URL-unsafe base64 characters.
	* Some characters produced by base64 encoding are not safe for use in URLs
	* and must be escaped. The escape character is "E".
	*
	* @static
	* @access private
	* @return array
	*/
	static function _GetEscape(){
		return [
			"E" => "EE",
			"/" => "ES",
			"\\" => "EB",
			"+" => "EP",
			"=" => "EQ",
			"." => "ED"
		];
	}

	/**
	* Base64-encodes the input string and escapes URL-unsafe characters.
	*
	* @static
	* @access private
	* @param string $data_string
	* @return string
	*/
	static function _EncodeDataString($data_string){
		$data_string = (string)$data_string;
		$base64 = base64_encode($data_string);
		return strtr($base64,self::_GetEscape());
	}

	/**
	* Unescapes the input string and base64-decodes it.
	*
	* @static
	* @access private
	* @param string $encoded_data_string
	* @return string
	*/
	static function _DecodeDataString($encoded_data_string){
		$encoded_data_string = (string)$encoded_data_string;
		if(strlen($encoded_data_string)==0){
			return "";
		}
		$base64 = strtr($encoded_data_string,array_flip(self::_GetEscape()));
		return base64_decode($base64);
	}

	static function _EncryptData($data,$extra_salt = ""){
		$secret = PACKER_CONSTANT_SECRET_SALT . Packer::_GetSetSalt() . $extra_salt;
		$key = hash('sha256', $secret, true); // raw binary key
		$iv = function_exists('random_bytes') ? random_bytes(16) : openssl_random_pseudo_bytes(16);
		return $iv.openssl_encrypt($data,"AES-256-CBC",$key,true,$iv);
	}

	static function _DecryptData($data,$extra_salt = ""){
		$secret = PACKER_CONSTANT_SECRET_SALT . Packer::_GetSetSalt() . $extra_salt;
		$key = hash('sha256', $secret, true); // raw binary key
		$iv = substr($data, 0, 16);
		return openssl_decrypt(substr($data,16),"AES-256-CBC",$key,true,$iv);
	}
}
