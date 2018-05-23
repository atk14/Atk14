<?php
/**
* YAML dumper & loader
*
* It can dump and load associative or indexed arrays.
* 
* Usage:
*    $ary = miniYAML::Load($yaml_str);
*    $yaml = miniYAML::Dump($ary);
*
* TODO:
*   Parser nenacte takovy zapis, ve kterem jsou vsechny radky odsazeny o spolecny indent.
*
* Changelog:
*
* 2013-03-02
*     Better string escaping
*     An associative array can be passed to miniYAML::Dump()
*
* 2008-04-09
*     Vylepseno rozpoznavani asociativniho pole.
*
* 2008-01-28
*      Prepracovani nacitani YAML dokumentu. Parser si nyni poradi i s indexovym pole,
*      jeho prevky jsou jine pole (indexove nebo asociativni):
*            ---
*            - element 1
*            - - element 2.1
*              - element 2.2
*            - key1: val1
*              key2: val2
*
* 2008-01-25
*     Doplnena schopnost zpracovat prazdne indexove pole.
*           ---
*           status: success
*           message: Ok
*           data: 
*             domain: test.cz
*             registrant: ZUZANA-PROKOPOVA
*             nsset: HOSTING-NS
*             admin: 
*             - JAN-PROKOP
*             - JANA-PROKOPOVA
*             tempcontact: []
*             
*             registrar: REG-GENREG
*             create_date: 2001-01-10
*             expiry_date: 2014-01-11
*
* 2008-01-09
*    Dodelana schopnost zpracovavat indexovana pole. Priklad YAML:
*           ---
*           command: check domains availability
*           params:
*             domains:
*             - test1.cz
*             - test2.cz
*             - test3.cz
*             - test4.cz
*/
class miniYAML{
  var $_Lines = array();

  /**
  * Prevede YAML zapis na pole.
  * 
  * @static
  * @access public
  * @param string $yaml      zapis struktury v YAML
  * @return array
  */
  static function Load($yaml,$options = array()){
		$options = array_merge(array(
			"interpret_php" => false,
			"values" => array(),
		),$options);
		if($options["interpret_php"]){
			$yaml = miniYAML::InterpretPHP($yaml,$options["values"]);
		}
    $obj = new miniYAML();
    return $obj->_load($yaml);
  }

  /**
  * Prevedete pole na do YAML zapisu.
  * 
  * Reverzni metoda k miniYAML::Load();
  * 
  * @static
  * @access public
  * @param array $ar
  * @return string
  */
  static function Dump($ar){
    $obj = new miniYAML();
    $out = "---";
		if($obj->_isIndexedArray($ar)){
			$out .= "\n".$obj->_dumpIndexedArray($ar,0);
		}elseif(is_array($ar)){
			$out .= "\n".$obj->_dumpHashArray($ar,0);
		}
		$out .= "\n";
		return $out;
  }

	/**
	* $yaml = miniYAML::InterpretPHP('
	*		---
	*		login: <?= $login ?>
	*
	*		password: <?= $password ?>
	*	',array(
	*		"login" => "admin",
	*		"password" => "magic"
	*	));
	*
	* Note: Think of newline removal at the end of php end tag!
	*/
	static function InterpretPHP($__yaml,$__values = array()){
		foreach($__values as $__k => $__v){
			eval("\$$__k = \$__v;");
		}
		$__error_reporting = error_reporting(0);
		ob_start();
		eval("?".">".$__yaml);
		$__yaml = ob_get_contents();
		ob_end_clean();	
		error_reporting($__error_reporting);
		return $__yaml;
	}

  function _load($yaml){
    $yaml = str_replace("\r","",$yaml);
    $ar = explode("\n",$yaml);
    $this->_Lines = array();
    $got_structure_begin = false;
    for($i=0;$i<sizeof($ar);$i++){
      if(trim($ar[$i]) == "---"){ // zacatek nove struktury - muze byt v zapisu pouze 1x a to na jejim zacatku
        if($got_structure_begin){ return null; }
        $got_structure_begin = true;
        continue;
      }
      if($this->_containsData($ar[$i])){
        $got_structure_begin = true;
        $this->_Lines[] = $ar[$i];
      }
    }

    $out = $this->_readVar($this->_Lines,$lines_read);
    // nasledujici podminka zachyti situaci, kdyz nejsou zpracovany vsechny radky a uz mame nejaky hotovy vystup...
    // muze se to tykat napr. tohoto neplatneho zapisu:
    //      ---
    //      - jedna
    //      - dve
    //      klic:
    if($lines_read!=sizeof($this->_Lines)){
      return null;
    }
    return $out;
  }

  /**
  * Spocita mezery na zacatku radku.
  *
  * @access private
  * @param string $line
  * @return int
  */
  function _getIndent($line){
    preg_match("/^( *)/",$line,$matches);
    return strlen($matches[1]);
  }

