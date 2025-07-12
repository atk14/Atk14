<?php
/**
 * Class for headaches free string manipulation.
 *
 * @package Atk14
 * @subpackage String4
 * @filesource
 */

/**
 * Class for headaches free string manipulation.
 *
 * Here is an inspiration:
 * http://api.rubyonrails.org/classes/String4.html
 *
 */
class String4{

	/**
	 * @var string
	 */
	protected $_String4;

	/**
	 * @var string
	 */
	protected $_Encoding;

	/**
	 * Constructor
	 *
	 * Setup new instance.
	 *
	 * ```
	 * $str = new String4();
	 * $str = new String4("Hello");
	 * $str2 = new String4($str);
	 * ```
	 *
	 * @param string $string String4 to store
	 * @param string $encoding Charset in which is the $string stored
	 */
	function __construct($string = "",$encoding = null){
		if(!isset($encoding) && is_object($string) && method_exists($string,"getEncoding")){
			$encoding = $string->getEncoding();
		}
		if(!isset($encoding)){
			$encoding = defined("DEFAULT_CHARSET") ? DEFAULT_CHARSET : "UTF-8";
		}

		$this->_String4 = "$string";
		$this->_Encoding = $encoding;
	}

	/**
	 * Converts a string to a String4 class object.
	 *
	 * Example
	 *
	 * ```
	 * $str = String4::ToObject("Hello");
	 * $str = String4::ToObject($str);
	 * ```
	 *
	 * @param string $string
	 * @param string $encoding
	 * @return String4
	 */
	static function ToObject($string,$encoding = null){
		if(is_object($string) && strtolower(get_class($string))=="string"){
			return $string;
		}
		return new self($string,$encoding);
	}

	/**
	 * Generates a random string.
	 *
	 * Generates a random string that contains only alfanumeric characters ([A-Za-z0-9]).
	 * Special chars can be passed in $options.
	 *
	 * ```
	 * echo String4::RandomString();
	 * echo String4::RandomString(8);
	 * echo String4::RandomString(array("length" => 8));
	 * echo String4::RandomString(array("length" => 8, "extra_chars" => "#$!%^")); 
	 * ```
	 *
	 * @param int $length
	 * @param array $options
	 * - length -
	 * - extra_chars -
	 *
	 * @return String4
	 */
	static function RandomString($length = 32,$options = array()){
		if(is_array($length)){
			$options = $length;
			$length = 32;
		}

		$options += array(
			'extra_chars' => '',
			'length' => $length,
		);

		srand(floor((double) microtime() * 1000000));
		$chars = array('a','i','o','s','t','u','v','3','4','5','8','B','C','D','E','F','7','G','H','I','J','K','L','M','N','O','j','k','l','6','P','Q','W','b','c','d','e','f','g','h','p','q','r','x','y','z','0','1','S','T','U','w','2','9','A','R','V','m','n');
		foreach(preg_split('//',$options['extra_chars']) as $ch){
			strlen($ch) && ($chars[] = $ch);
		}

		$s = sizeof($chars);

		$out = array();
		$c = 0;
		for($i=0;$i<$options['length'];$i++){
			if($i%$s==0){
				shuffle($chars);
				$rand = array_rand($chars,$s);
				$c = 0;
			}
			$out[] = $chars[$rand[$c]];
			$c++;
		}
		return join('',$out);
	}

	/**
	 * Some characters are not suitable for passwords, because they cause mistakes.
	 * Like zero and capital O: 0 versus O.
	 *
	 * @param integer $length
	 * @return String4
	 */
	static function RandomPassword($length = 10){
		settype($length,"integer");
		$numeric_versus_alpha_total = 10;
		$numeric_versus_alpha_numeric = 2;
		$piece_min_length = 2;
		$piece_max_length = 3;
		$numeric_piece_min_length = 1;
		$numeric_piece_max_length = 2;
		$s1 = "aeuyr";
		$s2 = "bcdfghjkmnpqrstuvwxz";
		$password = "";
		$last_s1 = rand(0,1);
		while(strlen($password)<=$length){
			$numeric = rand(0,$numeric_versus_alpha_total);
			if($numeric<=$numeric_versus_alpha_numeric){
				$numeric = 1;
			}else{
				$numeric = 0;
			}
			if($numeric==1){
				$piece_lenght = rand($numeric_piece_min_length,$numeric_piece_max_length);
				while($piece_lenght>0){
					$password .= rand(2,9);
					$piece_lenght--;
				}   
			}else{  
				$uppercase = rand(0,1);
				$piece_lenght = rand($piece_min_length,$piece_max_length);
				while($piece_lenght>0){
					if($last_s1==0){
						if($uppercase==1){
							$password .= strtoupper($s1[rand(0,strlen($s1)-1)]);
						}else{
							$password .= $s1[rand(0,strlen($s1)-1)];
						}
						$last_s1 = 1;
					}else{
						if($uppercase==1){
							$password .= strtoupper($s2[rand(0,strlen($s2)-1)]);
						}else{
							$password .= $s2[rand(0,strlen($s2)-1)];
						}
						$last_s1 = 0;
					}
					$piece_lenght--;
				}
			}
		}
		if(strlen($password)>$length){
			$password = substr($password,0,$length);
		}
		return new self($password);
	}

