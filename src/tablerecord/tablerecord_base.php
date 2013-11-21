<?php
/**
 * Basic class for manipulating records.
 *
 * @package Atk14
 * @subpackage TableRecord
 * @filesource
 */

/**
* define("INOBJ_TABLERECORD_CACHES_STRUCTURES",60 * 60); // cachovani struktur po dobu 1 hodiny
*/

/**
 * Basic class for manipulating records.
 *
 * @package Atk14
 * @subpackage TableRecord
 */
class TableRecord_Base extends inobj{
	/**
	 * Name of database table.
	 *
	 * Is filled in constructor.
	 *
	 * @access private
	 * @var string
	 */
	var $_TableName = "";

	/**
	 * Name of sequence used by primary key of the table specified in $_TableName.
	 *
	 * @access private
	 * @var string
	 */
	var $_SequenceName = "";

	/**
	 * Id of the record
	 *
	 * @access private
	 * @var integer|string
	 */
	var $_Id = null;

	/**
	 * Name of table column used as primary key.
	 *
	 * Default name is 'id' but can be changed in constructor.
	 *
	 * @access private
	 * @var string
	 */
	var $_IdFieldName = "id";

	/**
	 * Type of primary key column.
	 *
	 * By default integer is used but can be changed in constructor.
	 * 
	 * @var string
	 * @access private
	 */
	var $_IdFieldType = "integer";

	/**
	 * Structure of table
	 *
	 * @var array
	 * @access private
	 */
	var $_TableStructure = null;

	/**
	 * Columns which values shouldn't be read in during instantiation of object.
	 *
	 * If some columns shouldn't be read in from a table (ie.for performance purposes) specify it here.
	 *
	 * @var array
	 * @access private
	 */
	var $_DoNotReadValues = array(); // pole, jejiz hodnoty se nemaji nacitat behem vytvareni instanci; array("image_body")
	
	/**
	 * Values contained in a table record.
	 *
	 * @var array
	 * @access private
	 */
	var $_RecordValues = array();

	/**
	 * Constructor
	 *
	 * @param mixed $table_name_or_options
	 * @param array $options
	 * <ul>
	 * 	<li><b>do_not_read_values</b> - list of columns that shouldn't be fetched at the moment of object instantiation. It can increase performance. Typically when reading from tables cotaining binary objects</li>
	 * 	<li><b>id_field_name</b> - name of field containing primary key</li>
	 * 	<li><b>id_field_type</b> - type of field containing primary key</li>
	 * 	<li><b>sequence_name</b> - </li>
	 * </ul>
	 */
	function __construct($table_name_or_options = null,$options = array()){
		static $DEFAULT_OPTIONS = array();

		inobj::inobj();

		if(is_array($table_name_or_options)){
			$options = $table_name_or_options;
			$table_name = null;
		}else{
			$table_name = $table_name_or_options;
		}

		$class_name = get_class($this);

		$defaults = isset($DEFAULT_OPTIONS[$class_name]) ? $DEFAULT_OPTIONS[$class_name] : array(
			"table_name" => $table_name,
			"do_not_read_values" => array(),
			"id_field_name" => "id",
			"id_field_type" => null, // "integer", "string"
			"sequence_name" => null,
		);

		$options += $defaults;

		if(is_null($options["table_name"])){
			$options["table_name"] = new String($class_name);
			$options["table_name"] = $options["table_name"]->tableize();
		}
		$options["table_name"] = (string)$options["table_name"];

		$this->_TableName = $options["table_name"]; // could be member of String

		if(is_null($options["sequence_name"])){
			$options["sequence_name"] = $this->_DetermineSequenceName();
		}
		$this->_SequenceName = $options["sequence_name"];
		$this->_DoNotReadValues = $options["do_not_read_values"];

		$cache = defined("INOBJ_TABLERECORD_CACHES_STRUCTURES") ? INOBJ_TABLERECORD_CACHES_STRUCTURES : 0;
		if(defined("DEVELOPMENT") && DEVELOPMENT){ $cache = 0; }
		$this->_readTableStructure(array("cache" => $cache));

		if(!$this->_TableStructure){
			throw new Exception("There is not table $options[table_name] in the database ".$this->_dbmole->getDatabaseName());
		}

		$this->_IdFieldName = $options["id_field_name"];
		if(is_null($options["id_field_type"])){
			// autodetection
			$options["id_field_type"] = preg_match('/char/i',$this->_TableStructure[$this->_IdFieldName]) ? "string" : "integer";
		}
		$this->_IdFieldType = $options["id_field_type"];

		// all values of the objects should be null
		// TODO: seems to be useless -> refactoring
		foreach(array_keys($this->_TableStructure) as $_key){	
			$this->_RecordValues[$_key] = null;
		}

		if(!isset($DEFAULT_OPTIONS[$class_name])){
			// things may be a little faster next time
			$DEFAULT_OPTIONS[$class_name] = $options;
		}
	}

	/**
	 * Returns instance of a class for an id.
	 *
	 * Works in PHP5.3 and above.
	 *
	 * There is no need to define GetInstanceById() in the Article class.
	 * <code>
	 * $article = Article::GetInstanceById($id);
	 * </code>
	 *
	 * @param mixed $id record ID
	 * @param array $options
	 * @return TableRecord
	 */
	static function GetInstanceById($id,$options = array()){
		return TableRecord::_GetInstanceById(get_called_class(),$id,$options);
	}

