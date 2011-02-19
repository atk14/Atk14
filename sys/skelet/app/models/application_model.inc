<?
/**
* The base class of all the application models.
* Do you have any common methods or attributes for all your models? Put them right here.
* Otherwise there's no need to care :)
*/
class ApplicationModel extends inobj_TableRecord{
	
	/**
	* Converts object into XML.
	* 
	* @return string
	*/
	function toXml(){
		$class_name = new String(get_class($this));
		$root = $class_name->underscore(); // "LittleKitty" turns into "little_kitty"
		$out = array();
		$out[] = "<$root>";
		foreach($this->toExportArray() as $k => $v){
			$out[] = "<$k>".XMole::ToXml($v)."</$k>"; // escaping $v to be placed inside XML
		}
		$out[] = "</$root>";
		return join("\n",$out);
	}

	/**
	* Converts object into JSON.
	* 
	* @return string
	*/
	function toJson(){
		return json_encode($this->toExportArray());
	}

	/**
	* Returns associative array with object`s attributes and their values.
	* This array is used for exporting object as XML or JSON.
	* 
	* Cover it in a given class if you want to return something else than just $object->toArray().
	* 
	* @return array
	*/
	function toExportArray(){ return $this->toArray(); }
}
