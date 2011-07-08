<?php
/**
 * Implementation of dictionary to store key => value pairs
 *
 * @package Atk14
 * @subpackage InternalLibraries
 * @filesource
 */
/**
 * Implementation of dictionary to store key => value pairs
 *
 * Basic usage:
 * <code>
 * $dict = new Dictionary(array(
 *  "key1" => "value1",
 *  "key2" => "value2",
 *  "key3" => "value3"
 * ));
 *
 * echo $dict->getValue("key1");
 * $dict->setValue("key4","new value");
 *
 * if($dict->defined("key1")){
 * //...
 * }
 * </code>
 *
 * @package Atk14
 * @subpackage InternalLibraries
 */
class Dictionary{

	/**
	 * Internal storage of values
	 *
	 * @access private
	 */
	var $_Values = array();

	/**
	 * By default the constructor initializes empty dictionary. The initial dictionary can be defined by array passed to constructor.
	 *
	 * @param array $ar initial array
	 */
	function Dictionary($ar = array()){
		$this->_Values = $ar;
	}

	/**
	 * Returns all Dictionary entries as array.
	 *
	 * @return array
	 */
	function toArray(){
		return $this->_Values;
	}

	/**
	 *
	 * Returns value from the distionary.
	 *
	 * Returns value from the dictionary specified by $key. Returned value can be retyped by passing $type parameter.
	 * Parameter $type recognizes same values as PHP.
	 *
	 * <code>
	 * $dictionary->getValue("user_id", "integer");
	 * </code>
	 *
	 * @param string $key
	 * @param string $type
	 * @return mixed
	 */
	function getValue($key,$type = null){
		if(!isset($this->_Values[$key])){
			return null;
		}
		if(isset($type)){
			$_out = $this->_Values[$key];
			settype($_out,$type);
			return $_out;
		}
		return $this->_Values[$key];
	}

	/**
	 * Alias to method {@link getValue()}.
	 *
	 * @return mixed
	 * @uses getValue()
	 */
	function g($key,$type = null){ return $this->getValue($key,$type); }

		// some shortcuts

	/**
	 * Shortcut to method getValue().
	 *
	 * This call is a shorter variant of the call getValue("key", "integer").
	 *
	 * @param string $key
	 * @return integer
	 * @uses getValue()
	 */
	function getInt($key){ return $this->g($key,"integer"); }
	function getFloat($key){ return $this->g($key,"float"); }
	function getBool($key){
		$value = $this->g($key);
		if(!isset($value)){ return null; }
		return
			in_array(strtoupper($value),array("Y","YES","YUP","T","TRUE","1","ON","E","ENABLE","ENABLED")) ||
			(is_numeric($value) && $value>0);
	}
	
	/**
	 * Shortcut to method getValue().
	 *
	 * This call is a shorter variant of the call getValue("key", "string").
	 *
	 * @param string $key
	 * @return string
	 * @uses getValue()
	 */
	function getString($key){ return $this->g($key,"string"); }

	/**
	 * Shortcut to method getValue().
	 *
	 * This call is a shorter variant of the call getValue("key", "array").
	 *
	 * @param string $key
	 * @return array
	 * @uses getValue()
	 */
	function getArray($key){ return $this->g($key,"array"); }

	/**
	 * Sets value in the dictionary.
	 *
	 * @param string $key
	 * @param mixed $value
	 */
	function setValue($key,$value){
		$this->_Values[$key] = $value;
	}

	/**
	 * Shortcut to method setValue().
	 *
	 * @uses setValue()
	 */
	function s($key,$value){ return $this->setValue($key,$value); }

	/**
	 * Alias for setValue()
	 *
	 * @uses setValue()
	 */
	function add($key,$value){ return $this->setValue($key,$value); }

	/**
	 * Unsets value in the dictionary.
	 *
	 * Unsets / removes a value from the dictionary specified by $key.
	 *
	 * @param string $key
	 */
	function unsetValue($key){
		unset($this->_Values[$key]);
	}

	/**
	 * Checks if a key is in the dictionary.
	 *
	 * @param string $key
	 * @return bool
	 */
	function defined($key){
		return isset($this->_Values[$key]);
	}

	function keys(){
		return array_keys($this->_Values);
	}
	
	/**
	 * Alias to method defined().
	 *
	 * @param string $key
	 * @return bool
	 * @uses defined()
	 */
	function contains($key){ return $this->defined($key); }

	function keyPresents($key){
		return in_array($key,array_keys($this->_Values));
	}

	/**
	 * Merges another Dictionary.
	 *
	 * Takes another {@link Dictionary} or {@link array} and merges its values with values in current {@link Dictionary}.
	 * Values in the merged/passed Dictionary override values in current Dictionary.
	 *
	 * @param Dictionary|array
	 */
	function merge($ary){
		if(is_object($ary)){ $ary = $ary->toArray(); }
		if(!isset($ary)){ return; }
		$this->_Values = array_merge($this->_Values,$ary);
	}

	/**
	 * Alias to method unsetValue().
	 *
	 * @param string $key
	 */
	function delete($key){ return $this->unsetValue($key); }

	function del($key){ return $this->unsetValue($key); }

	/**
	 * Checks size of Dictionary.
	 *
	 * @return integer
	 */
	function size(){
		return sizeof($this->_Values);
	}

	/**
	 * Checks if the dictionary is empty.
	 *
	 * Returns true if there are no values in Dictionary.
	 *
	 * @return bool
	 */
	function isEmpty(){ return $this->size()==0; }

	/**
	 * Checks if the dictionary is empty.
	 *
	 * Returns true if there are values in Dictionary.
	 *
	 * @return bool
	 */
	function notEmpty(){ return !$this->isEmpty(); }

	/**
	 * Adds value to the beginning of array.
	 *
	 * <code>
	 * $dict->unshift("color","green");
	 * $dict->toArray(); // prvni klic a hodnota bude "color" resp. "green"
	 * </code>
	 * First returned key should be "color" with value "green".
	 *
	 */
	function unshift($key,$value){
		$out = array($key => $value);
		foreach($this->_Values as $key => $value){
			$out[$key] = $value;
		}
		$this->_Values = $out;
	}

	/**
	 * Clones the dictionary object.
	 *
	 * @return Dictionary
	 */
	function copy(){
		return new Dictionary($this->toArray());
	}
}
