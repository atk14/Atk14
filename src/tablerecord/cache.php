<?php
/**
 * Caching mechanism
 * @filesource
 */

/**
 * An ObjectCacher is a object which is caching instance of one specific class
 *
 * <code>
 *	// Instantiating
 *	$article_cacher = Cache::GetObjectCacher("Article");
 *
 *  // preparing & retriewing objects
 *	$article_cacher->preparing(123);
 *	$article_cacher->preparing(124);
 *	$article_cacher->preparing(array(125,126));
 *	//
 *	$article = $article_cacher->get(123);
 *	$articles = $article_cacher->get(array(124,125));
 * </code>
 *
 * For more information see Cache.
 */
class ObjectCacher {
	protected static $InitilizedCachers = array();

	protected $class;
	protected $cache = array();
	protected $prepare = array();

	/**
	 * Constructor
	 *
	 * @access protected
	 */
	protected function __construct($class) {
		$this->class = $class;
	}

	static function &GetInstance($class,$create = true){
		static $object_cachers = array();

		assert(class_exists($class)); // this needs to be called before lowering the name of the class (autoload issue)
		$class = strtolower($class);
		if(!key_exists($class, self::$InitilizedCachers)) {
			if(!$create) { $null = null; return $null; }
			self::$InitilizedCachers[$class] = new ObjectCacher($class);
		}
		return self::$InitilizedCachers[$class];
	}

	static function &GetAllInitializedCachers(){
		return self::$InitilizedCachers;
	}

	/**
	 * Reads records that should be read (from $this->prepare) to cache.
	 *
	 * @access protected
	 */
	protected function _readToCache() {
		if(!$this->prepare) { return; }
		$cname = $this->class;
		$this->cache += $cname::GetInstanceById($this->prepare,array("use_cache" => false));
		$this->prepare = array();
	}

	/**
	 * Prepare the given $id or $ids to be read into cache
	 * 
	 * When first $cacher->get() is called, all previously prepared ids are automatically read to cache
	 */
	function prepare($ids) {
		$ids = self::_ToIds($ids);
		foreach($ids as $id){
			if(	$id == null ||
					key_exists($id, $this->prepare) ||
					key_exists($id, $this->cache)
			){ continue; }
			$this->prepare[$id] = $id;
		}
	}

	/**
	 * Returns records from cache, instantiate (i.e. read from db) not yet cached records.
	 */
	function get($ids) {
		$array_given = false;
		$ids = self::_ToIds($ids, $array_given);
		$this->prepare($ids);
		$this->_readToCache();
		$out = $this->getCached($ids);
		if(!$array_given){ $out = $out[0]; }
		return $out;
	}

	/**
	 * Returns only records (by ids), which have been already read to cache.
	 *
	 * Returns nulls instead of all unread.
	 */
	function getCached($ids) {
		$out = array();
		foreach($ids as $k => $id){
			$out[$k] = $id === null || !key_exists($id, $this->cache) ? null : $this->cache[$id];
		}
		return $out;
	}

	/**
	 * Returns ids of all cached and prepared records
	 */
	function cachedIds() {
		$out = $this->prepare;
		if($cached = array_keys($this->cache)){
			$out += array_combine($cached,$cached);
		}
		return array_values($out);
	}

	/**
	 * Clears whole cache, or (by id) selected record(s) from cache
	 */
	function clear($ids = null) {
		if($ids === null) {
			$this->cache = array();
		} else {
			$ids = self::_ToIds($ids);
			$this->cache = array_diff_key($this->cache, array_flip($ids));
		}
	}

	/**
	 * Is given record in cache?
	 */
	function inCache($id) {
		if(is_object($id)) {$id = $id->getId();};
		return key_exists($id, $this->cache);
	}

	/**
	 * Creates array of ids from argument.
	 *
	 * Just auxiliary method.
	 */
	static protected function _ToIds($ids, &$array_given = true){
		if(!is_array($ids)){
			$ids = array($ids);
			$array_given = false;
		} else {
			$array_given = true;
		}

		foreach($ids as &$v){
			if(is_object($v)){ $v = $v->getId(); }
		}
		return $ids;
	}
}

