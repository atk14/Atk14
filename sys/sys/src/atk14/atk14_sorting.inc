<?
class Atk14Sorting{
	var $_Ordering = array();
	var $_OrderingStrings = array();
	function Atk14Sorting($params,$options = array()){
		$this->_Params = $params;
	}

	function add($key,$options = array()){
		$options = array_merge(array(
			"order_by" => "$key",
			"ascending_ordering" => null,
			"descending_ordering" => null,
			"title" => _("Sort table by this column"),
		),$options);

		if(!isset($options["ascending_ordering"])){
			$options["ascending_ordering"] = "$options[order_by] ASC";
		}
		if(!isset($options["descending_ordering"])){
			$options["descending_ordering"] = "$options[order_by] DESC";
		}

		$this->_Ordering[$key] = $options;
		$this->_OrderingStrings["$key-asc"] = $options["ascending_ordering"];
		$this->_OrderingStrings["$key-desc"] = $options["descending_ordering"];
	}

	function getOrder(){
		(($key = $this->_Params->g("order","string")) && isset($this->_OrderingStrings[$key])) || ($key = $this->_getDefaultKey());

		$this->_ActiveKey = $key;
		
		return $this->_OrderingStrings[$key];
	}

	function _getDefaultKey(){
		$_ar = array_keys($this->_Ordering);
		return "$_ar[0]-asc";
	}

	function getActiveKey(){
		if(!isset($this->_ActiveKey)){
			$this->getOrder();
		}
		return $this->_ActiveKey;
	}

	function getTitle($key){
		return $this->_Ordering[$key]["title"];
	}
}
?>