	/**
	 * ATK14 sometimes converts objects into their scalar representation automatically by calling getId() method.
	 * Due to it we need this silly looking method here.
	 *
	 * @return string
	 */
	function getId(){ return $this->toString(); }

	/**
	 * Returns encoding of the stored string.
	 *
	 * ```
	 * echo $s->getEncoding(); // "UTF-8", "UTF8","utf-8"...
	 * echo $s->getEncoding(true); // always "utf8" for UTF-8 encoding
	 * ```
	 *
	 *
	 * @return string encoding
	 */
	function getEncoding($normalize = false){
		if($normalize){
			return Translate::_GetCharsetByName($this->_Encoding);
		}
		return $this->_Encoding;
	}

	/**
	 * Returns array of chars.
	 *
	 *
	 *
	 * @return array
	 */
	function chars($options = array()){
		$options += array(
			"stringify" => false,
		);

		if($this->length()===0){ return array(); }

		$u = $this->getEncoding(true)==="utf8" ? "u" : "";
		$chars = preg_split("//s$u",$this->_String4,-1,PREG_SPLIT_NO_EMPTY);
		if($chars === false){
			$chars = str_split($this->_String4);
		}

		if($options["stringify"]){
			return $chars;
		}

		$out = array();
		foreach($chars as $ch){
			$out[] = $this->_copy($ch);
		}
		return $out;
	}

	/**
	 * Divides str into substrings based on a delimiter, returning an array of these substrings.
	 *
	 * The separator can be a regular expression. In this case, the `preg_split` option must be set to true.
	 *
	 *	$words = $s->split(" ");
	 *	$words = $s->split('\s+',["preg_split" => true]);
	 */
	function split($separator,$options = array()){
		$options += array(
			"preg_split" => false,
			"stringify" => false,
		);

		if(!$this->length()){
			return array();
		}
		$separator = (string)$separator;
		$chunks = $options["preg_split"] ? preg_split($separator,$this->toString()) : explode($separator,$this->toString());

		if($options["stringify"]){
			return $chunks;
		}

		$out = array();
		foreach($chunks as $chunk){
			$out[] = $this->_copy($chunk);
		}
		return $out;
	}

	/**
	 * Alias for String4::split($separator,["preg_split" => true])
	 *
	 *	$words = $s->pregSplit('\s+');
	 */
	function pregSplit($separator,$options = array()){
		$options["preg_split"] = true;
		return $this->split($separator,$options);
	}

	/**
	 * Returns length of string.
	 *
	 * @return integer length of the string
	 */
	function length(){
		//return strlen($this->_String4);
		return Translate::Length($this->_String4,$this->getEncoding());
	}
	
	/**
	 * Replaces string(s) with another string(s).
	 *
	 *
	 * Replaces a portion of string in the stored string.
	 *
	 * ```
	 * $str = new String4("Hello World");
	 * $str->replace("World","Guys");
	 * ```
	 *
	 * or
	 *
	 * ```
	 * $str->replace(array(
	 * 	"Hello" => "Hi",
	 * 	"World" => "Guys",
	 * ));
	 * ```
	 *
	 * !! Changes the object state
	 *
	 * @param string|array $search
	 * @param string|array $replace
	 * @return String4 
	 */
	function replace($search,$replace = null){
		if(is_array($search)){
			$_replaces_keys = array();
			$_replaces_values = array();
			foreach(array_keys($search) as $key){
				$_replaces_keys[] = $key;
				$_replaces_values[] = $search[$key];
			}   
			if(sizeof($_replaces_keys)==0){
				return $this;
			}   
			$this->_String4 = str_replace($_replaces_keys,$_replaces_values,$this->_String4);
			return $this;
		}
		$this->_String4 = str_replace($search,$replace,$this->_String4);
		return $this;
	}

