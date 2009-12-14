<?
if(!defined("XMOLE_AUTO_TRIM_ALL_DATA")){
	/**
	* Definuje defaultni chovani - trimovani dat
	* @var boolean
	*/
	define("XMOLE_AUTO_TRIM_ALL_DATA",true);
}

/**
* Jednoduchy XML parser
*
* Parsuje XML do strukturovaneho pole.
*	Uzel pole je:
*		array(
*			"element" => "jmeno_elementu",
*			"attribs" => array("jmeno_atributu" => "hodnota_atributu",...),
*			"data" => "data_elementu",
*			"children" => array(),
*			"xml_source" => "" //usek z XML textu
*		);
*	kde children obsahuje children elementy
*
*	//pokud se nastavuje rozdilne encoding, je nutna i trida translate.
*	$XMole = new XMole();
*	$XMole->set_input_encoding("utf8");
*	$XMole->set_output_encoding("windows-1250");
*	$_stat = $XMole->parse($DATA);
*	if(!$_stat){
*		echo $XMole->get_error_message();
*	}
*	$TREE = $XMole->get_xml_tree();
*	unset($XMole);
*
* 
* Vyhledavani elementu podle cesty:
* $username_tree = $XMole->get_first_matching_branch("Login/Username");
* $user_data = $XMole->get_data("Login/Username");
* $attribute_value = $XMole->get_attribute("Login/Username","case_sensitive");
* $branches = $XMole->get_all_matching_branches("kniha/nazev");
*/

class XMole{
	
		/**
		* Objekt vraceny fci xml_parser_create
		* @access private
		* @var xml_parser
		*/
		var $_parser = null;
		
		/**
		* Vstupni XML data
		* Nastavuje se parametrem metody parse().
		* @access private
		* @var string
		* @see XMole::parse()
		*/

		var $_data = null;

		/**
		* Priznak chyby
		* Bude true v pripade, ze nastala chyba ve zpracovani.
		* @access private
		* @var boolean
		*/

		var $_error = false;
		
		/**
		* Popis chyby
		* Bude obsahovat popis chyby v pripade, ze nastala chyba ve zpracovani.
		* @access private
		* @var string
		* @see XMole::get_error_message()
		*/
		var $_error_msg = null;

		var $_element_store = array();
		
		var $_attrib_store = array();

		var $_data_store = array();

		var $_xml_source_store = array();

		/**
		* Vstupni kodovani.
		* Je zjistovano automaticky nebo je mozne jej nastavit volanim metody XMole::set_input_encoding().
		* @access private
		* @var string
		* @see XMole::set_input_encoding()
		*/
		var $_input_encoding = null;

		/**
		* Vystupni kodovani.
		* Je nastaveno automaticky podle vstupniho kodovani nebo je mozne jej nastavit volanim metody XMole::set_output_encoding().
		* @access private
		* @var string
		* @see XMole::set_output_encoding()
		*/
		var $_output_encoding = null;

		var $_tree = array();

		var $_tree_references = array();

	/**
	* Konstruktor tridy
	*
	* @access public
	* @changelist
	*		2005-11-17: $this->_parser se vytvari v metode XMole::parse()
	*/
	function XMole($xml_data = null,$options = array()){
		$options = array_merge(array(
			"trim_data" => XMOLE_AUTO_TRIM_ALL_DATA
		),$options);

		$this->set_trim_data($options["trim_data"]);

		if(isset($xml_data)){
			$this->parse($xml_data);
		}
	}

	function error(){ return $this->_error; }

	/**
	* Parsuje XML data
	*
	* @access public
	* @param string $xml_data 	XML data
	* @return boolean 					true v pripade uspechu, false v pripade neuspechu
	*/
	function parse($xml_data){
		$xml_data = trim($xml_data);

		$this->_error = false;
		$this->_error_msg = null;

		if($xml_data==""){
			$this->_error = true;
			$this->_error_msg = "empty XML data";
			return false;
		}

		$this->_element_store = array();
		$this->_attrib_store = array();
		$this->_data_store = array();

		$this->_tree = array();
		$this->_tree_references = array();

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
		if(!isset($this->_output_encoding)){
			$this->_output_encoding = $this->_input_encoding;
		}

		$this->_data = $xml_data;
		$stat = xml_parse($this->_parser,$this->_data);
		if(!$stat){
			$this->_error = true;
			$this->_error_msg = "XML parser error (".(xml_get_error_code($this->_parser))."): ".xml_error_string(xml_get_error_code($this->_parser))." on line ".xml_get_current_line_number($this->_parser);
			return false;
		}

		return true;
	}