	/**
	 * Creates new record.
	 *
	 * Creates new record in database and returns an object of class
	 *
	 * Works in PHP5.3 and above.
	 *
	 * Example:
	 * <code>
	 * $article = Article::CreateNewRecord(array("title" => "February Highlights")); // there's no need to define CreateNewRecord() in the Article class.
	 * </code>
	 *
	 * @todo Revise options
	 * @param array $id
	 * @param array $options
	 * @return TableRecord
	 */
	static function CreateNewRecord($id,$options = array()){
		return TableRecord::_CreateNewRecord(get_called_class(),$id,$options);
	}

	/**
	 * Method to obtain instance of DbMole.
	 */
	function &GetDbmole(){
		// TODO: toto je takove vachrlate
		if(class_exists("OracleMole")){
			return OracleMole::GetInstance();
		}
		return PgMole::GetInstance();
	}

	/**
	 * Converts object to in id.
	 *
	 * Takes instantiated object and returns its database id.
	 * Can be a string of course if the id is of char type
	 *
	 * It also converts an array of objects to an array of identifiers.
	 *
	 * <code>
	 * $article = inobj_Article:GetInstanceById(123);
	 * $id = TableRecord::ObjToId($article); // returns 123
	 * $id = TableRecord::ObjToId(123); // returns 123
	 * $id = TableRecord::ObjToId(null); // returns null
	 * $ids = TableRecord::ObjToId(array($article,$article2)); // returns array(123,124)
	 * </code>
	 *
	 * @static
	 * @param TableRecord $object
	 * @return mixed id of the record from db
	 */
	function ObjToId($object){
		if(is_array($object)){
			foreach($object as &$item){
				$item = is_object($item) ? $item->getId() : $item;
			}
			return $object;
		}
		return is_object($object) ? $object->getId() : $object;
	}

	/**
	 * Converts an $id (integer) to instance of a $class_name.
	 *
	 * <code>
	 * $article = TableRecord::IdToObj(123,"inobj_Article");
	 * $article = TableRecord::IdToObj(null,"inobj_Article"); // returns null
	 * $article = inobj_Article:GetInstanceById(123);
	 * $article = TableRecord::IdToObj($article,"inobj_Article"); // returns $article untouched
	 * </code>
	 *
	 * @static
	 * @param integer $id
	 * @param string $class_name
	 * @return TableRecord
	 *
	 */
	function IdToObj($id,$class_name){
		if(!isset($id)){ return null; }
		if(is_object($id)){ return $id; }
		return call_user_func(array($class_name,"GetInstanceById"),$id);
	}

	/**
	 * Returns a next value of the sequence related to the class.
	 * It's useful when you need to know $id before creation of an object.
	 * 
	 * <code>
	 * 	$id = User::GetSequenceNextval();
	 * 	User::CreateNewRecord(array(
	 *		"id" => $id,
	 *		"password" => md5($password.$id),
	 *	));
	 * </code>
	 */
	static function GetSequenceNextval(){
		$class_name = get_called_class();
		$obj = new $class_name;
		$dbmole = &TableRecord::GetDbmole();
		return $dbmole->selectSequenceNextval($obj->getSequenceName());
	}

	/**
	 * An alias for GetSequenceNextval()
	 */
	static function GetNextId(){
		$class_name = get_called_class();
		return call_user_func(array($class_name,"GetSequenceNextval"));
	}

	/**
	 * Returns name of table.
	 *
	 * @return string
	 */
	function getTableName(){ return $this->_TableName; }
	
	/**
	 * Returns name of table sequence
	 *
	 * @return string
	 */
	function getSequenceName(){ return $this->_SequenceName; }

	/**
	 * Returns record id.
	 *
	 * @return mixed
	 */
	function getId(){ return $this->_RecordValues[$this->_IdFieldName]; }


	/**
	 * Checks presence of a column.
	 *
	 * @param string $key
	 * @return bool
	 */
	function hasKey($key){ return in_array((string)$key,array_keys($this->_RecordValues)); }

	/**
	 * getBelongsTo.
	 *
	 * <code>
	 *	 $article = Article::GetInstanceById(111);
	 *	 $author = $article->getBelongsTo("author");
	 *	 $author = $article->getBelongsTo("Author");
	 * </code>
	 *
	 * <code>
	 * 	$author = $article->getBelongsTo("author",array(
	 *		"class_name" => "inobj_Author",
	 *		"attribute_name" => "author_id"
	 *	));
	 * </code>
	 *
	 * @param string $object
	 * @param array $options
	 * @return TableRecord
	 * @todo add comment
	 */
	function getBelongsTo($object,$options = array()){
		TableRecord::_NormalizeOptions(array($options),$options);

		$str = new String($object);

		$guessed_class_name = str_replace("_","",$object);
		if(class_exists("inobj_$guessed_class_name")){ $guessed_class_name = "inobj_$guessed_class_name"; }

		$options += array(
			"class_name" => $guessed_class_name,
			"attribute_name" => $str->underscore()."_id",
		);

		$class_name = $options["class_name"];
		$attribute_name = $options["attribute_name"];

		if(is_null($value = $this->getValue($options["attribute_name"]))){
			return null;
		}
		
		return call_user_func(array($class_name,"GetInstanceById"),$value);
	}

	/**
	 * Gets Lister
	 *
	 * Example
	 * <code>
	 * $lister = $article->getLister("Authors");
	 * $lister->append($author);
	 * $authors = $lister->getRecords(); // Author[]
	 * </code>
	 *
	 * @param $subjects Name of associated classes
	 * @param $options array {@link TableRecord_Lister()}
	 *
	 * @return TableRecord_Lister
	 */
	function getLister($subjects,$options = array()){
		return new TableRecord_Lister($this,$subjects,$options);
	}