	/**
	 * Does string substitutions.
	 *
	 * Part of a string that should be replaced is specified by a regexp pattern.
	 *
	 * Hello World => Hexxo Worxd
	 * ```
	 * $string = new String4("Hello World");
	 * $string = $string->gsub("/l/","x");
	 * ```
	 *
	 * The same as above using callback
	 * ```
	 * $string = new String4("Hello World");
	 * $string = $string->gsub("/l/", function($m){
	 * 	return "x";
	 * } );
	 * ```
	 *
	 * @param string $pattern regexp string
	 * @param string|callable $replace_or_callable string replacement or callback function
	 * @return String4 new instance of String4 class with replaced content
	 */
	function gsub($pattern,$replace_or_callable){
		if (!is_string($replace_or_callable) && is_callable($replace_or_callable)) {
			return $this->_copy(preg_replace_callback($pattern,$replace_or_callable,$this->_String4));
		}
		return $this->_copy(preg_replace($pattern,$replace_or_callable,$this->_String4));
	}

	/**
	 * Prepends a string to the object.
	 *
	 * Prepend 'Hello' to 'World'
	 * ```
	 * $string = new String4("World");
	 * $string->prepend("Hello ");
	 * ```
	 *
	 * @param $content
	 * @return String4
	 */
	function prepend($content){
		$this->_String4 = "$content".$this->_String4;
		return $this;
	}

	/**
	 * Appends a string to the end of stored string.
	 *
	 * Append 'World' to 'Hello'
	 * ```
	 * $string = new String4("Hello");
	 * $string->append(" World");
	 * ```
	 *
	 * @param string $content string to append to end of the stored string
	 * @return String4
	 */
	function append($content){
		$this->_String4 .= "$content";
		return $this;
	}

	/**
	 * Strip whitespace (or other characters) from the beginning and end of the string
	 *
	 * If optional parameter $remove_hidden_characters is set to true, it also remove hidden characters.
	 *
	 * @return String4
	 */
	function trim($remove_hidden_characters = false){
		return $this->_copy($this->_trim($this->_String4,$remove_hidden_characters));
	}

	protected function _trim($string,$remove_hidden_characters = false){
		$encoding = $this->getEncoding(true);
		if($encoding!=="utf8"){
			//if($remove_hidden_characters){ return preg_replace('/^(\s|\x00){0,}(.*?)(\s|\x00){0,}$/s','\2',$string); }
			return trim($string);
		}

		static $white_characters, $white_and_invisible_characters;
		if(!$white_characters){
			$white_characters = [
				'\x09', // Horizontal Tab
				'\x0A', // Line Feed
				'\x0B', // Vertical Tab
				'\x0C', // Form Feed
				'\x0D', // Carriage Return
				'\x20', // Space
				'\xC2\x85', // Next Line
				'\xC2\xA0', // No-Break Space (NBSP)
				'\xE1\x9A\x80', // Ogham Space Mark
				'\xE1\xA0\x8E', // Mongolian Vowel Separator
				'\xE2\x80\x80', // En Quad
				'\xE2\x80\x81', // Em Quad
				'\xE2\x80\x82', // En Space
				'\xE2\x80\x83', // Em Space
				'\xE2\x80\x84', // Three-Per-Em Space
				'\xE2\x80\x85', // Four-Per-Em Space
				'\xE2\x80\x86', // Six-Per-Em Space
				'\xE2\x80\x87', // Figure Space
				'\xE2\x80\x88', // Punctuation Space
				'\xE2\x80\x89', // Thin Space
				'\xE2\x80\x8A', // Hair Space
				'\xE2\x80\xA8', // Line Separator
				'\xE2\x80\xA9', // Paragraph Separator
				'\xE2\x80\xAF', // Narrow No-Break Space
				'\xE2\x81\x9F', // Medium Mathematical Space
				'\xE3\x80\x80', // Ideographic Space
			];

			$invisible_characters = [
				'\x00', // Null byte
				'\xC2\xAD', // Soft Hyphen
				'\xCD\x8F', // Combining Grapheme Joiner
				'\xE1\xA0\x8E', // Mongolian Vowel Separator
				'\xE2\x80\x8B', // Zero Width Space (ZWSP)
				'\xE2\x80\x8C', // Zero Width Non-Joiner (ZWNJ)
				'\xE2\x80\x8D', // Zero Width Joiner (ZWJ)
				'\xE2\x81\xA0', // Word Joiner
				'\xE2\x80\xAA', // Left-to-Right Embedding (LRE)
				'\xE2\x80\xAB', // Right-to-Left Embedding (RLE)
				'\xE2\x80\xAC', // Pop Directional Formatting (PDF)
				'\xE2\x80\xAD', // Left-to-Right Override (LRO)
				'\xE2\x80\xAE', // Right-to-Left Override (RLO)
				'\xE2\x81\xA1', // Function Application
				'\xE2\x81\xA2', // Invisible Times
				'\xE2\x81\xA3', // Invisible Separator
				'\xE2\x81\xA4', // Invisible Plus
				'\xEF\xBB\xBF', // Byte Order Mark (BOM)
			];

			$white_and_invisible_characters = array_merge($white_characters,$invisible_characters);
			$white_and_invisible_characters = "(".join("|",$white_and_invisible_characters).")";

			$white_characters = "(".join("|",$white_characters).")";
		}
		if($remove_hidden_characters){
			return preg_replace("/^$white_and_invisible_characters{0,}(.*?)$white_and_invisible_characters{0,}$/s",'\2',$string);
		}
		return preg_replace("/^$white_characters{0,}(.*?)$white_characters{0,}$/s",'\2',$string);
	}