	function set_trim_data($trim = true){
		$this->_trim_data = $trim;
	}

	function trim_data(){ return $this->_trim_data; }

	/**
	* Nastavuje znakove kodovani vstupnich dat.
	*
	* Nutno volat pred volanim metody parse(). Pokud volano nebude,
	* bude urceno vstupni kodovani automaticky ve chvili volani parse().
	*
	* @param string $encoding jmeno kodovani
	* @access public
	*/
	function set_input_encoding($encoding){
		settype($encoding,"string");
		$this->_input_encoding = $encoding;
	}

	/**
	* Vrati vstupni kodovani.
	*
	* Vrati to, co bylo nastaveno behem volani metody set_input_encoding,
	* nebo to, co bylo zjisteno automaticky v metode parse().
	*
	* @access public
	*/
	function get_input_encoding(){
		return $this->_input_encoding;
	}

	/**
	* Nastavuje znakove kodovani vystupnich dat.
	*
	* Nutno volat pred metodou parse().
	* Nebude-li volano pred metodou parse(), bude nastaveno na hodnotu vstupniho kodovani.
	*
	* @param string $encoding jmeno kodovani
	* @access public
	*/
	function set_output_encoding($encoding){
		settype($encoding,"string");
		$this->_output_encoding = $encoding;
	}

	/**
	* Vrati vystupni kodovani.
	*
	* @access public
	*/
	function get_output_encoding(){
		return $this->_output_encoding;
	}

	/**
	* Vrati chybovou zpravu.
	* 
	* @access public
	*/
	function get_error_message(){
		return $this->_error_msg;
	}

	/**
	* Vrati XML strom.
	*
	* @access public
	* @return array XML strom
	*/
	function get_xml_tree(){
		return $this->_tree;
	}

	/**
	* Vrati prvni nalezenou vetev z celeho XML stromu,
	* kde jmeno tagu odpovida ceste $path
	*
	* @access public
	* @param string $path		napr:. "/DistributedSearchXML/Login/UserName" nebo "Login/UserName"
	* @return array 				vetev XML stromu nebo null, pokud vetev nebyla nalezena
	*/
	function get_first_matching_branch($path){
		settype($path,"string");

		//odseknuti posledniho lomitka,
		//pokud se v ceste nachazi
		if(strlen($path)>0 && $path[strlen($path)-1]=="/"){
			$path = substr($path,0,strlen($path)-1);
		}

		$current_path = "/";

		return $this->_search_branch_by_path($path,$current_path,$this->_tree);
	}

	/**
	* Vrati novou instanci tridy XMole pro dany usek urceny cestou.
	*
	* @access public
	* @param string $path
	* @return XMole				nebo null, pokud vetev neexistuje nebo dojde k chybe v XML parsovani - to by nemelo...
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

	function get_all_matching_branches($path){
		settype($path,"string");

		//odseknuti posledniho lomitka,
		//pokud se v ceste nachazi
		if($path[strlen($path)-1]=="/"){
			$path = substr($path,0,strlen($path)-1);
		}

		$current_path = "/";

		return $this->_search_branches_by_path($path,$current_path,$this->_tree);
	}
	// zkratka
	function get_xmole($path){ return $this->get_xmole_by_first_matching_branch($path); }

	function get_xmoles_by_all_matching_branches($path){
		$branches = $this->get_all_matching_branches($path);
		$out = array();
		for($i=0;$i<sizeof($branches);$i++){
			$xmole = $this->_new_instance();
			if(!$xmole->parse($branches[$i]["xml_source"])){
				return null;
			}
			$out[] = $xmole;
		}
		return $out;
	}
	// zkratka
	function get_xmoles($path){ return $this->get_xmoles_by_all_matching_branches($path); }

	/**
	* vrati instanci XMole pro child element podle indexu (pocitano od nuly).
	*/
	function get_child($index = 0){
		if(isset($this->_tree[0]["children"][$index])){
			$xmole = $this->_new_instance();
			if($xmole->parse($this->_tree[0]["children"][$index]["xml_source"])){
				return $xmole;
			}
		}
	}
	function get_next_child(){
		if(!isset($this->_next_child_index)){ $this->_next_child_index = -1; }
		$this->_next_child_index++;
		return $this->get_child($this->_next_child_index);
	}