	/**
	 * Automatically guess sequence name by the name of a table.
	 *
	 * Toto je vychozi nastaveni, ktere funguje v GR.
	 *
	 * @access private
	 */
	function _DetermineSequenceName(){
		return "seq_$this->_TableName";
	}

	/**
	 * Finds a record by id.
	 *
	 * @param mixed $id	integer, string, objekt, pole
	 * @param $options @
	 * @return TableRecord|array	returns null, when record with given id does not exist or error occured.
	 * When $id is array method returns array of TableRecord
	 */
	function find($id,$options = array()){
		if(!isset($id)){ return null; }

		if(is_object($id)){ $id = $id->getId(); }

		if(is_array($id)){
			return $this->_FindByArray($id,$options);
		}

		settype($id,$this->_IdFieldType);
		$this->_Id = $id;
		if(!$this->_readValues()){
			return null;
		}
		$this->_Hook_Find();
		return $this;
	}

	/**
	 * Record finder.
	 *
	 * <code>
	 * $finder = TableRecord::Finder(array(
	 *		"class_name" => "Book",
	 *		"conditions" => array("title LIKE :q"),
	 *		"bind_ar" => array(":q" => "%Prague%"),
	 *		"limit" => 20,
	 *		"offset" => 0,
	 *	));
	 *
	 *	$total_amount = $finder->getTotalAmount();
	 *	$books = $finder->getRecords();
	 * </code>
	 *
	 * It is possible to define custom SQL query. Then the counting SQL query should be also specified in "query_count" option.
	 * <code>
	 * $finder = TableRecord::Finder(array(
	 *		"class_name" => "Book",
	 *		"query" => "SELECT books.id FROM books,book_authors WHERE ...",
	 *		"query_count" => "SELECT COUNT(*) FROM ...",
	 *		"bind_ar" => $bind_ar,
	 *		"order" => null, // nekdy je dobre nenechat metodu Finder pripojit ORDER BY automaticky
	 *	));
	 * </code>
	 *
	 *
	 * Conditions can be passed as an associative array:
	 * <code>
	 *	$finder = TableRecord::Finder(array(
	 *		"class_name" => "Book",
	 *		"conditions" => array(
	 *			"author_id" => 123,
	 *		),
	 *	));
	 * </code>
	 *
	 * Since PHP5.3 Finder can be used in context with a specific class. Then the "class_name" option is not needed.
	 * <code>
	 *	$finder = Book::Finder(array(
	 *		"limit" => 20,
	 *	));
	 *	$finder = Book::Finder(array(
	 *		"conditions" => array("title" => "Foo Bar"),
	 *		"limit" => 20
	 *	));
	 *	$finder = Book::Finder("title","Foo Bar",array(
	 *		"limit" => 20
	 *	));
	 * </code>
	 *
	 * @param array $options
	 * @todo describe $options
	 */
	static function Finder(){
		TableRecord::_NormalizeOptions(func_get_args(),$options);

		if(isset($options["class_name"])){
			$class_name = $options["class_name"];
			unset($options["class_name"]);
		}else{
			$class_name = get_called_class();
		}
		$obj = new $class_name();
		return $obj->_finder($options);
	}

	/**
	 * This method is used by Finder() method and does the main job.
	 *
	 * @access private
	 * @param array $options
	 * @todo describe $options
	 * @ignore
	 */
	function _finder($options){
		// order_by se prevede na order
		if(in_array("order_by",array_keys($options))){
			$options["order"] = $options["order_by"];
			unset($options["order_by"]);
		}

		$options += array(
			"order" => $this->_IdFieldName,
			"conditions" => array(),
			"bind_ar" => array(),
			"limit" => 20,
			"offset" => 0,

			"query" => null,
			"query_count" => null,
		);

		$conditions = $options["conditions"];
		if(is_string($conditions) && strlen($conditions)==0){ $conditions = array(); }
		if(is_string($conditions)){ $conditions = array($conditions); }
		$bind_ar = $options["bind_ar"];

		TableRecord_Base::_NormalizeConditions($conditions,$bind_ar);

		if(isset($options["query"])){
			$query = $options["query"];
			if(isset($options["query_count"])){
				$query_count = $options["query_count"];
			}else{
				$query_count = "SELECT COUNT(*) FROM ($query)__q__";
			}

		}else{
			$query = $this->_dbmole->escapeTableName4Sql($this->_TableName);
			if(sizeof($conditions)>0){
				$query .= " WHERE (".join(") AND (",$conditions).")";
			}

			$query_count = "SELECT COUNT(*) FROM ".$query;


			$query = "SELECT $this->_IdFieldName FROM $query";
		}

		if(isset($options["order"])){
			$query .= " ORDER BY $options[order]";
		}

		unset($options["order"]);
		unset($options["bind_ar"]);
		unset($options["conditions"]);
		unset($options["query"]);
		unset($options["query_count"]);

		$finder = new TableRecord_Finder(array(
			"class_name" => get_class($this),
			"query" => $query,
			"query_count" => $query_count,
			"options" => $options,
			"bind_ar" => $bind_ar,
		),$this->_dbmole);
		$finder->_dbmole = &$this->_dbmole;
		
		return $finder;

	}

