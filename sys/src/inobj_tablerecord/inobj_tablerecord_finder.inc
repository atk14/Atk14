<?
/**
* $finder = inobj_TableRecord::Finder(array(
*		"class_name" => "Books",
*		"conditions" => array("title LIKE :title"),
*		"bind_ar" => array(":title" => "%British%"),
*		"limit" => 10,
*		"offset" => 0,
*	));
*
* $finder->getRecordsCount(); // celkovy pocet nalezenych zaznamu
* $finder->getRecords(); // pole objektu, max velikost je omezena nastavenim "limit"
*/
class inobj_TableRecord_Finder{

	/**
	* @access private 
	*/
	function inobj_TableRecord_Finder($options,&$dbmole){
		$this->_Query = $options["query"];
		$this->_QueryCount = $options["query_count"];
		$this->_BindAr = $options["bind_ar"];
		$this->_QueryOptions = $options["options"];
		$this->_ClassName = $options["class_name"];

		$this->_dbmole = &$dbmole;
	}

	function getRecords(){
		if(!isset($this->_Records)){
			$this->_Records = call_user_func_array(array($this->_ClassName,"GetInstanceById"),array($this->_dbmole->selectIntoArray($this->_Query,$this->_BindAr,$this->_QueryOptions)));
		}
		return $this->_Records;
	}

	function getRecordsCount(){
		if(!isset($this->_RecordsCount)){
			$options = $this->_QueryOptions;
			unset($options["limit"]);
			unset($options["offset"]);
			$options["type"] = "integer";
			$this->_RecordsCount = $this->_dbmole->selectSingleValue($this->_QueryCount,$this->_BindAr,$options);
		}
		return $this->_RecordsCount;
	}
}
