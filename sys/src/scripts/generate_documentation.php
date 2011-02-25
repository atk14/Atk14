#!/usr/bin/env php
<?php
/**
 * Generates Atk14 documentation
 *
 * Just run ./generate_documentation.php
 *
 * Add -a or -app flag to also generate documentation of application
 *
 *
 * Requires PhpDocumentor
 *
 * You can install it with pear.
 * pear install PhpDocumentor.
 *
 * More info about PhpDocumentor is available at http://phpdoc.org/
 *
 */

require_once(dirname(__FILE__)."/load.inc");

$src_dirs = array();
$switches = array();

// ignored files
$ignores = array(
	"test/tc_*.inc",
	"load.inc",
	"initialize.inc"
);

// framework is documented by default
$_args = array_merge($argv, array("-f"));

if (isset($_args)) {
	foreach($_args as $arg) {
		switch($arg) {
		case "-a":
		case "-app":
			$src_dirs[] = $ATK14_GLOBAL->getApplicationPath();
			// when we generate documentation for application use its name as packagename
			// TODO: chtelo by to odnekud prevzit jmeno aplikace
			$switches["--defaultpackagename"] = "ApplicationDoc";
			// output only applications package
			$switches["--packageoutput"] = "ApplicationDoc";
			break;
		case "-f":
		case "-framework":
			$src_dirs[] = $ATK14_GLOBAL->getApplicationPath()."../sys/src/atk14";
			$src_dirs[] = $ATK14_GLOBAL->getApplicationPath()."../sys/src/dbmole";
			$src_dirs[] = $ATK14_GLOBAL->getApplicationPath()."../sys/src/dictionary";
			$src_dirs[] = $ATK14_GLOBAL->getApplicationPath()."../sys/src/forms";
			$src_dirs[] = $ATK14_GLOBAL->getApplicationPath()."../sys/src/http";
			$src_dirs[] = $ATK14_GLOBAL->getApplicationPath()."../sys/src/stringbuffer";
			$src_dirs[] = $ATK14_GLOBAL->getApplicationPath()."../sys/src/inobj_tablerecord";
			break;
		}
	}
}

$output_dir = $ATK14_GLOBAL->getApplicationPath()."../tmp/documentation";

if (!file_exists($output_dir)) {
	files::mkdir($output_dir, &$err, &$err_str);
}

$src_dir = join(",", $src_dirs);


// --ignore switch
if (sizeof($ignores)>0) {
	$switches["--ignore"] = implode(",", $ignores);
}

// --directory switch
if (sizeof($src_dirs)>0) {
	$switches["--directory"] = implode(",", $src_dirs);
}

$prms = "";
foreach($switches as $sw => $val) {
	$prms .= " $sw $val";
}

$command = sprintf("phpdoc -o HTML:frames:DOM/phpdoc.de %s -t $output_dir", $prms);

$val = system($command, $ret);

if ($ret == 127) {
	echo "\nPhpDocumentor tool not found\n\n";
	echo "Please check that you have installed PhpDocumentor and you have phpdoc command in \$PATH\n";
	echo "If you don't have PhpDocumentor install it from http://phpdoc.org/ and try again.\n\n";
	exit(2);
}




