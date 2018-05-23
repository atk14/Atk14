<?php
/**
 * Simple XML parser.
 *
 * @filesource
 *
 */

if(!defined("XMOLE_AUTO_TRIM_ALL_DATA")){
	/**
	 * Defines default behaviour of data trimming
	 *
	 * @var boolean
	 */
	define("XMOLE_AUTO_TRIM_ALL_DATA",true);
}

/**
 * Simple XML parser.
 *
 * Outputs parsed XML into structured array.
 *
 * A node looks like this
 * ```
 *	array(
 *		"element" => "jmeno_elementu",
 *		"attribs" => array("jmeno_atributu" => "hodnota_atributu",...),
 *		"data" => "data_elementu",
 *		"children" => array(),
 *		"xml_source" => "" //usek z XML textu
 *	);
 * ```
 * where children field contains children elements.
 *
 * When different encodings are set class {@link Translate} is also required.
 * ```
 *	$XMole = new XMole();
 *	$XMole->set_input_encoding("utf8");
 *	$XMole->set_output_encoding("windows-1250");
 *	$_stat = $XMole->parse($DATA);
 *	if(!$_stat){
 *		echo $XMole->get_error_message();
 *	}
 *	$TREE = $XMole->get_xml_tree();
 *	unset($XMole);
 * ```
 *
 * Find element by path:
 * ```
 *	$username_tree = $XMole->get_first_matching_branch("Login/Username");
 *	$user_data = $XMole->get_data("Login/Username");
 *	$attribute_value = $XMole->get_attribute("Login/Username","case_sensitive");
 *	$branches = $XMole->get_all_matching_branches("kniha/nazev");
 * ```
 *
 * @package Atk14\XMole
 * @filesource
 * @uses Translate
 */
class XMole{
	
		/**
		 * Object returned by {@link xml_parser_create}
		 *
		 * @access private
		 * @var xml_parser
		 */
		var $_parser = null;
		
		/**
		 * Input XML data.
		 *
		 * Is set by {@link parse()} method.
		 *
		 * @access private
		 * @var string
		 * @see XMole::parse()
		 *
		 */
		var $_data = null;

		/**
		 * Error flag.
		 *
		 * True when error occurs during processing XML data.
		 *
		 * @access private
		 * @var boolean
		 */
		var $_error = false;
		
		/**
		 * Error description.
		 *
		 * Contains error description in case error occurs during processing XML data.
		 *
		 * @var string
		 * @see XMole::get_error_message()
		 */
		private $_error_msg = null;

		/**
		 * @ignore Internal storage of xml data
		 */
		var $_data_store = array();

		/**
		 * @ignore Internal storage of xml structure
		 */
		var $_xml_source_store = array();

		/**
		 * Input encoding
		 *
		 * Encoding is detected automatically or can be set by {@link set_input_encoding() set_input_encoding()} method.
		 *
		 * @access private
		 * @var string
		 * @see XMole::set_input_encoding() set_input_encoding
		 */
		private $_input_encoding = null;

		/**
		 * Output encoding
		 *
		 * Encoding is set automatically by input encoding or can be set by {@link set_output_encoding()} method.
		 *
		 * @var string
		 * @see XMole::set_output_encoding() set_output_encoding
		 */
		private $_output_encoding = null;

		/**
		 * Input encoding differs from output encoding so translation is needed
 		 */
		private $_translate=false;
		
		/**
		 * Xml elements tree
		 *
		 * @var array
		 */
		private $_tree = array();

		/**
		 * @ignore Internal array to store structures
		 * @var array
		 */
		private $_tree_references = array();

	/**
	 * Creates new instance.
	 *
	 * Data can also be passed as a parameter to be parsed immediately.
	 *
	 * Options description
	 *
	 * - trim_data - boolean - returns data from elements without white spaces at the beginning ant the end of the stored data. 
	 * Defaults to true
	 *
	 * @param string $xml_data
	 * @param array $options
	 */
	function __construct($xml_data = null,$options = array()){
		$options = array_merge(array(
			"trim_data" => XMOLE_AUTO_TRIM_ALL_DATA
		),$options);

		$this->set_trim_data($options["trim_data"]);

		if(isset($xml_data)){
			$this->parse($xml_data);
		}
	}


