<?php
define("PACKER_CONSTANT_SECRET_SALT","Violet");
define("PACKER_USE_COMPRESS",true);
define("PACKER_ENABLE_ENCRYPTION",true);

if(preg_match('/tc_signature_(\d+)/',$_TEST["FILENAME"],$matches)){
	// tc_signature_24.php -> PACKER_SIGNATURE_LENGTH defined to 24
	define("PACKER_SIGNATURE_LENGTH",(int)$matches[1]);
}

require(__DIR__."/../src/packer.php");