	/**
	 * First removes all whitespaces and hidden characters on both ends of the string, and then changes remaining consecutive whitespace groups into one space each.
	 *
	 * @return String4
	 */
	function squish(){
		$out = $this->trim(true);
		return $out->gsub('/\s+/',' ');
	}

	/**
	 * Removes HTML tags
	 *
	 * @return String4
	 */
	function stripTags(){
		return $this->_copy(strip_tags($this->_String4));
	}

	function stripHtml(){
		$content = $this->_String4;

		// removing HTML comments
		$content = preg_replace('/<!--[^-].*?[^-]-->/s','',$content);

		// the following tags are removed with their content
		$tags = array(
			"head",
			"style",
			"script",
			"object",
			"embed",
			"applet",
			"noframes",
			"noscript",
			"noembed",
		);
		$tags = join('|',$tags);
		$content = preg_replace("#<($tags)[^>]*?>.*?</\\1>#siu"," ", $content);

		// remove inline tags
		$inline_tags = array(
			"a",
			"abbr",
			"acronym",
			"b",
			"bdo",
			"big",
			"button",
			"cite",
			"code",
			"dfn",
			"em",
			"i",
			"img",
			"input",
			"kbd",
			"label",
			"map",
			"object",
			"output",
			"q",
			"samp",
			"script",
			"select",
			"small",
			"span",
			"strong",
			"sub",
			"sup",
			"textarea",
			"time",
			"tt",
			"var",
		);
		$inline_tags = join('|',$inline_tags);
		$content = preg_replace("#<($inline_tags)(|\\s[^>]*?)>#si","",$content);
		$content = preg_replace("#</($inline_tags)>#si","",$content);

		//
		$content = preg_replace('#<[^>]*?>#s',' ',$content);

		$content = html_entity_decode($content); // e.g. "&amp;" -> "&"

		$content = $this->_trim($content);
		$content = preg_replace('#[\t\r\n]#',' ',$content);
		$content = preg_replace('#\s{2,}#',' ',$content);

		return $this->_copy($content);
	}

	/**
	 * Returns the number of times pattern matches the string.
	 *
	 * When the pattern matches the string it returns these matches in $matches array as if it was returned by preg_match
	 * but strings are instantiated to String4 objects.
	 *
	 * @param string $pattern Regular expression
	 * @param $matches
	 * @return integer|bool number of matches or false if an error occurs
	 */
	function match($pattern,&$matches = null){
		$out = preg_match($pattern,$this,$matches);
		if(is_array($matches)){
			foreach($matches as &$m){
				$m = new self($m);
			}
		}
		return $out;
	}

