<?
/**
 * Class for sorting records
 *
 * @package Atk14
 * @subpackage Core
 *
 */

/**
 * Class that simplifies sorting of records.
 * It's closely connected from any template by {sortable} smarty helper.
 *
 *
 * Here is example of use.
 *
 * Within a controller's method:
 *
 * <code>
 *	$sorting = new Atk14Sorting($this->params);
 *	$sorting->add("name");
 *	$sorting->add("created",array("reverse" => true));
 *	$sorting->add("rank",array("ascending_ordering" => "rank DESC, id ASC", "descending_ordering" => "rank ASC, id DESC"));
 *
 *	$finder = inobj_TableRecord::Finder(array(
 *		"class_name" => "Book",
 *		"order" => $sorting->getOrder(),
 *	));
 *	
 *	$this->tpl_data["finder"] = $finder;
 * 	$this->sorting = $sorting;
 * 	</code>
 *
 * Within a template:
 *	
 *	<code>
 *		<table>
 *			<thead>
 *				<tr>
 *					{sortable key=name}<th>Name</th>{/sortable}
 *					{sortable key=created}<th>Create date</th>{/sortable}
 *					...
 *				</tr>
 *			</thead>
 *		</table>
 *	</code>
 * @package Atk14
 * @subpackage Core
 * 
 */
class Atk14Sorting{
	/**
	 * @access private
	 */
	var $_Ordering = array();

	/**
	 * @access private
	 */
	var $_OrderingStrings = array();

	/**
	 * Constructor
	 * @param array $params Parameters from request
	 */
	function Atk14Sorting($params,$options = array()){
		$this->_Params = $params;
	}

	/**
	 * Adds a sorting key which represents a table column by default. You can assign own definition to a key.
	 *
	 * @param string $key Name of the key which can then be used in a template by {sortable} helper.
	 * @param array $options Options to customize sorting
	 * <ul>
	 * 	<li>order_by - </li>
	 * 	<li>ascending_ordering - specifies custom ascending ordering, eg. 'created,id asc'</li>
	 * 	<li>descending_ordering - specifies custom descending ordering, eg. 'created,id desc'</li>
	 * 	<li>reverse - </li>
	 * 	<li>title - string for the title attribute of the generated <a /> tag.</li>
	 * </ul>
	 *
	 */
	function add($key,$options = array()){
		$options = array_merge(array(
			"order_by" => "$key",
			"ascending_ordering" => null,
			"descending_ordering" => null,
			"title" => _("Sort table by this column"),
			"reverse" => false,
		),$options);

		if(!isset($options["ascending_ordering"])){
			$options["ascending_ordering"] = "$options[order_by] ".($options["reverse"] ? "DESC" : "ASC");
		}
		if(!isset($options["descending_ordering"])){
			$options["descending_ordering"] = "$options[order_by] ".($options["reverse"] ? "ASC" : "DESC");
		}

		$this->_Ordering[$key] = $options;
		$this->_OrderingStrings["$key-asc"] = $options["ascending_ordering"];
		$this->_OrderingStrings["$key-desc"] = $options["descending_ordering"];
	}

	/**
	 * Returns the ordering key.
	 * It is a string and this form is suitable for usage in any finding method used by {@link DbMole}.
	 *
	 * @return string the ordering key
	 *
	 */
	function getOrder(){
		(($key = $this->_Params->g("order","string")) && isset($this->_OrderingStrings[$key])) || ($key = $this->_getDefaultKey());

		$this->_ActiveKey = $key;
		
		return $this->_OrderingStrings[$key];
	}

	/**
	 * @access private
	 */
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

	/**
	 * Returns string which is used to describe the sorting link.
	 *
	 * @param string $key Name of the key
	 * @return string Text shown on the sorting link
	 *
	 */
	function getTitle($key){
		return $this->_Ordering[$key]["title"];
	}
}
?>