	/**
	 * Set new xml tree
	 *
	 * @todo some explanation needed
	 * @param array $tree
	 */
	function inherit($tree){
		$this->_tree=array(unserialize(serialize($tree)));
		return true;
	}
	


	/**
	 * Gets status of error flag.
	 *
	 * @return string
	 */
	function error(){ return $this->_error; }


	/**
	 * Parses XML data.
	 *
	 * @param string $xml_data 	XML data
	 * @param integer $err_code error code when a problem occurs in xml parser {@see xml_get_error_code()}. Does not have to be set when $err_message is set
	 * @param string $err_message error message
	 * @return boolean true on success, false on error
	 */
	function parse($xml_data,&$err_code = null,&$err_message = null){
		$err_code = null;
		$err_message = null;

		$xml_data = trim($xml_data);

		$this->_error = false;
		$this->_error_msg = null;

		if($xml_data==""){
			$this->_error = true;
			$this->_error_msg = $err_message = "empty XML data";
			return false;
		}

		//debug_print_backtrace();
		//die();

		$this->_data_store = array();
		$this->_tree = array();
		
		unset($this->_parser);
		$this->_parser = xml_parser_create();
		xml_set_object($this->_parser,$this);
		xml_set_element_handler($this->_parser, "_startElement", "_endElement");
		xml_set_character_data_handler($this->_parser, "_characterData");
    xml_parser_set_option($this->_parser, XML_OPTION_CASE_FOLDING, false);

		//automaticke zjisteni vstupniho kodovani
		//deje se v pripade, kdyz neni $this->_input_encoding nastaveno
		if(!isset($this->_input_encoding)){
			$this->_input_encoding = "";
			$_start = strpos($xml_data,'<?');
			if(!is_bool($_start)){
				$_stop = strpos($xml_data,'?>',$_start);
				if(!is_bool($_stop) && $_stop>$_start && ($_stop-$_start)<1000){
					$_tmp = substr($xml_data,$_start+2,$_stop-$_start-2);
					if(preg_match("/encoding=['\"]{0,1}([a-zA-Z0-9-]*)['\"]{0,1}/",$_tmp,$_matches)){
						$this->_input_encoding = $_matches[1];
					}
				}
			}
		}

		//pokud neni nastaveno vystupni kodovani,
		//bude nastaveno stejne jako vstupni
		if(!isset($this->_output_encoding) || $this->_output_encoding==''){
			$this->_output_encoding = $this->_input_encoding;
		}
		$this->_set_translate();
		$this->_data = $xml_data;
		//first reference head to my tree (respective forrest)
		$this->_tree_references=array(array('children' => &$this->_tree));

		$stat = xml_parse($this->_parser,$this->_data);
		if(!$stat){
			$this->_error = true;
			$err_code = xml_get_error_code($this->_parser);
			$this->_error_msg = $err_message = "XML parser error ($err_code): ".xml_error_string($err_code)." on line ".xml_get_current_line_number($this->_parser);
			xml_parser_free($this->_parser);
			return false;
		}

		xml_parser_free($this->_parser);

		if(sizeof($this->_tree_references)>1){
			// neco chybi do konce dokumentu...
			// toto muze nastat napr. u <xml><tag>DATA</tag>
			$this->_error = true;
			$this->_error_msg = $err_message = "missing the end of the document";
			return false;
		}
		return true;
	}

	/**
	 * Set trim data flag.
	 *
	 * When $trim param is not set the flag is set to true by default.
	 *
	 * @param boolean $trim defaults to true
	 */
	function set_trim_data($trim = true){
		$this->_trim_data = $trim;
	}

	/**
	 * Returns status of the trim_data flag.
	 *
	 * @return boolean
	 */
	function trim_data(){ return $this->_trim_data; }

	/**
	 * Sets both input and output encoding to the same value.
	 *
	 * @param string $encoding
	 */	
	function set_encoding($encoding){
		$this->set_input_encoding($encoding);
		$this->set_output_encoding($encoding);
	}