	/**
	 * Returns char at given position.
	 *
	 * Position starts from 0.
	 *
	 * ```
	 * $str = new String4("Hello");
	 * $str->at(1); // 'e'
	 * ```
	 *
	 * @param integer $position in string starting from 0
	 * @return String4
	 */
	function at($position){
		return $this->_copy($this->substr($position,1));
	}

	/**
	 * Returns substring of the stored string.
	 *
	 * ```
	 * $str = new String4("Lorem Ipsum");
	 * echo $str->substr(0,5); // "Lorem"
	 * echo $str->substr(-5); // "Ipsum"
	 * ```
	 *
	 * @param integer $start
	 * @param integer $length
	 * @return String4
	 */
	function substr($start,$length = null){
		if(function_exists("mb_substr")){
			if(PHP_VERSION_ID<50408 && is_null($length)){
				return $this->_copy(mb_substr($this->_String4,$start));
			}
			return $this->_copy(mb_substr($this->_String4,$start,$length,$this->getEncoding()));
		}

		if(is_null($length)){
			return $this->_copy(substr($this->_String4,$start));
		}
		return $this->_copy(substr($this->_String4,$start,$length));
	}

	/**
	 * Returns the first character of the string or the first $limit characters.
	 *
	 * @param integer $limit
	 * @return String4 new instance that contains the first characters of the stored string
	 */
	function first($limit = 1){
		return $this->substr(0,$limit);
	}

	/**
	 * Checks if the string contains another string.
	 *
	 * Example
	 * ```
	 * $str = new String4("Hello World");
	 * $str->contains("Hello"); // true
	 * $str->contains(array("Hello","World")); // true
	 * ```
	 *
	 * @param string|array $needle
	 * @return bool
	 */
	function contains($needle){
		if(is_array($needle)){
			foreach($needle as $n){
				if(!$this->contains($n)){ return false; }
			}
			return true;
		}
		return !is_bool(strpos($this->_String4,(string)$needle));
	}

	/**
	 * Does contains at least one of the given strings?
	 *
	 * Example
	 * ```
	 * if($breakfast->containsOneOf("orange","lemon","apple"){
	 * 	// sort of vitamin stuff
	 * }
	 * ```
	 *
	 * ```
	 * if($breakfast->containsOneOf(array("orange","lemon","apple"))){
	 * 	// just for sure...
	 * }
	 * ```
	 *
	 * @param array $needles array of string
	 * @return bool
	 */
	function containsOneOf(){
		$needles = array();
		foreach(func_get_args() as $arg){
			if(is_array($arg)){
				$needles = array_merge($arg,$needles);
			}else{
				$needles[] = $arg;
			}
		}
		foreach($needles as $needle){
			if($this->contains($needle)){ return true; }
		}
		return false;
	}

	/**
	 * Converts string into CamelCase format.
	 *
	 * Example
	 *
	 * "hello_world" -> "HelloWorld"
	 * ```
	 * $camel_case = $string->camelize();
	 * ```
	 *
	 * "hello_world" -> "helloWorld"
	 * ```
	 * $camel_case = $string->camelize(array("lower" => true));
	 * ```
	 *
	 * @param array $options
	 * - lower - leave first character lowercase
	 * @return String4
	 */
	function camelize($options = array()){
		$options += array(
			"lower" => false,
		);
		$out = $this->_copy();
		$s = &$out->_String4;
		$s = preg_replace_callback("/_([a-z0-9\p{Ll}])/ui",function($matches){ return mb_strtoupper($matches[1]); },$this->_String4);

		if(mb_strlen($s)){
			$first = $out->substr( 0, 1);
			$first = $options["lower"] ? mb_strtolower($first) : mb_strtoupper($first);
			$s = $first.$out->substr(1);
		}

		return $out;
	}

