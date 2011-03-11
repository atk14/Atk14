<?php
/**
* Class for headaches free string manipulation.
*
* Here is an inspiration:
* http://api.rubyonrails.org/classes/String.html
*/
class String{

	/**
	 * <code>
	 * $str = new String();
	 * $str = new String("Hello");
	 * $str2 = new String($str);
	 * </code>
	 */
	function String($string = ""){
		$this->_String = "$string";
	}

	/**
	 * <code>
	 * $str = String::ToObject("Hello");
	 * $str = String::ToObject($str);
	 * </code>
	 */
	function ToObject($string){
		if(is_object($string) && strtolower(get_class($string))=="string"){
			return $string;
		}
		return new String($string);
	}

	/**
	 *	Builds an random string consists of [A-Za-z0-9].
	 * Special chars could be passed in $options.
	 *
	 * echo String::RandomString();
	 * echo String::RandomString(8);
	 * echo String::RandomString(array("length" => 8));
	 * echo String::RandomString(array("length" => 8, "extra_chars" => "#$!%^")); 
	 */
	function RandomString($length = 32,$options = array()){
		if(is_array($length)){
			$options = $length;
			$length = 32;
		}

		$options = array_merge(array(
			"extra_chars" => "",
			"length" => $length,
		),$options);

		srand ((double) microtime() * 1000000);
		$temp = array("a","i","o","s","t","u","v","3","4","5","8","B","C","D","E","F","7","G","H","I","J","K","L","M","N","O","j","k","l","6","P","Q","W","b","c","d","e","f","g","h","p","q","r","x","y","z","0","1","S","T","U","w","2","9","A","R","V","m","n");
		foreach(preg_split("//",$options["extra_chars"]) as $ch){
			strlen($ch) && ($temp[] = $ch);
		}
		shuffle($temp);

		$rand = array_rand($temp,$options["length"]);
		$out = '';
		for($i=0;$i<sizeof($rand);$i++){
			$out .= $temp[$rand[$i]];
		}
		return $out;
	}

	/**
	 * Some characters are not suitable for passwords, because they cause mistakes.
	 * Like zero and capital O: 0 versus O.
	 */
	function RandomPassword($length = 10){
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
		return new String($password);
	}

	/**
	 * ATK14 sometimes converts objects into their scalar representation automatically by calling getId() method.
	 * Due to it we need this silly looking method here.
	 * 
	 * @return string
	 */
	function getId(){ return $this->_String; }

	function length(){ return strlen($this->_String); }
	
	/**
	 * <code>
	 * $str = new String("Hello World");
	 *
	 * $str->replace("World","Guys");
	 *	// or
	 * $str->replace(array(
	 * 	"Hello" => "Hi",
	 * 	"World" => "Guys",
	 * ));
	 * </code>
	 *
	 * !! Changes the object state
	 */
	function replace($search,$replace = null){
		if(is_array($search)){
			$_replaces_keys = array();
			$_replaces_values = array();
			reset($search);
			while(list($key,) = each($search)){
				$_replaces_keys[] = $key;
				$_replaces_values[] = $search[$key];
			}   
			if(sizeof($_replaces_keys)==0){
				return $this;
			}   
			$this->_String = str_replace($_replaces_keys,$_replaces_values,$this->_String);
			return $this;
		}
		$this->_String = str_replace($search,$replace,$this->_String);
		return $this;
	}

	/**
	* <code>
	* $string = new String("World");
	* $string->prepend("Hello "); // Hello World
	* </code>
	*/
	function prepend($content){
		$this->_String = "$content".$this->_String;
		return $this;
	}

	/**
	* <code>
	* $string = new String("Hello");
	* $string->append(" World"); // Hello World
	* </code>
	*/
	function append($content){
		$this->_String .= "$content";
		return $this;
	}

	function match($pattern,&$matches = null){
		return preg_match($pattern,$this,$matches);
	}

	/**
	 * Returns char at given position.
	 * Position starts from 0.
	 *
	 *	<code>
	 * $str = new String("Hello");
	 * $str->at(2); // 'e'
	 *	</code>
	 *
	 * @return Str
	 */
	function at($position){
		return $this->_copy($this->substr($position,1));
	}

	function substr($start,$length = null){
		return $this->_copy(substr($this->_String,$start,$length));
	}

	/**
	 * @return bool
	 */
	function contains($needle){
		return !is_bool(strpos($this->_String,(string)$needle));
	}

	/**
	 * Converts string into CamelCase format.
	 *
	 * <code>
	 * $camel_case = $string->camelize(); // "hello_world" -> "HelloWorld"
	 * $camel_case = $string->camelize(array("lower" => true)); // "hello_world" -> "helloWorld"
	 * </code>
	 *
	 * @return String
	 */
	function camelize($options = array()){
		$options = array_merge(array(
			"lower" => false,
		),$options);
		$out = $this->_copy();
		$s = &$out->_String;
		$s = preg_replace("/_([a-z0-9])/ie","strtoupper('\\1')",$this->_String);
		if(isset($s[0])){
			$s[0] = $options["lower"] ? strtolower($s[0]) : strtoupper($s[0]);
		}
			
		return $out;
	}

	/**
	 * Converts string into underscore format.
	 * 
	 * <code>
	 * $underscore = $camel_case->underscore(); // "HelloWorld" -> "hello_world"
	 * </code>
	 * 
	 * @return String
	 */
	function underscore(){
		$out = $this->_copy();
		$out->_String = strtolower(preg_replace("/([a-z0-9])([A-Z])/","\\1_\\2",$this->_String));
		return $out;
	}

	function copy(){ return $this->_copy(); }
	function _copy($string = null){
		if(!isset($string)){ $string = $this->_String; }
		return new String($string);
	}
	
	function __toString(){
		return $this->_String;
	}
}