	/**
	 * Returns empty Finder
	 *
	 * For cases, where it is needed to have finder which behaves as if it was empty.
	 * For example when error occurs in searching form.
	 *
	 * <code>
	 * $finder = TableRecord::EmptyFinder();
	 * </code>
	 *
	 * @return TableRecord_EmptyFinder
	 */
	function EmptyFinder(){
		return new TableRecord_EmptyFinder();
	}


	/**
	 * Finds records with conditions.
	 *
	 * <code>
	 * $articles = TableRecord::FindAll(array(
	 *		"class_name" => "inobj_Article",
	 *		"conditions" => array("deleted='N'","published='Y'"),
	 *		"order" => "create_date",
	 *		"limit" => 20,
	 *		"offset" => 80,
	 * ));
	 * </code>
	 *
	 * Since PHP 5.3 you can use:
	 * <code>
	 * $articles = Article::FindAll(array(
	 *		"conditions" => array("deleted='N'","published='Y'"),
	 *		"order" => "create_date",
	 *		"limit" => 20,
	 *		"offset" => 80,
	 * ));
	 * </code>
	 *
	 * @todo obsah metody predelat jako implementaci volani TableRecord::Finder()
	 * @param array $options
	 * @return array
	 */
	static function FindAll(){
		TableRecord::_NormalizeOptions(func_get_args(),$options);

		if(isset($options["class_name"])){
			$class_name = $options["class_name"];
			unset($options["class_name"]);
		}else{
			$class_name = get_called_class();
		}
		$obj = new $class_name();
		return $obj->_findAll($options);
	}

	/**
	 * Find records.
	 *
	 * @ignore
	 * @param array $options
	 * @return array
	 */
	function _findAll($options = array()){
		// order_by se prevede na order
		if(in_array("order_by",array_keys($options))){
			$options["order"] = $options["order_by"];
			unset($options["order_by"]);
		}

		$options += array(
			"order" => $this->_IdFieldName,
			"conditions" => array(),
			"bind_ar" => array()
		);

		$conditions = $options["conditions"];
		if(is_string($conditions) && strlen($conditions)==0){ $conditions = array(); }
		if(is_string($conditions)){ $conditions = array($conditions); }
		$bind_ar = $options["bind_ar"];

		TableRecord_Base::_NormalizeConditions($conditions,$bind_ar);

		$query = "SELECT $this->_IdFieldName FROM ".$this->_dbmole->escapeTableName4Sql($this->_TableName);
		if(sizeof($conditions)>0){
			$query .= " WHERE (".join(") AND (",$conditions).")";
		}
		$query .= " ORDER BY $options[order]";

		unset($options["order"]);
		unset($options["bind_ar"]);
		unset($options["conditions"]);

		return $this->find($this->_dbmole->selectIntoArray($query,$bind_ar,$options));
	}

	/**
	 * Finds one record with conditions.
	 *
	 * Method behaves similar to {@link FindAll()} but returns only the first found record.
	 *
	 * <code>
	 * $article = TableRecord::FindFirst(array(
	 *		"class_name" => "Article",
	 *		"conditions" => array(
	 *			"created_at" => "2011-02-01" 
	 *		),
	 *	));
	 * </code>
	 *
	 * Since PHP5.3 it's possible call the method in context of a specific class.
	 * <code>
	 * $article = Article::FindFirst(array(
	 *		"conditions" => array(
	 *			"created_at" => "2011-02-01" 
	 *		),
	 *	));
	 * $article = Article::FindFirst("title=:title",array(":title" => "Foo Bar"));
	 * $article = Article::FindFirst("title","Foo Bar");
	 * $article = Article::FindFirst("title","Foo Bar",array("order_by" => "created_at DESC"));
	 * $article = Article::FindFirst("title","Foo Bar","author_id",123,array("order_by" => "created_at DESC"));
	 * </code>
	 *
	 * @param array $options
	 * @return TableRecord
	 */
	static function FindFirst(){
		TableRecord::_NormalizeOptions(func_get_args(),$options);

		if(isset($options["class_name"])){
			$class_name = $options["class_name"];
			unset($options["class_name"]);
		}else{
			$class_name = get_called_class();
		}
		$obj = new $class_name();
		return $obj->_findFirst($options);
	}

	/**
	 * internal method 
	 *
	 * @ignore
	 * @return TableRecord
	 */
	function _findFirst($options = array()){
		$options["limit"] = 1;
		$records = $this->_findAll($options);

		if(isset($records[0])){ return $records[0]; }
		return null;
	}

	/**
	 * Converts associative array of conditions to indexed array.
	 *
	 * @ignore
	 * @access private
	 */
	static function _NormalizeConditions(&$conditions,&$bind_ar){
		$_conditions = array();
		foreach($conditions as $k => $v){
			if(!is_numeric($k)){
				if(is_array($v)){
					$_conditions[] = "$k IN :$k";
					$bind_ar[":$k"] = $v;
				}elseif(!is_null($v)){
					$_conditions[] = "$k=:$k";
					$bind_ar[":$k"] = $v;
				}else{
					$_conditions[] = "$k IS NULL";
				}
			}else{
				$_conditions[] = $conditions[$k];
			}
		}
		$conditions = $_conditions;
	}