  /**
  * Smaze z radky odsazeni.
  *
  * @access private
  * @param string $line
  * @param int $indent          bude-li -1, bude delka odsazeni urcena automaticky
  * @return string
  */
  function _stripIndent($line, $indent = -1){
    if ($indent == -1){
      $indent = $this->_getIndent($line);
    }  
    return substr ($line, $indent);
  }

  /**
  * Vysekne z pole radku blok s danym odsazenim.
  *
  * Vybere vsechny radky, ktere maji alespon urcene odsazeni,
  * ale i vsechny radky s odsazenim delsim.
  *
  * Zamerne vsak neni porovnavana delka odsazeni prvniho radku.
  * Navic je prvnimu radku toto odsazeni automaticky nastaveno.
  *
  * @access public
  * @param int $start_at    prvni index v poli
  * @param int $indent       delka nejmensiho odsazeni
  * @param string[]  $lines   pole radek; pokud nebude nastaveno, bude se uvazovat $this->_Lines
  * @return string[]
  */
  function _cutOutBlock($start_at,$indent,$lines = null){
    if(!isset($lines)){ $lines = &$this->_Lines; }
    $out = array();
    $out[] = str_repeat(" ",$indent).substr($lines[$start_at],$indent);
    for($i=$start_at+1;$i<sizeof($lines);$i++){
      $_indent = $this->_getIndent($lines[$i]);
      if($_indent<$indent){
        break;
      }
      $out[] = $lines[$i];
    }
    return $out;
  }

  /**
  * Provede vyseknuti bloku. Navic vsem radkum odstrani indent.
  *
  * @access public
  * @param int $start_at    prvni index v poli
  * @param int $indent       delka nejmensiho odsazeni
  * @param string[]  $lines   pole radek; pokud nebude nastaveno, bude se uvazovat $this->_Lines
  * @return string[]
  */
  function _cutOutBlock_Stripped($start_at,$indent,$lines = null){
    $lines = $this->_cutOutBlock($start_at,$indent,$lines);
    for($i=0;$i<sizeof($lines);$i++){
      $lines[$i] = substr($lines[$i],$indent);
    }
    return $lines;
  }

  /**
  * Nacte datovou strukturu z daneho pole radku.
  *
  * @access private
  * @param string[] $block
  * @param int &$lines_read    pocet radku pole potrebnych pro nacteni struktury
  * @param array $options      parametry nacitani
  * @return mixed              indexove pole, hash pole nebo string
  */
  function _readVar($block,&$lines_read,$options = array()){
    $options = array_merge(array(
      "testing_for_array" => true
    ),$options);

    if(is_string($block)){ $block = array($block); }

    $lines_read = 0;

		if(sizeof($block)==0){ return null; }

    if($options["testing_for_array"]){
      if(preg_match("/^- /",$block[0])){
        return $this->_readIndexedArray($block,$lines_read);
      }
      if(preg_match("/^[^\\s\"]+?:(\\s+[^\\s].*|\\s*)$/",$block[0])){
        return $this->_readHashArray($block,$lines_read);
			}
    }

    if(sizeof($block)==1){ // toto je spatne!!!! zde zapadne i indexove pole o velikosti 1
      $lines_read = 1;
      $out = trim($block[0]);
      if($out == "[]"){ return array(); }
      $this->_unescapeString($out);
      return $out;
    }
  }

  /**
  * Nacte indexove pole z pole radku
  * 
  * @access private
  * @param string[] $block
  * @param int &$lines_read    pocet radku pole potrebnych pro nacteni vraceneho pole
  * @return array
  */
  function _readIndexedArray($block,&$lines_read){
    $out = array();
    $lines_read = 0;
    for($i=0;$i<sizeof($block);$i++){
      $line = $block[$i];
      if(!preg_match("/^- /",$line)){
        break;
      }
      $value_block = $this->_cutOutBlock_Stripped($i,2,$block);
      $out[] = $this->_readVar($value_block,$li);
      $i += $li-1; // -1 -> zaciname cist na akt. radku
    }
    $lines_read = $i;
    return $out;
  }

  /**
  * Nacte asociativni pole (hash) z pole radku.
  * 
  * @access private
  * @param string[] $block
  * @param int &$lines_read    pocet radku pole potrebnych pro nacteni vraceneho pole
  * @return array
  */
  function _readHashArray($block,&$lines_read){
    $out = array();
    $lines_read = 0;
    for($i=0;$i<sizeof($block);$i++){
      $line = $block[$i];
      $next_line = null;
      if(isset($block[$i+1])){ $next_line = $block[$i+1]; }
      if(!preg_match("/^(.+?):(.*)/",$line,$matches)){
        $i--;
        break;
      }
      $key = $matches[1];
      $_values = trim($matches[2]);
      $next_line_indent = $this->_getIndent($next_line);
      if($next_line_indent>0 || preg_match("/^- /",$next_line)){
        $value_block = $this->_cutOutBlock_Stripped($i+1,$next_line_indent,$block);
        $value = $this->_readVar($value_block,$li);
        $i += $li;
      }else{
        $value = $this->_readVar($_values,$li,array("testing_for_array" => false));
      }
			if(preg_match('/^\s/',$key)){
				throw new Exception("token cannot begin with tabulator or other white character on line: $line");
			}
      $out[$key] = $value;
    }
    $lines_read = $i;
    return $out;
  }

