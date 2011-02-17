#!/usr/bin/env php
<?
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

if (isset($argv)) {
	foreach($argv as $arg) {
		switch($arg) {
		case "-a":
		case "-app":
			$src_dirs[] = $ATK14_GLOBAL->getApplicationPath();
		}
	}
}
$src_dirs[] = $ATK14_GLOBAL->getApplicationPath()."../sys/src/atk14";
$src_dirs[] = $ATK14_GLOBAL->getApplicationPath()."../sys/src/dictionary";
$src_dirs[] = $ATK14_GLOBAL->getApplicationPath()."../sys/src/forms";
$src_dirs[] = $ATK14_GLOBAL->getApplicationPath()."../sys/src/http";
$src_dirs[] = $ATK14_GLOBAL->getApplicationPath()."../sys/src/stringbuffer";

$output_dir = $ATK14_GLOBAL->getApplicationPath()."../tmp/documentation";

if (!file_exists($output_dir)) {
	files::mkdir($output_dir, &$err, &$err_str);
}

$src_dir = join(",", $src_dirs);
$command = "phpdoc -o HTML:frames:DOM/phpdoc.de -d $src_dir -t $output_dir -i tc_*.inc,load.inc,initialize.inc";

$val = system($command, $ret);

if ($ret == 127) {
	echo "\nPhpDocumentor tool not found\n\n";
	echo "Please check that you have installed PhpDocumentor and you have phpdoc command in \$PATH\n";
	echo "If you don't have PhpDocumentor install it from http://phpdoc.org/ and try again.\n\n";
	exit(2);
}




