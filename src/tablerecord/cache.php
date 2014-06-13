<?php
/**
 * Class for caching mainly TableRecord objects
 *
 * It is possible to cache objects having methods getId() and GetInstanceById()
 *
 * Cache::Prepare("Article",10023);
 * Cache::Prepare("Article",10024);
 * Cache::Prepare("Article",array(10025,10026));
 * 
 * Cache::Get("Article",10023); // reads all the previous declared articles (10023, 10024, 10025 and 10026); returns the 10023
 * Cache::Get("Article",array(10024,10025)); // reads nothing from database as the requested objects are already in cache, returns array(Article#10024,Article#10025)
 * 
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
		$ids = Cache::_Deobjectilize($ids);
		assert(class_exists($class)); // this needs to be called before lowering the name of the class (autoload issue)
		$class = strtolower($class);
		$c = Cache::GetInstance();
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
		$ids = Cache::_Deobjectilize($ids);
		assert(class_exists($class)); // this needs to be called before lowering the name of the class (autoload issue)
		$class = strtolower($class);
		Cache::Prepare($class,$ids);
		$c = Cache::GetInstance();
		$c->_readToCache($class);
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

	/**
	 * Cache::Clear(); // flushes every object in cache
	 * Cache::Clear("Article"); // flushes every Article member in cache, if there are any
	 * Cache::Clear("Article",123); // flushes just Article#123, if there is such object
	 */
	static function Clear($class = null,$id = null){
		$id = Cache::_Deobjectilize($id);
		$c = Cache::GetInstance();
		if(isset($class)){
			$class = strtolower($class);
			if(isset($id)){
				unset($c->_Cache[$class][$id]);
			}else{
				$c->_Cache[$class] = array();
			}
			return;
		}
		$c->_Cache = array();
	}

	function _readToCache($class){
		if(!isset($this->_Prepare[$class]) || !$this->_Prepare[$class]){ return; }
		$ids = $this->_Prepare[$class];
		$objs = call_user_func(array($class,"GetInstanceById"),$ids);
		foreach($objs as $k => $o){
			$this->_Cache[$class][$k] = $o;
		}
		//$this->_Cache[$class] = $objs + $this->_Cache[$class]; // TODO: does this equal to the previous foreach loop?
		$this->_Prepare[$class] = array();
	}

	static function _Deobjectilize($id){
		if(is_array($id)){
			foreach($id as &$v){
				if(is_object($v)){ $v = $v->getId(); }
			}
			return $id;
		}
		if(is_object($id)){ $id = $id->getId(); }
		return $id;
	}
}