	/**
	 * Sets input character encoding of data.
	 *
	 * Must be called before {@link parse()}.
	 * When set_input_encoding() is not called input encoding will be detected automatically.
	 *
	 * @param string $encoding jmeno kodovani
	 */
	function set_input_encoding($encoding){
		settype($encoding,"string");
		$this->_input_encoding = $encoding;
		$this->_set_translate();
	}
	
	/**
	 * Initializes $this->_translate after encoding change.
	 *
	 * @ignore
	 */
	private function _set_translate(){
		$this->_translate=
			isset($this->_input_encoding) && $this->_input_encoding!="" && 
			isset($this->_output_encoding) && $this->_output_encoding!="" && 
			$this->_input_encoding!=$this->_output_encoding;
	}
	
	/**
	 * Get input encoding.
	 *
	 * Returns value that was set by {@link set_input_encoding()} or detected in {@link parse()}.
	 *
	 * @return string input character encoding
	 */
	function get_input_encoding(){
		return $this->_input_encoding;
	}

	/**
	 * Sets encoding for output data.
	 *
	 * Must be called before {@link parse()}
	 * When set_output_encoding() is not called before parse() output encoding will be set the same as input encoding.
	 *
	 * @param string $encoding character encoding
	 */
	function set_output_encoding($encoding){
		settype($encoding,"string");
		$this->_output_encoding = $encoding;
		$this->_set_translate();
	}

	/**
	 * Get output encoding.
	 *
	 * @return string output encoding
	 */
	function get_output_encoding(){
		return $this->_output_encoding;
	}

	/**
	 * Get error message.
	 *
	 * @return string String with error message
	 */
	function get_error_message(){
		return $this->_error_msg;
	}

	/**
	 * Returns XML tree.
	 *
	 * @return array XML tree
	 */
	function get_xml_tree(){
		return $this->_tree;
	}

	/**
	 * Get first matching branch of while XML tree.
	 *
	 * Tag name is specified by $path.
	 *
	 * ```
	 *	$xmole->get_first_matching_branch("/DistributedSearchXML/Login/Username")
	 * ```
	 *
	 * @param string $path
	 * @return array 	branch of XML tree or null when path is not found
	 */
	function get_first_matching_branch($path){
		settype($path,"string");

		//odseknuti posledniho lomitka,
		//pokud se v ceste nachazi
		if(strlen($path)>0 && $path[strlen($path)-1]=="/"){
			$path = substr($path,0,strlen($path)-1);
		}
		
		//$curent_path='/'
		//return $this->_search_branch_by_path($path,$current_path,$this->_tree);
		
		
		//$current_path='/';
		//$o2=$this->_search_branch_by_path($path,$current_path,$this->_tree);
		
		$top=$path=='' || $path[0]=='/'?1:0;
		$path=explode('/', $path);
		
		if(count($path)==$top)
		    return $this->_tree[0];
		return $this->_get_first_matching_branch($path, $top, $this->_tree);
		}

	/**
	 * @ignore
	 */
	private function _get_first_matching_branch($path, $top, $tree) {
		$desired=$path[$top];
		foreach($tree as $element){
			if($element['element']==$desired){
				$ntop=$top+1;
				if($ntop==count($path))
					return $element;
				$out=$this->_get_first_matching_branch($path, $ntop, $element["children"]);
				if($out)
					return $out;
			}
			elseif(!$top){
				$out=$this->_get_first_matching_branch($path, $top, $element["children"]);
				if($out)
					return $out;
			}
		}
		return null;
	}
	
	/**
	 * Get new XMole instance for a branch specified by path.
	 *
	 * @param string $path
	 * @return XMole	null when branch does not exist or when error occurs (which shouldn't).
	 */
	function get_xmole_by_first_matching_branch($path){
		if(!($element_data = $this->get_first_matching_branch($path))){
			return null;
		}
		$xmole = $this->_new_instance();

		if(!$xmole->parse($element_data["xml_source"])){
			return null;
		}

		return $xmole;
	}

