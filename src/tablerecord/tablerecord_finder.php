<?php
/**
 *
 * @package Atk14
 * @subpackage InternalLibraries
 * @filesource
 *
 */

/**
 * Class for finding records.
 *
 * Initialization of Finder:
 * <code>
 * $finder = TableRecord::Finder(array(
 *		"class_name" => "Books",
 *		"conditions" => array("title LIKE :title"),
 *		"bind_ar" => array(":title" => "%British%"),
 *		"limit" => 10,
 *		"offset" => 0,
 *	));
 * </code>
 *
 * This call returns total number of found records:
 * <code>
 * $finder->getRecordsCount();
 * </code>
 *
 * Get records. Number of records is limited by option limit
 * <code>
 * $finder->getRecords(); // pole objektu, max velikost je omezena nastavenim "limit"
 * </code>
 *
 *
 * @package Atk14
 * @subpackage InternalLibraries
 *
 */
class TableRecord_Finder implements ArrayAccess, Iterator, Countable {

	/**
	 *
	 * @access private 
	 * @param array $options
	 * <ul>
	 * 	<li><b>query</b> - custom query</li>
	 * 	<li><b>query_count</b> - custom query for counting records</li>
	 * 	<li><b>bind_ar</b> - array of binding parameters</li>
	 * 	<li><b>class_name</b> - specifies class of returned objects</li>
	 * 	<li><b>options</b></li>
	 * </ul>
	 * @param DbMole $dbmole
	 *
	 */
	function TableRecord_Finder($options,&$dbmole){
		$this->_Query = $options["query"];
		$this->_QueryCount = $options["query_count"];
		$this->_BindAr = $options["bind_ar"];
		$this->_QueryOptions = $options["options"];
		$this->_ClassName = $options["class_name"];

		$this->_dbmole = &$dbmole;
	}

	/**
	 * Gets found records.
	 *
	 * Returns array of records of specified class.
	 *
	 * @return array array of records
	 */
	function getRecords(){
		if(!isset($this->_Records)){
			$this->_Records = call_user_func_array(array($this->_ClassName,"GetInstanceById"),array($this->_dbmole->selectIntoArray($this->_Query,$this->_BindAr,$this->_QueryOptions)));
		}
		return $this->_Records;
	}

	/**
	 * Gets total amount of found records.
	 *
	 * @return integer
	 */
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

	/**
	 * Gets total amount of found records.
	 *
	 * Alias to method {@link getRecordsCount()}.
	 *
	 * @return integer
	 * @uses getRecordsCount()
	 */
	function getTotalAmount(){
		return $this->getRecordsCount();
	}

	/**
	 * Checks if the returned recordset was empty.
	 *
	 * @return bool true when no records were found otherwise false
	 */
	function isEmpty(){ return $this->getTotalAmount()==0; }
	function notEmpty(){ return !$this->isEmpty(); }

	/**
	 *
	 * @return integer
	 */
	function getLimit(){
		return $this->_QueryOptions["limit"];
	}

	/**
	 * @return integer
	 */
	function getOffset(){
		return $this->_QueryOptions["offset"];
	}
	
	
	/*** functions implementing array like access ***/
	function offsetGet($value)
	{
		$x=$this->getRecords();
		return $x[$value];
	}
	
	function offsetSet($value, $name)	{
		$this->getRecords();
		$this->_Records[$name]=$value;	
	}
	
	function offsetUnset($value)	{
		$this->getRecords();
		unset($this->_Records[$name]);	
	}
	
	function offsetExists($value) 	{
		$this->getRecords();
		return array_key_exists($name, $this->_Records);				
	}
	
	/*** functions implementing iterator like access (foreach cycle)***/
	public function current()		{
		return current($this->_Records);
	}
		
	public function key()	{
		return key($this->_Records);
	}
	public function next() {
		return next($this->_Records);
	}
  public function rewind() {
   $this->getRecords();
 	 return reset($this->_Records);
	} 
	public function valid()	{
		return isset($this->_Records) && current($this->_Records);
	}
	
	public function count()
	{
		$this->GetRecords();
		return count($this->_Records);
	}
}