	/**
	 * Renames some option`s names to others. Like class to class_name...
	 *
	 * @ignore
	 */
	static function _NormalizeOptions($args,&$options){
		if(!isset($args[0])){ $args[0] = array(); }
		if(sizeof($args)==1){ $args[1] = array(); }

		$extra_options = null;

		if(sizeof($args)==2){
			$options = $args[0];
			$bind_ar = $args[1];
			$args = array();
		}elseif(sizeof($args)==3 && is_string($args[0])){
			$options = $args[0];
			$bind_ar =  $args[1];
			$extra_options = $args[2];
		}else{
			$options = array(
				"conditions" => array(),
				"bind_ar" => array(),
			);
			$bind_ar = array();
			$conditions = array();

			while(sizeof($args)>=2){
				$field = array_shift($args);
				$value = array_shift($args);
				if(is_null($value)){
					$conditions[] = "$field IS NULL";
				}else{
					$conditions[] = "$field=:$field";
					$bind_ar[":$field"] = $value;
				}
			}

			// when one item left it should be $options containing "order_by", "limit"...
			if(isset($args[0]) && is_array($args)){
			 $extra_options = $args[0];
			}

			$options["conditions"] += $conditions;
			$options["bind_ar"] += $bind_ar;

		}

		// Article::FindFirst("title","Foo Bar") -> Article::FindFirst(array("conditions" => array("title" => "Foo Bar")));
		// Article::FindFirst("id",123);
		// Article::FindFirst("id",null);
		if(is_string($options)){
		  if(is_array($bind_ar)){
		      $options = array(
		        "conditions" => $options,
		        "bind_ar" => $bind_ar
		        );
		  }else{
		      $options = array(
		        "conditions" => array("$options" => $bind_ar)
		      );
		  }
		}

		if(isset($extra_options) && is_array($extra_options)){
			$options = $extra_options + $options;
		}

		foreach(array(
			"condition" => "conditions",
			"class" => "class_name",
			"bind" => "bind_ar",
		) as $alt_key => $right_key){
			if(array_key_exists($alt_key,$options)){	
				$options[$right_key] = $options[$alt_key];
				unset($options[$alt_key]);
			}
		}

		// tady kontrolujeme, ze bind_ar obsahuje vsechny klice zacinajici dvojteckou (:key1, :key2...)
		// TODO: presunout to nekam do dbmole?
		if(isset($options["bind_ar"])){
			foreach($options["bind_ar"] as $key => $value){
				if(!is_string($key) || strlen($key)<1 || $key[0]!=":"){
					throw new Exception("Insecure bind value: $key");
				}
			}
		}
	}

	/**
	 * Creates array of instances by array of ids.
	 *
	 * Corresponding instances will be returned in the same order as specified in ids array.
	 * If there is no record for an id where will be null in the output array.
	 * Associative array can also be used.
	 *
	 * @access private
	 * @param array $ids record ids, ie array(1223,1224,1225) or array("product1"=>1223,"product2"=>1224,"product3"=>1225)
	 * @param array $options
	 * Option <b>omit_nulls</b> can be passed to return array without nulls.
	 * @return array
	 */
	function _FindByArray($ids,$options = array()){
		$ids = TableRecord::ObjToId($ids);

		$options = array_merge(array(
			"omit_nulls" => false
		),$options);

		$MAX_ELEMENTS = 200;
		if(sizeof($ids)>$MAX_ELEMENTS){
			$out = array();

			$part = array();
			$counter = 0;
			foreach($ids as $key => $value){	
				$part[$key] = $value;
				$counter ++;

				if($counter == $MAX_ELEMENTS){
					$_out = $this->_FindByArray($part,$options);
					foreach($_out as $_key => $_value){	
						$out[$_key] = $_value;
					}
					$part = array();
					$counter = 0;
				}
			}

			$_out = $this->_FindByArray($part,$options);
			foreach($_out as $_key => $_value){	
				$out[$_key] = $_value;
			}

			return $out;
		}

		$bind_ar = array();

		$class_name = get_class($this);

		$i = 0;
		foreach($ids as $_key => $id){	
			if(is_object($id)){ $id = $id->getId(); }
			if(!isset($id)){ continue; } // v poli se muze klidne nachazet nejaky null
			settype($id,$this->_IdFieldType);
			$bind_ar[":id$i"] = $id;
			$i++;
		}

		$objs = array();

		if(sizeof($bind_ar)>0){
			$query = "SELECT ".join(",",$this->_fieldsToRead())." FROM ".$this->_dbmole->escapeTableName4Sql($this->_TableName)." WHERE $this->_IdFieldName IN (".join(", ",array_keys($bind_ar)).")";
			$rows = $this->_dbmole->selectRows($query,$bind_ar);
			if(!is_array($rows)){ return null; }
			foreach($rows as $row){	
				$obj = new $class_name();
				$obj->_setRecordValues($row);
				$obj->_Hook_Find();
				$objs[$obj->getId()] = $obj;
			}
		}

		$out = array();
		foreach($ids as $_key => $_value){	
			$id = $_value;
			if(!isset($objs[$id])){
				if(!$options["omit_nulls"]){ $out[$_key] = null; }
				continue;
			}
			$out[$_key] = &$objs[$id];
		}

		return $out;

		/*
		// a Matyas` try, doesn't work
		$ids=(array)$ids;

		$options += array(
			"omit_nulls" => false
		);
		$MAX_ELEMENTS = 200;
		if(sizeof($ids)>$MAX_ELEMENTS){
		  $out = array();
		  $parts = array_chunk($MAX_ELEMENTS);

			foreach($parts as $part){	
					$out = $out + $this->_FindByArray($part,$options);
			}
			return $out;
		}

		
		$class_name = get_class($this);

		$bind_ar = array();
		$i=0;
		foreach($ids as $_key => &$id){	
			if(is_object($id)){ $id = $id->getId(); }
			if($id!==null)
			  {
			  settype($id,$this->_IdFieldType);
			  $bind_ar[":id$i"] = $id;
			  }
			$i++;
		}
		$ids_flip=array_flip($ids);
		$out=array_fill_keys(array_keys($ids),null);
		
		if(sizeof($bind_ar)>0){
			$query = "SELECT ".join(",",$this->_fieldsToRead())." FROM ".$this->_dbmole->escapeTableName4Sql($this->_TableName)." WHERE $this->_IdFieldName IN (".join(", ",array_keys($bind_ar)).")";
			$rows = $this->_dbmole->selectRows($query,$bind_ar);
			if(!is_array($rows)){ return null; }
			foreach($rows as $row){	
				$obj = new $class_name();
				$obj->_setRecordValues($row);
				$obj->_Hook_Find();
				$out[$ids_flip[$obj->getId()]] = $obj;
			}
		}
	  if(!$options["omit_nulls"]){
		   array_filter($out); 
	  }
		return $out;
		*/
  }
  
