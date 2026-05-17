<?php
/**
* Minimalistic YAML loader and dumper.
*
* Supports hash and indexed arrays, nested structures, block scalars (| and >),
* quoted strings, null values, and embedded PHP template evaluation.
*
* Usage:
*    $ar   = miniYAML::Load($yaml_string);
*    $yaml = miniYAML::Dump($ar);
*
* @see README.md for full documentation and list of limitations.
*/
class miniYAML{

	protected $_Lines = [];
	protected $nullable = true;

	function __construct($options = []){
		$options += [
			"nullable" => true, // Whether to consider strings null and NULL as true NULL?
		];

		$this->nullable = $options["nullable"];
	}

	/**
	* Converts a YAML string into a PHP array.
	*
	* @static
	* @access public
	* @param string $yaml      YAML-encoded structure
	* @return array
	*/
	static function Load($yaml,$options = []){
		$options += [
			"interpret_php" => false,
			"values" => [],
		];

		if($options["interpret_php"]){
			$yaml = miniYAML::InterpretPHP($yaml,$options["values"]);
		}

		unset($options["interpret_php"]);
		unset($options["values"]);

		$obj = new miniYAML($options);
		return $obj->_load($yaml);
	}

	/**
	* Converts a PHP array into a YAML string.
	*
	* Reverse of miniYAML::Load().
	*
	* @static
	* @access public
	* @param array $ar
	* @return string
	*/
	static function Dump($ar,$options = []){
		$obj = new miniYAML($options);
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
	*	',[
	*		"login" => "admin",
	*		"password" => "magic"
	*	]);
	*
	* Note: Think of newline removal at the end of php end tag!
	*/
	static function InterpretPHP($__yaml,$__values = []){
		// Validate keys to prevent variable injection
		foreach(array_keys($__values) as $__k){
			if(!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $__k)){
				throw new InvalidArgumentException("Invalid variable name: $__k");
			}
		}

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

	protected function _load($yaml){
		$yaml = str_replace("\r","",$yaml);
		$ar = explode("\n",$yaml);
		$this->_Lines = [];
		$got_structure_begin = false;
		$cnt = count($ar);
		for($i=0;$i<$cnt;$i++){
			if(trim($ar[$i]) == "---"){ // start of structure — may appear only once, at the beginning
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
		// The following condition catches situations where not all lines have been consumed
		// but a result is already available — this can happen with invalid input such as:
		//      ---
		//      - jedna
		//      - dve
		//      klic:
		if($lines_read!=count($this->_Lines)){
			return null;
		}
		return $out;
	}

	/**
	* Counts leading spaces on a line.
	*
	* @access protected
	* @param string $line
	* @return int
	*/
	protected function _getIndent($line){
		preg_match("/^( *)/",(string)$line,$matches);
		return strlen($matches[1]);
	}

	/**
	* Cuts out a block of lines with the given indentation.
	*
	* Selects all lines that have at least the specified indentation,
	* as well as all lines with greater indentation.
	*
	* The indentation of the first line is intentionally not compared.
	* Instead, the first line's indentation is automatically set to the given value.
	*
	* @access protected
	* @param int $start_at    first index in the array
	* @param int $indent       minimum indentation length
	* @param string[]  $lines   array of lines; if not set, $this->_Lines is used
	* @return string[]
	*/
	protected function _cutOutBlock($start_at,$indent,$lines = null){
		if(!isset($lines)){ $lines = &$this->_Lines; }
		$out = [];
		$out[] = str_repeat(" ",$indent).substr($lines[$start_at],$indent);
		$cnt = count($lines);
		for($i=$start_at+1;$i<$cnt;$i++){
			$_indent = $this->_getIndent($lines[$i]);
			if($_indent<$indent){
				break;
			}
			$out[] = $lines[$i];
		}
		return $out;
	}

	/**
	* Cuts out a block of lines and strips indentation from all of them.
	*
	* @access protected
	* @param int $start_at    first index in the array
	* @param int $indent       minimum indentation length
	* @param string[]  $lines   array of lines; if not set, $this->_Lines is used
	* @return string[]
	*/
	protected function _cutOutBlock_Stripped($start_at,$indent,$lines = null){
		$lines = $this->_cutOutBlock($start_at,$indent,$lines);
		$cnt = count($lines);
		for($i=0;$i<$cnt;$i++){
			$lines[$i] = substr($lines[$i],$indent);
		}
		return $lines;
	}

	/**
	* Reads a data structure from the given array of lines.
	*
	* @access protected
	* @param string[] $block
	* @param int &$lines_read    number of lines consumed to read the structure
	* @param array $options      parsing options
	* @return mixed              indexed array, hash array, or string
	*/
	protected function _readVar($block,&$lines_read,$options = []){
		$options += [
			"testing_for_array" => true
		];

		if(is_string($block)){ $block = [$block]; }

		$lines_read = 0;

		if(count($block)==0){ return null; }

		if($options["testing_for_array"]){
			if(preg_match("/^- /",$block[0])){
				return $this->_readIndexedArray($block,$lines_read);
			}
			if(preg_match("/^[^\\s\"]+?:(\\s+[^\\s].*|\\s*)$/",$block[0])){
				return $this->_readHashArray($block,$lines_read);
			}
		}

		if(count($block)==1){
			$lines_read = 1;
			$out = trim($block[0]);
			if($out == "[]"){ return []; }
			$this->_unescapeString($out);
			return $out;
		}

		throw new Exception("Unexpected multi-line scalar value starting with: ".trim($block[0]));
	}

	/**
	* Reads an indexed array from an array of lines.
	*
	* @access protected
	* @param string[] $block
	* @param int &$lines_read    number of lines consumed to read the returned array
	* @return array
	*/
	protected function _readIndexedArray($block,&$lines_read){
		$out = [];
		$lines_read = 0;
		$cnt = count($block);
		for($i=0;$i<$cnt;$i++){
			$line = $block[$i];
			if(!preg_match("/^- /",$line)){
				break;
			}
			$value_block = $this->_cutOutBlock_Stripped($i,2,$block);
			$out[] = $this->_readVar($value_block,$li);
			$i += $li-1; // -1 because the loop starts reading at the current line
		}
		$lines_read = $i;
		return $out;
	}

	/**
	* Reads an associative array (hash) from an array of lines.
	*
	* @access protected
	* @param string[] $block
	* @param int &$lines_read    number of lines consumed to read the returned array
	* @return array
	*/
	protected function _readHashArray($block,&$lines_read){
		$out = [];
		$lines_read = 0;
		$cnt = count($block);
		for($i=0;$i<$cnt;$i++){
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
			if($_values === "|" || $_values === ">"){
				if($next_line_indent > 0){
					$value_block = $this->_cutOutBlock_Stripped($i+1,$next_line_indent,$block);
					$i += count($value_block);
				}else{
					$value_block = [];
				}
				$value = $_values === "|" ? implode("\n",$value_block) : implode(" ",$value_block);
			}elseif($next_line_indent>0 || preg_match("/^- /",(string)$next_line)){
				$value_block = $this->_cutOutBlock_Stripped($i+1,$next_line_indent,$block);
				$value = $this->_readVar($value_block,$li);
				$i += $li;
			}else{
				$value = $this->_readVar($_values,$li,["testing_for_array" => false]);
			}
			if(preg_match('/^\s/',$key)){
				throw new Exception("token cannot begin with tabulator or other white character on line: $line");
			}
			$out[$key] = $value;
		}
		$lines_read = $i;
		return $out;
	}

	protected function _isComment($line){
		if(preg_match("/^#/",$line)){ return true;}
		return false;
	}

	protected function _isEmpty($line){
		return (strlen(trim($line))==0);
	}

	protected function _containsData($line){
		return !($this->_isComment($line) || $this->_isEmpty($line));
	}

	protected function _unescapeString(&$str){
		if(preg_match('/^("(.*)"|\'(.*)\')/',$str,$matches)){
			$str = end($matches);
			$str = preg_replace('/(\'\'|\\\\\')/',"'",$str);
			$str = preg_replace('/\\\\"/','"',$str);
			return true;
		}
		if($this->nullable && ($str==="null" || $str==="NULL")){
			 $str = null;
			 return true;
		}
		return false;
	}

	/*
	 * Dumping methods.
	 */

	protected function _dumpVar($var,$indent = 0){
		$out = [];
		if($this->_isIndexedArray($var)){
			$out[] = count($var)==0 ? "[]" : "";
			$out[] = $this->_dumpIndexedArray($var,$indent); // indexed arrays are printed at the same indent level
		}elseif(is_array($var)){
			$out[] = "";
			$out[] = $this->_dumpHashArray($var,$indent + 1);
		}elseif(is_string($var) && strpos($var,"\n") !== false){
			$prefix = $this->_dumpIndent($indent + 1);
			$lines = explode("\n",rtrim($var,"\n"));
			foreach($lines as &$line){ $line = $prefix.$line; }
			$out[] = "|\n".implode("\n",$lines);
		}else{
			$out[] = $this->_dumpString($var); // $indent intentionally omitted — indent is placed before the key
		}
		return join("\n",$out);
	}

	protected function _dumpString($str,$indent = 0){
		$patterns_to_escape = [
			"/^\\s+/", "/\\s+$/",
			"/^yes$/i", "/^on$/i", "/^\\+$/", "/^y$/", "/^true$/i",
			"/^no$/i", "/^off$/i", "/^-$/", "/^n$/", "/^false$/i",
			"/^null$/i", "/^~$/", "/^$/",
			"/^\\-?.inf$/i", "/^.nan$/i",
			"/^\"/", "/^'/", "/#/",
			"/^{/", "/^}/", "/^\\[/", "/^\\]/", "/^=$/", "/^\\?$/", "/^\\|/", "/^>/", "/^<<$/",
			"/^!/", "/^\\*/", "/^\\&/",
			"/:\s/",
			"/^:/",
			"/:$/",
		];

		if(is_null($str) && $this->nullable){
			$str = "NULL";
		}elseif(is_numeric($str) || is_numeric(str_replace("_","",(string)$str))){
			$str = $this->_escapeString($str);
		}else{
			foreach($patterns_to_escape as $pattern){
				if(preg_match($pattern,(string)$str)){
					$str = $this->_escapeString($str);
					break;
				}
			}
		}
		return $this->_dumpIndent($indent).$str;
	}

	protected function _isIndexedArray($ar){
		if(!is_array($ar)){ return false; }
		$count = count($ar);
		return $count === 0 || array_keys($ar) === range(0, $count - 1);
	}

	protected function _dumpIndexedArray($ar,$indent){
		$out = [];
		foreach($ar as $_value){
			if($this->_isIndexedArray($_value) && count($_value)>0){
				$_dump = $this->_dumpIndexedArray($_value,$indent + 1);
				$_prefix = $this->_dumpIndent($indent)."- ";
				$out[] = $_prefix.substr($_dump,strlen($_prefix));
			}elseif(is_array($_value) && count($_value)>0){
				$_dump = $this->_dumpHashArray($_value,$indent + 1);
				$_prefix = $this->_dumpIndent($indent)."- ";
				$out[] = $_prefix.substr($_dump,strlen($_prefix));
			}else{
				$out[] = $this->_dumpIndent($indent)."- ".$this->_dumpVar($_value,$indent + 2);
			}
		}
		return join("\n",$out);
	}

	protected function _dumpHashArray($ar,$indent){
		$out = [];
		foreach($ar as $_key => $_value){
			$out[] = $this->_dumpIndent($indent).$this->_dumpString($_key).": ".$this->_dumpVar($_value,$indent);
		}
		return join("\n",$out);
	}

	protected function _dumpIndent($indent){
		if($indent<=0){ return ""; }
		return str_repeat(" ",$indent * 2);
	}

	protected function _escapeString($str){
		return "\"".str_replace("\"","\\\"",(string)$str)."\"";
	}
}