	/**
	 * Capitalizes all the words and replaces some characters in the string to create a nicer looking title
	 *
	 * The trailing ‘_id’,‘Id’.. can be kept and capitalized by setting the optional parameter keep_id_suffix to true. By default, this parameter is false.
	 *
	 *	$string = new String4("x-men: the last stand");
	 *	echo $string->titleize(); // "X Men: The Last Stand"
	 */
	function titleize($options = array()){
		$options += array(
			"keep_id_suffix" => false,
		);
		$string = $this->_copy();

		if(!$options["keep_id_suffix"]){
			$string = $string->gsub('/[_\s]id$/i','');
		}

		$chunks = $string
			->gsub('/([a-z0-9])([A-Z])/','\1 \2')
			->gsub('/[_-]/',' ')
			->gsub('/\s+/',' ')
			->trim()
			->split(" ");

		$out = array();
		foreach($chunks as $chunk){
			$out[] = $chunk->capitalize()->toString();
		}

		return $this->_copy(join(" ",$out));
	}

	/**
	 * Returns corresponding table name for a given ClassName.
	 *
	 * Example
	 * Book => books
	 *
	 * ```
	 * $class = new String4("Book");
	 * echo $class->tableize();
	 * ```
	 *
	 * @return String4
	 */
	function tableize(){
		return $this->underscore()->pluralize();
	}

	/**
	 * Makes plural form of a word.
	 *
	 * Example
	 * apple => apples
	 * ```
	 * $apple = new String4("apple");
	 * echo $apple->pluralize();
	 * ```
	 *
	 * @return String4
	 */
	function pluralize(){
		return $this->_copy(_Inflect::pluralize((string)$this));
	}

	/**
	 * Makes singular form of a word.
	 *
	 * Example
	 * ```
	 * $apples = new String4("Rotten Apples");
	 * echo $apples->singularize(); // "Rotten Apple"
	 * ```
	 *
	 * @return String4
	 */
	function singularize(){
		return $this->_copy(_Inflect::singularize((string)$this));
	}

	/**
	 * Converts string into underscore format.
	 *
	 * Example
	 * HelloWorld => hello_world
	 * ```
	 * $underscore = $camel_case->underscore();
	 * ```
	 *
	 * @return String4
	 */
	function underscore(){
		$out = $this->_copy();
		$out->_String4 = mb_strtolower(preg_replace("/([a-z0-9\p{Ll}])([A-Z\p{Lu}])/u","\\1_\\2",$this->_String4));
		return $out;
	}

	/**
		* Returns instance with string in lower case
		*
		* @return String4
	 */
	function downcase(){
		return $this->_copy(Translate::Lower($this->toString(),$this->getEncoding()));
	}

	/**
	 * Alias to downcase()
	 *
	 * @return String4
	 */
	function lower(){ return $this->downcase(); }

	/**
	 * Returns true when all character in the string are lowercase
	 *
	 * @return bool
	 */
	function isLower(){ return $this->length()>0 && $this->toString()===$this->downcase()->toString(); }

	/**
	 * Returns instance with string in upper case
	 *
	 * For empty string it returns false.
	 *
	 * @return String4
	 */
	function upcase(){
		return $this->_copy(Translate::Upper($this->toString(),$this->getEncoding()));
	}

	/**
	 * Alias to upcase()
	 *
	 * @return String4
	 */
	function upper(){ return $this->upcase(); }

	/**
	 * Returns true when all character in the string are uppercase
	 *
	 * For empty string it returns false.
	 *
	 * @return bool
	 */
	function isUpper(){ return $this->length()>0 && $this->toString()===$this->upcase()->toString(); }

	/**
	 * Makes first character of string uppercase.
	 *
	 * @return String4
	 */
	function capitalize() {
		$first = $this->substr(0,1)->upcase();
		return $first->append($this->substr(1));
	}

	/**
	 * Makes first character of string lowercase.
	 *
	 * @return String4
	 */
	function uncapitalize(){
		$first = $this->substr(0,1)->downcase();
		return $first->append($this->substr(1));
	}

	/**
	 * Converts string to ASCII
	 *
	 * @return String4 object containing string converted to ASCII
	 */
	function toAscii(){
		return $this->_copy(Translate::Trans($this->toString(),$this->getEncoding(),"ASCII"),"ASCII");
	}

