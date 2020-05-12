<?php
/**
 * Class for sorting records
 *
 * @filesource
 */

/**
 * Class that simplifies sorting of records.
 * It's closely connected from any template by {sortable} smarty helper.
 *
 *
 * Here is an example of usage.
 *
 * Within a controller's method:
 * ```
 * $sorting = new Atk14Sorting($this->params);
 * $sorting->add("name");
 * $sorting->add("created",array("reverse" => true));
 * $sorting->add("rank",array(
 * 	"ascending_ordering" => "rank DESC, id ASC",
 * 	"descending_ordering" => "rank ASC, id DESC",
 * ));
 * $finder = TableRecord::Finder(array(
 * 	"class_name" => "Book",
 * 	"order" => $sorting->getOrder(),
 * ));
 * $this->tpl_data["finder"] = $finder;
 * $this->sorting = $sorting;
 * ```
 *
 * In a template:
 * ```
 * <table>
 * 	<thead>
 * 		<tr>
 * 			{sortable key=name}<th>Name</th>{/sortable}
 * 			{sortable key=created}<th>Create date</th>{/sortable}
 * 			...
 * 		</tr>
 * 	</thead>
 * </table>
 * ```
 *
 * Atk14Sorting implements ArrayAccess which helps to simplify the configuration:
 * ```
 * $sorting = new Atk14Sorting($params);
 * $sorting["name"] = "name";
 * $sorting["title"] = "UPPER(name)";
 * $sorting["year"] = array("year ASC, id ASC", "year DESC, id DESC");
 * ```
 * @package Atk14\Core
 */
class Atk14Sorting implements ArrayAccess, IteratorAggregate, Countable {

	/**
	 * Stored parameters from constructor.
	 *
	 * @var Dictionary
	 */
	protected $_Params;

	/**
	 * @ignore
	 */
	protected $_Ordering = array();

	/**
	 * @ignore
	 */
	protected $_OrderingStrings = array();

	/**
	 * Constructor
	 *
	 * @param Dictionary $params Parameters from request
	 * - **order** - key to be used for `order` option in DbMole classes
	 * @param array $options
	 */
	function __construct($params = null,$options = array()){
		if(is_null($params)){
			$params = new Dictionary($GLOBALS["HTTP_REQUEST"]->getVars("PG"));
		}
		$this->_Params = $params;
	}