	function reset_next_child_index(){
		$this->_next_child_index = -1;
	}

	function get_root_name(){ return $this->_tree[0]["element"];		}


	/**
	* Vrati data element urceneho podle cesty.
	* Vracena jsou data prvniho elementu, ktery vyhovuje ceste.
	* 
	* @access public
	* @param string $path		napr:. "/DistributedSearchXML/Login/UserName" nebo "Login/UserName"
	* @return string 				data elementu nebo null, pokud element neni nalezen
	*/
	function get_element_data($path = "/"){
		if($_tree = $this->get_first_matching_branch($path)){
			return isset($_tree[0]["data"]) ? $_tree[0]["data"] : $_tree["data"];
		}
	}
	// zkratka
	function get_data($path = "/"){ return $this->get_element_data($path); }

	/**
	* Vrati hodnotu atributu elementu urceneho podle cesty.
	* Najde prvni element podle cesty a v nem se pokusi najit dany atribut.
	*
	* Element 
	*
	* @access public
	* @param string $element_path				cesta elementu
	* @param string $attribute_name			jmeno atributu
	* @return string										hodnota atributu nebo null, pokud nebyl element nalezen nebo neobsahuje takovy atribut
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
	// zkratka
	function get_attribute($element_path,$attribute_name = null){ return $this->get_attribute_value($element_path,$attribute_name); }

	function get_attributes($element_path = "/"){
		settype($element_path,"string");

		if($_tree = $this->get_first_matching_branch($element_path)){
			return isset($_tree[0]["attribs"]) ? $_tree[0]["attribs"] : $_tree["attribs"];
		}
	}

	/**
	* Porovna, zda XML rozparsovane v teto instanci je stejne jako XML z jine instance.
	* Roli nehraje poradi atributu. Tyto XML struktury budou vyhodnoceny jako stejne.
	* 
	* $xml_1 = '
	*		<lide>
	*		 <kluk vek="12" vyska="163" />
	*		</lide>
	* ';
	* $xml_2 = '
	*		<lide>
	*		 <kluk vyska="163" vek="12" />
	*		</lide>
	* ';
	*
	* $xm1 = new XMole($xml_1);
	* $xm2 = new XMole($xml_2);
	* if($xm1->is_same_like($xm2)){
	*		// stejne
	* }
	* // nebo
	* if($xm1->is_same_like($xml_2)){
	*		// stejne
	* }
	* 
	* 
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

	function _compare_xml_branch($that_branch,$this_branch){
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
	* Zjisti, zda jsou 2 XML stejna.
	* Parametry mohou byl stringy obsahujici XML nebo instance XMole.
	*/
	function AreSame($xmole1,$xmole2){
		if(is_string($xmole1)){ $xmole1 = new XMole($xmole1); } 
		if(is_string($xmole2)){ $xmole2 = new XMole($xmole2); } 

		return $xmole1->is_same_like($xmole2);
	}

	/**
	* Tato fce je volana rekurzivne pri vyhledavani vetve XML stromu podle cesty.
	* Prvni volani je z fce get_first_matching_branch().
	*
	* @see XMole::get_first_matching_branch()
	* @access private
	* @param string $wished_path				pozadovana cesta
	* @param string $current_path				aktualni cesta
	* @param array &$xml_tree						vetev xml stromu
	*/
	function _search_branch_by_path($wished_path,$current_path,&$xml_tree){
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
	* @access private
	* @param string $wished_path				pozadovana cesta
	* @param string $current_path				aktualni cesta
	* @return array					pole $xml_tree
	*/
	function _search_branches_by_path($wished_path,$current_path,&$xml_tree){
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
			reset($_out);
			while(list(,$_item) = each($_out)){
				$out[] = $_item;
			}
		}

		return $out;
	}
	