	/**
	 * Returns branches matching given path
	 * 
	 * @param string $path
	 * @return string[] array of XMole objects
	 */
	function get_all_matching_branches($path){
		settype($path,"string");

		//odseknuti posledniho lomitka,
		//pokud se v ceste nachazi
		if($path[strlen($path)-1]=="/"){
		  $path = substr($path,0,strlen($path)-1);
		}

		//$current_path = "/";
		//return $this->_search_branches_by_path($path,$current_path,$this->_tree);
		
    $top=$path=='' || $path[0]=='/'?1:0;		  
		$path=explode('/', $path);
    $out=array();
    
    if(count($path)==$top)
		    return $this->_tree;
		$this->_get_all_matching_branches($out, $path, $top, $this->_tree);
		return $out;
	}

	/**
	 * @ignore
	 */
	private function _get_all_matching_branches(&$out, $path, $top, $tree) {
		$desired=$path[$top];
		foreach($tree as $element){
			if($element['element']==$desired){
				$ntop=$top+1;
				if($ntop==count($path))
					$out[]=$element;
				else
					$this->_get_all_matching_branches($out, $path, $ntop, $element["children"]);
			}
			if(!$top)
				$this->_get_all_matching_branches($out, $path, $top, $element["children"]);
		}
	}

	/**
	 * Shortcut to get_xmole_by_first_matching_branch method.
	 *
	 * @param string $path
	 * @return XMole
	 * @see get_xmole_by_first_matching_branch()
	 */
	function get_xmole($path){ return $this->get_xmole_by_first_matching_branch($path); }

	/**
	 * Returns XMole objects matching all branches that suit given path
	 *
	 * First finds all branches matching given path and returns its corresponding XMole objects
	 *
	 * @param string $path
	 * @return XMole[]
	 */
	function get_xmoles_by_all_matching_branches($path){
		$branches = $this->get_all_matching_branches($path);
		$out = array();
		for($i=0;$i<sizeof($branches);$i++){
			$xmole = $this->_new_instance();
			if(!$xmole->inherit($branches[$i])){
			//if(!$xmole->parse($branches[$i]["xml_source"])){
				return null;
			}
			$out[] = $xmole;
		}
		return $out;
	}

	/**
	 * Alias to get_xmoles_by_all_matching_branches method.
	 *
	 * @param string $path
	 * @return XMole[]
	 * @see get_xmoles_by_all_matching_branches()
	 */
	function get_xmoles($path){ return $this->get_xmoles_by_all_matching_branches($path); }

	/**
	 * Get XMole instance of first child element.
	 *
	 * Child elements are indexed starting from 0.
	 *
	 * @param integer $index index of the child
	 * @return XMole
	 */
	function get_child($index = 0){
		if(isset($this->_tree[0]["children"][$index])){
			$xmole = $this->_new_instance();
			if($xmole->parse($this->_tree[0]["children"][$index]["xml_source"])){
				return $xmole;
			}
		}
	}

	/**
	 * Get XMole instance of next child.
	 *
	 * increments internal position pointer to the next child and return the child XMole object.
	 *
	 * @return XMole
	 */
	function get_next_child(){
		if(!isset($this->_next_child_index)){ $this->_next_child_index = -1; }
		$this->_next_child_index++;
		return $this->get_child($this->_next_child_index);
	}

	/**
	 * Resets internal position pointer to the first child.
	 */
	function reset_next_child_index(){
		$this->_next_child_index = -1;
	}

	/**
	 * Get name of the element
	 *
	 * @return string
	 */
	function get_root_name(){ return $this->_tree[0]["element"];		}


	/**
	 * Returns data stored in element.
	 *
	 * Element is specified by $path.
	 * Returns first element matching the path
	 *
	 * Example
	 * ```
	 *	$xmole->get_element_data("Login/UserName");
	 * ```
	 * 
	 * @param string $path
	 * @return string|null Data from element or null if the element is not found
	 */
	function get_element_data($path = "/"){
		if($_tree = $this->get_first_matching_branch($path)){
			return isset($_tree[0]["data"]) ? $_tree[0]["data"] : $_tree["data"];
		}
	}

	/**
	 * Alias to get_element_data method.
	 *
	 * @param string $path
	 * @return string
	 */
	function get_data($path = "/"){ return $this->get_element_data($path); }