	/**
	 * Adds a sorting key which represents a table column by default. You can assign own definition to a key.
	 *
	 * First added key is the default sorting key.
	 *
	 * Basic usage
	 * ```
	 * $sorting->add("create_date");
	 * $sorting->add("create_date",array("reverse" => true));
	 * $sorting->add("title",array("order_by" => "UPPER(title)"));
	 * $sorting->add("title",array(
	 * 	"asc" => "UPPER(title), id",
	 * 	"desc" => "UPPER(title) DESC, id DESC"
	 * ));
	 * $sorting->add("title",array(
	 * 	"ascending_ordering" => "UPPER(title), id",
	 * 	"descending_ordering" => "UPPER(title) DESC, id DESC"
	 * ));
	 * $sorting->add("title","UPPER(title), id", "UPPER(title) DESC, id DESC");
	 * ```
	 *
	 * @param string $key Name of the key which can then be used in a template by {sortable} helper.
	 * @param string|array $options_or_asc_ordering string for sql definition of ascending ordering or array for options. see description of $options below.
	 * @param string $desc_ordering sql definition of descending ordering
	 * @param array $options Options to customize sorting
	 * - **order_by** -
	 * - **ascending_ordering** - specifies custom ascending ordering, eg. 'created,id asc'
	 * - **descending_ordering** - specifies custom descending ordering, eg. 'created,id desc'
	 * - **reverse** - used only in conjunction with `order_by` option. Reverts order for both descending and ascending ordering
	 * - **title** - string for the title attribute of the generated &lt;a /&gt; tag.
	 */
	function add($key,$options_or_asc_ordering = array(), $desc_ordering = null, $options = array()){
		$asc_ordering = null;

		if(is_array($desc_ordering)){
			$options = $desc_ordering;
			$desc_ordering = null;
		}

		if(is_array($options_or_asc_ordering)){
			$options = $options_or_asc_ordering;
		}

		if(is_string($options_or_asc_ordering)){
			$asc_ordering = $options_or_asc_ordering;
			if(!isset($desc_ordering)){
				$desc_ordering = preg_replace('/\sASC$/i','',$asc_ordering);
				$desc_ordering .= " DESC"; // TOTO: "name ASC, author ASC" -> "name DESC, author DESC"
			}
		}

		// shortcuts:
		//	 asc -> asc_ordering
		//	 desc -> desc_ordering
		foreach(array("asc","desc") as $_k){
			if(isset($options[$_k])){
				$options["{$_k}ending_ordering"] = $options[$_k];
				unset($options[$_k]);
			}
		}

		$options = array_merge(array(
			"order_by" => "$key",
			"ascending_ordering" => $asc_ordering,
			"descending_ordering" => $desc_ordering,
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
		$this->_OrderingStrings["$key"] = $options["ascending_ordering"];
		$this->_OrderingStrings["$key-asc"] = $options["ascending_ordering"]; // obsolete ascendant key, TODO: to be removed in the future
		$this->_OrderingStrings["$key-desc"] = $options["descending_ordering"];
	}

	/**
	 * IteratorAggregate intergate
	 *
	 * Iterates over keys of defined orderings.
	 */
	function getIterator() {
		return new ArrayIterator(array_keys($this->_Ordering));
	}

	/**
	 * Returns the ordering string.
	 *
	 * This returns the string to append to a query as the ORDER BY expression.
	 *
	 * @param string $key
	 * @return string the ordering key
	 */
	function getOrder($key = null){
		if(!$this->_Ordering){ return null; } // not even one ordering is set

		if(is_null($key)){
			$key = $this->_Params->g(ATK14_SORTING_PARAM_NAME,"string");
		}

		(isset($this->_OrderingStrings[$key])) || ($key = $this->_getDefaultKey());

		$this->_ActiveKey = $key;
		
		return $this->_OrderingStrings[$key];
	}

	/**
	 * Returns the ordering key when the order parameter is not present in url query.
	 *
	 * It is the first defined by {@link add()} method.
	 * Returns null when there are no keys added with that method.
	 *
	 * @return string|null
	 */
	private function _getDefaultKey(){
		$_ar = array_keys($this->_Ordering);
		if(!$_ar){ return null; }
		return "$_ar[0]";
	}

	/**
	 * Returns name of current sorting key
	 *
	 * @return string
	 */
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
	 */
	function getTitle($key){
		return $this->_Ordering[$key]["title"];
	}

	/**
	 * Returns the string representation of the objects' instance.
	 *
	 */
	function toString(){ return (string)$this->getOrder(); }

	/**
	 * Magical method to get string representation of the objects' instance.
	 *
	 * @return string
	 */
	function __toString(){ return $this->toString(); }

	/**
	 * Methods to implement ArrayAccess interface.
	 */

	/**
	 * @ignore
	 */
	function offsetExists($key){
		return isset($this->_Ordering[$key]);
	}

	/**
	 * $sorting["rank"] = "rank";
	 * $sorting["rank"] = array("rank ASC","rank DESC");
	 *
	 * @ignore
	 */
	function offsetSet($key,$value){
		if(is_array($value) && isset($value[0]) && isset($value[1])){
			$value = array(
				"ascending_ordering" => $value[0],
				"descending_ordering" => $value[1],
			);
		}
		return $this->add($key,$value);
	}

	/**
	 * @ignore
	 */
	function offsetGet($key){
		if(isset($this->_Ordering[$key])){
			return array(
				$this->_Ordering[$key]["ascending_ordering"],
				$this->_Ordering[$key]["descending_ordering"],
			);
		}
	}

	/**
	 * @ignore
	 */
	function offsetUnset($key){
		unset($this->_Ordering[$key]);
		unset($this->_OrderingStrings["$key"]);
		unset($this->_OrderingStrings["$key-asc"]);
		unset($this->_OrderingStrings["$key-dec"]);
	}

	/**
	 * Return the number of possible sort options.
	 * > echo count($sorting);
	 * > echo $sorting->count();
	 **/
	function count() {
		return count($this->_Ordering);
	}
}