  function _isComment($line){
    if(preg_match("/^#/",$line)){ return true;}
    return false;
  }

  function _isEmpty($line){
    return (strlen(trim($line))==0);
  }

  function _containsData($line){
    return !($this->_isComment($line) || $this->_isEmpty($line));
  }

  function _unescapeString(&$str){
    if (preg_match('/^("(.*)"|\'(.*)\')/',$str,$matches)){
      $str = end($matches);
      $str = preg_replace('/(\'\'|\\\\\')/',"'",$str);
      $str = preg_replace('/\\\\"/','"',$str);
      return true;
    }
    return false;
  }

  /*
   * Metody pro dumpovani.
   */

  function _dumpVar($var,$indent = 0){
    $out = array();
    if($this->_isIndexedArray($var)){
      $out[] = sizeof($var)==0 ? "[]" : "";
      $out[] = $this->_dumpIndexedArray($var,$indent); // indexove pole se tiskne se stejnym indentem
    }elseif(is_array($var)){
      $out[] = "";
      $out[] = $this->_dumpHashArray($var,$indent + 1);
    }else{
      $out[] = $this->_dumpString($var); // schvalne je vynechan $indent, ident je pred klicem
    }
    return join("\n",$out);
  }

  function _dumpString($str,$indent = 0){
    $patterns_to_escape = array(
      "/^\\s+/", "/\\s+$/","/\\n/",
      "/^yes$/i", "/^on$/i", "/^\\+$/", "/^y$/", "/^true$/i",
      "/^no$/i", "/^off$/i", "/^-$/", "/^n$/", "/^false$/i",
      "/^null$/i", "/^~$/", "/^$/",
      "/^\\-?.inf$/i", "/^.nan$/i",
      "/^\"/", "/^'/", "/#/",
      "/^{/", "/^}/", "/^\\[/", "/^\\]/", "/^:/", "/^=$/", "/^\\?$/", "/^\\|/", "/^>/", "/^-$/", "/^<<$/",
      "/^!/", "/^\\*/", "/^\\&/",
			"/:\s/",
			"/^:/",
			"/:$/",
    );

    if(is_numeric($str) || is_numeric(str_replace("_","",$str))){
      $str = $this->_escapeString($str);
    }else{
      $_escaped = false;
      foreach($patterns_to_escape as $pattern){
        if(preg_match($pattern,$str)){
          $str = $this->_escapeString($str);
          $_escaped = true;
          break;
        }
      }
    }
    return $this->_dumpIndent($indent).$str;
  }

  function _isIndexedArray($ar){
    if(!is_array($ar)){ return false; }
    $expected_key = 0;
    foreach(array_keys($ar) as $_key){
      if(!is_int($_key) || $_key!=$expected_key){ return false; }
      $expected_key++;
    }
    return true;
  }

  function _dumpIndexedArray($ar,$indent){
    $out = array();
    foreach($ar as $_value){
      if($this->_isIndexedArray($_value) && sizeof($_value)>0){
        $_dump = $this->_dumpIndexedArray($_value,$indent + 1); // "- "
        $_prefix = $this->_dumpIndent($indent)."- ";
        $out[] = $_prefix.substr($_dump,strlen($_prefix));
      }elseif(is_array($_value) && sizeof($_value)>0){
        $_dump = $this->_dumpHashArray($_value,$indent + 1); // "- "
        $_prefix = $this->_dumpIndent($indent)."- ";
        $out[] = $_prefix.substr($_dump,strlen($_prefix));
        //$out[] = $_prefix.$_dump;
      }else{
        $out[] = $this->_dumpIndent($indent)."- ".$this->_dumpVar($_value,$indent + 2); // "- "
      }
    }
    return join("\n",$out);
  }

  function _dumpHashArray($ar,$indent){
    $out = array();
    foreach($ar as $_key => $_value){
      $out[] = $this->_dumpIndent($indent).$this->_dumpString($_key).": ".$this->_dumpVar($_value,$indent);
    }
    return join("\n",$out);
  }

  function _dumpIndent($indent){
    if($indent<=0){ return ""; }
    return str_repeat(" ",$indent * 2);
  }

  function _escapeString($str){
    return "\"".str_replace("\"","\\\"",$str)."\"";
  }
}
// vim: set expandtab:
