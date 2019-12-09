<?php
/**
 * Base class for manipulating records.
 *
 * @package Atk14\TableRecord
 * @filesource
 */

// An obsolete constant
if(defined("INOBJ_TABLERECORD_CACHES_STRUCTURES")){
	define("TABLERECORD_CACHES_STRUCTURES",INOBJ_TABLERECORD_CACHES_STRUCTURES);
}

// Structures of tables will be cached for the given amount of seconds, in production it should be greater than 0
defined("TABLERECORD_CACHES_STRUCTURES") || define("TABLERECORD_CACHES_STRUCTURES",0);

// Use the caching infrastructure whenever it is possible?
defined("TABLERECORD_USE_CACHE_BY_DEFAULT") || define("TABLERECORD_USE_CACHE_BY_DEFAULT",false);

defined("TABLERECORD_MAX_NUMBER_OF_RECORDS_READ_AT_ONCE") || define("TABLERECORD_MAX_NUMBER_OF_RECORDS_READ_AT_ONCE",200);

/**
 * Base class for manipulating records.
 *
 */
class TableRecord extends inobj {

	/**
	 * Time interval to store table structures cached
	 *
	 * @var integer
	 */
	static $TableStructuresCacheDuration = TABLERECORD_CACHES_STRUCTURES;

	/**
	 * Storage for table structures
	 *
	 * @var array
	 */
	static protected $_TableStructuresCache;

	static protected $_TableStructureKeysCache;

	/**
	 * Database interface.
	 *
	 * @var DbMole
	 */
	public $dbmole = null;

	/**
	 * Name of database table.
	 *
	 * Is filled in constructor.
	 *
	 * @access private
	 * @var string
	 */
	protected $_TableName = "";

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
	protected  $_Id = null;

	/**
	 * Name of table column used as primary key.
	 *
	 * Default name is 'id' but can be changed in constructor.
	 *
	 * @access private
	 * @var string
	 */
	protected $_IdFieldName = "id";

	/**
	 * Type of primary key column.
	 *
	 * By default integer is used but can be changed in constructor.
	 *
	 * @var string
	 * @access private
	 */
	protected $_IdFieldType = "integer";

	/**
	 * Columns which values shouldn't be read in during instantiation of object.
	 *
	 * If some columns shouldn't be read in from a table (ie.for performance purposes) specify it here.
	 *
	 * @var array
	 * @access private
	 */
	static protected $_DoNotReadValues = array();

	/**
	 * Values contained in a table record.
	 *
	 * @var array
	 * @access private
	 */
	protected $_RecordValues = array();

	/**
	 * Constructor
	 *
	 * @param mixed $table_name_or_options
	 * @param array $options
	 * - <b>do_not_read_values</b> - list of columns that shouldn't be fetched at the moment of object instantiation. It can increase performance. Typically when reading from tables containing binary objects
	 * - <b>id_field_name</b> - name of field containing primary key (default: id)
	 * - <b>id_field_type</b> - type of field containing primary key. When not set, it is guessed from database type of the primary key field.
	 * - <b>sequence\_name</b> - set the sequence name in case it doesn't suit atk14s pattern 'seq_' + $table_name
	 */
	function __construct($table_name_or_options = null,$options = array()){
		static $DEFAULT_OPTIONS = array();

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
			"dbmole" => $GLOBALS["dbmole"],
		);

		$options += $defaults;

		$this->dbmole = $options["dbmole"];

		if(is_null($options["table_name"])){
			$options["table_name"] = new String4($class_name);
			$options["table_name"] = $options["table_name"]->tableize();
		}
		$options["table_name"] = (string)$options["table_name"]; // could be member of String4

		$this->_TableName = $options["table_name"];

		if(is_null($options["sequence_name"])){
			$options["sequence_name"] = $this->_determineSequenceName();
		}
		$this->_SequenceName = $options["sequence_name"];
		self::$_DoNotReadValues = $options["do_not_read_values"];

		$structure = $this->_getTableStructure();

		$this->_IdFieldName = $options["id_field_name"];
		if(is_null($options["id_field_type"])){
			// autodetection
			$options["id_field_type"] = preg_match('/char/i',$structure[$this->_IdFieldName]) ? "string" : "integer";
		}
		$this->_IdFieldType = $options["id_field_type"];

		if(!isset($DEFAULT_OPTIONS[$class_name])){
			// things may be a little faster next time
			$DEFAULT_OPTIONS[$class_name] = $options;
		}