	/**
	 * Returns value of a column/s.
	 *
	 * More fields can be specified by array
	 *
	 * <code>
	 * $u->getValue("name");
	 * $u->getValue(array("name","email")); // returns array("Pan Davelka","davelka@gm.com")
	 * $u->getValue(array("name" => "name","email" => "email")); // returns array("name" => "Pan Davelka", "email" => "davelka@gm.com")
	 * </code>
	 *
	 * @param string|array $field_name
	 * @return mixed
	 */
	function getValue($field_name){
		if(is_array($field_name)){
			$out = array();
			foreach($field_name as $k => $v){
				$out[$k] = $this->getValue($v);
			}
			return $out;
		}
		settype($field_name,"string");
		if(!in_array($field_name,$this->getKeys())){
			error_log(get_class($this)."::getValue() accesses non existing field $this->_TableName.$field_name, returning null");
			return null;
		}
		$this->_readValueIfWasNotRead($field_name);
		return $this->_RecordValues[$field_name];

		/*
		// a Matyas` try, it doesn't pass tests
	  if(is_array($field_name))  
	    {
			$all_fields=$this->_TableStructure;
 			$field_name = array_flip($field_name); // now its in form db field name => required value
 			$diff=array_diff_key($field_name, $all_fields);
 			$out=array();  
 			if($diff)
 			  {
 			  foreach($diff as $key => $value)
			    {
 			    $out[$key]=null;
 			    error_log(get_class($this)."::getValue() accesses non existing field $this->_TableName.$key, returning null");
 			    }
 			  $field_name=array_intersect_key($field_name, $all_fields);
 				}
 			$this->_readValueIfWasNotRead($field_name);
 			foreach(array_intersect_key($this->_RecordValues, $field_name) as $k => $v)
 			  $out[$field_name[$k]]=$v;
 		  return $out;
 			}
		$field_name=(string) $field_name;
		if(!$this->hasKey($field_name)){
			error_log(get_class($this)."::getValue() accesses non existing field $this->_TableName.$field_name, returning null");
			return null;
		}
		$this->_readValueIfWasNotRead($field_name);
		return $this->_RecordValues[$field_name];
		*/
	}
	/**
	 * Alias to method getValue().
	 *
	 * Alias to method {@link getValue()}.
	 *
	 * @param string|array $field_name
	 * @return mixed
	 */
	function g($field_name){ return $this->getValue($field_name); }

	/**
	 * Returns array of record values.
	 *
	 * Returns all values except record id.
	 * Passing option return_id=>true forces method to return even column with record id.
	 *
	 * Example how to simply create a copy of a record:
	 * <code>
	 * $article = inobj_Articles::GetInstanceById(1000);
	 * $article_copy = inobj_Articles::CreateNewRecord(
	 * 	$article->getValues();
	 * );
	 * </code>
	 *
	 * @param array $options
	 * @return array
	 */
	function getValues($options = array()){
	  $options += array("return_id" => false);
		$this->_readValueIfWasNotRead(array_keys($this->_TableStructure));
		$out = $this->_RecordValues;
		if(!$options["return_id"]){
			unset($out[$this->_IdFieldName]);
		}
		return $out;
	}

	/**
	 * Converts records values into array.
	 *
	 * @return array
	 *
	 */
	function toArray(){ return $this->getValues(array("return_id" => true)); }

	/**
	 * Returns array of field names that the record contains.
	 *
	 * <code>
	 * $rec->getKeys();
	 * </code>
	 * outputs for example array("id","title","body","perex");
	 *
	 * @return array
	 */
	function getKeys(){
		return array_keys($this->_RecordValues);
	}

	
	
	
	/**
	 * Set value in a table column.
	 *
	 * @param string $field_name name of table column
	 * @param mixed $value							hodnota (cislo, string...)
	 * @param array $options
	 * <ul>
	 * 	<li><b>do_not_escape</b> - </li>
	 * </ul>
	 * @return boolean									true -> uspesne nastaveno
	 *																	false -> nenestaveno, doslo k chybe
	 */
	function setValue($field_name,$value,$options = array()){
		$field_name = (string) $field_name;
		$options = ((array) $options);

		if(isset($options["do_not_escape"]) && $options["do_not_escape"]==true){
			$options["do_not_escape"] = array($field_name);
		}else{
			$options["do_not_escape"] = array();
		}

		return $this->setValues(array("$field_name" => $value),$options);
	}

