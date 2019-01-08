<?php
/**
 * Implementation of dictionary to store key => value pairs
 *
 * @filesource
 */
/**
 * Implementation of dictionary to store key => value pairs
 *
 * Basic usage:
 * ```
 * $dict = new Dictionary(array(
 * 	"key1" => "value1",
 * 	"key2" => "value2",
 * 	"key3" => "value3"
 * ));
 *
 * echo $dict->getValue("key1");
 * $dict->setValue("key4","new value");
 *
 * if($dict->defined("key1")){
 * 	//...
 * }
 * ```
 *
 * @package Atk14\Dictionary
 */
class Dictionary implements ArrayAccess, Iterator, Countable{


	const VERSION = "1.0";

	/**
	 * Internal storage of values
	 *
	 * @var array
	 */
	protected $_Values = array();

	/**
	 * By default the constructor initializes empty dictionary. The initial dictionary can be defined by array passed to constructor.
	 *
	 * @param array $ar initial array
	 */
	function __construct($ar = array()){
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
	 * Returns keys of dictionary data as associative or indexed array
	 *
	 *
	 * ```
	 * $keys = $user_data_dict->getKeys(); // array("firstname" => "firstname", "lastname" => "lastname", "email" => "email")
	 * $keys = $user_data_dict->getKeys(array("as_hash" => false)); // array("firstname", "lastname", "email")
	 * ```
	 */
	function getKeys($options = array()){
		$options += array(
			"as_hash" => true,
		);
		$keys = array_keys($this->_Values);
		if($options["as_hash"]){
			$keys = array_combine($keys,$keys);
		}
		return $keys;
	}

	/**
	 *
	 * Returns value from the distionary.
	 *
	 * Returns value from the dictionary specified by $key. Returned value can be retyped by passing $type parameter.
	 * Parameter $type recognizes same values as PHP.
	 *
	 * ```
	 * $dictionary->getValue("user_id", "integer");
	 * ```
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
	 * Alias to method getValue().
	 *
	 * @param string $key
	 * @param string $type
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

	/**
	 * Get value from dictionary converted to float.
	 *
	 * @param string $key
	 * @return float
	 */
	function getFloat($key){ return $this->g($key,"float"); }

	/**
	 * Get value from dictionary converted to boolean.
	 *
	 * @param string $key
	 * @return boolean
	 */
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
	 * @param string $key
	 * @param mixed $value
	 * @uses setValue()
	 */
	function s($key,$value){ return $this->setValue($key,$value); }

	/**
	 * Alias for setValue()
	 *
	 * @param string $key
	 * @param mixed $value
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

	/**
	 * Return key names set in dictionary.
	 *
	 * @return array
	 */
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

	/**
	 * Checks if a key is present in dictionary
	 *
	 * @param string $key
	 * @return boolean
	 */
	function keyPresents($key){
		return in_array($key,array_keys($this->_Values));
	}

	/**
	 * Merges another Dictionary into the current.
	 *
	 * Takes another {@link Dictionary Dictionary} or {@link array} and merges its values with values in current {@link Dictionary}.
	 * Values in the merged/passed Dictionary override values in current Dictionary.
	 *
	 * @param Dictionary|array $ary
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

	/**
	 * Alias to method unsetValue().
	 *
	 * @param string $key
	 */
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
	 * First element will have the key 'color' and value 'green'
	 * ```
	 * $dict->unshift("color","green");
	 * ```
	 *
	 * ```
	 * $dict->toArray();
	 * ```
	 *
	 *
	 * @param string $key
	 * @param string $value
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
	
	
	/*** functions implementing array like access ***/
	/**
	 * @ignore
	 */
	function offsetGet($value){ return $this->getValue($value);	}

	/**
	 * @ignore
	 */
	function offsetSet($key, $value){
		if(is_null($key)){
			$keys = array_keys($this->_Values);
			$keys = array_filter($keys,function($k){ return is_numeric($k); });
			sort($keys,SORT_NUMERIC);
			$key = $keys ? array_pop($keys) + 1 : 0;
		}
		$this->setValue($key, $value);
	}

	/**
	 * @ignore
	 */
	function offsetUnset($value){ $this->unsetValue($value);	}

	/**
	 * @ignore
	 */
	function offsetExists($value){ return $this->defined($value);	}

	/**
	 * @ignore
	 */
	function current(){ return current($this->_Values); }

	/**
	 * @ignore
	 */
	function key(){ return key($this->_Values); }

	/**
	 * @ignore
	 */
	function next(){ return next($this->_Values); }

	/**
	 * @ignore
	 */
	function rewind(){ reset($this->_Values); }

	/**
	 * @ignore
	 */
	function valid(){
		$key = key($this->_Values);
		return ($key !== null && $key !== false);
	}

	/**
	 * @ignore
	 */
	function count(){ return $this->size(); }
}
