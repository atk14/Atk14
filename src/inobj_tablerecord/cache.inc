<?
/**
* Cache::Prepare("Article",10023);
* Cache::Prepare("Article",10024);
* Cache::Prepare("Article",array(10025,10026));
* Cache::Get("Article",10023); // reads all the previous declared articles (10023, 10024, 10025 and 10026); returns the 10023
* Cache::Clear(); // cleares all stored data in the cache
* Cache::Clear("Article",10023); // cleares only Article#10023 from the cache
*/
class Cache{
	var $_Prepare = array();
	var $_Cache = array();

	static function &GetInstance(){
		static $instance;
		if(!isset($instance)){
			$instance = new Cache();
		}
		return $instance;
	}

	static function Prepare($class,$ids){
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

	static function Get($class,$ids){
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

	static function Clear($class = null,$id = null){
		$c = &Cache::GetInstance();
		if(isset($class)){
			$class = strtolower($class);
			unset($c->_Cache[$class][$id]);
			return;
		}
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