	/**
	 * Sets values in a record.
	 *
	 * <code>
	 * $this->setValues(array("paid" => "Y","paid_date" => "2007-10-29 15:13", "paid_note" => "zaplaceno"));
	 * </code>
	 *
	 *
	 * @param array $data
	 * @param array $options
	 * <ul>
	 * 	<li><b>do_not_escape</b> - </li>
	 * 	<li><b>validates_updating_of_fields</b> - </li>
	 * </ul>
	 * @return boolean true if successfully set, false when not set or error occured
	 */
	function setValues($data,$options = array()){
		$data = (array) $data;
		$options = ((array ) $options) +
                array(
                "do_not_escape" => array(),
                "validates_updating_of_fields" => null,
              );
		
		if(!is_array($options["do_not_escape"])){ $options["do_not_escape"] = array($options["do_not_escape"]); }

		foreach($data as $_key => &$value){
			if(isset($options["validates_updating_of_fields"]) && !in_array($_key,$options["validates_updating_of_fields"])){
				unset($data[$_key]);
				continue;
			}
			if(is_object($value)){
				$value = $data[$_key]->getId();
			}
		}

		if(sizeof($data)==0){ // nic neni treba menit
			return true;
		}

		if($this->_setValues($data,$options)){
			$this->_readValues();
			$this->_Hook_setValues(array_keys($data));
			return true;
		}
		return false;
	}

	/**
	 * @ignore
	 */
	function _setValues($data,$options){
		$updates = array();
		$bind_ar = array();
		foreach($data as $field => $value){
			if(in_array($field,$options["do_not_escape"])){
				$updates[] = "$field=$value";
				continue;
			}
			$updates[] = "$field=:$field";
			$bind_ar[":$field"] = $value;
		}
		$bind_ar[":id"] = $this->getId();

		return $this->_dbmole->doQuery("UPDATE ".$this->_dbmole->escapeTableName4Sql($this->_TableName)." SET\n  ".join(",\n  ",$updates)."\nWHERE\n  $this->_IdFieldName=:id",$bind_ar);
	}

	/**
	 * Alias to methods setValue() a setValues().
	 *
	 * Alias to methods {@link setValue()} a {@link setValues()}.
	 *
	 * Example:
	 * <code>
	 * $rec->s("name","Jan Novak");
	 * $rec->s(array(
	 *		"name" => "Jan Novak",
	 *		"birth_date" => "2001-01-01"
	 *	));
	 * </code>
	 *
	 * Options can be passed to both example calls:
	 * <code>
	 * $rec->s("create_at","NOW()",array("do_not_escape" => true));
	 * $rec->s(array(
	 *		"name" => "Jan Novak",
	 *		"birth_date" => "2001-01-01",
	 *		"create_at" => "NOW()"
	 *	),array("do_not_escape" => "create_at"));
	 * </code>
	 *
	 * @param string|array $field_name
	 * @param mixed $value
	 * @param array $options
	 * @return boolean true - values successfully set, false - values not set and error
	 */
	function s($field_name,$value = null,$options = array()){
		if(is_array($field_name)){
			if(!is_array($value)){ $value = array(); }
			return $this->setValues($field_name,$value);
		}
		return $this->setValue($field_name,$value,$options);
	}

	/**
	 * Sets value only in instance.
	 *
	 * @param string $field_name
	 * @param mixed $value
	 */
	function setValueVirtually($field_name,$value){
		$this->setValuesVirtually(array("$field_name" => $value));
	}

	/**
	 * Sets more values only in instance.
	 *
	 * @param array $values
	 */
	function setValuesVirtually($values){
		$keys = array_keys($this->_RecordValues);

		foreach($values as $_key => $_value){	
			if(in_array($_key,$keys)){
				$this->_RecordValues[$_key] = $_value;
			}
		}
	}

	/**
	 * Fetches record values.
	 *
	 * Column fields that will be read can be specified by passing $fields.
	 *
	 * <code>
	 * $this->_readValues("title");
	 * $this->_readValues(array("title","create_date"));
	 * </code>
	 *
	 * @param mixed $fields
	 * @ignore
	 * @return array
	 */
	function _readValues($fields = null){
		if(!isset($fields)){ $fields = $this->_fieldsToRead(); }
		if(is_array($fields))
		  $fields = join(",",$fields);
		if(!$row = $this->_dbmole->selectFirstRow("SELECT $fields FROM ".$this->_dbmole->escapeTableName4Sql($this->_TableName)." WHERE $this->_IdFieldName=:id",array(":id" => $this->_Id))){
			return null;
		}
		$this->_setRecordValues($row);

		return $this->_RecordValues;
	}

	/**
	 * @ignore
	 */
	function _readValueIfWasNotRead($field){
		 if(is_array($field)){
 			$this->_DoNotReadValues = array_diff($this->_DoNotReadValues,$field);
 			$this->_readValues($field);	    
 		  }
 		elseif(in_array($field,$this->_DoNotReadValues)){
			$this->_DoNotReadValues = array_diff($this->_DoNotReadValues,array($field));
			$this->_readValues($field);
		}
	}


	/**
	 * Deletes current record.
	 *
	 * <code>
	 * $article = $article->destroy();
	 * </code>
	 *
	 * @return null
	 */
	function destroy(){
		$this->_Hook_BeforeDestroy();
		$this->_dbmole->doQuery("DELETE FROM ".$this->_dbmole->escapeTableName4Sql($this->_TableName)." WHERE $this->_IdFieldName=:id",array(":id" => $this->_Id));
		return null;
	}

