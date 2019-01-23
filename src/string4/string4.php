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

		srand ((double) microtime() * 1000000);
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
	 * @return string encoding
	 */
	function getEncoding(){ return $this->_Encoding; }

	/**
	 * Returns array of chars.
	 *
	 * @todo: make it work with multibyte strings
	 * @return array
	 */
	function chars(){
		return str_split($this->toString());
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
	 * @param string $pattern regexp string
	 * @param string $replace string replacement
	 * @return String4 Object of String4 class with replaced content
	 */
	function gsub($pattern,$replace){
		return $this->_copy(preg_replace($pattern,$replace,$this->_String4));
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
	 * Removes all whitespace.
	 *
	 * @return String4
	 */
	function trim(){
		return $this->_copy(trim($this->_String4));
	}

	/**
	 * First removes all whitespace on both ends of the string, and then changes remaining consecutive whitespace groups into one space each.
	 *
	 * @return String4
	 */
	function squish(){
		$out = $this->trim();
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
		$s = preg_replace_callback("/_([a-z0-9])/i",function($matches){ return strtoupper($matches[1]); },$this->_String4);

		if(isset($s[0])){
			$s[0] = $options["lower"] ? strtolower($s[0]) : strtoupper($s[0]);
		}
			
		return $out;
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
		$out->_String4 = strtolower(preg_replace("/([a-z0-9])([A-Z])/","\\1_\\2",$this->_String4));
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
		* Returns instance with string in upper case
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
	 * @param integer $max_length
	 * @return string
	 */
	function toSlug($max_length = null){
		return $this->toAscii()->lower()->gsub('/[^a-z0-9]+/',' ')->substr(0,$max_length)->trim()->replace(' ','-');
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
		if(!isset($encoding)){ $encoding = $this->_Encoding; }
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
