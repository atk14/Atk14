#!/usr/bin/env php
<?
/**
 * Generates Atk14 documentation
 *
 * Just run ./generate_documentation.php
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

$src_dir = $ATK14_GLOBAL->getApplicationPath()."../sys/src/atk14";
$output_dir = $ATK14_GLOBAL->getApplicationPath()."../tmp/documentation";

if (!file_exists($output_dir)) {
	files::mkdir($output_dir, &$err, &$err_str);
}
var_dump($src_dir);
var_dump($output_dir);
exit();

$command = "phpdoc -o HTML:frames:DOM/phpdoc.de -d $src_dir -t $output_dir";

passthru($command);