	/**
	 * 
	 * @see TableRecord::_CreateNewRecord()
	 * @ignore
	 */
	function _insertRecord($values,$options = array()){
		$values=(array)$values;
		$options=(array)$options;

		foreach($values as $_key => $value){
			if(isset($options["validates_inserting_of_fields"]) && !in_array($_key,$options["validates_inserting_of_fields"])){
				unset($values[$_key]);
				continue;
			}

			if(is_object($values[$_key])){ $values[$_key] = $values[$_key]->getId(); }
		}

		$id = null;
		if(isset($values[$this->_IdFieldName])){
			$id = $values[$this->_IdFieldName];
		}elseif($this->_dbmole->usesSequencies()){
			$id = $this->_dbmole->selectSequenceNextval($this->_SequenceName);
			if(!isset($id)){ return null; }
			$values[$this->_IdFieldName] = $id;			
		}

		/*
		if(!isset($values[$this->_IdFieldName])){
			$id = $this->_dbmole->selectSequenceNextval($this->_SequenceName);
			if(!isset($id)){ return null; }
			$values[$this->_IdFieldName] = $id;
		}else{
			$id = $values[$this->_IdFieldName];
		}
		*/

		if(!$this->_dbmole->insertIntoTable($this->_TableName,$values,$options)){ return null; }

		if(!isset($id)){
			$id = $this->_dbmole->selectInsertId();
		}
		
		$out = TableRecord::_GetInstanceById(get_class($this),$id);
		$out->_Hook_afterCreateNewRecord();
		return $out;
	}

	/**
	 * @ignore
	 */
	function _fieldsToRead(){
		$out = array();
		foreach($this->_TableStructure as $field => $vals){
			if(in_array($field,$this->_DoNotReadValues)){ continue; }
			$out[] = $field;
		}
		return $out;
	}

	/**
	 * After find hook method.
	 *
	 * This method is called after a record is successfully found and values in $_RecordValues are set.
	 * Can be overriden in descendant.
	 *
	 * @access protected
	 */
	function _Hook_Find(){
		
	}

	/**
	 * After update hook method.
	 *
	 * This method is called after a record is successfully updated.
	 * Can be overriden in descendant.
	 *
	 * 
	 * @param array $fields
	 * @access protected
	 */
	function _Hook_setValues($fields){
		
	}

	/**
	 * After create hook method.
	 *
	 * This method is called after a record is successfully created.
	 *
	 * @access protected
	 */
	function _Hook_afterCreateNewRecord(){

	}

	/**
	 * Before destroy hook method.
	 *
	 * This method is called before a record is destroyed.
	 *
	 * @access protected
	 */
	function _Hook_BeforeDestroy(){

	}

	/**
	 * Display this class as a string.
	 *
	 * Can be modified in descendants method.
	 *
	 * @return string
	 */
	function toString(){
		return sprintf("%s#%s",get_class($this),$this->getId());
	}

	/**
	 * Magic method:
	 * 
	 * echo "$object"; 
	 * @ignore
	 */
	function __toString(){
		return $this->toString();
	}

	/**
	 * Magic method changes calling to an nonexistent method in this way:
	 * 
	 * $object->getEmailAddress() -> $object->g("email_address");
	 *
	 * $object->getUserId() -> $object->g("user_id");
	 * $object->getUser() -> User::GetInstanceById($object->g("user_id"));
	 *
	 * @param string $name
	 * @param string $arguments
	 * @ignore
	 */
	function __call($name,$arguments){
		static $CACHES;

		$class_name = get_class($this);

		if(!isset($CACHES)){ $CACHES = array(); }
		if(!isset($CACHES[$class_name])){
			$CACHES[$class_name] = array(
				"fields" => array(),
			);
		}

		$CACHE = &$CACHES[$class_name];

		if(isset($CACHE["fields"][$name])){
			return $this->g($CACHE["fields"][$name]);
		}

		$name = new String($name);
		if($name->match("/^get(.+)/",$matches)){
			$field = $matches[1]->underscore();
			if($this->hasKey($field)){
				$CACHE["fields"][(string)$name] = (string)$field;
				return $this->g($field);
			}

			// Looking for ClassName or inobj_ClassName. The prefix inobj_ (which means internal object) exists on my legacy classes.
			if($this->hasKey("{$field}_id") && (class_exists($c = (string)$field->camelize()) || class_exists($c = "inobj_$c"))){
				return call_user_func_array(array($c,"GetInstanceById"),array($this->g("{$field}_id")));
			}
		}

		throw new Exception("TableRecord_Base::__call(): unknown method ".get_class($this)."::$name()");
	}

	/**
	 * @ignore
	 */
	static function __callStatic($name,$arguments){
		$class_name = function_exists("get_called_class") ? get_called_class() : "unknown";

		if(preg_match('/^Find(|First|All)By(.+)/',$name,$matches)){
			$method = $matches[1]=="All" ? "FindAll" : "FindFirst";
			$field = new String($matches[2]);
			$field = $field->underscore();
			$params = array("$field",$arguments[0],isset($arguments[1]) ? $arguments[1] : array());
			return call_user_func_array(array($class_name,$method),$params);
		}

		throw new Exception("TableRecord_Base::__callStatic(): unknown static method $class_name::$name()");
	}

}
