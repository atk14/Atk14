<?php
if(!class_exists("TcSuperBase")){
	class TcSuperBase{ }
}

/**
 * Base class for fields testing
 *
 * <code>
 *	<?php
 *	// file: test/fields/tc_iso_date_field.php
 *	class TcIsoDateFieldField extends TcAtk14Field{
 *		function test(){
 *			$this->field = new IsoDateField();
 *			
 *			$this->assertEquals("2013-10-31",$this->clean("31.10.2013"));
 *			// or
 *			$this->assertValid("31.10.2013",$cleaned_value);
 *			$this->assertEquals("2013-10-31",$cleaned_value);
 *		}
 *	}
 * </code>
 */
class TcAtk14Field extends TcSuperBase{
	var $field = null;

	function __construct(){
		$ref = new ReflectionClass("TcSuperBase");
		$ref->newInstance(func_get_args());

		$this->dbmole = $GLOBALS["dbmole"];
	}

	function clean($value,&$err = null){
		list($err,$cleaned_value) = $this->field->clean($value);
		if(!is_null($err)){ $cleaned_value = null; }
		return $cleaned_value;
	}

	function assertValid($value,$message = ""){
		$cleaned_value = $this->clean($value,$err);
		if(!is_null($err)){
			$this->fail("not valid: $value [$err]" . ($message ? " ($message)" : ""));
		}
		return $cleaned_value;
	}

	function assertInvalid($value,$message = null){
		$cleaned_value = $this->clean($value,$err);
		if(is_null($err)){
			$this->fail("valid: $value [$cleaned_value]" . ($message ? " ($message)" : ""));
		}
		return $err;
	}
}
