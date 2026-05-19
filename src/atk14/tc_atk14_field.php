<?php
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
class TcAtk14Field extends TcAtk14Base{

	public $field = null;

	/*
	function __construct($name = NULL, array $data = [), $dataName = ''){
		parent::__construct($name, $data, $dataName);

		$ref = new ReflectionClass("TcAtk14Base");
		$ref->newInstance(func_get_args());
	}
	*/

	function clean($value,&$err = null){
		list($err,$cleaned_value) = $this->field->clean($value);
		if(!is_null($err)){ $cleaned_value = null; }
		return $cleaned_value;
	}

	/**
	 *
	 * ```
	 * function test(){
	 *  $this->field = new IntegerField(["required" => false));
	 *  $cleaned_value = $this->assertValid(" 123 ");
	 *  $this->assertEquals(123,$cleaned_value);
	 * }
	 * ```
	 */
	function assertValid($value,$message = ""){
		$cleaned_value = $this->clean($value,$err);
		if(!is_null($err)){
			$this->fail("not valid: ".$this->___stringifyValue($value)." [$err]" . ($message ? " ($message)" : ""));
		}
		$this->assertTrue(true); // On success, just one valid assertion is needed.
		return $cleaned_value;
	}

	/**
	 *
	 * ```
	 * function test(){
	 *  $this->field = new IntegerField(["required" => false));
	 *  $err = $this->assertInvalid("abc");
	 *  $this->assertEquals("Enter a number",$err);
	 * }
	 * ```
	 */
	function assertInvalid($value,$message = null){
		$cleaned_value = $this->clean($value,$err);
		if(is_null($err)){
			$this->fail("valid: ".$this->___stringifyValue($value)." [".$this->___stringifyValue($cleaned_value)."]" . ($message ? " ($message)" : ""));
		}
		$this->assertTrue(true); // On success, just one valid assertion is needed.
		return $err;
	}

	private function ___stringifyValue($value){
		if(is_object($value)){
			if(method_exists($value,"__toString")){ return "$value"; }
			if(method_exists($value,"toString")){ return $value->toString(); }
			return "instance of ".get_class($value);
		}
		return "$value";
	}
}
