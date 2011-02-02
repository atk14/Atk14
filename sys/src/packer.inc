<?php
if(!defined("PACKER_CONSTANT_SECRET_SALT")){
	/**
	* salt pro podpis prenasenych dat
	* tajny konstatni salt pro podepisovani a overovani pakovanych dat
	*/
	define("PACKER_CONSTANT_SECRET_SALT","Put_Some_Secret_Text_Here");
}


if(!defined("PACKER_USE_COMPRESS")){
	/**
	* flag pouzivani komprese
	* pouzivat gzcompress nebo nikoli pro zmenseni pakovaneho retezce...
	*/
	define("PACKER_USE_COMPRESS",false);
}

/**
* Komprimace libovolne promenne (napr. pole).
* Trida packer nabizi staticke metody pro kompresi a dekompresi promennych do ascii podoby,
* ktera je rovnou vhodna do hidden formularoveho poli nebo jako parametr do URL.
*
* schema komprese
* promenna -> serialize -> base64_encoding -> podpis pro zpetne overeni platnosti
*
*	$var = array(
*		"klic1" => "hodnota1",
*		"klic2" => "hodnota2",
*		"klic3" => array(
*			"klic4" => "hodnota4"
*		)
*	);
*
*	Packer::set_salt("secret salt 48392134 fiejfiefj");
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
	* Nastavuje salt pro podpis prenasenych dat 
	* Defaltni salt je bran z konstanty PACKER_CONSTANT_SECRET_SALT.
	* Vracen je predchozi salt.
	*
	* @access public
	* @static
	* @param string  	$salt novy salt
	* @return string  predchozi salt
	*/
	function set_salt($salt){
		settype($salt,"string");
		$current_salt = Packer::_get_set_salt(true,$salt);
		
		return $current_salt;
	}

	/**
	* Zabali promennou do ascii retezce mimo jine bezpecne pouzitelneho jako parametr v URL.
	*
	* $p = Packer::Pack("hello!");
	* $p = Packer::Packer(array("a","b","c"));
	*/
	function Pack($variable){
		$out = serialize($variable);

		if(defined("PACKER_USE_COMPRESS") && PACKER_USE_COMPRESS==true){
			$out = gzcompress($out,5);
			$out = "g".$out;
		}else{
			$out = "p".$out;
		}
		$out = Packer::_encode_data_string($out);
		$sign = Packer::_get_signature($out);
		$out = $sign.$out;

		return $out;
	}

	/**
	* Rozbali promennou ze zabalene podoby.
	*
	* $packed_value = Packer::Pack(array("hello" => "world"));
	* if(Packer::Unpack($packed_value,$outpout_value)){
	*		// ok
	*		print_r($outpout_value);
	*	}
	*/
	function Unpack($packed,&$out){
		settype($packed,"string");
		$out = null;
		if(strlen($packed)<=16){
			return false;
		}
		$sign = substr($packed,0,16);
		$data = substr($packed,16);
		$expected_sign = Packer::_get_signature($data);
		if($expected_sign!=$sign){
			return false;
		}
		$serialized = Packer::_decode_data_string($data);
		if(strlen($serialized)<=1){
			return false;
		}
		$method = $serialized[0];
		$serialized = substr($serialized,1);
		if($method[0]!="p" && $method[0]!="g"){
			return false;
		}
		if($method=="g"){
			$serialized = gzuncompress($serialized);
			if(is_bool($serialized)){
				return false;
			}
		}
		$out = unserialize($serialized);
		return true;
	}

	/**
	* Rozbali promennou a vrati rovnou jeji hodnotu.
	* Tedy oproti Unpack() nevraci bool/true.
	*
	*
	* Vrati null v pripade, ze $packed byla je porusena.
	* Vrati ovsem null i v pripade, ze do $packed byl zabalen null.
	*/
	function Decode($packed){
		if(Packer::Unpack($packed,$out)){
			return $out;
		}
	}


	/**
	* Pro dany ascii retezec urci podpis.
	* Vraci polovinu md5 retezec _+
	*
	* @access private
	* @static
	* @param string  &$str 			zabalena promenna
	* @return string podpis
	*/
	function _get_signature(&$str){
		$_constant_secret_salt = "";
		if(defined(PACKER_CONSTANT_SECRET_SALT)){
			$_constant_secret_salt = PACKER_CONSTANT_SECRET_SALT;
			settype($_constant_secret_salt,"string");
		}
		$_user_secret_salt = Packer::_get_set_salt();
		return substr(md5($str.$_constant_secret_salt.$_user_secret_salt),0,16);
	}

	/**
	* Vrati nebo nastavi novy salt pro vypocet podpisu.
	*
	* @access private
	* @static
	* @param bool $set 	  false => nenastavuje se; true => nastavuje se novy salt
	* @param string $salt novy salt, $set musi byt nastaven na true, pokud je nutne nastavit novy salt
	* @return string  aktualni nebo predchozi (pri nastavovani) salt
	*/
	function _get_set_salt($set = false,$salt = ""){
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
	* Pole escapovanych znaku.
	* Nektere znaky v base64 encodovanem textu jsou nevhodna pro umisteni do url,
	* proto je nutne je escapovat.
	* Escapovaci znak je E.
	* 
	* @static
	* @access private
	* @return array
	*/
	function _get_escapes(){

		//nevim presne, jake znaky muze obsahovat base64, snad to bude stacit
		return array(
			"E" => "EE",
			"/" => "ES",
			"\\" => "EB",
			"+" => "EP",
			"=" => "EQ",
			"." => "ED"
		);
		
	}

	/**
	* Zakoduje vstupni retezec do base64 a zde pak zaescapuje urcite znaky.
	* Vstupni retezec byva serializovana promenna, ale to je podruzne.
	*
	* @static
	* @access private
	* @param string $data_string
	* @return string
	*/
	function _encode_data_string($data_string){
		settype($data_string,"string");
		
		$out = array();
		$escapes = Packer::_get_escapes();
		$base64 = base64_encode($data_string);

		for($i=0;$i<strlen($base64);$i++){
			if(isset($escapes[$base64[$i]])){
				$out[] = $escapes[$base64[$i]];
				continue;
			}
			$out[] = $base64[$i];
		}

		return join("",$out);
	}

	/**
	* Odescapuje vstupni retezec a pak jej base64 decoduje.
	* Vystupni retezec byva serializovana promenna, ale to je podruzne.
	* 
	* @static
	* @access private
	* @param string $decoded_data_string
	* @return string
	*/
	function _decode_data_string($encoded_data_string){
		settype($encoded_data_string,"string");
	
		if(strlen($encoded_data_string)==0){
			return "";
		}
	
		$base64 = array();
		$_escapes = Packer::_get_escapes();
		$escapes = array();
		reset($_escapes);
		while(list($_key,$_value) = each($_escapes)){
			$escapes[$_value] = $_key;
		}

		$out_ar = array();
		for($i=0;$i<strlen($encoded_data_string);$i++){
			if(isset($encoded_data_string[$i+1]) && isset($escapes[$encoded_data_string[$i].$encoded_data_string[$i+1]])){
				$out_ar[] = $escapes[$encoded_data_string[$i].$encoded_data_string[$i+1]];
				$i++;
				continue;
			}
			$out_ar[] = $encoded_data_string[$i];
		}
		$out = join("",$out_ar);

		return base64_decode($out);
	}
}
