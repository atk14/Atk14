<?php
/**
 * Empty finder
 *
 * @package Atk14\TableRecord
 * @filesource
 */

/**
 * Empty finder
 *
 * @package Atk14\TableRecord
 */
class TableRecord_EmptyFinder extends TableRecord_Finder{
	protected $_Records = array();
	function __construct($options = array()){
		$options += array(
			"options" => array()
		);
		$options["options"] += array(
			"offset" => null,
			"limit" => null,
		);

		$this->_QueryOptions = $options["options"];
	}

	/**
	 * @return integer always returns 0
	 */
	function getRecordsCount(){ return 0; }

		/**
		 * @return array always returns empty array
		 */
	function getRecords(){ return array(); }
}