	/**
	 * Returns string as slug.
	 *
	 * ```
	 * $s = new String4("Amazing facts about foxes!");
	 * echo $s->toSlug();
	 * ```
	 * this example outputs "amazing-facts-about-foxes"
	 *
	 * max length
	 * ```
	 * echo $s->toSlug(["max_length" => 10]); // "amazing-fa"
	 * // or
	 * echo $s->toSlug(10); // "amazing-fa"
	 * ```
	 *
	 * mandatory suffix
	 * ```
	 * echo $s->toSlug(["suffix" => "Really nice!"]); // "amazing-facts-about-foxes-really-nice"
	 *
	 * echo $s->toSlug(["suffix" => "123"]); // "amazing-facts-about-foxes-123"
	 * echo $s->toSlug(["suffix" => 10, "suffix" => "123"]) // "amazin-123"
	 * ```
	 *
	 * @param array $options
	 * @return string
	 */
	function toSlug($options = array()){
		if(!is_array($options)){
			$options = array(
				"max_length" => $options
			);
		}
		$options += array(
			"max_length" => null,
			"suffix" => "",
		);

		$suffix = strlen($options["suffix"]) ? String4::ToObject($options["suffix"])->toSlug()->toString() : "";
		$suffix = strlen($suffix) ? "-$suffix" : "";

		$max_length = $options["max_length"] && strlen($suffix) ? $options["max_length"] - strlen($suffix) : $options["max_length"];
		$max_length = $max_length<0 ? 0 : $max_length;

		$slug = $this->toAscii()->lower()->gsub('/[^a-z0-9]+/',' ')->trim()->substr(0,$max_length)->trim()->replace(' ','-')->append($suffix)->gsub('/^-/','');
		return $slug;
	}

	/**
	 * Returns a string shortened to given length.
	 *
	 * When a string is shortened a sequence of characters can be appended. By default '...' is appended.
	 * This can be changed by option 'omission'.
	 *
	 * It can also detect certain characters and limit the string to this character.
	 * So you can take care of split words.
	 *
	 * @param integer $length
	 * @param array $options
	 * - **omission** string to append to the end of the truncated string [default: '...']
	 * - **separator** last character at which the string will end if it appears before the end of the truncated string
	 * @return string
	 */
	function truncate($length,$options = array()){
		$options += array(
			"omission" => "...",
			"separator" => "",
		);

		$text = $this->_copy();
		$omission = new self($options["omission"]);

		$length_with_room_for_omission = $length - $omission->length();

		$stop = $length_with_room_for_omission;

		if($text->length()>$length){
			$text = $text->substr(0,$stop);
			if($options["separator"]){
				$_text = $text->copy();
				while($_text->length()>0){
					$ch = $_text->at($_text->length() - 1);
					$_text = $_text->substr(0,$_text->length() - 1);
					if((string)$ch==(string)$options["separator"]){ break; }
				}
				if($_text->length()>0){ $text = $_text; }
			}
			$text->append($omission);
		}

		return $text;
	}

	/**
	 *
	 */
	function fixEncoding($options = array()){
		if(is_string($options)){
			$options = array("replacement" => $options);
		}

		$options += array(
			"replacement" => "�", // U+FFFD REPLACEMENT CHARACTER used to replace an unknown, unrecognized or unrepresentable character
		);

		$replacement = $options["replacement"];

		$text = $this->_String4;

		if($this->getEncoding(true)!=="utf8"){
			return $this->_copy($text);
		}

		// Source code for this method taken from https://github.com/yarri/Utf8Cleaner

		// https://stackoverflow.com/questions/1401317/remove-non-utf8-characters-from-string
		$regex = <<<'END'
/
  (
    (?: [\x00-\x7F]               # single-byte sequences   0xxxxxxx
    |   [\xC0-\xDF][\x80-\xBF]    # double-byte sequences   110xxxxx 10xxxxxx
    |   [\xE0-\xEF][\x80-\xBF]{2} # triple-byte sequences   1110xxxx 10xxxxxx * 2
    |   [\xF0-\xF7][\x80-\xBF]{3} # quadruple-byte sequence 11110xxx 10xxxxxx * 3 
    ){1,100}                      # ...one or more times
  )
| ( [\x80-\xBF] )                 # invalid byte in range 10000000 - 10111111
| ( [\xC0-\xFF] )                 # invalid byte in range 11000000 - 11111111
/x
END;
			$replacer = function($captures) use ($replacement) {
				if ($captures[1] != "") {
					// Valid byte sequence. Return unmodified.
					return $captures[1];
				}
				elseif ($captures[2] != "") {
					// Invalid byte of the form 10xxxxxx.
					// Encode as 11000010 10xxxxxx.
					//return "\xC2".$captures[2];
					return $replacement;
				}
				else {
					// Invalid byte of the form 11xxxxxx.
					// Encode as 11000011 10xxxxxx.
					//return "\xC3".chr(ord($captures[3])-64);
					return $replacement;
				}
			};
			$text = preg_replace_callback($regex, $replacer, $text);
			// return $text;

			return $this->_copy($text);
	}

