<?
/**
* Implementace slovniku pro uchovavani dvojic klic - hodnota.
*
*
* $dict = new Dictionary(array(
*		"key1" => "value1",
*		"key2" => "value2",
*		"key3" => "value3"
*	));
*
* echo $dict->getValue("key1");
*
*	$dict->setValue("key4","new value");
*
* if($dict->defined("key1")){
*		//...
*	}
*/
class Dictionary{
	var $_Values = array();

	/**
	* Konstruktor.
	*
	* @param array $ar					asociativni pole
	*/
	function Dictionary($ar = array()){
		$this->_Values = $ar;
	}

	function toArray(){
		return $this->_Values;
	}

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
	function g($key,$type = null){ return $this->getValue($key,$type); }

	// some shortcuts
	function getInt($key){ return $this->g($key,"integer"); }
	function getString($key){ return $this->g($key,"string"); }
	function getArray($key){ return $this->g($key,"array"); }

	function setValue($key,$value){
		$this->_Values[$key] = $value;
	}
	function s($key,$value){ return $this->setValue($key,$value); }

	function unsetValue($key){
		unset($this->_Values[$key]);
	}

	function defined($key){
		return isset($this->_Values[$key]);
	}
	
	function contains($key){ return $this->defined($key); }

	function keyPresents($key){
		return in_array($key,array_keys($this->_Values));
	}

	function merge($ary){
		if(is_object($ary)){ $ary = $ary->toArray(); }
		$this->_Values = array_merge($this->_Values,$ary);
	}

	function delete($key){
		unset($this->_Values[$key]);
	}

	function size(){
		return sizeof($this->_Values);
	}

	function isEmpty(){ return $this->size()==0; }

	/**
	* Prida hodnotu na zacatek pole.
	* $dict->unshif("color","green");
	* $dict->toArray(); // prvni klic a hodnota bude "color" resp. "green"
	*/
	function unshift($key,$value){
		$out = array($key => $value);
		foreach($this->_Values as $key => $value){
			$out[$key] = $value;
		}
		$this->_Values = $out;
	}

	function copy(){
		return new Dictionary($this->toArray());
	}
}
?>
