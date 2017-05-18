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
	var $field = null;

	/*
	function __construct($name = NULL, array $data = array(), $dataName = ''){
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
	 *  $this->field = new IntegerField(array("required" => false));
	 *  $cleaned_value = $this->assertValid(" 123 ");
	 *  $this->assertEquals(123,$cleaned_value);
	 * }
	 * ```
	 */
	function assertValid($value,$message = ""){
		$cleaned_value = $this->clean($value,$err);
		if(!is_null($err)){
			$this->fail("not valid: $value [$err]" . ($message ? " ($message)" : ""));
		}
		return $cleaned_value;
	}

	/**
	 *
	 * ```
	 * function test(){
	 *  $this->field = new IntegerField(array("required" => false));
	 *  $err = $this->assertInvalid("abc");
	 *  $this->assertEquals("Enter a number",$err);
	 * }
	 * ```
	 */
	function assertInvalid($value,$message = null){
		$cleaned_value = $this->clean($value,$err);
		if(is_null($err)){
			$this->fail("valid: $value [$cleaned_value]" . ($message ? " ($message)" : ""));
		}
		return $err;
	}
}