	/**
	 * Returns value from tags attribute.
	 *
	 * Element is specified by $path
	 * If finds first element if there are more.
	 *
	 * Element
	 *
	 * @param string $element_path
	 * @param string $attribute_name Name of attribute_name
	 * @return string|null Value of the attribute or null if the element is not found or the element does not contain specified attribute
	 */
	function get_attribute_value($element_path,$attribute_name = null){
		if(!isset($attribute_name)){
			$attribute_name = $element_path;
			$element_path = "/";
		}
		$attrs = $this->get_attributes($element_path);
		if(isset($attrs[$attribute_name])){
			return $attrs[$attribute_name];
		}
	}
	/**
	 * Alias to get_attribute_value method.
	 * 
	 * @param string $element_path
	 * @param string $attribute_name
	 * @return string
	 */
	function get_attribute($element_path,$attribute_name = null){ return $this->get_attribute_value($element_path,$attribute_name); }

	/**
	 * Get attributes of an element
	 *
	 * Element is specified by $path.
	 *
	 * @param string $element_path
	 * @return array
	 */
	function get_attributes($element_path = "/"){
		settype($element_path,"string");

		if($_tree = $this->get_first_matching_branch($element_path)){
			return isset($_tree[0]["attribs"]) ? $_tree[0]["attribs"] : $_tree["attribs"];
		}
	}

	/**
	 * Compares this instance XML with XML from another instance.
	 *
	 * Order of attributes is not important.
	 * XMLs with different order of attributes will be evaluated as same.
	 *
	 * We have two xmls:
	 * ```
	 *	$xml_1 = '
	 *		<lide>
	 *		 <kluk vek="12" vyska="163" />
	 *		</lide>
	 *	';
	 *
	 *	$xml_2 = '
	 *		<lide>
	 *		 <kluk vyska="163" vek="12" />
	 *		</lide>
	 *	';
	 * ```
	 * We can compare objects
	 * ```
	 *	$xm1 = new XMole($xml_1);
	 *	$xm2 = new XMole($xml_2);
	 *	if($xm1->is_same_like($xm2)){
	 *		// same
	 *	}
	 * ```
	 * or we can compare row xml as they are internally compared as objects
	 * ```
	 *	if($xm1->is_same_like($xml_2)){
	 *		// same
	 *	}
	 * ```
	 * 
	 * @param XMole $xmole
	 * @return boolean
	 */
	function is_same_like($xmole){
		if(is_string($xmole)){ $xmole = new XMole($xmole); }
		if($xmole->error() || $this->error()){ return null; }

		$this_tree = $this->get_xml_tree();
		$that_tree = $xmole->get_xml_tree();

		if(sizeof($this_tree)!=sizeof($that_tree)){ return false; }

		for($i=0;$i<sizeof($that_tree);$i++){
			if(!$this->_compare_xml_branch($that_tree[$i],$this_tree[$i])){ return false; }
		}

		return true;
	}

	/**
	 * @ignore
	 */
	private function _compare_xml_branch($that_branch,$this_branch){
		if(!(
			$that_branch["element"]==$this_branch["element"] &&
			$that_branch["attribs"]==$this_branch["attribs"] &&
			$that_branch["data"]==$this_branch["data"] &&
			sizeof($that_branch["children"])==sizeof($this_branch["children"])
		)){ return false; }

		for($i=0;$i<sizeof($that_branch["children"]);$i++){
			if(!$this->_compare_xml_branch($that_branch["children"][$i],$this_branch["children"][$i])){ return false; }
		}
		return true;
	}

	/**
	 * Compares two xml data.
	 *
	 * Checks if two XML data are the same.
	 * Compared data can be strings or XMole instances.
	 *
	 * @param string|XMole $xmole1
	 * @param string|XMole $xmole2
	 * @return boolean
	 */
	static function AreSame($xmole1,$xmole2){
		if(is_string($xmole1)){ $xmole1 = new XMole($xmole1); } 
		if(is_string($xmole2)){ $xmole2 = new XMole($xmole2); } 

		return $xmole1->is_same_like($xmole2);
	}

