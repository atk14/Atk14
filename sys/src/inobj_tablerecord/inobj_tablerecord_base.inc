<?
/**
* define("INOBJ_TABLERECORD_CACHES_STRUCTURES",60 * 60); // cachovani struktur po dobu 1 hodiny
*/
class inobj_TableRecord_Base extends inobj{
	var $_TableName = "";				// jmeno tabulky v db; nastavuje se v konstruktoru
	var $_SequenceName = "";		// jmeno sequence na primarnim klici

	var $_Id = null;	// integer nebo string
	var $_IdFieldName = "id";
	var $_IdFieldType = "integer";

	var $_TableStructure = null;

	var $_DoNotReadValues = array(); // pole, jejiz hodnoty se nemaji nacitat behem vytvareni instanci; array("image_body")
	var $_RecordValues = array();


	function inobj_TableRecord_Base($table_name,$options = array()){
		inobj::inobj();

		$this->_TableName = (string)$table_name;

		$options = array_merge(array(
			"do_not_read_values" => array(),
			"id_field_name" => "id",
			"id_field_type" => "integer",
			"sequence_name" => $this->_DetermineSequenceName(),
		),$options);

		$this->_SequenceName = $options["sequence_name"];
		$this->_IdFieldName = $options["id_field_name"];
		$this->_IdFieldType = $options["id_field_type"];
		$this->_DoNotReadValues = $options["do_not_read_values"];

		$cache = defined("INOBJ_TABLERECORD_CACHES_STRUCTURES") ? INOBJ_TABLERECORD_CACHES_STRUCTURES : 0;
		if(defined("DEVELOPMENT") && DEVELOPMENT){ $cache = 0; }
		$this->_readTableStructure(array("cache" => $cache));

		// vsechny hodnoty tohoto objektu nastavime na null
		reset($this->_TableStructure);
		while(list($_key,) = each($this->_TableStructure)){
			$this->_RecordValues[$_key] = null;
		}
	}

	/**
	* Works in PHP5.3 and above.
	* 
	* $article = Article::GetInstanceById($id); // there's no need to define GetInstanceById() in the Article class.
	*/
	static function GetInstanceById($id,$options = array()){
		return inobj_TableRecord::_GetInstanceById(get_called_class(),$id,$options);
	}

	/**
	* Works in PHP5.3 and above.
	* 
	* $article = Article::CreateNewRecord(array("title" => "February Highlights")); // there's no need to define CreateNewRecord() in the Article class.
	*/
	static function CreateNewRecord($id,$options = array()){
		return inobj_TableRecord::_CreateNewRecord(get_called_class(),$id,$options);
	}

	function &GetDbmole(){
		// TODO: toto je takove vachrlate
		if(class_exists("OracleMole")){
			return OracleMole::GetInstance();
		}
		return PgMole::GetInstance();
	}

	/**
	* $article = inobj_Article:GetInstanceById(123);
	* $id = inobj_TableRecord::ObjToId($article); // returns 123
	* $id = inobj_TableRecord::ObjToId(123); // returns 123
	* $id = inobj_TableRecord::ObjToId(null); // returns null
	*/
	function ObjToId($object){
		return is_object($object) ? $object->getId() : $object;
	}

	/**
	* Converts an $id (integer) to instance of a $class_name.
	*
	* $article = inobj_TableRecord::IdToObj(123,"inobj_Article");
	* $article = inobj_TableRecord::IdToObj(null,"inobj_Article"); // returns null
	* 
	* $article = inobj_Article:GetInstanceById(123);
	* $article = inobj_TableRecord::IdToObj($article,"inobj_Article"); // returns $article untouched
	*/
	function IdToObj($id,$class_name){
		if(!isset($id)){ return null; }
		if(is_object($id)){ return $id; }
		return call_user_func(array($class_name,"GetInstanceById"),$id);
	}

	function getTableName(){ return $this->_TableName; }
	function getSequenceName(){ return $this->_SequenceName; }
	function getId(){ return $this->_RecordValues[$this->_IdFieldName]; }
	function hasKey($key){ return in_array($key,array_keys($this->_RecordValues)); }