		parent::__construct();
	}

	/**
	 * Returns instance of a class for an id.
	 *
	 * Works in PHP5.3 and above.
	 *
	 * There is no need to define GetInstanceById() in the Article class.
	 * ```
	 * $article = Article::GetInstanceById($id);
	 * ```
	 *
	 * @param mixed $id record ID
	 * @param array $options
	 * @return TableRecord
	 */
	static function GetInstanceById($id,$options = array()){
		return TableRecord::_GetInstanceById(get_called_class(),$id,$options);
	}

	/**
	 * Creates an object of a class and reads in values from table.
	 *
	 * Method takes record $id, finds corresponding record and reads its values into newly created object.
	 *
	 * This method is used in a descendants {@link GetInstanceById()} method.
	 * ```
	 * class Article extends TableRecord{
	 *	//...
	 *	function GetInstanceById($id,$options = array()){
	 *		return TableRecord::_GetInstanceById("Article",$id,$options);
	 *	}
	 *	//...
	 *	}
	 * ```
	 *
	 *
	 * @access protected
	 * @ignore
	 * @param string $class_name	ie. "Article"
	 * @param mixed $id						identifikator zaznamu v tabulce; integer, string nebo pole
	 * @param array $options
	 * @return TableRecord	resp. tridu, ktera je urcena v $class_name
	 */
	static function _GetInstanceById($class_name,$id,$options = array()){
		$out = new $class_name();
		return $out->find($id,$options);
	}


	/**
	 * Creates new record.
	 *
	 * Creates new record in database and returns an instance of class.
	 *
	 * Works in PHP5.3 and above.
	 *
	 * Example:
	 * ```
	 * $article = Article::CreateNewRecord(array("title" => "February Highlights")); // there's no need to define CreateNewRecord() in the Article class.
	 *
	 * $article = Article::CreateNewRecord($values,array("use_cache" => true));
	 * ```
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
	 * Creates a record in a table
	 *
	 * Method takes array of values and creates a record in a table.
	 * Then returns an object of given class.
	 *
	 *
	 * Tuto metodu pouzijte v implementaci metody CreateNewRecord().
	 * Pozn. od PHP5.3 toto jiz neni treba (zde uz je k dispozici fce get_called_class()).
	 * Pouzijte ji nasledujicim zpusobem:
	 * ```
	 *		class Article extends TableRecord{
	 *			//...
	 *			function CreateNewRecord($values,$options = array()){
	 *				return TableRecord::_CreateNewRecord("Article",$values,$options);
	 *			}
	 *			//...
	 *		}
	 * ```
	 *
	 *
	 * @access private
	 * @ignore
	 * @param string $class_name					id. "Article"
	 * @param array $values
	 * @param array $options
	 * @return TableRecord
	 */
	static function _CreateNewRecord($class_name,$values,$options = array()){
		$out = new $class_name();
		return $out->_insertRecord($values,$options);
	}

	/**
	 * Method to obtain instance of DbMole.
	 *
	 * ```
	 *	$dbmole = Article::GetDbmole();
	 *	$dbmole_users = User::GetDbmole(); // The DbMole of another class may be different, e.g. it may be connectect to a database of different type
	 * ```
		 @return DbMole
	 */
	static function &GetDbmole(){
		$class = get_called_class();
		$o = new $class();
		return $o->dbmole;
	}

	/**
	 * Converts object to its id.
	 *
	 * Takes instantiated object and returns its database id.
	 * Can be a string of course if the id is of char type
	 *
	 * It also converts an array of objects to an array of identifiers.
	 *
	 * ```
	 *	$article = inobj_Article:GetInstanceById(123);
	 *	$id = TableRecord::ObjToId($article); // returns 123
	 *	$id = TableRecord::ObjToId(123); // returns 123
	 *	$id = TableRecord::ObjToId(null); // returns null
	 *	$ids = TableRecord::ObjToId(array($article,$article2)); // returns array(123,124)
	 * ```
	 *
	 * @param TableRecord $object
	 * @return mixed id of the record from db
	 */
	static function ObjToId($object){
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
	 * ```
	 *	$article = TableRecord::IdToObj(123,"inobj_Article");
	 *	$article = TableRecord::IdToObj(null,"inobj_Article"); // returns null
	 *	$article = inobj_Article:GetInstanceById(123);
	 *	$article = TableRecord::IdToObj($article,"inobj_Article"); // returns $article untouched
	 * ```
	 *
	 * @param integer $id
	 * @param string $class_name
	 * @return TableRecord
	 *
	 */
	static function IdToObj($id,$class_name){
		if(!isset($id)){ return null; }
		if(is_object($id)){ return $id; }
		return call_user_func(array($class_name,"GetInstanceById"),$id);
	}

	/**
	 * Returns a next value of the sequence related to the class.
	 *
	 */
	static function GetSequenceNextval(){
		$class_name = get_called_class();
		$dbmole = $class_name::GetDbmole();
		if(!$dbmole->usesSequencies()){ return; } // e.g. MySQL
		$obj = new $class_name;
		return $dbmole->selectSequenceNextval($obj->getSequenceName());
	}

	/**
	 * Generates a new id for a new record.
	 *
	 * Naturally it looks like an alias of self::GetSequenceNextval()
	 * but it's logic can be changed in a descendant.
	 *
	 * It's useful when you need to know $id before creation of an object.
	 *
	 * ```
	 *	$id = User::GetNextId();
	 *	User::CreateNewRecord(array(
	 *		"id" => $id,
	 *		"password" => md5($password.$id),
	 *	));
	 * ```
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
	final function getTableName(){ return $this->_TableName; }

	/**
	 * Returns name of id field.
	 *
	 * @return string
	 */
	final function getIdFieldName() { return $this->_IdFieldName; }

	/**
	 * Returns name of table sequence
	 *
	 * @return string
	 */
	function getSequenceName(){ return $this->_SequenceName; }

	/**
	 * Returns record id.
	 *
	 * TODO: make this function final
	 *
	 * @return mixed
	 */
	function getId(){ return isset($this->_RecordValues[$this->_IdFieldName]) ? $this->_RecordValues[$this->_IdFieldName] : null; }

	/**
	 * Checks presence of a column.
	 *
	 * @param string $key
	 * @return bool
	 */
	function hasKey($key){
		$struct = $this->_getTableStructure();
		return isset($struct[(string)$key]);
	}

	/**
	 * getBelongsTo.
	 *
	 * ```
	 *	$article = Article::GetInstanceById(111);
	 *	$author = $article->getBelongsTo("author");
	 *	$author = $article->getBelongsTo("Author");
	 * ```
	 *
	 * ```
	 *	$author = $article->getBelongsTo("author",array(
	 *		"class_name" => "inobj_Author",
	 *		"attribute_name" => "author_id"
	 *	));
	 * ```
	 *
	 * @param string $object
	 * @param array $options
	 * @return TableRecord
	 * @todo add comment
	 */
	function getBelongsTo($object,$options = array()){
		TableRecord::_NormalizeOptions(array($options),$options);

		$str = new String4($object);

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
	 * ```
	 *	$lister = $article->getLister("Authors");
	 *	$lister->append($author);
	 *	$authors = $lister->getRecords(); // Author[]
	 * ```
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
	function _determineSequenceName(){
		return preg_replace('/^(.*\.|)(.*?)$/','\1seq_\2',$this->getTableName()); // "articles" -> "seq_articles", "public.articles" -> "public.seq_articles"
	}

	/**
	 * Finds a record by id.
	 *
	 * Finds one or more records.
	 * In the first parameter the method accepts id of the record or array of ids.
	 * It also accepts objects, they are internally converted to integers.
	 * When one integer or object is passed, returns one object. In case array is passed, also returns array.
	 *
	 * Can return null, when record with given id does not exist or error occured.
	 *
	 * @param (integer|TableRecord)[]|integer|TableRecord $id
	 * @param $options
	 * @return TableRecord|TableRecord[] single TableRecord object or array of objects.
	 */
	function find($id,$options = array()){
		$options += array(
			"use_cache" => TABLERECORD_USE_CACHE_BY_DEFAULT,
		);

		if(!isset($id)){ return null; }

		if(is_object($id)){ $id = $id->getId(); }

		if(is_array($id)){
			return $this->_FindByArray($id,$options);
		}

		if($options["use_cache"]){
			return Cache::Get(get_class($this),$id);
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
	 * ```
	 *	$finder = TableRecord::Finder(array(
	 *		"class_name" => "Book",
	 *		"conditions" => array("title LIKE :q"),
	 *		"bind_ar" => array(":q" => "%Prague%"),
	 *		"limit" => 20,
	 *		"offset" => 0,
	 *	));
	 *
	 *	$total_amount = $finder->getTotalAmount();
	 *	$books = $finder->getRecords();
	 * ```
	 *
	 * It is possible to define custom SQL query. Then the counting SQL query should be also specified in "query_count" option.
	 * ```
	 *	$finder = TableRecord::Finder(array(
	 *		"class_name" => "Book",
	 *		"query" => "SELECT books.id FROM books,book_authors WHERE ...",
	 *		"query_count" => "SELECT COUNT(*) FROM ...",
	 *		"bind_ar" => $bind_ar,
	 *		"order" => null, // nekdy je dobre nenechat metodu Finder pripojit ORDER BY automaticky
	 *	));
	 * ```
	 *
	 *
	 * Conditions can be passed as an associative array:
	 * ```
	 *	$finder = TableRecord::Finder(array(
	 *		"class_name" => "Book",
	 *		"conditions" => array(
	 *			"author_id" => 123,
	 *		),
	 *	));
	 * ```
	 *
	 * Since PHP5.3 Finder can be used in context with a specific class. Then the "class_name" option is not needed.
	 * ```
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
	 * ```
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

			"use_cache" => TABLERECORD_USE_CACHE_BY_DEFAULT,

			"query" => null,
			"query_count" => null,
		);

		$conditions = $options["conditions"];
		if(is_string($conditions) && strlen($conditions)==0){ $conditions = array(); }
		if(is_string($conditions)){ $conditions = array($conditions); }
		$bind_ar = $options["bind_ar"];
		$use_cache = $options["use_cache"];

		TableRecord::_NormalizeConditions($conditions,$bind_ar);

		if(isset($options["query"])){
			$query = $options["query"];
			if(isset($options["query_count"])){
				$query_count = $options["query_count"];
			}else{
				$query_count = "SELECT COUNT(*) FROM ($query)__q__";
			}

		}else{
			$query = $this->dbmole->escapeTableName4Sql($this->getTableName());
			if(sizeof($conditions)>0){
				$query .= " WHERE (".join(") AND (",$conditions).")";
			}

			$query_count = "SELECT COUNT(*) FROM ".$query;


			$query = "SELECT $this->_IdFieldName FROM $query";
		}

		if(strlen($options["order"])>0){
			$query .= " ORDER BY $options[order]";
		}

		unset($options["order"]);
		unset($options["bind_ar"]);
		unset($options["conditions"]);
		unset($options["query"]);
		unset($options["query_count"]);
		unset($options["use_cache"]);

		$finder = new TableRecord_Finder(array(
			"class_name" => get_class($this),
			"query" => $query,
			"query_count" => $query_count,
			"options" => $options, // options for querying
			"bind_ar" => $bind_ar,
			"use_cache" => $use_cache
		),$this->dbmole);
		$finder->dbmole = &$this->dbmole;

		// TODO: toto by melo byt v TableRecord_Finder
		if($use_cache){
			Cache::Prepare(get_class($this),$finder->getRecordIds());
		}

		return $finder;

	}

	/**
	 * Returns empty Finder
	 *
	 * For cases, where it is needed to have finder which behaves as if it was empty.
	 * For example when error occurs in searching form.
	 *
	 * ```
	 * $finder = TableRecord::EmptyFinder();
	 * ```
	 *
	 * @return TableRecord_EmptyFinder
	 */
	static function EmptyFinder(){
		return new TableRecord_EmptyFinder();
	}


	/**
	 * Finds records with conditions.
	 *
	 * ```
	 *	$articles = TableRecord::FindAll(array(
	 *		"class_name" => "inobj_Article",
	 *		"conditions" => array("deleted='N'","published='Y'"),
	 *		"order" => "create_date",
	 *		"limit" => 20,
	 *		"offset" => 80,
	 *	));
	 * ```
	 *
	 * Since PHP 5.3 you can use:
	 * ```
	 *	$articles = Article::FindAll(array(
	 *		"conditions" => array("deleted='N'","published='Y'"),
	 *		"order" => "create_date",
	 *		"limit" => 20,
	 *		"offset" => 80,
	 *	));
	 * ```
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
			"bind_ar" => array(),
			"use_cache" => TABLERECORD_USE_CACHE_BY_DEFAULT,
		);

		$conditions = $options["conditions"];
		if(is_string($conditions) && strlen($conditions)==0){ $conditions = array(); }
		if(is_string($conditions)){ $conditions = array($conditions); }
		$bind_ar = $options["bind_ar"];
		$use_cache = $options["use_cache"];

		TableRecord::_NormalizeConditions($conditions,$bind_ar);

		$query = "SELECT $this->_IdFieldName FROM ".$this->dbmole->escapeTableName4Sql($this->getTableName());
		if(sizeof($conditions)>0){
			$query .= " WHERE (".join(") AND (",$conditions).")";
		}
		$query .= " ORDER BY $options[order]";

		unset($options["order"]);
		unset($options["bind_ar"]);
		unset($options["conditions"]);
		unset($options["use_cache"]);

		return $this->find($this->dbmole->selectIntoArray($query,$bind_ar,$options),array("use_cache" => $use_cache));
	}

	/**
	 * Finds one record with conditions.
	 *
	 * Method behaves similar to {@link FindAll()} but returns only the first found record.
	 *
	 * ```
	 *	$article = TableRecord::FindFirst(array(
	 *		"class_name" => "Article",
	 *		"conditions" => array(
	 *			"created_at" => "2011-02-01"
	 *		),
	 *	));
	 * ```
	 *
	 * Since PHP5.3 it's possible call the method in context of a specific class.
	 * ```
	 *	$article = Article::FindFirst(array(
	 *		"conditions" => array(
	 *			"created_at" => "2011-02-01"
	 *		),
	 *	));
	 *	$article = Article::FindFirst("title=:title",array(":title" => "Foo Bar"));
	 *	$article = Article::FindFirst("title","Foo Bar");
	 *	$article = Article::FindFirst("title","Foo Bar",array("order_by" => "created_at DESC"));
	 *	$article = Article::FindFirst("title","Foo Bar","author_id",123,array("order_by" => "created_at DESC"));
	 * ```
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
			"omit_nulls" => false,
			"use_cache" => TABLERECORD_USE_CACHE_BY_DEFAULT,
		),$options);

		$MAX_ELEMENTS = TABLERECORD_MAX_NUMBER_OF_RECORDS_READ_AT_ONCE;
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


		$class_name = get_class($this);

		if($options["use_cache"]){
			$out = Cache::Get($class_name,$ids);
			if($options["omit_nulls"]){
				$_out = array();
				foreach($out as $_key => $_value){
					if(!is_null($_value)){ $_out[$_key] = $_value; }
				}
				$out = $_out;
			}
			return $out;
		}

		$bind_ar = array();

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
			$query = "SELECT ".join(",",$this->_fieldsToRead())." FROM ".$this->dbmole->escapeTableName4Sql($this->getTableName())." WHERE $this->_IdFieldName IN (".join(", ",array_keys($bind_ar)).")";
			$rows = $this->dbmole->selectRows($query,$bind_ar);
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
  }

	/**
	 * Returns value of a column/s.
	 *
	 * More fields can be specified by array
	 *
	 * ```
	 * $u->getValue("name");
	 * $u->getValue(array("name","email")); // returns array("Pan Davelka","davelka@gm.com")
	 * $u->getValue(array("name" => "name","email" => "email")); // returns array("name" => "Pan Davelka", "email" => "davelka@gm.com")
	 * ```
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
		$field_name = (string)$field_name;
		if(array_key_exists($field_name,$this->_RecordValues)){
			return $this->_RecordValues[$field_name];
		}
		if(!in_array($field_name,$this->getKeys())){
			throw new Exception(get_class($this)."::getValue() accesses non existing field ".$this->getTableName().".$field_name");
		}
		$this->_readValuesIfWasNotRead($field_name);
		return isset($this->_RecordValues[$field_name]) ? $this->_RecordValues[$field_name] : null;
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
	 * ```
	 * $article = inobj_Articles::GetInstanceById(1000);
	 * $article_copy = inobj_Articles::CreateNewRecord(
	 * 	$article->getValues();
	 * );
	 * ```
	 *
	 * @param array $options
	 * @return array
	 */
	function getValues($options = array()){
	  $options += array("return_id" => true);
		$keys = $this->getKeys();
		$this->_readValuesIfWasNotRead($keys);
		$out = $this->_RecordValues;

		if(!isset($this->_RecordValues[$this->_IdFieldName])){ // HACK for a virtual object (for some reason we couldn't call $this->getId(), unless the method getId() is marked final)
			foreach($keys as $k){
				if(!isset($out[$k])){ $out[$k] = null; }
			}
		}
		
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
	 * ```
	 * $rec->getKeys();
	 * ```
	 * outputs for example array("id","title","body","perex");
	 *
	 * @return array
	 */
	function getKeys(){
		$this->_getTableStructure($keys);
		return $keys;
	}

	/**
	 * Set value in a table column.
	 *
	 * When $value is an object, it will be converted to its id.
	 *
	 * @param string $field_name name of table column
	 * @param mixed $value number, string, object ...
	 * @param array $options {@see setValues()}
	 * @return boolean
	 * - true - successfully set,
	 * - false - not set and error occured
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
	 * ```
	 * $this->setValues(array(
	 * 	"paid" => "Y",
	 * 	"paid_date" => "2007-10-29 15:13",
	 * 	 "paid_note" => "paid fast"
	 * ));
	 * ```
	 *
	 * In case a value should not be escaped, (for example db function is passed as value), it can be passed in option 'do_not_escape'.
	 *
	 * In this call no values will be escaped
	 * ```
	 * $rec->s("create_at","NOW()",array(
	 * 	"do_not_escape" => true
	 * ));
	 * ```
	 * In this call only value 'create_at' will not be escaped
	 * ```
	 * $rec->s(array(
	 * 	"name" => "Jan Novak",
	 * 	"birth_date" => "2001-01-01",
	 * 	"create_at" => "NOW()"
	 * ),array("do_not_escape" => "create_at"));
	 * ```
	 * Pass array of values to the option when more values are not to be escaped
	 *
	 * @param array $data
	 * @param array $options
	 * - <b>do_not_escape</b> - string|array - values that will not be escaped
	 * - <b>validates_updating_of_fields</b>
	 *
	 * @return boolean
	 * - true - successfully set,
	 * - false - not set and error occured
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
				$value = $this->_objectToScalar($data[$_key]);
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

		return $this->dbmole->doQuery("UPDATE ".$this->dbmole->escapeTableName4Sql($this->getTableName())." SET\n  ".join(",\n  ",$updates)."\nWHERE\n  $this->_IdFieldName=:id",$bind_ar);
	}

	/**
	 * Alias to methods setValue() and setValues().
	 *
	 * @see TableRecord::setValues()
	 * @see setValue()
	 * @param string|array $field_name
	 * @param mixed $value
	 * @param array $options {@link setValues()}
	 * @return boolean
	 * - true - successfully set,
	 * - false - not set and error occured
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
		$keys = $this->getKeys();

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
	 * ```
	 * $this->_readValues("title");
	 * $this->_readValues(array("title","create_date"));
	 * ```
	 *
	 * @param mixed $fields
	 * @ignore
	 * @return array
	 */
	function _readValues($fields = null){
		if(!isset($fields)){ $fields = $this->_fieldsToRead(); }
		if(is_array($fields))
		  $fields = join(",",$fields);
		if(!$row = $this->dbmole->selectFirstRow("SELECT $fields FROM ".$this->dbmole->escapeTableName4Sql($this->getTableName())." WHERE $this->_IdFieldName=:id",array(":id" => $this->_Id))){
			return null;
		}
		$this->_setRecordValues($row);

		return $this->_RecordValues;
	}

	/**
	 * $this->_readValuesIfWasNotRead("image_body");
	 * $this->_readValuesIfWasNotRead(array("body","perex"));
	 *
	 * @ignore
	 */
	function _readValuesIfWasNotRead($fields){
		if(!isset($this->_RecordValues[$this->_IdFieldName])){ return; } // HACK for a virtual object (for some reason we couldn't call $this->getId(), unless the method getId() is marked final)

		if(!is_array($fields)){ $fields = array($fields); }

		$fields_to_be_read = array_diff($fields,array_keys($this->_RecordValues));

		if($fields_to_be_read){
			$this->_readValues($fields_to_be_read);
		}
	}


	/**
	 * Deletes current record.
	 *
	 * ```
	 * $article = $article->destroy();
	 * ```
	 *
	 * @return null
	 */
	function destroy(){
		$this->_Hook_BeforeDestroy();
		$this->dbmole->doQuery("DELETE FROM ".$this->dbmole->escapeTableName4Sql($this->getTableName())." WHERE $this->_IdFieldName=:id",array(":id" => $this->_Id));
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
		$class_name = get_class($this);

		$options += array(
			"use_cache" => TABLERECORD_USE_CACHE_BY_DEFAULT,
		);

		foreach($values as $_key => $value){
			if(isset($options["validates_inserting_of_fields"]) && !in_array($_key,$options["validates_inserting_of_fields"])){
				unset($values[$_key]);
				continue;
			}

			if(is_object($values[$_key])){ $values[$_key] = $this->_objectToScalar($values[$_key]); }
		}

		$id = null;
		if(isset($values[$this->_IdFieldName])){
			$id = $values[$this->_IdFieldName];
		}else{
			$id = call_user_func(array($class_name,"GetNextId"));
			if(!is_null($id)){
				$values[$this->_IdFieldName] = $id;
			}
		}

		if(!$this->dbmole->insertIntoTable($this->getTableName(),$values,$options)){ return null; }

		if(!isset($id)){
			$id = $this->dbmole->selectInsertId();
		}
		
		Cache::Clear(get_class($this),$id); // ensure that the newly created record is not stored in the Cache (obviously as null)
		$out = TableRecord::_GetInstanceById(get_class($this),$id,array("use_cache" => $options["use_cache"]));
		$out->_Hook_afterCreateNewRecord();
		return $out;
	}

	/**
	 * @ignore
	 */
	function _fieldsToRead(){
		$out = array();
		foreach($this->_getTableStructure() as $field => $vals){
			if(in_array($field,self::$_DoNotReadValues)){ continue; }
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
	final function __toString(){
		return (string)$this->toString();
	}

	/**
	 * Method called automatically before serialization.
	 *
	 * @ignore
	 */
	function __sleep(){
		$this->_dbmole_wakeup_data_ = array(
			"class_name" => get_class($this->dbmole),
			"configuration" => $this->dbmole->getConfigurationName(),
		);
		$vars = get_object_vars($this);
		unset($vars["dbmole"]);
		return array_keys($vars);
	}

	/**
	 * Method called automatically after serialization.
	 *
	 * @ignore
	 */
	function __wakeup(){
		$class_name = get_class($this);
		$dbmole_class_name = $this->_dbmole_wakeup_data_["class_name"];
		$dbmole_configuration = $this->_dbmole_wakeup_data_["configuration"];
		$this->dbmole = $dbmole_class_name::GetInstance($dbmole_configuration);
	}

	/**
	 * Magic method changes calling to an nonexistent method in this way:
	 *
	 * $object->getEmailAddress() -> $object->getValue("email_address");
	 *
	 * $object->getUserId() -> $object->getValue("user_id");
	 * $object->getUser() -> User::GetInstanceById($object->getValue("user_id"));
	 *
	 * @param string $name
	 * @param string $arguments
	 * @ignore
	 */
	function __call($name,$arguments){
		static $CACHES = array();

		$class_name = get_class($this);

		if(!isset($CACHES[$class_name])){
			$CACHES[$class_name] = array(
				"fields" => array(),
			);
		}

		$CACHE = &$CACHES[$class_name];

		if(isset($CACHE["fields"][$name])){
			return $this->getValue($CACHE["fields"][$name]);
		}

		$name = new String4($name);
		if($name->match("/^get(.+)/",$matches)){
			$field = $matches[1]->underscore();
			if($this->hasKey($field)){
				$CACHE["fields"][(string)$name] = (string)$field;
				return $this->getValue($field);
			}

			// Looking for ClassName or inobj_ClassName. The prefix inobj_ (which means internal object) exists on my legacy classes.
			if($this->hasKey("{$field}_id") && (class_exists($c = (string)$field->camelize()) || class_exists($c = "inobj_$c"))){
				return call_user_func_array(array($c,"GetInstanceById"),array($this->getValue("{$field}_id")));
			}
		}

		throw new Exception("TableRecord::__call(): unknown method $class_name::$name()");
	}

	/**
	 * @ignore
	 */
	static function __callStatic($name,$arguments){
		$class_name = function_exists("get_called_class") ? get_called_class() : "unknown";

		if(preg_match('/^Find(|First|All)By(.+)/',$name,$matches)){
			$method = $matches[1]=="All" ? "FindAll" : "FindFirst";
			$field = new String4($matches[2]);
			$field = $field->underscore();
			$params = array("$field",$arguments[0],isset($arguments[1]) ? $arguments[1] : array());
			return call_user_func_array(array($class_name,$method),$params);
		}

		throw new Exception("TableRecord::__callStatic(): unknown static method $class_name::$name()");
	}

	/**
	 * Reads physical database table structure into internal structure
	 *
	 * It must be covered by a descendant.
	 * @param array $options standard dbmole options
	 * @see DbMole::selectIntoAssociativeArray() to see options
	 */
	function _readTableStructure($options = array()){
		$accessor_class = "TableRecord_DatabaseAccessor_".$this->dbmole->getDatabaseType();
		return $accessor_class::ReadTableStructure($this,$options);
	}

	/**
	 * Returns table structure of the given table in a associative array
	 *
	 * ```
	 * $structure = $this->_getTableStructure();
	 * ```
	 */
	function _getTableStructure(&$keys = null){
		$cache_key = $this->dbmole->getDatabaseType().".".$this->dbmole->getConfigurationName().".".$this->getTableName(); // e.g. "postgresql.default.articles"

		if(!isset(self::$_TableStructuresCache[$cache_key])){
			$structure = $this->_readTableStructure(array("cache" => self::$TableStructuresCacheDuration));

			if(!$structure && self::$TableStructuresCacheDuration){
				// This fix covers situation when a table is created after a cache for its structure was saved
				$structure = $this->_readTableStructure(array("recache" => true));
			}

			if(!$structure){
				throw new Exception("There is not table ".$this->getTableName()." in the database ".$this->dbmole->getDatabaseName()." (".$this->dbmole->getDatabaseType().")");
			}
			
			self::$_TableStructuresCache[$cache_key] = $structure;
			self::$_TableStructureKeysCache[$cache_key] = array_keys($structure);
		}

		$keys = self::$_TableStructureKeysCache[$cache_key];
		return self::$_TableStructuresCache[$cache_key];
	}

	/**
	 * Sets record values into internal structures.
	 *
	 * @param array $row raw data read from table
	 */
	function _setRecordValues($row){
		static $INT_TYPES_CACHE = array();

		$database_type = $this->dbmole->getDatabaseType();
		$accessor_class = "TableRecord_DatabaseAccessor_$database_type";
		$structure = $this->_getTableStructure();

		if(!isset($INT_TYPES_CACHE[$database_type])){ $INT_TYPES_CACHE[$database_type] = array(); }

		foreach($row as $key => $value){
			if($value===null){
				$this->_RecordValues[$key] = null;
				continue;
			}

			$type = isset($structure[$key]) ? $structure[$key] : null;
			if(!$type){
				$internal_type = "string";
			}else{
				if(!isset($INT_TYPES_CACHE[$database_type][$type])){
					$INT_TYPES_CACHE[$database_type][$type] = $accessor_class::DatabaseTypeToInternalType($type);
				}
				$internal_type = $INT_TYPES_CACHE[$database_type][$type];
			}

			if($internal_type == "boolean"){
				$this->_RecordValues[$key] = $this->dbmole->parseBoolFromSql($value);
				continue;
			}
			if($internal_type == "timestamp"){
				$this->_RecordValues[$key] = substr($value,0,19);
				continue;
			}
			if($internal_type == "integer"){
				#in 32 system integer can overflow, but float can be sufficient 
				$real = (float)$value;
				$value = (int)$value;
				if($value!=$real){
					$value = $real;
				}
				$this->_RecordValues[$key] = $value;
				continue;
			}
			if($internal_type == "float"){
				$this->_RecordValues[$key] = (float)$value;
				continue;
			}
			$this->_RecordValues[$key] = $value;
		}
		$this->_Id = $this->_RecordValues[$this->_IdFieldName];
	}

	/**
	 * Convert an object into a scalar value
	 *
	 * $scalar_value = $this->_objectToScalar($object);
	 *
	 * @ignore
	 */
	private function _objectToScalar($object){
		if(!is_object($object)){
			return $object;
		}

		if(method_exists($object,"getId")){ return $object->getId(); }
		if(method_exists($object,"toString")){ return $object->toString(); }
		if(method_exists($object,"__toString")){ return $object->__toString(); }

		throw new Exception(sprintf("Can't convert %s object into a scalar value",get_class($object)));
	}

	/**
	 * Flushes out all data from the table structure cache
	 *
	 * It may be useful in tasks like db schema migration...
	 *
	 * ```
	 * TableRecord::FlushTableStructureCache();
	 * ```
	 */
	static function FlushTableStructureCache(){
		self::$TableStructuresCacheDuration = 0;
		self::$_TableStructuresCache = array();
	}

	/**
	 * Instantiates ObjectCacher for caching objects of the given class
	 *
	 * @return ObjectCacher
	 */
	static function CreateObjectCacher(){
		return new ObjectCacher(get_called_class());
	}
}