/**
 * Class for caching mainly TableRecord objects
 *
 * It is possible to cache objects having methods getId() and GetInstanceById()
 *
 * Put an object into cache.
 * ```
 * Cache::Prepare("Article",10023);
 * Cache::Prepare("Article",10024);
 * Cache::Prepare("Article",array(10025,10026));
 * ```
 *
 * == Retrieving an object from cache
 *
 * This reads all the previously declared articles (10023, 10024, 10025 and 10026); returns only that with id 10023
 * ```
 * Cache::Get("Article",10023);
 * ```
 *
 * This call reads nothing from database as the requested objects are already in cache and returns array(Article#10024,Article#10025)
 * ```
 * Cache::Get("Article",array(10024,10025));
 * ```
 *
 * This call clears all data stored in the cache
 * ```
 * Cache::Clear();
 * ```
 *
 * Clears only Article#10023 from the cache
 * ```
 * Cache::Clear("Article",10023);
 * ```
 *
 * Multiple cache operations with one class
 * ```
 *	$cacher = Cache::GetObjectCacher('Article');
 *	$cacher->prepare(1);
 *	$cacher->prepare(3);
 *	$cacher->prepare(array(4,5));
 *	$article = $cacher->get(2);
 *  ```
 * @package Atk14\Cache
 */
class Cache{

	/**
	 * Constructor
	 *
	 * @access protected
	 */
	protected function __construct(){ }

	/**
	 * Returns a object that caches instances of the given class
	 *
	 */
	function &getCacher($class,$create = true) {
		$class = (string)$class;
		return ObjectCacher::GetInstance($class,$create);
	}

	/**
	 * Get global cache instance
	 */
	static function &GetInstance(){
		static $instance;
		if(!isset($instance)){
			$instance = new Cache();
		}
		return $instance;
	}

	/**
	 * Get an object that caches instances of the given class
	 *
	 * <code>
	 *	$cache = Cache::GetObjectCacher('Article');
	 *	$cache->prepare(1);
	 *	$cache->prepare(2);
	 *	$cache->prepare(array(1,2));
	 *	$cache->get(2);
	 *	// .....
	 * </code>
	 */
	static function &GetObjectCacher($class){
		return self::GetInstance()->getCacher($class);
	}

	/**
	 * Prepares the given $id(s) to be read at once into cache in the future
	 *
	 * <code>
	 * 	Cache::Prepare("Article",123);
	 * 	Cache::Prepare("Article",124);
	 * 	Cache::Prepare("Article",array(125,126));
	 * </code>
	 */
	static function Prepare($class,$ids){
		self::GetInstance()->getCacher($class)->prepare($ids);
	}

	/**
	 * Gets the given object(s) from cache
	 *
	 * <code>
	 * 	$article = Cache::Get("Article",123); // Article#123
	 * 	$articles = Cache::Get("Article",array(123,124)); // array(Article#123,Article#124)
	 * </code>
	 */
	static function Get($class,$ids){
		return self::GetInstance()->getCacher($class)->get($ids);
	}

	/**
	 * Flushes out content of cache
	 *
	 * <code>
	 *	Cache::Clear(); // flushes every object in cache
	 *	Cache::Clear("Article"); // flushes every Article instance in cache, if there are any
	 *	Cache::Clear("Article",123); // flushes just Article#123, if there is such object in cache
	 *	Cache::Clear("Article",array(123,124)); // flushes just Article#123 and Article#124, if there are such objects in cache
	 * </code>
	 */
	static function Clear($class = null,$id = null){
		$c = self::GetInstance();
		$c->clearCache($class, $id);
	}

	function clearCache($class = null, $ids = null) {
		if($class == null) {
			foreach(ObjectCacher::GetAllInitializedCachers() as $cacher){
				$cacher->clear();
			}
		} else {
			$cacher = $this->getCacher($class);
			$cacher->clear($ids);
		}
	}

	/**
	 * $ids = Cache::CachedIds("Article"); // array(123,453,223)
	 */
	static function CachedIds($class){
		return self::GetInstance()->getCacher($class)->cachedIds();
	}
}