	/**
	 * Tato fce je volana rekurzivne pri vyhledavani vetve XML stromu podle cesty.
	 * Prvni volani je z fce get_first_matching_branch().
	 *
	 * @see XMole::get_first_matching_branch()
	 * @internal not used any more ?
	 *
	 * @ignore
	 * @param string $wished_path				pozadovana cesta
	 * @param string $current_path				aktualni cesta
	 * @param array $xml_tree						vetev xml stromu
	 */
	private function _search_branch_by_path($wished_path,$current_path,&$xml_tree){
		settype($wished_path,"string");
		settype($current_path,"string");

		if($wished_path==""){
			return $xml_tree;
		}

		$_current_path = $current_path;
		for($i=0;$i<sizeof($xml_tree);$i++){

			if($current_path=="/"){
				$_current_path = "/".$xml_tree[$i]["element"];
			}else{
				$_current_path = $current_path."/".$xml_tree[$i]["element"];
			}

			//porovnani cele cesty - cesta musi zacinat znakem ""
			if($wished_path[0]=="/"){
				if($_current_path==$wished_path){
					return $xml_tree[$i];
				}
			
			//porovnani konce cesty - cesta nesmi zacinat znakem "/"	
			}elseif(substr($_current_path,-strlen($wished_path))==$wished_path){
				return $xml_tree[$i];
			}

			$_out = $this->_search_branch_by_path($wished_path,$_current_path,$xml_tree[$i]["children"]);
			if(isset($_out)){
				return $_out;
			}
		}

		return null;
	}

	/**
	 * Tato fce je volana rekurzivne pri vyhledavani vetvi XML stromu podle cesty.
	 *	Vraceno je pole vsech vetvi, ktere vyhovuji $wished_path.
	 *
	 * @see XMole::get_all_matching_branches()
	 * @internal obsoleted ?
	 *
	 * @ignore
	 * @param string $wished_path				pozadovana cesta
	 * @param string $current_path				aktualni cesta
	 * @param array $xml_tree
	 * @return array					pole $xml_tree
	 */
	private function _search_branches_by_path($wished_path,$current_path,&$xml_tree){
		settype($wished_path,"string");
		settype($current_path,"string");

		$out = array();

		if($wished_path==""){
			return array();
		}

		$_current_path = $current_path;
		for($i=0;$i<sizeof($xml_tree);$i++){

			if($current_path=="/"){
				$_current_path = "/".$xml_tree[$i]["element"];
			}else{
				$_current_path = $current_path."/".$xml_tree[$i]["element"];
			}

			//porovnani cele cesty - cesta musi zacinat znakem ""
			if($wished_path[0]=="/"){
				if($_current_path==$wished_path){
					$out[] = $xml_tree[$i];
				}
			
			//porovnani konce cesty - cesta nesmi zacinat znakem "/"	
			}elseif(substr($_current_path,-strlen($wished_path))==$wished_path){
				$out[] = $xml_tree[$i];
			}

			$_out = $this->_search_branches_by_path($wished_path,$_current_path,$xml_tree[$i]["children"]);
			foreach($_out as $_item){
				$out[] = $_item;
			}
		}

		return $out;
	}
	
	/**
	 * Handler of a function used by xml_parser.
	 *
	 * @ignore
	 */
	protected function _startElement($parser,$name,$attribs){
		if($this->_translate){
			$name = Translate::Trans($name,$this->_input_encoding,$this->_output_encoding);
			foreach($attribs as $key => $value){
				$attribs[$key] = Translate::Trans($attribs[$key],$this->_input_encoding,$this->_output_encoding);
			}
		}

		$old_ref = &$this->_tree_references[sizeof($this->_tree_references)-1];
		$ref = &$old_ref["children"];

    //xml zdroj
		$_source_index = sizeof($this->_xml_source_store);
		$_xml_source_store = "<$name";
		
		foreach($attribs as $_name => $_value){
			$_xml_source_store .= " $_name=\"".XMole::ToAttribsValue($_value)."\"";
		}
		$_xml_source_store .= ">";
		$this->_xml_source_store[$_source_index] = $_xml_source_store;

		$ref[] = array(
			"element" => $name,
			"attribs" => $attribs,
			"data" => "",
			"children" => array(),
			"xml_source" => "",
			"_xml_source_starts_at_index_" => $_source_index			//Zapamatujeme si, kde tento text zacina v XML zdroji zacina.
																														//Pri uzavreni tohoto tagu potom bude source rekonstruovano.
		);
		//uschovani nove reference
		$this->_tree_references[] = &$ref[sizeof($ref)-1];

		//inicializace noveho _data_store
		$this->_data_store[] = "";
	}