	/**
	* Handle funkce pro xml_parser.
	*
	* @access private
	*/
	function _startElement($parser,$name,$attribs){
		if(isset($this->_output_encoding) && strlen($this->_output_encoding)>0 && isset($this->_input_encoding) && strlen($this->_input_encoding)>0 && $this->_input_encoding!=$this->_output_encoding){
			$name = translate::trans($name,$this->_input_encoding,$this->_output_encoding);
			reset($attribs);
			while(list($key,) = each($attribs)){
				$attribs[$key] = translate::trans($attribs[$key],$this->_input_encoding,$this->_output_encoding);
			}
		}
		$this->_element_store[] = $name;

		$this->_attrib_store[] = $attribs;
	
		if(sizeof($this->_tree_references)==0){
			$ref = &$this->_tree;
		}else{
			$old_ref = &$this->_tree_references[sizeof($this->_tree_references)-1];
			$ref = &$old_ref["children"];
		}

		//xml zdroj
		$_source_index = sizeof($this->_xml_source_store);
		$_xml_source_store = "<$name";
		reset($attribs);
		while(list($_name,$_value) = each($attribs)){
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
	* Handle funkce pro xml_parser.
	*
	* @access private
	*/
	function _endElement($_parser,$name){
		$element = array_pop($this->_element_store);
		$attribs = array_pop($this->_attrib_store);
		$data = array_pop($this->_data_store);
		if(isset($this->_input_encoding) && $this->_input_encoding!="" && isset($this->_output_encoding) && $this->_output_encoding!="" && $this->_input_encoding!=$this->_output_encoding){
			$data = translate::trans($data,$this->_input_encoding,$this->_output_encoding);
		}
		if($this->trim_data()){ $data = trim($data); }

		$_reference_index = sizeof($this->_tree_references)-1;
		
		$_start_source_index = $this->_tree_references[$_reference_index]["_xml_source_starts_at_index_"];
		$_end_source_index = sizeof($this->_xml_source_store);
		unset($this->_tree_references[$_reference_index]["_xml_source_starts_at_index_"]);	//v teto chvili uz muzeme informaci o pocatecnim indexu v $this->_xml_source_store zapomenout...

		//pridavani, aktualizace do posledni reference
		$this->_tree_references[$_reference_index]["data"] = $data;

		//xml zdroj
		$this->_xml_source_store[$_end_source_index] = "</$name>";

		$_source_ar = array();
		for($i=$_start_source_index;$i<=$_end_source_index;$i++){
			$_source_ar[] = $this->_xml_source_store[$i];
		}
		$this->_tree_references[$_reference_index]["xml_source"] = join("",$_source_ar);

		//odstraneni posledni reference
		array_pop($this->_tree_references);
	}

	/**
	* Handle funkce pro xml_parser.
	*
	* @access private
	*/
	function _characterData($_parser,$data){
		//pridavani do posledniho _data_store
		$this->_data_store[sizeof($this->_data_store)-1] .= $data;

		//xml zdroj
		$this->_xml_source_store[] = XMole::ToXML($data);
	}

	/**
	* Bezpecne zakoduje nebezpecne znaky vstupniho textu do XML entit 
	*
	* Vystup je mozno pouzit mezi tagy XML textu. Mozno volat staticky.
	*
	* $xml = "<data>".XMole::ToXML($value)."</data>";
	*/
	function ToXML($str){
		settype($str,"string");
		return strtr($str,
			array(
				"<" => "&lt;",
				">" => "&gt;",
				"&" => "&amp;"
			)
		);	
	}

	/**
	* Bezpecne zakoduje nektere znaky vstupniho textu do XML entit
	*
	* Vystup je mozno bezpecne pouzit jako hodnotu atributu tagu XML textu. Mozno volat staticky.
	*
	* $xml = '<person name="'.XMole::ToAttribsValue($name).'" />';
	*/
	function ToAttribsValue($str){
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
	* Vytvori novou instanci XMole a zkopiruje do nej nektere vlastnosti.
	*/
	function _new_instance(){
		$x = new XMole();
		$x->_trim_data = $this->_trim_data;
		$x->_input_encoding = $this->_input_encoding;
		$x->_output_encoding = $this->_output_encoding;
		return $x;
	}
}
?>