	/**
	* $article = inobj_Article::GetInstanceById(111);
	* $author = $article->getBelongsTo("author");
	*
	* $author = $article->getBelongsTo("author",array(
	*		"class_name" => "inobj_Author",
	*		"attribute_name" => "author_id"
	*	));
	*
	*/
	function getBelongsTo($object,$options = array()){
		$options = array_merge(array(
			"class_name" => "inobj_".str_replace("_","",$object),
			"attribute_name" => "{$object}_id"
		),$options);

		$class_name = $options["class_name"];
		$attribute_name = $options["attribute_name"];
		
		eval("\$out = $class_name::GetInstanceById(".$this->getValue($options["attribute_name"]).");");
		return $out;
	}

	/**
	* Automaticke stanoveni nazvu sequence z nazvu tabulky.
	* Toto je vychozi nastaveni, ktere funguje v GR.
	* 
	* @access private
	*/
	function _DetermineSequenceName(){
		return "seq_$this->_TableName";
	}

	/**
	* Vyhleda zaznam podle id.
	* 
	* @access public
	* @param mixed $id							integer, string, objekt, pole
	* @return inobj_TableRecord			nebo null, pokud zaznam neexistuje (nebo doslo k chybe);
	*																pokue je $id pole, bude vraceno rovnez pole
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
	* $finder = inobj_TableRecord::Finder(array(
	*		"class_name" => "Book",
	*		"conditions" => array("title LIKE :q"),
	*		"bind_ar" => array(":q" => "%Prague%"),
	*		"limit" => 20,
	*		"offset" => 0,
	*	));
	*
	*	$total_amount = $finder->getTotalAmount();
	*	$books = $finder->getRecords();
	*
	* // it's possible to define the specific SQL query
	* $finder = inobj_TableRecord::Finder(array(
	*		"class_name" => "Book",
	*		"query" => "SELECT books.id FROM books,book_authors WHERE ...",
	*		"query_count" => "SELECT COUNT(*) FROM ...",
	*		"bind_ar" => $bind_ar,
	*		"order" => null, // nekdy je dobre nenechat metodu Finder pripojit ORDER BY automaticky
	*	));
	*
	* // conditions could be passed as an associative array
	*	$finder = inobj_TableRecord::Finder(array(
	*		"class_name" => "Book",
	*		"conditions" => array(
	*			"author_id" => 123,
	*		),
	*	));
	*
  * // as of PHP5.3
	* $finder = Book::Finder(array(
	*		"limit" => 20,
	*	));
	*/
	static function Finder($options){
		if(isset($options["class_name"])){
			$class_name = $options["class_name"];
			unset($options["class_name"]);
		}else{
			$class_name = get_called_class();
		}
		$obj = new $class_name();
		return $obj->_finder($options);
	}