	/**
	 * Handler of a function used by xml_parser.
	 *
	 * @ignore
	 */
	protected function _endElement($_parser,$name){
		$data = array_pop($this->_data_store);
		if($this->_translate){
			$data = Translate::Trans($data,$this->_input_encoding,$this->_output_encoding);
		}
		if($this->_trim_data){ $data = trim($data); }

		$ref = &$this->_tree_references[count($this->_tree_references)-1];
		$_start_source_index = $ref["_xml_source_starts_at_index_"];
		unset($ref["_xml_source_starts_at_index_"]);	//v teto chvili uz muzeme informaci o pocatecnim indexu v $this->_xml_source_store zapomenout...

		//pridavani, aktualizace do posledni reference
		$ref["data"] = $data;

		//xml zdroj
		$this->_xml_source_store[] = "</$name>";
		$_end_source_index = count($this->_xml_source_store);
		$_source_ar = array_slice($this->_xml_source_store, $_start_source_index, $_end_source_index - $_start_source_index);
		$ref["xml_source"] = join("",$_source_ar);
		
		//odstraneni posledni reference
		array_pop($this->_tree_references);
}

	/**
	 * Handler of a function used by xml_parser.
	 *
	 * @param $_parser
	 * @param $data
	 * @ignore
	 */
	protected function _characterData($_parser,$data){
		//pridavani do posledniho _data_store
		$this->_data_store[sizeof($this->_data_store)-1] .= $data;
		//xml zdroj
		$this->_xml_source_store[] = XMole::ToXML($data);
	}

	/**
	 * Safely encodes illegal input characters to XML entities.
	 *
	 * Output can be used in XML text.
	 *
	 * ```
	 *	$xml = "<data>".XMole::ToXML($value)."</data>";
	 * ```
	 *
	 * @param string $str
	 * @return string
	 */
	static function ToXML($str){
		settype($str,"string");
		$illegal_chars = array(
			'/&/',
			'/</',
			'/>/',
			'/\"/',
			'/\'/',
			'/[\x00-\x08\x0b-\x0c\x0e-\x1f]/', // characters invalid for XML 1.0; see http://www.w3.org/TR/2006/REC-xml-20060816/#dt-character
		);
		$replaces = array(
			"&amp;",
			"&lt;",
			"&gt;",
			"&quot;",
			"&apos;",
			"", // applies to XML-1.0
		);
		return preg_replace($illegal_chars, $replaces, $str);
	}

	/**
	 * Encodes some characters as XML entities.
	 *
	 * Output can then be used as a tags attribute value.
	 *
	 * Example
	 * ```
	 *	$xml = '<person name="'.XMole::ToAttribsValue($name).'" />';
	 * ```
	 *
	 * @param string $str string to be encoded
	 * @return string string encoded as xml entities
	 */
	static function ToAttribsValue($str){
		settype($str,"string");
		return strtr($str,
			array(
				"<" => "&lt;",
				">" => "&gt;",
				"&" => "&amp;",
				"\n" => " ",
				'"' => "&quot;",
				"'" => "&apos;"
			)
		);
	}

	/**
	 * Creates new instance of XMole and copies some attributes to it.
	 *
	 * @ignore
	 */
	function _new_instance(){
		$x = new XMole();
		$x->_trim_data = $this->_trim_data;
		$x->_input_encoding = $this->_input_encoding;
		$x->_output_encoding = $this->_output_encoding;
		return $x;
	}

	/**
	 * Outputs string representation of the object 
	 *
	 * @return string
	 */
	function __toString(){
		if(!isset($this->_tree[0])){
			return "[empty ".get_class($this)."]";
		}
		if($this->error()){
			return "[".get_class($this)." with invalid document: ".$this->get_error_message()."]";
		}
		return $this->_tree[0]["xml_source"];
	}
}
