<?php
/**
 * Empty finder
 *
 * @package Atk14
 * @subpackage InternalLibraries
 * @filesource
 */

/**
 * Empty finder
 *
 * @package Atk14
 * @subpackage InternalLibraries
 */
class inobj_TableRecord_EmptyFinder extends inobj_TableRecord_Finder{
	function inobj_TableRecord_EmptyFinder(){
		
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
