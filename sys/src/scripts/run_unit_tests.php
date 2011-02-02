#!/usr/bin/env php
<?
/*
* V pracovnim adresari vyhleda soubory tc_*.inc. Ty postupne nainkluduje a spusti v nich testy.
*
* V souboru tc_inobj_currency.inc se ocekava trida tc_inobj_currency.
*
* Na prikazove radce mozno definovat seznam testu, ktere se maji provest:
*		$ run_unit_tests.php tc_inobj_account tc_inobj_cznicdomain_create_pending
*		pripadne i s priponou .inc
*		$ run_unit_tests.php tc_inobj_account.inc tc_inobj_cznicdomain_create_pending.inc
*
* Nebezpecne testy
* ----------------
* Jsou to takove testy, ktere zmeni/nastavi neco, co muze mit nejake nasledky. Napriklad testy pro registraci domeny.
* Takove testy umistime do souboru zacinajici vykricnikem, napr. !tc_domain_registration.inc
*
* Test nebude automaticky spusten, pokud nebude implicitne uveden na prikazove radce:
*		$ run_unit_tests.php
*		$ run_unit_tests.php	\!tc_domain_registration.inc
*/

error_reporting(255);

// v PHP5.3 neexistuje $_ENV["PWD"] ??
isset($_ENV["PWD"]) && chdir($_ENV["PWD"]);

if(preg_match("/^4/",phpversion())){
	define("PHP4",true);
	define("PHP5",false);
}
if(preg_match("/^5/",phpversion())){
	define("PHP4",false);
	define("PHP5",true);
}

if(PHP4){
	require_once 'PHPUnit.php';
}
if(PHP5){
	require_once 'PHPUnit2/Framework/TestSuite.php';
	require_once 'PHPUnit2/Framework/TestCase.php';
	require_once 'PHPUnit2/TextUI/ResultPrinter.php';
	require_once 'Benchmark/Timer.php';
}

isset($argv) || $argv = array();

$RUN_TESTS_ONLY = array();
for($i=1;$i<sizeof($argv);$i++){
	$RUN_TESTS_ONLY[] = $argv[$i];
}

if(PHP4){
	if(file_exists("tc_base.inc")){
		eval("class tc_super_base extends PHPUnit_TestCase{ }");
		require_once("tc_base.inc");
	}else{
		eval("class tc_base extends PHPUnit_TestCase{ }");
	}
}
if(PHP5){
	if(file_exists("tc_base.inc")){
		eval("class tc_super_base extends PHPUnit2_Framework_TestCase{ }");
		require_once("tc_base.inc");
	}else{
		eval("class tc_base extends PHPUnit2_Framework_TestCase{ }");
	}
}

if(file_exists("initialize.inc")){ require_once("initialize.inc"); }

if(class_exists("pgmole")){	
	$dbmole = &PgMole::GetInstance();
	DbMole::RegisterErrorHandler("_test_dbmole_error_handler");
}
if(class_exists("oraclemole")){
	$dbmole = &OracleMole::GetInstance();
	DbMole::RegisterErrorHandler("_test_dbmole_error_handler");
}
if(class_exists("inobj")){
	inobj::RegisterErrorCallback("_test_inobj_error_handler");
}

$ALLOWED_TESTS = array();
$ALLOWED_DANGEROUS_TESTS = array();
$tests_to_execute = array();

$dir = opendir("./");
while($file = readdir($dir)){
	if(in_array($file,array(".","..","initialize.inc","state.inc","tc_base.inc"))){ continue; } // tyto souboryu ignorujeme

	if(preg_match("/^(tc_.*)\\.inc$/",$file,$matches)){
		$ALLOWED_TESTS[$file] = $matches[1];
	}elseif(preg_match("/^!(tc_.*)\\.inc$/",$file,$matches)){
		$ALLOWED_DANGEROUS_TESTS[$file] = $matches[1];
	}
}
closedir($dir);

ksort($ALLOWED_TESTS);

reset($ALLOWED_TESTS);
while(list($filename,$classname) = each($ALLOWED_TESTS)){
	if(sizeof($RUN_TESTS_ONLY)>0 && (!in_array($filename,$RUN_TESTS_ONLY) && !in_array($classname,$RUN_TESTS_ONLY))){
		continue;
	}

	//_test_runner($filename);
	$tests_to_execute[] = $filename;
}

reset($ALLOWED_DANGEROUS_TESTS);
while(list($filename,$classname) = each($ALLOWED_DANGEROUS_TESTS)){
	if((!in_array($filename,$RUN_TESTS_ONLY) && !in_array($classname,$RUN_TESTS_ONLY))){
		continue;
	}

	//_test_runner($filename);
	$tests_to_execute[] = $filename;
}

if(sizeof($tests_to_execute)==1){
	_test_runner($tests_to_execute[0]);
	exit;
}


foreach($tests_to_execute as $_f){
	$cmd = escapeshellcmd($argv[0])." ".escapeshellarg($_f)." 2>&1";
	if(isset($_SERVER["_"])){
		// v $_SERVER["_"] je interpret PHP: /usr/bin/php
		$cmd = escapeshellcmd($_SERVER["_"])." ".escapeshellarg($argv[0])." ".escapeshellarg($_f)." 2>&1";
	}
	passthru($cmd);
}

function _test_dbmole_error_handler($dbmole){
	echo "An error comes from DbMole\n";
	echo "database type: ".$dbmole->getDatabaseType()."\n";
	echo "message ".$dbmole->getErrorMessage()."\n";
	echo "query: ".$dbmole->getQuery()."\n";
	echo "bind_ar:\n";
	print_r($dbmole->getBindAr());
	echo "options:\n";
	print_r($dbmole->getOptions());
	exit;
}

function _test_inobj_error_handler($values){
	echo "An error comes from inobj\n";
	print_r($values);
	exit;
}

function _test_runner($filename){
	$classname = preg_replace("/\\.[^.]*$/","",$filename);
	$classname = preg_replace("/^!/","",$classname);

	require($filename);

	if(!class_exists($classname)){
		echo "--- $filename\n";
		echo "!!! class $classname doesn't exist\n";
		return;
	}

	if(PHP4){
		$suite  = new PHPUnit_TestSuite($classname);
		$result = PHPUnit::run($suite);
		echo "--- $filename\n";
		print $result->toString();
	}
	if(PHP5){
		$timer  = new Benchmark_Timer;
		$printer = new PHPUnit2_TextUI_ResultPrinter;

		$suite  = new PHPUnit2_Framework_TestSuite(
			new ReflectionClass($classname)
		);

		$timer->start();
		$result = $suite->run();
		$timer->stop();

		echo "--- $filename\n";
		$printer->printResult($result, $timer->timeElapsed());
	}
}
?>
