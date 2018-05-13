<?php
/**
 * Smarty {dump} tag to output value of a variable.
 *
 * Php function print_r is used to output the value.
 * <code>
 * {dump var=$basket}
 * {dump var=$basket->getTotalPrice()}
 * </code>
 *
 * @package Atk14
 * @subpackage Helpers
 * @filesource
 */

/**
 * Smarty {dump} tag to output value of a variable.
 *
 * @param array $params
 * @param array $content
 */
function smarty_function_dump($params,$template){
	$smarty = atk14_get_smarty_from_template($template);

	if(!in_array("var",array_keys($params))){
		$out = array();
		$out[] = "<ul>";
		$keys = array_keys($smarty->getTemplateVars());
		sort($keys);
		foreach($keys as $key){
			$out[] = '<li>';
			$out[] = '<pre><code>'.h('$'.$key).': '.Dumper::Dump($smarty->getTemplateVars($key),0,array('collapse_structure' => true)).'</code></pre>';
			$out[] = '</li>';
		}
		$out[] = "</ul>";
		return join("\n",$out);
	}
	$out = isset($params["var"]) ? Dumper::Dump($params["var"]) : "NULL";
	return "<pre><code>$out</code></pre>";
}

/**
 * Class for dumping a variable.
 */
class Dumper{

	/**
	 * echo Dumper::Dump($variable);
	 * echo Dumper::Dump(true); // [true]
	 * echo Dumper::Dump(null); // NULL
	 */
	static function Dump($var,$offset = 0,$options = array()){
		$dumper = new Dumper();

		return $dumper->_dump($var,$offset,$options);
	}

	function _dump($var,$offset,$options = array()){
		if(!isset($var)){
			return $this->_Pad("NULL",$offset);
		}
		if(is_bool($var)){
			return $this->_Pad($var ? "[true]" : "[false]",$offset);
		}
		if(is_object($var)){
			return $this->_Pad($this->_DumpObject($var,$offset,$options),$offset);
		}
		if(is_array($var)){
			return $this->_Pad($this->_DumpArray($var,$offset,$options),$offset);
		}
		return h($this->_Pad(print_r($var,true),$offset));	
	}

	function _DumpObject($obj,$offset,$options = array()){
		$options += array(
			"collapse_structure" => false,
		);

		if(is_a($obj,"Closure")){
			return $this->_Pad("Closure",$offset);
		}

		if($offset>0){
			// in case of a deeper nesting a structure of a object is not being displayed
			return $this->_Pad(get_class($obj)." Object",$offset);
		}

		if(method_exists($obj,$_method = "toArray") || method_exists($obj,$_method = "to_array")){
			//return Dumper::_Pad(get_class($obj,$obj)." Object\n".preg_replace('/^Array\n/s','',print_r($obj->toArray(),true)),$offset);
			$options["label"] = "Object";
			return get_class($obj)." ".$this->_DumpArray($obj->toArray(),0,$options);
		}

		if(isset($obj->_beeing_dumped_by_dumper)){ return $this->_Pad("[recursion]",$offset); }
		$obj->_beeing_dumped_by_dumper = true;
		$attrs = array();
		foreach(array_keys(get_object_vars($obj)) as $attr){
			if(preg_match('/^_/',$attr)){ continue; }
			$attrs[] = $attr;
		}
		if(!$attrs){
			unset($obj->_beeing_dumped_by_dumper);
			return $this->_Pad(get_class($obj).' Object',$offset);
		}

		$out[] = get_class($obj).' Object(';

		foreach($attrs as $attr){
			$out[] = "  [$attr] => ".trim($this->Dump($obj->$attr,1));
			//$out[] = "  [$attr] => ...";
		}

		$out[] = ")";

		if($options["collapse_structure"]){
			$this->_makeCollapsible($out);
		}

		unset($obj->_beeing_dumped_by_dumper);

		return $this->_Pad($out,$offset);
	}

	function _makeCollapsible(&$out){

		// "Form Object("
		// "Array("
		preg_match('/^(.+)\($/',$out[0],$matchces);
		$label = $matchces[1];

		$id = "dump_".uniqid();
		$id_to_be_hidden = $id."_h";
	
		// TODO: uf, to be rewritten somehow
		$out[0] = '<span id="'.$id_to_be_hidden.'"><a href="#" onclick="JavaScript: document.getElementById(\''.$id.'\').style.display=\'inline\'; document.getElementById(\''.$id_to_be_hidden.'\').style.display=\'none\'; return false;" title="expand">'.$label.'(+)</a></span><span style="display:none;" id="'.$id.'"><a href="#" onclick="JavaScript: document.getElementById(\''.$id_to_be_hidden.'\').style.display=\'inline\'; document.getElementById(\''.$id.'\').style.display=\'none\'; return false;" title="collapse">'.$label.'(</a>';

		$out[sizeof($out)-1] .= "</span>";
	}

	function _DumpArray($ar,$offset,$options = array()){
		$options = array_merge(array(
			"label" => "Array",
			"collapse_structure" => false,
		),$options);

		$out = array();
		$out[] = $options["label"].'(';
		foreach($ar as $k => $v){
			$out[] = " [$k] => ".trim($this->_Pad($this->Dump($v),1));
		}
		$out[] = ')';

		if($options["collapse_structure"]){
			$this->_makeCollapsible($out);
		}

		return $this->_Pad($out,$offset);
	}

	function _Pad($out,$offset){
		$padding = str_repeat(" ",$offset * 2);
		if(is_string($out)){ $out = explode("\n",$out); }
		foreach($out as &$l){
			$l = "{$padding}$l";
		}
		return join("\n",$out);
	}
}
