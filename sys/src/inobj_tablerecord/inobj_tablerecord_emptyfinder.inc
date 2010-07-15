<?
class inobj_TableRecord_EmptyFinder extends inobj_TableRecord_Finder{
	function inobj_TableRecord_EmptyFinder(){
		
	}

	function getRecordsCount(){ return 0; }
	function getRecords(){ return array(); }
}
