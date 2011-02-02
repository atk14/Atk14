<?
$__CONFIG_CONSTANTS__ = array();

__defaults__(array(
	"SECRET_TOKEN" => "make_sure_you_define_strong_secret_phrase_in_SECRET_TOKEN_constant",

	"INOBJ_TABLERECORD_CACHES_STRUCTURES" => PRODUCTION ? 60*60 : 0,
));

__defaults__(array(
	"PACKER_CONSTANT_SECRET_SALT" => SECRET_TOKEN,
	"PACKER_USE_COMPRESS" => false,
));


function __defaults__($defaults){
	global $__CONFIG_CONSTANTS__;
	foreach($defaults as $key => $value){
		!defined($key) && define($key,$value);
		$__CONFIG_CONSTANTS__["$key"] = constant($key);
	}
}