	/**
	 * Returns copy of the object.
	 *
	 * @return String4
	 */
	function copy(){ return $this->_copy(); }

	/**
	 * @ignore
	 */
	function _copy($string = null,$encoding = null){
		if(!isset($string)){ $string = $this->_String4; }
		if(!isset($encoding)){ $encoding = $this->getEncoding(); }
		return new self($string,$encoding);
	}

	/**
	 * Returns contents as string.
	 *
	 * @return string
	 */
	function toString(){
		return $this->_String4;
	}

	/**
	 * Converts string to a boolean value
	 */
	function toBoolean(){
		if(in_array($this->lower(),array('','false','off','no','n','f'))){
			return false;
		}
		return (bool)$this->toString();
	}

	function removeEmptyLines($options = []){
		$options += [
			"max_empty_lines" => 0,
			"trim_empty_lines" => true,
		];

		$text = $this->_String4;
		$out = [];
		$empty_lines_counter = 0;
		while(preg_match('/^(.*?)(\r\n|\n\r|\n|\r)/s',$text,$matches)){
			$line = $matches[1];
			$ending = $matches[2];

			$text = substr($text,strlen($line) + strlen($ending));

			if($this->_trim($line,true)!==""){
				$empty_lines_counter = 0;
			}else{
				$empty_lines_counter++;

				if($empty_lines_counter>$options["max_empty_lines"]){
					continue;
				}

				if($options["trim_empty_lines"]){
					$line = $this->_trim($line);
				}
			}

			$out[] = $line;
			$out[] = $ending;
		}

		$line = $text;

		if($this->_trim($line,true)!==""){
			$out[] = $line;
		}else{
			$empty_lines_counter++;

			if($empty_lines_counter<=$options["max_empty_lines"]){
				if($options["trim_empty_lines"]){
					$line = $this->_trim($line);
				}

				$out[] = $line;
			}
		}

		return $this->_copy(join("",$out));
	}

	/**
	 * Applies the callback to the every line of the content
	 *
	 * ```
	 * // trim every line
	 * $string = $string->eachLineMap(function($line){ return $line->trim(); });
	 * ```
	 */
	function eachLineMap($callback){
		$text = $this->_String4;
		$out = [];
		while(preg_match('/^(.*?)(\r\n|\n\r|\n|\r)/s',$text,$matches)){
			$line = $matches[1];
			$ending = $matches[2];

			$text = substr($text,strlen($line) + strlen($ending));

			$out[] = (string)$callback($this->_copy($line));
			$out[] = $ending;
		}

		if(strlen($text)>0){
			$out[] = (string)$callback($this->_copy($text));
		}

		return $this->_copy(join("",$out));
	}

	/**
	 * Filters lines of the content using a callback function
	 *
	 * ```
	 * // filter out empty lines
	 * $string = $string->eachLineFilter(function($line){ return $line->trim()->length()>0; });
	 * ```
	 */
	function eachLineFilter(callable $filter = null){
		if(!$filter){
			$filter = function($line){ return $line->length()>0; };
		}

		$text = $this->_String4;
		$out = [];
		$ending = "";
		while(preg_match('/^(.*?)(\r\n|\n\r|\n|\r)/s',$text,$matches)){
			$prev_ending = $ending;

			$line = $matches[1];
			$ending = $matches[2];

			$text = substr($text,strlen($line) + strlen($ending));

			$line = $this->_copy($line);
			if((bool)$filter($line)){
				$out && ($out[] = $prev_ending);
				$out[] = $line;
			}
		}

		if(strlen($text)>0){
			$line = $this->_copy($text);
			if((bool)$filter($line)){
				$out && ($out[] = $ending);
				$out[] = $line;
			}
		}

		return $this->_copy(join("",$out));
	}
	
	/**
	 * Magic method
	 *
	 * Example
	 * ```
	 * $s = new String4("Hello");
	 * echo "$s"; // prints Hello
	 * ```
	 *
	 * @return string
	 */
	function __toString(){
		return $this->_String4;
	}
}