	function _finder($options){
		// order_by se prevede na order
		if(in_array("order_by",array_keys($options))){
			$options["order"] = $options["order_by"];
			unset($options["order_by"]);
		}

		$options = array_merge(array(
			"order" => $this->_IdFieldName,
			"conditions" => array(),
			"bind_ar" => array(),
			"limit" => 20,
			"offset" => 0,

			"query" => null,
			"query_count" => null,
		),$options);

		$conditions = $options["conditions"];
		if(is_string($conditions) && strlen($conditions)==0){ $conditions = array(); }
		if(is_string($conditions)){ $conditions = array($conditions); }
		$bind_ar = $options["bind_ar"];

		inobj_TableRecord_Base::_NormalizeConditions($conditions,$bind_ar);

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

		$finder = new inobj_TableRecord_Finder(array(
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
	* Pro pripady, kdy je potreba nekam vratit Finder, ktery se bude tvarit jako prazdny.
	* Vhodna napr. pri vyskytu chyby ve vyhledavacim formulari.
	*
	* $finder = inobj_TableRecord::EmptyFinder();
	*/
	function EmptyFinder(){
		return new inobj_TableRecord_EmptyFinder();
	}


	/**
	* $articles = inobj_TableRecord::FindAll(array(
	*		"class_name" => "inobj_Article",
	*		"conditions" => array("deleted='N'","published='Y'"),
	*		"order" => "create_date",
	*		"limit" => 20,
	*		"offset" => 80,
	* ));
	*
	* // as of PHP5.3
	* $articles = Article::FindAll(array(
	*		"conditions" => array("deleted='N'","published='Y'"),
	*		"order" => "create_date",
	*		"limit" => 20,
	*		"offset" => 80,
	* ));
	*
	* TODO: obsah metody predelat jako implementaci volani inobj_TableRecord::Finder()
	*/
	static function FindAll($options = array()){
		if(isset($options["class_name"])){
			$class_name = $options["class_name"];
			unset($options["class_name"]);
		}else{
			$class_name = get_called_class();
		}
		$obj = new $class_name();
		return $obj->_findAll($options);
	}

	function _findAll($options = array()){
		// order_by se prevede na order
		if(in_array("order_by",array_keys($options))){
			$options["order"] = $options["order_by"];
			unset($options["order_by"]);
		}

		$options = array_merge(array(
			"order" => $this->_IdFieldName,
			"conditions" => array(),
			"bind_ar" => array()
		),$options);

		$conditions = $options["conditions"];
		if(is_string($conditions) && strlen($conditions)==0){ $conditions = array(); }
		if(is_string($conditions)){ $conditions = array($conditions); }
		$bind_ar = $options["bind_ar"];

		inobj_TableRecord_Base::_NormalizeConditions($conditions,$bind_ar);

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
	* $article = inobj_TableRecord::FindFirst(array(
	*		"class_name" => "Article",
	*		"conditions" => array(
	*			"created_at" => "2011-02-01" 
	*		),
	*	));
	*
	*	// as of PHP5.3
	* $article = Article::FindFirst(array(
	*		"conditions" => array(
	*			"created_at" => "2011-02-01" 
	*		),
	*	));
	*
	*/
	static function FindFirst($options = array()){
		if(isset($options["class_name"])){
			$class_name = $options["class_name"];
			unset($options["class_name"]);
		}else{
			$class_name = get_called_class();
		}
		$obj = new $class_name();
		return $obj->_findFirst($options);
	}

	function _findFirst($options = array()){
		$options["limit"] = 1;
		$record = $this->FindAll($options);

		if(isset($records[0])){ return $records[0]; }
		return null;
	}

	// prevod asociativniho pole podminek na pole indexovane
	function _NormalizeConditions(&$conditions,&$bind_ar){
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
	* Vytvori instance podle pole id.
	*
	* Prisl. instrance budou vraceny v poli pod stejnym indexem.
	*
	* Pokud zaznam pro nektere id nebude existovat, bude v danem miste vystupniho pole null.
	*
	* Muze byt rovnez pouzito asociativni pole!
	*
	* @access private
	*	@param array $ids				ie array(1223,1224,1225) nebo array("product1"=>1223,"product2"=>1224,"product3"=>1225)
	* @return array
	*/
	function _FindByArray($ids,$options = array()){
		settype($ids,"array");

		$options = array_merge(array(
			"omit_nulls" => false
		),$options);

		$MAX_ELEMENTS = 200;
		if(sizeof($ids)>$MAX_ELEMENTS){
			$out = array();

			$part = array();
			$counter = 0;
			reset($ids);
			while(list($key,$value) = each($ids)){
				$part[$key] = $value;
				$counter ++;

				if($counter == $MAX_ELEMENTS){
					$_out = $this->_FindByArray($part,$options);
					reset($_out);
					while(list($_key,) = each($_out)){
						$out[$_key] = $_out[$_key];
					}
					$part = array();
					$counter = 0;
				}
			}

			$_out = $this->_FindByArray($part,$options);
			reset($_out);
			while(list($_key,) = each($_out)){
				$out[$_key] = $_out[$_key];
			}

			return $out;
		}

		$bind_ar = array();

		$class_name = get_class($this);

		$i = 0;
		reset($ids);
		while(list($_key,$_value) = each($ids)){
			if(!isset($_value)){ continue; } // v poli se muze klidne nachazet nejaky null
			settype($ids[$_key],$this->_IdFieldType);
			$id = $ids[$_key];
			$bind_ar[":id$i"] = $id;
			$i++;
		}

		$objs = array();

		if(sizeof($bind_ar)>0){
			$query = "SELECT ".join(",",$this->_fieldsToRead())." FROM ".$this->_dbmole->escapeTableName4Sql($this->_TableName)." WHERE $this->_IdFieldName IN (".join(", ",array_keys($bind_ar)).")";
			$rows = $this->_dbmole->selectRows($query,$bind_ar);
			if(!is_array($rows)){ return null; }
			while(list(,$row) = each($rows)){
				$obj = new $class_name();
				$obj->_setRecordValues($row);
				$obj->_Hook_Find();
				$objs[$obj->getId()] = $obj;
			}
		}

		$out = array();
		reset($ids);
		while(list($_key,$_value) = each($ids)){
			$id = $_value;
			if(!isset($objs[$id])){
				if(!$options["omit_nulls"]){ $out[$_key] = null; }
				continue;
			}
			$out[$_key] = &$objs[$id];
		}

		reset($out);
		return $out;
	}

	/**
	* Vrati hodnotu daneho policka (prip. policek).
	*
	* $u->getValue("name");
	* $u->getValue(array("name","email")); // returns array("Pan Davelka","davelka@gm.com")
	* $u->getValue(array("name" => "name","email" => "email")); // returns array("name" => "Pan Davelka", "email" => "davelka@gm.com")
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
	}
	function g($field_name){ return $this->getValue($field_name); }

	/**
	* Vrati pole vsech hodnot krome "id".
	*
	* Vytvorit kopii radku je pak velmi jednoduche:
	*	
	*		$article = inobj_Articles::GetInstanceById(1000);
	*		$article_copy = inobj_Articles::CreateNewRecord(
	*			$article->getValues();
	*		);
	*/
	function getValues($options = array()){
		$options = array_merge(array(
			"return_id" => false,
		),$options);
		$this->_readValueIfWasNotRead(array_keys($this->_TableStructure));
		$out = $this->_RecordValues;
		if(!$options["return_id"]){
			unset($out[$this->_IdFieldName]);
		}
		return $out;
	}

	/**
	* 
	*/
	function toArray(){ return $this->getValues(array("return_id" => true)); }

	/**
	* Vrati pole vsech poli, ktere zaznam obsahuje.
	* $rec->getKeys(); // array("id","title","body","perex");
	*/
	function getKeys(){
		return array_keys($this->_RecordValues);
	}

	/**
	* Nastavi hodnotu daneho policka v db.
	* 
	* @access public
	* @param string $field_name				jmeno db policka
	* @param mixed $value							hodnota (cislo, string...)
	* @return boolean									true -> uspesne nastaveno
	*																	false -> nenestaveno, doslo k chybe
	*/
	function setValue($field_name,$value,$options = array()){
		settype($field_name,"string");
		settype($options,"array");

		if(isset($options["do_not_escape"]) && $options["do_not_escape"]==true){
			$options["do_not_escape"] = array("$field_name");
		}else{
			unset($options["do_not_escape"]);
		}

		return $this->setValues(array("$field_name" => $value),$options);
	}

	/**
	* Nastavi vice hodnot v db.
	*
	* $this->setValues(array("paid" => "Y","paid_date" => "2007-10-29 15:13", "paid_note" => "zaplaceno"));
	*
	* @return boolean							true -> uspesne nastaveno
	*															false -> nenestaveno, doslo k chybe
	*/
	function setValues($data,$options = array()){
		settype($data,"array");
		settype($options,"array");

		$options = array_merge(array(
			"do_not_escape" => array(),
			"validates_updating_of_fields" => null,
		),$options);
		
		if(!is_array($options["do_not_escape"])){ $options["do_not_escape"] = array($options["do_not_escape"]); }

		$_keys = array_keys($data);
		foreach($_keys as $_key){
			if(isset($options["validates_updating_of_fields"]) && !in_array($_key,$options["validates_updating_of_fields"])){
				unset($data[$_key]);
				continue;
			}
			if(is_object($data[$_key])){
				$data[$_key] = $data[$_key]->getId();
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
	* Zkratka pro setValue() a setValues()
	* 
	* $rec->s("name","Jan Novak");
	* $rec->s(array(
	*		"name" => "Jan Novak",
	*		"birth_date" => "2001-01-01"
	*	));
	*
	* Oba priklady je mozne doplnit o $options:
	* $rec->s("create_at","NOW()",array("do_not_escape" => true));
	* $rec->s(array(
	*		"name" => "Jan Novak",
	*		"birth_date" => "2001-01-01",
	*		"create_at" => "NOW()"
	*	),array("do_not_escape" => "create_at"));
	*
	*/
	function s($field_name,$value = null,$options = array()){
		if(is_array($field_name)){
			if(!is_array($value)){ $value = array(); }
			return $this->setValues($field_name,$value);
		}
		return $this->setValue($field_name,$value,$options);
	}

	function setValueVirtually($field_name,$value){
		$this->setValuesVirtually(array("$field_name" => $value));
	}

	function setValuesVirtually($values){
		reset($values);
		$keys = array_keys($this->_RecordValues);

		while(list($_key,$_value) = each($values)){
			if(in_array($_key,$keys)){
				$this->_RecordValues[$_key] = $_value;
			}
		}
	}

	/**
	* Nacte udaje z db podle $this->_Id.
	* 
	* @param array $fields
	* @access private
	*/
	function _readValues($fields = null){
		if(!isset($fields)){ $fields = $this->_fieldsToRead(); }

		$fields = join(",",$fields);
		if(!$row = $this->_dbmole->selectFirstRow("SELECT $fields FROM ".$this->_dbmole->escapeTableName4Sql($this->_TableName)." WHERE $this->_IdFieldName=:id",array(":id" => $this->_Id))){
			return null;
		}
		$this->_setRecordValues($row);

		return $this->_RecordValues;
	}

	function _readValueIfWasNotRead($field){
		if(is_array($field)){
			foreach($field as $f){
				$this->_readValueIfWasNotRead($f);
			}
			return;
		}
		if(in_array($field,$this->_DoNotReadValues)){
			$this->_DoNotReadValues = array_diff($this->_DoNotReadValues,array($field));
			$this->_readValues(array($field));
		}
	}


	/**
	* Smaze tento zaznam.
	* Vraci null.
	*
	* $article = $article->destroy();
	*/
	function destroy(){
		$this->_Hook_BeforeDestroy();
		$this->_dbmole->doQuery("DELETE FROM ".$this->_dbmole->escapeTableName4Sql($this->_TableName)." WHERE $this->_IdFieldName=:id",array(":id" => $this->_Id));
		return null;
	}

	/**
	* 
	*/
	function _insertRecord($values,$options = array()){
		settype($values,"array");
		settype($options,"array");

		$_keys = array_keys($values);
		foreach($_keys as $_key){
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
		
		$out = inobj_TableRecord::_GetInstanceById(get_class($this),$id);
		$out->_Hook_afterCreateNewRecord();
		return $out;
	}

	function _fieldsToRead(){
		$out = array();
		foreach($this->_TableStructure as $field => $vals){
			if(in_array($field,$this->_DoNotReadValues)){ continue; }
			$out[] = $field;
		}
		return $out;
	}

	/**
	* Je volano po uspesnem vyhledani zaznamu z db a nastaveni hodnot do $this->_RecordValues.
	*
	*	Muze byt podle potreby prekryto potomkem. 
	* 
	* @access private
	*/
	function _Hook_Find(){
		
	}

	/**
	* Je volano po uspesne aktualizaci udaju - pop volani setValues().
	*
	*	Muze byt podle potreby prekryto potomkem. 
	*
	* @access private
	*/
	function _Hook_setValues($fields){
		
	}

	/**
	* Bude spusteno ihned po vytvoreni zaznamu.
	*/
	function _Hook_afterCreateNewRecord(){

	}

	function _Hook_BeforeDestroy(){

	}

	function __toString(){
		return sprintf("[%s #%s]",get_class($this),$this->getId());
	}
}
?>
