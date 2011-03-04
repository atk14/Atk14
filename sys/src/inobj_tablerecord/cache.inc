<?
/**
* Cache::Prepare("inobj_Article",10023);
* Cache::Prepare("inobj_Article",10024);
* Cache::Prepare("inobj_Article",array(10025,10026));
* Cache::Get("inobj_Article",10023); // nacte vsechny clanky 10023, 10024, 10025 i 10026; vrati instanci 10023
* Cache::Clear(); // smaze cache
*/
class Cache{
	var $_Prepare = array();
	var $_Cache = array();
	function &GetInstance(){
		static $instance;
		if(!isset($instance)){
			$instance = new Cache();
		}
		return $instance;
	}

	function Prepare($class,$ids){
		$class = strtolower($class);
		$c = &Cache::GetInstance();
		!is_array($ids) && ($ids = array($ids));
		!isset($c->_Prepare[$class]) && ($c->_Prepare[$class] = array());
		!isset($c->_Cache[$class]) && ($c->_Cache[$class] = array());
		$cached_ids = array_keys($c->_Cache[$class]);
		foreach($ids as $id){
			if(!isset($id)){ continue; }
			!in_array($id,$c->_Prepare[$class]) && !in_array($id,$cached_ids) && ($c->_Prepare[$class][$id] = $id);
		}
	}

	function Get($class,$ids){
		$class = strtolower($class);
		Cache::Prepare($class,$ids);
		$c = &Cache::GetInstance();
		$c->_readToCache();
		$array_given = true;
		if(!is_array($ids)){ $ids = array($ids);  $array_given = false; }
		$out = array();
		foreach($ids as $k => $id){
			if(!isset($id)){ $out[$k] = null; continue; }
			$out[$k] = $c->_Cache[$class][$id];
		}
		if(!$array_given){ return $out[0]; }
		return $out;
	}

	function Clear(){
		$c = &Cache::GetInstance();
		$c->_Cache = array();
	}

	function _readToCache(){
		foreach($this->_Prepare as $class => $ids){
			eval("\$objs = $class::GetInstanceById(\$ids);");
			while(list($k,) = each($objs)){
				$this->_Cache[$class][$k] = $objs[$k];
			}
		}
		$this->_Prepare = array();
	}
}
