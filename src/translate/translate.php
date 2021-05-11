<?php
/**
 * Class for converting strings between charsets.
 *
 *
 * @internal
 *  updates
 *  3.12.2003 - pridana funkce _TO_windows_1250
 *  4.12.2003 - pridana funkce _TO_ascii pro osmibitove kodovani
 * 15.12.2003 - pridana funkce _check_encoding_ascii a _check_encoding_utf8
 * 13.3.2006 - opravena chyba pri Translate::Lower($neco,"windows-1250") a Translate::Upper($neco,"windows-1250")
 * 13.3.2006 - do metod lower a upper pridano kodovani iso-8859-2
 * 16.6.2006 - konvert z windows-1250 do 852 a naopak
 * 26.9.2007 - konvert z utf-8 do ascii (jen ceske znaky)
 * 22.10.2007 - parametrem pro prekodovani ted muze byt i pole
 * 29.11.2007 - doplneno urcovani delky retyezce}
 *
 * @package Atk14
 * @subpackage Translate
 * @filesource
 */

/**
 * Translating character sets is faster with iconv.
 * Unfortunately iconv doesn't work correctly on all systems though is is installed.
 * Enable iconv if you are sure that it works (passes tests)
 */
defined("TRANSLATE_USE_ICONV") || define("TRANSLATE_USE_ICONV",false);

/**
 * This class converts strings between charsets.
 *
 * It's important to search the code to discover the charset support.
 *
 * The class uses its own conversion methods, but you can use phps` internal iconv extension.
 * You can force the class to use iconv by defining constant TRANSLATE_USE_ICONV to true.
 *
 * When iconv extension is not installed Translate class uses its own translation methods for some charsets.
 *
 *
 * Usage:
 * ```
 * $output_text = Translate::Trans($input_text,$from_charset,$to_charset);
 * ```
 * When it is impossible to convert the string the trans() method returns the input string. 
 *
 * The Translate class is able to change the string to lowercase or uppercase
 * Example:
 * ```
 * $output_text = Translate::Lower($input_text,"windows-1250");
 * $output_text = Translate::Upper($input_text,"windows-1250");
 * ```
 *
 * Checking that string is in selected charset.
 * ```
 * $output_text = Translate::CheckEncoding($input_text,"utf-8");
 * ```
 *
 * Checking length of a UTF-8 string:
 * ```
 * $length = Translate::Length($str,"UTF-8");
 * ```
 *
 * @package Atk14
 * @subpackage Translate
 * @uses iconv
 * @filesource
 *
 */
class Translate{

	const VERSION = "1.2.2";

	/**
	 * Converts string from source charset to target charset.
	 *
	 * Parameter can be an array of string and the method also returns array with all converted strings.
	 *
	 * $options description
	 * - "recode_array_keys" - when $text is of type array, also its keys will be converted
	 * 
	 * @param string|array $text
	 * @param string $from_charset input charset
	 * @param string $to_charset output charset
	 * @param array $options
	 * - recode_array_keys usable only when $text is array; then also keys will be converted
	 * @return string|array
	 */
	static function Trans($text,$from_charset,$to_charset,$options = array()){
		$from_charset = self::_GetCharsetByName($from_charset);
		$to_charset = self::_GetCharsetByName($to_charset);
		if($from_charset==$to_charset){
			return $text;
		}
		return self::_Trans($text,$from_charset,$to_charset,$options);
	}

	/**
	 * @ignore
	 */
	static function _Trans($text,$from_charset,$to_charset,$options = array()){
		if(is_array($text)){
			$out = array();
			foreach($text as $key => $value){
				$_key = $key;
				if(isset($options["recode_array_keys"]) && $options["recode_array_keys"]){
					$_key = self::_Trans($key,$from_charset,$to_charset);
				}
				$out[$_key] = (is_string($text[$key]) || is_array($text[$key])) ? self::_Trans($text[$key],$from_charset,$to_charset) : $text[$key];
			}
			return $out;
		}

		if($from_charset=="utf8" && $to_charset!="utf8"){
			$text = self::_RemoveUtf8Headaches($text);
		}

		if(TRANSLATE_USE_ICONV && function_exists('iconv')){
			$success=true;
			($out=@iconv($from_charset, $to_charset.'//TRANSLIT', $text)) or ($success=false);
			if($out!==false && $success)
				return $out;
		}

		switch($to_charset){
			case "iso-8859-2":
				$text = self::_TO_iso_8859_2($text,$from_charset);
				break;
			case "utf8":
				$text = self::_TO_utf8($text,$from_charset);
				break;
			case "windows-1250":
			  $text = self::_TO_windows_1250($text,$from_charset);
				break;
			case "852":
				$text = self::_TO_852($text,$from_charset);
				break;			
			case "ascii":
				$text = self::_TO_ascii($text,$from_charset);
				break;
			case "HTML entities":
				$text = self::_TO_HTML_entitites($text,$from_charset);
				break;
		}

		return $text;
	}

	/**
	 * Removes special chars which don't have substitution in 8bit charsets.
	 *
	 * @ignore
	 */
	static function _RemoveUtf8Headaches($text){
		return strtr($text,array(
			chr(0xE2).chr(0x80).chr(0x93) => "-",
			chr(0xC2).chr(0xA0) => " ", // Non-breaking space
		));
	}

	static function _Transliteration($text){
		static $tr_table;

		if(!$tr_table){
			// the new table for transliteration
			require(__DIR__ . "/tr_tables/transliteration/tr_table.php");

			// the original array for transliteration
			$tr_table = array(
				// Cyrillic Transliteration Table
				// http://homes.chass.utoronto.ca/~tarn/courses/translit-table.html
				"А" => "A",
				"Б" => "B",
				"В" => "V",
				"Г" => "G", // "H" (in Ukrainian)
				"Ґ" => "G",
				"Д" => "D",
				"E" => "E", // ??
				"Е" => "E", // ??
				"Є" => "Je",
				"Ж" => "Z", // "Ž"
				"З" => "Z",
				"И" => "Y",
				"І" => "I",
				"Ї" => "Ji",
				"Й" => "J",
				"К" => "K",
				"Л" => "L",
				"М" => "M",
				"Н" => "N",
				"О" => "O",
				"П" => "P",
				"Р" => "R",
				"С" => "S",
				"Т" => "T",
				"У" => "U",
				"Ф" => "F",
				"Х" => "X",
				"Ц" => "C",
				"Ч" => "C", // "Č"
				"Ш" => "S", // "Š"
				"Щ" => "Sc", // "Šč"
				"Ю" => "Ju",
				"Я" => "Ja",
				"Ь" => "", // "'"
				"Ё" => "E",
				"Э" => "E",
				"Ъ" => "", // '"'
				"Ы" => "Y",
				//
				"а" => "a",
				"б" => "b",
				"в" => "v",
				"г" => "g", // "h" (Ukrainian)
				"ґ" => "g",
				"д" => "d",
				"е" => "e", // ??
				"є" => "je",
				"ж" => "z", // "ž"
				"з" => "z",
				"и" => "y",
				"і" => "i",
				"ї" => "ji",
				"й" => "j",
				"к" => "k",
				"л" => "l",
				"м" => "m",
				"н" => "n",
				"о" => "o",
				"п" => "p",
				"р" => "r",
				"с" => "s",
				"т" => "t",
				"у" => "u",
				"ф" => "f",
				"х" => "x",
				"ц" => "c",
				"ч" => "c", // "č"
				"ш" => "s", // "š"
				"щ" => "sc", // "šč"
				"ю" => "ju",
				"я" => "ja",
				"ь" => "", // "'"
				"ё" => "e",
				"э" => "e",
				"ъ" => "", // '"'
				"ы" => "y",

				// German
				"ä" => "ae",
				"ö" => "oe",
				"ü" => "ue",
				"Ä" => "Ae",
				"Ö" => "Oe",
				"Ü" => "Ue",
				"ß" => "ss",

				// Slovak - there are conflicts with German!!
				"ä" => "a",
				"Ä" => "A",
				"ľ" => "l",
				"Ľ" => "L",
				"ĺ" => "l",
				"Ĺ" => "L" 
			) + $tr_table;

			// Symbols, specials
			$tr_table += array(
				'’' => "'",
				'„' => '"',
				'“' => '"',
				'»' => '>>',
				'«' => '<<',
				'›' => '>',
				'‹' => '<',

				"–" => "-", // ndash
				"—" => "-", // mdash
				"®" => "(R)",
				"™" => "TM",
				"¼" => "1/4",
				"½" => "1/2",
				"¾" => "3/4",
				"…" => "...", // hellip
			) + $tr_table;

		}
		return strtr($text,$tr_table);
	}

	static function _RemoveUtf8Chars($text,$options = array()){
		$options += array(
			"unknown" => "?",
		);

		$chars = preg_split('//u',$text);
		foreach($chars as &$char){
			if(strlen($char)>1){
				$char = $options["unknown"];
			}
		}
		return join("",$chars);
	}

	/**
	 * Converts string to lowercase.
	 *
	 * @param string $text
	 * @param string $charset
	 * @return string
	 */
	static function Lower($text,$charset = null){
		static $TR_TABLES = array();

		$charset = self::_GetCharsetByName($charset);
		switch($charset){
			case "windows-1250":
			case "iso-8859-2":
				if(!isset($TR_TABLES[$charset])){
					require(dirname(__FILE__)."/tr_tables/lower_upper/$charset.php");
				}
				$text = strtr($text,$TR_TABLES[$charset]["velka"],$TR_TABLES[$charset]["mala"]);
				break;
			case "utf8":
				$text = mb_strtolower($text,"utf8");
				break;
			default: 
				$text = strtolower($text);
		}

		return $text;
	}

	/**
	 * Converts string to uppercase.
	 *
	 * @param string $text
	 * @param string $charset
	 * @return string
	 */
	static function Upper($text,$charset = null){
		static $TR_TABLES = array();

		$charset = self::_GetCharsetByName($charset);
		switch($charset){
			case "windows-1250":
			case "iso-8859-2":
				if(!isset($TR_TABLES[$charset])){
					require(dirname(__FILE__)."/tr_tables/lower_upper/$charset.php");
				}
				$text = strtr($text,$TR_TABLES[$charset]["mala"],$TR_TABLES[$charset]["velka"]);
				break;
			case "utf8":
				$text = mb_strtoupper($text,"utf8");
				break;
			case "ascii":
			default: 
				$text = strtoupper($text);
		}

		return $text;
	}

	/**
	 *
	 * @ignore
	 * @param string $charset unified chrset name
	 * @return string
	 */
	static function _GetCharsetByName($charset){
		if(!$charset){ $charset = defined("DEFAULT_CHARSET") ? DEFAULT_CHARSET : "utf8"; }

        static $trans=array( 	  
              "8859_2" => "iso-8859-2",
              "iso8859-2" => "iso-8859-2",
              "iso_8859-2:1987" => "iso-8859-2",
              "latin2" => "iso-8859-2",
              "latin-2" => "iso-8859-2",
              "lat2" => "iso-8859-2",
              "lat-2" => "iso-8859-2",
              "il2" => "iso-8859-2",
              
              "8859_1" => "iso-8859-1",
              "iso8859-1" => "iso-8859-1",
              "iso_8859-1:1987" => "iso-8859-1",
              "latin1" => "iso-8859-1",
              "latin-1" => "iso-8859-1",
              "lat1" => "iso-8859-1",
              "lat-1" => "iso-8859-1",
              "il1" => "iso-8859-1",
              
              "cp-1250" => "windows-1250",
              "cp1250" => "windows-1250",
              "windows1250" => "windows-1250",
              "win-1250" => "windows-1250",
              "win1250" => "windows-1250",
              "1250" => "windows-1250",
              
              "cp-1252" => "windows-1252",
              "cp1252" => "windows-1252",
              "windows1252" => "windows-1252",
              "win-1252" => "windows-1252",
              "win1252" => "windows-1252",
              "1252" => "windows-1252",
              
              "cskoi8r" => "koi8",
              "koi-8" => "koi8",

              "us-ascii" => "ascii",
              "usascii" => "ascii",
              
              "utf-8" => "utf8",
              
              "utf-16" => "utf16",
              
              "html entities" => "HTML entities",
              
              "cp-852" => "852",
              "cp852" => "852",
              
              "ibm-852" => "852",
              "ibm852" => "852",
              );
	  
	  $charset=strtolower(trim($charset));
	  if(array_key_exists($charset,$trans))
	      return $trans[$charset];
		return $charset;
	}

	/**
	 * @ignore
	 */
	static function _TO_HTML_entitites(&$text,$from_cp){
		switch($from_cp){
			case "windows-1250":
				$_z = array(chr(138),chr(140),chr(141),chr(142),chr(143),chr(154),chr(156),chr(157),chr(158),chr(159),chr(160),chr(161),chr(162),chr(163),chr(164),chr(165),chr(166),chr(167),chr(168),chr(169),chr(170),chr(171),chr(172),chr(173),chr(174),chr(175),chr(176),chr(177),chr(178),chr(179),chr(180),chr(181),chr(183),chr(184),chr(185),chr(186),chr(187),chr(188),chr(189),chr(190),chr(191),chr(192),chr(193),chr(194),chr(195),chr(196),chr(197),chr(198),chr(199),chr(200),chr(201),chr(202),chr(203),chr(204),chr(205),chr(206),chr(207),chr(208),chr(209),chr(210),chr(211),chr(212),chr(213),chr(214),chr(215),chr(216),chr(217),chr(218),chr(219),chr(220),chr(221),chr(222),chr(223),chr(224),chr(225),chr(226),chr(227),chr(228),chr(229),chr(230),chr(231),chr(232),chr(233),chr(234),chr(235),chr(236),chr(237),chr(238),chr(239),chr(240),chr(241),chr(242),chr(243),chr(244),chr(245),chr(246),chr(247),chr(248),chr(249),chr(250),chr(251),chr(252),chr(253),chr(254),chr(255));
				$_do = array('&#352;','&#346;','&#356;','&#381;','&#377;','&#353;','&#347;','&#357;','&#382;','&#378;','&#160;','&#711;','&#728;','&#321;','&#164;','&#260;','&#166;','&#167;','&#168;','&#169;','&#350;','&#171;','&#172;','&#173;','&#174;','&#379;','&#176;','&#177;','&#731;','&#322;','&#180;','&#181;','&#729;','&#184;','&#261;','&#351;','&#187;','&#317;','&#733;','&#318;','&#380;','&#340;','&#193;','&#194;','&#258;','&#196;','&#313;','&#262;','&#199;','&#268;','&#201;','&#280;','&#203;','&#282;','&#205;','&#206;','&#270;','&#208;','&#323;','&#327;','&#211;','&#212;','&#336;','&#214;','&#215;','&#344;','&#366;','&#218;','&#368;','&#220;','&#221;','&#354;','&#223;','&#341;','&#225;','&#226;','&#259;','&#228;','&#314;','&#263;','&#231;','&#269;','&#233;','&#281;','&#235;','&#283;','&#237;','&#238;','&#271;','&#240;','&#324;','&#328;','&#243;','&#244;','&#337;','&#246;','&#247;','&#345;','&#367;','&#250;','&#369;','&#252;','&#253;','&#355;','&#729;');
				return str_replace($_z,$_do,$text);
			case "iso-8859-2":
				$_z = array(chr(160),chr(161),chr(162),chr(163),chr(164),chr(165),chr(166),chr(167),chr(168),chr(169),chr(170),chr(171),chr(172),chr(173),chr(174),chr(175),chr(176),chr(177),chr(178),chr(179),chr(180),chr(181),chr(182),chr(183),chr(184),chr(185),chr(186),chr(187),chr(188),chr(189),chr(190),chr(191),chr(192),chr(193),chr(194),chr(195),chr(196),chr(197),chr(198),chr(199),chr(200),chr(201),chr(202),chr(203),chr(204),chr(205),chr(206),chr(207),chr(208),chr(209),chr(210),chr(211),chr(212),chr(213),chr(214),chr(215),chr(216),chr(217),chr(218),chr(219),chr(220),chr(221),chr(222),chr(223),chr(224),chr(225),chr(226),chr(227),chr(228),chr(229),chr(230),chr(231),chr(232),chr(233),chr(234),chr(235),chr(236),chr(237),chr(238),chr(239),chr(240),chr(241),chr(242),chr(243),chr(244),chr(245),chr(246),chr(247),chr(248),chr(249),chr(250),chr(251),chr(252),chr(253),chr(254),chr(255));
				$_do = array('&#160;','&#260;','&#728;','&#321;','&#164;','&#317;','&#346;','&#167;','&#168;','&#352;','&#350;','&#356;','&#377;','&#173;','&#381;','&#379;','&#176;','&#261;','&#731;','&#322;','&#180;','&#318;','&#347;','&#711;','&#184;','&#353;','&#351;','&#357;','&#378;','&#733;','&#382;','&#380;','&#340;','&#193;','&#194;','&#258;','&#196;','&#313;','&#262;','&#199;','&#268;','&#201;','&#280;','&#203;','&#282;','&#205;','&#206;','&#270;','&#208;','&#323;','&#327;','&#211;','&#212;','&#336;','&#214;','&#215;','&#344;','&#366;','&#218;','&#368;','&#220;','&#221;','&#354;','&#223;','&#341;','&#225;','&#226;','&#259;','&#228;','&#314;','&#263;','&#231;','&#269;','&#233;','&#281;','&#235;','&#283;','&#237;','&#238;','&#271;','&#240;','&#324;','&#328;','&#243;','&#244;','&#337;','&#246;','&#247;','&#345;','&#367;','&#250;','&#369;','&#252;','&#253;','&#355;','&#729;');
				return str_replace($_z,$_do,$text);
			default:
				return $text;
		}
	}

	/**
	 * @ignore
	 */
	static function _TO_iso_8859_2(&$text,$from_cp){
		static $TR_TABLES = array();

		switch($from_cp){
			case "windows-1250":
			case "windows-1252":
			case "iso-8859-1":
			case "kam":
			case "koi8":
			case "mac":
			case "macce":
			case "pc2":
			case "pc2a":
			case "utf16":	
			case "utf8":
			case "vga":
				//utf-16 je podmnozina kodovani utf-8	s nejakyma dalsim vyfikundacema.
				//mela by byt plne kompatibilni ve spodnich dvou planech.
				//znaky schopne konverze to iso-8859-2 by mely v techto planech.
				$_cp = $from_cp=="utf16" ? "utf8" : $from_cp;
				if(!isset($TR_TABLES[$_cp])){
					require(dirname(__FILE__)."/tr_tables/to_iso_8859_2/$_cp.php");
				}
				return strtr($text, $TR_TABLES["$_cp"]);
				break;
			case "HTML entities":
				$_do = array(chr(160),chr(161),chr(162),chr(163),chr(164),chr(165),chr(166),chr(167),chr(168),chr(169),chr(170),chr(171),chr(172),chr(173),chr(174),chr(175),chr(176),chr(177),chr(178),chr(179),chr(180),chr(181),chr(182),chr(183),chr(184),chr(185),chr(186),chr(187),chr(188),chr(189),chr(190),chr(191),chr(192),chr(193),chr(194),chr(195),chr(196),chr(197),chr(198),chr(199),chr(200),chr(201),chr(202),chr(203),chr(204),chr(205),chr(206),chr(207),chr(208),chr(209),chr(210),chr(211),chr(212),chr(213),chr(214),chr(215),chr(216),chr(217),chr(218),chr(219),chr(220),chr(221),chr(222),chr(223),chr(224),chr(225),chr(226),chr(227),chr(228),chr(229),chr(230),chr(231),chr(232),chr(233),chr(234),chr(235),chr(236),chr(237),chr(238),chr(239),chr(240),chr(241),chr(242),chr(243),chr(244),chr(245),chr(246),chr(247),chr(248),chr(249),chr(250),chr(251),chr(252),chr(253),chr(254),chr(255));
				//preklad s decimalnich kodu
				$_z = array('&#160;','&#260;','&#728;','&#321;','&#164;','&#317;','&#346;','&#167;','&#168;','&#352;','&#350;','&#356;','&#377;','&#173;','&#381;','&#379;','&#176;','&#261;','&#731;','&#322;','&#180;','&#318;','&#347;','&#711;','&#184;','&#353;','&#351;','&#357;','&#378;','&#733;','&#382;','&#380;','&#340;','&#193;','&#194;','&#258;','&#196;','&#313;','&#262;','&#199;','&#268;','&#201;','&#280;','&#203;','&#282;','&#205;','&#206;','&#270;','&#208;','&#323;','&#327;','&#211;','&#212;','&#336;','&#214;','&#215;','&#344;','&#366;','&#218;','&#368;','&#220;','&#221;','&#354;','&#223;','&#341;','&#225;','&#226;','&#259;','&#228;','&#314;','&#263;','&#231;','&#269;','&#233;','&#281;','&#235;','&#283;','&#237;','&#238;','&#271;','&#240;','&#324;','&#328;','&#243;','&#244;','&#337;','&#246;','&#247;','&#345;','&#367;','&#250;','&#369;','&#252;','&#253;','&#355;','&#729;');
				$text = str_replace($_z,$_do,$text);
				//preklad z hexa kodu
				$_z = array('&#xA0;','&#x104;','&#x2D8;','&#x141;','&#xA4;','&#x13D;','&#x15A;','&#xA7;','&#xA8;','&#x160;','&#x15E;','&#x164;','&#x179;','&#xAD;','&#x17D;','&#x17B;','&#xB0;','&#x105;','&#x2DB;','&#x142;','&#xB4;','&#x13E;','&#x15B;','&#x2C7;','&#xB8;','&#x161;','&#x15F;','&#x165;','&#x17A;','&#x2DD;','&#x17E;','&#x17C;','&#x154;','&#xC1;','&#xC2;','&#x102;','&#xC4;','&#x139;','&#x106;','&#xC7;','&#x10C;','&#xC9;','&#x118;','&#xCB;','&#x11A;','&#xCD;','&#xCE;','&#x10E;','&#xD0;','&#x143;','&#x147;','&#xD3;','&#xD4;','&#x150;','&#xD6;','&#xD7;','&#x158;','&#x16E;','&#xDA;','&#x170;','&#xDC;','&#xDD;','&#x162;','&#xDF;','&#x155;','&#xE1;','&#xE2;','&#x103;','&#xE4;','&#x13A;','&#x107;','&#xE7;','&#x10D;','&#xE9;','&#x119;','&#xEB;','&#x11B;','&#xED;','&#xEE;','&#x10F;','&#xF0;','&#x144;','&#x148;','&#xF3;','&#xF4;','&#x151;','&#xF6;','&#xF7;','&#x159;','&#x16F;','&#xFA;','&#x171;','&#xFC;','&#xFD;','&#x163;','&#x2D9;');
				$text = str_replace($_z,$_do,$text);
				//preklad z html entit
				$_do = array(chr(32),chr(33),chr(34),chr(35),chr(36),chr(37),chr(38),chr(39),chr(40),chr(41),chr(42),chr(43),chr(44),chr(45),chr(46),chr(47),chr(58),chr(59),chr(60),chr(61),chr(62),chr(63),chr(64),chr(91),chr(92),chr(93),chr(94),chr(95),chr(96),chr(123),chr(124),chr(125),chr(126),chr(160),chr(161),chr(162),chr(163),chr(164),chr(165),chr(166),chr(167),chr(168),chr(169),chr(170),chr(171),chr(172),chr(173),chr(174),chr(175),chr(176),chr(177),chr(178),chr(179),chr(180),chr(181),chr(182),chr(183),chr(184),chr(185),chr(186),chr(187),chr(188),chr(189),chr(190),chr(191),chr(192),chr(193),chr(194),chr(195),chr(196),chr(197),chr(198),chr(199),chr(200),chr(201),chr(202),chr(203),chr(204),chr(205),chr(206),chr(207),chr(208),chr(209),chr(210),chr(211),chr(212),chr(213),chr(214),chr(215),chr(216),chr(217),chr(218),chr(219),chr(220),chr(221),chr(222),chr(223),chr(224),chr(225),chr(226),chr(227),chr(228),chr(229),chr(230),chr(231),chr(232),chr(233),chr(234),chr(235),chr(236),chr(237),chr(238),chr(239),chr(240),chr(241),chr(242),chr(243),chr(244),chr(245),chr(246),chr(247),chr(248),chr(249),chr(250),chr(251),chr(252),chr(253),chr(254),chr(255));
				$_z = array('&space;','&exclam;','&quotedbl;','&numbersign;','&dollar;','&percent;','&ampersand;','&quoteright;','&parenleft;','&parenright;','&asterisk;','&plus;','&comma;','&minus;','&period;','&slash;','&colon;','&semicolon;','&less;','&equal;','&greater;','&question;','&at;','&bracketleft;','&backslash;','&bracketright;','&asciicircum;','&underscore;','&quoteleft;','&braceleft;','&bar;','&braceright;','&asciitilde;','&nobreakspace;','&Aogonek;','&breve;','&Lstroke;','&currency;','&Lcaron;','&Sacute;','&section;','&diaeresis;','&Scaron;','&Scedilla;','&Tcaron;','&Zacute;','&hyphen;','&Zcaron;','&Zabovedot;','&degree;','&aogonek;','&ogonek;','&lstroke;','&acute;','&lcaron;','&sacute;','&caron;','&cedilla;','&scaron;','&scedilla;','&tcaron;','&zacute;','&doubleacute;','&zcaron;','&zabovedot;','&Racute;','&Aacute;','&Acircumflex;','&Abreve;','&Adiaeresis;','&Lacute;','&Cacute;','&Ccedilla;','&Ccaron;','&Eacute;','&Eogonek;','&Ediaeresis;','&Ecaron;','&Iacute;','&Icircumflex;','&Dcaron;','&Eth;','&Nacute;','&Ncaron;','&Oacute;','&Ocircumflex;','&Odoubleacute;','&Odiaeresis;','&multiply;','&Rcaron;','&Uring;','&Uacute;','&Udoubleacute;','&Udiaeresis;','&Yacute;','&Tcedilla;','&ssharp;','&racute;','&aacute;','&acircumflex;','&abreve;','&adiaeresis;','&lacute;','&cacute;','&ccedilla;','&ccaron;','&eacute;','&eogonek;','&ediaeresis;','&ecaron;','&iacute;','&icircumflex;','&dcaron;','&eth;','&nacute;','&ncaron;','&oacute;','&ocircumflex;','&odoubleacute;','&odiaeresis;','&division;','&rcaron;','&uring;','&uacute;','&udoubleacute;','&udiaeresis;','&yacute;','&tcedilla;','&abovedot;');
				$text = str_replace($_z,$_do,$text);
				return $text;
				break;
			case "ascii":
				return $text;
				break;
			default:
				return $text;
		}
	}

	/**
	 * @ignore
	 */
	static function _TO_windows_1250(&$text,$from_cp){
		static $TR_TABLES = array();

		switch($from_cp){
			case "iso-8859-1":
			case "iso-8859-2":
			case "windows-1252":
			case "utf8":
			case "utf16":	
				$_cp = $from_cp=="utf16" ? "utf8" : $from_cp;
				if(!isset($TR_TABLES[$_cp])){
					require(dirname(__FILE__)."/tr_tables/to_windows_1250/$_cp.php");
				}
				return strtr($text, $TR_TABLES["$_cp"]);
				break;
			case "HTML entities":
				$_do = array(chr(138),chr(140),chr(141),chr(142),chr(143),chr(154),chr(156),chr(157),chr(158),chr(159),chr(160),chr(161),chr(162),chr(163),chr(164),chr(165),chr(166),chr(167),chr(168),chr(169),chr(170),chr(171),chr(172),chr(173),chr(174),chr(175),chr(176),chr(177),chr(178),chr(179),chr(180),chr(181),chr(183),chr(184),chr(185),chr(186),chr(187),chr(188),chr(189),chr(190),chr(191),chr(192),chr(193),chr(194),chr(195),chr(196),chr(197),chr(198),chr(199),chr(200),chr(201),chr(202),chr(203),chr(204),chr(205),chr(206),chr(207),chr(208),chr(209),chr(210),chr(211),chr(212),chr(213),chr(214),chr(215),chr(216),chr(217),chr(218),chr(219),chr(220),chr(221),chr(222),chr(223),chr(224),chr(225),chr(226),chr(227),chr(228),chr(229),chr(230),chr(231),chr(232),chr(233),chr(234),chr(235),chr(236),chr(237),chr(238),chr(239),chr(240),chr(241),chr(242),chr(243),chr(244),chr(245),chr(246),chr(247),chr(248),chr(249),chr(250),chr(251),chr(252),chr(253),chr(254),chr(255));
				//preklad s decimalnich kodu
				$_z = array('&#352;','&#346;','&#356;','&#381;','&#377;','&#353;','&#347;','&#357;','&#382;','&#378;','&#160;','&#711;','&#728;','&#321;','&#164;','&#260;','&#166;','&#167;','&#168;','&#169;','&#350;','&#171;','&#172;','&#173;','&#174;','&#379;','&#176;','&#177;','&#731;','&#322;','&#180;','&#181;','&#729;','&#184;','&#261;','&#351;','&#187;','&#317;','&#733;','&#318;','&#380;','&#340;','&#193;','&#194;','&#258;','&#196;','&#313;','&#262;','&#199;','&#268;','&#201;','&#280;','&#203;','&#282;','&#205;','&#206;','&#270;','&#208;','&#323;','&#327;','&#211;','&#212;','&#336;','&#214;','&#215;','&#344;','&#366;','&#218;','&#368;','&#220;','&#221;','&#354;','&#223;','&#341;','&#225;','&#226;','&#259;','&#228;','&#314;','&#263;','&#231;','&#269;','&#233;','&#281;','&#235;','&#283;','&#237;','&#238;','&#271;','&#240;','&#324;','&#328;','&#243;','&#244;','&#337;','&#246;','&#247;','&#345;','&#367;','&#250;','&#369;','&#252;','&#253;','&#355;','&#729;');
				$text = str_replace($_z,$_do,$text);
				//preklad z hexa kodu
				$_z = array('&#x160;','&#x15A;','&#x164;','&#x17D;','&#x179;','&#x161;','&#x15B;','&#x165;','&#x17E;','&#x17A;','&#xA0;','&#x2C7;','&#x2D8;','&#x141;','&#xA4;','&#x104;','&#xA6;','&#xA7;','&#xA8;','&#xA9;','&#x15E;','&#xAB;','&#xAC;','&#xAD;','&#xAE;','&#x17B;','&#xB0;','&#xB1;','&#x2DB;','&#x142;','&#xB4;','&#xB5;','&#x2D9;','&#xB8;','&#x105;','&#x15F;','&#xBB;','&#x13D;','&#x2DD;','&#x13E;','&#x17C;','&#x154;','&#xC1;','&#xC2;','&#x102;','&#xC4;','&#x139;','&#x106;','&#xC7;','&#x10C;','&#xC9;','&#x118;','&#xCB;','&#x11A;','&#xCD;','&#xCE;','&#x10E;','&#xD0;','&#x143;','&#x147;','&#xD3;','&#xD4;','&#x150;','&#xD6;','&#xD7;','&#x158;','&#x16E;','&#xDA;','&#x170;','&#xDC;','&#xDD;','&#x162;','&#xDF;','&#x155;','&#xE1;','&#xE2;','&#x103;','&#xE4;','&#x13A;','&#x107;','&#xE7;','&#x10D;','&#xE9;','&#x119;','&#xEB;','&#x11B;','&#xED;','&#xEE;','&#x10F;','&#xF0;','&#x144;','&#x148;','&#xF3;','&#xF4;','&#x151;','&#xF6;','&#xF7;','&#x159;','&#x16F;','&#xFA;','&#x171;','&#xFC;','&#xFD;','&#x163;','&#x2D9;');
				$text = str_replace($_z,$_do,$text);
				//preklad z html entit
				$_do = array(chr(32),chr(33),chr(34),chr(35),chr(36),chr(37),chr(38),chr(39),chr(40),chr(41),chr(42),chr(43),chr(44),chr(45),chr(46),chr(47),chr(58),chr(59),chr(60),chr(61),chr(62),chr(63),chr(64),chr(91),chr(92),chr(93),chr(94),chr(95),chr(96),chr(123),chr(124),chr(125),chr(126),chr(130),chr(132),chr(133),chr(134),chr(135),chr(137),chr(138),chr(139),chr(140),chr(141),chr(142),chr(143),chr(145),chr(146),chr(147),chr(148),chr(149),chr(150),chr(151),chr(153),chr(154),chr(155),chr(156),chr(157),chr(158),chr(159),chr(160),chr(161),chr(162),chr(163),chr(164),chr(165),chr(166),chr(167),chr(168),chr(169),chr(170),chr(171),chr(172),chr(173),chr(175),chr(174),chr(176),chr(177),chr(178),chr(179),chr(180),chr(181),chr(183),chr(184),chr(185),chr(186),chr(187),chr(188),chr(189),chr(190),chr(191),chr(192),chr(193),chr(194),chr(195),chr(196),chr(197),chr(198),chr(199),chr(200),chr(201),chr(202),chr(203),chr(204),chr(205),chr(206),chr(207),chr(208),chr(209),chr(210),chr(211),chr(212),chr(213),chr(214),chr(215),chr(216),chr(217),chr(218),chr(219),chr(220),chr(221),chr(222),chr(223),chr(224),chr(225),chr(226),chr(227),chr(228),chr(229),chr(230),chr(231),chr(232),chr(233),chr(234),chr(235),chr(236),chr(237),chr(238),chr(239),chr(240),chr(241),chr(242),chr(243),chr(244),chr(245),chr(246),chr(247),chr(248),chr(249),chr(250),chr(251),chr(252),chr(253),chr(254),chr(255));
				$_z = array('&space;','&exclam;','&quotedbl;','&numbersign;','&dollar;','&percent;','&ampersand;','&quoteright;','&parenleft;','&parenright;','&asterisk;','&plus;','&comma;','&minus;','&period;','&slash;','&colon;','&semicolon;','&less;','&equal;','&greater;','&question;','&at;','&bracketleft;','&backslash;','&bracketright;','&asciicircum;','&underscore;','&quoteleft;','&braceleft;','&bar;','&braceright;','&asciitilde;','&quotelowsingle;','&quotelowdouble;','&period3;','&dagger;','&doubledagger;','&permille;','&Scaron;','&anglequoteleftsingle;','&Sacute;','&Tcaron;','&Zcaron;','&Zacute;','&quoteleft;','&quoteright;','&quoteleftdouble;','&quotedbl;','&bullet;','&endash;','&emdash;','&trademark;','&scaron;','&anglequoterightsingle;','&sacute;','&tcaron;','&zcaron;','&zacute;','&nobreakspace;','&caron;','&breve;','&Lstroke;','&currency;','&Aogonek;','&brokenbar;','&section;','&diaeresis;','&copyright;','&Scedilla;','&guillemotleft;','&notsign;','&hyphen;','&Zabovedot;','&registered;','&degree;','&plusminus;','&ogonek;','&lstroke;','&acute;','&mu;','&abovedot;','&cedilla;','&aogonek;','&scedilla;','&guillemotright;','&Lcaron;','&doubleacute;','&lcaron;','&zabovedot;','&Racute;','&Aacute;','&Acircumflex;','&Abreve;','&Adiaeresis;','&Lacute;','&Cacute;','&Ccedilla;','&Ccaron;','&Eacute;','&Eogonek;','&Ediaeresis;','&Ecaron;','&Iacute;','&Icircumflex;','&Dcaron;','&Eth;','&Nacute;','&Ncaron;','&Oacute;','&Ocircumflex;','&Odoubleacute;','&Odiaeresis;','&multiply;','&Rcaron;','&Uring;','&Uacute;','&Udoubleacute;','&Udiaeresis;','&Yacute;','&Tcedilla;','&ssharp;','&racute;','&aacute;','&acircumflex;','&abreve;','&adiaeresis;','&lacute;','&cacute;','&ccedilla;','&ccaron;','&eacute;','&eogonek;','&ediaeresis;','&ecaron;','&iacute;','&icircumflex;','&dcaron;','&eth;','&nacute;','&ncaron;','&oacute;','&ocircumflex;','&odoubleacute;','&odiaeresis;','&division;','&rcaron;','&uring;','&uacute;','&udoubleacute;','&udiaeresis;','&yacute;','&tcedilla;','&abovedot;');
				$text = str_replace($_z,$_do,$text);
				return $text;
				break;
			case "852":
				$in = array(chr(128),chr(129),chr(130),chr(131),chr(132),chr(133),chr(134),chr(135),chr(136),chr(137),chr(138),chr(139),chr(140),chr(141),chr(142),chr(143),chr(144),chr(145),chr(146),chr(147),chr(148),chr(149),chr(150),chr(151),chr(152),chr(153),chr(154),chr(155),chr(156),chr(157),chr(158),chr(159),chr(160),chr(161),chr(162),chr(163),chr(164),chr(165),chr(166),chr(167),chr(168),chr(169),chr(170),chr(171),chr(172),chr(173),chr(174),chr(175),chr(181),chr(182),chr(183),chr(184),chr(189),chr(190),chr(198),chr(199),chr(207),chr(208),chr(209),chr(210),chr(211),chr(212),chr(213),chr(214),chr(215),chr(216),chr(221),chr(222),chr(224),chr(225),chr(226),chr(227),chr(228),chr(229),chr(230),chr(231),chr(232),chr(233),chr(234),chr(235),chr(236),chr(237),chr(238),chr(239),chr(240),chr(241),chr(242),chr(243),chr(244),chr(245),chr(246),chr(247),chr(248),chr(249),chr(250),chr(252),chr(253),chr(255));
				$out = array(chr(199),chr(252),chr(233),chr(226),chr(228),chr(249),chr(230),chr(231),chr(179),chr(235),chr(213),chr(245),chr(238),chr(143),chr(196),chr(198),chr(201),chr(197),chr(229),chr(244),chr(246),chr(188),chr(190),chr(140),chr(156),chr(214),chr(220),chr(141),chr(157),chr(163),chr(215),chr(232),chr(225),chr(237),chr(243),chr(250),chr(165),chr(185),chr(142),chr(158),chr(202),chr(234),chr(172),chr(159),chr(200),chr(186),chr(171),chr(187),chr(193),chr(194),chr(204),chr(170),chr(175),chr(191),chr(195),chr(227),chr(164),chr(240),chr(208),chr(207),chr(203),chr(239),chr(210),chr(205),chr(206),chr(236),chr(222),chr(217),chr(211),chr(223),chr(212),chr(209),chr(241),chr(242),chr(138),chr(154),chr(192),chr(218),chr(224),chr(219),chr(253),chr(221),chr(254),chr(180),chr(173),chr(189),chr(178),chr(161),chr(162),chr(167),chr(247),chr(184),chr(176),chr(168),chr(255),chr(216),chr(248),chr(160));
				$text = strtr($text,join("",$in),join("",$out));
			default:
				return $text;
		}		
	}

	/**
	 * @ignore
	 */
	static function _TO_852(&$text,$from_cp){
		switch($from_cp){
			case "utf8":
				$text = self::Trans($text,"utf8","windows-1250");
			case "windows-1250":
				$in = array(chr(199),chr(252),chr(233),chr(226),chr(228),chr(249),chr(230),chr(231),chr(179),chr(235),chr(213),chr(245),chr(238),chr(143),chr(196),chr(198),chr(201),chr(197),chr(229),chr(244),chr(246),chr(188),chr(190),chr(140),chr(156),chr(214),chr(220),chr(141),chr(157),chr(163),chr(215),chr(232),chr(225),chr(237),chr(243),chr(250),chr(165),chr(185),chr(142),chr(158),chr(202),chr(234),chr(172),chr(159),chr(200),chr(186),chr(171),chr(187),chr(193),chr(194),chr(204),chr(170),chr(175),chr(191),chr(195),chr(227),chr(164),chr(240),chr(208),chr(207),chr(203),chr(239),chr(210),chr(205),chr(206),chr(236),chr(222),chr(217),chr(211),chr(223),chr(212),chr(209),chr(241),chr(242),chr(138),chr(154),chr(192),chr(218),chr(224),chr(219),chr(253),chr(221),chr(254),chr(180),chr(173),chr(189),chr(178),chr(161),chr(162),chr(167),chr(247),chr(184),chr(176),chr(168),chr(255),chr(216),chr(248),chr(160));
				$out = array(chr(128),chr(129),chr(130),chr(131),chr(132),chr(133),chr(134),chr(135),chr(136),chr(137),chr(138),chr(139),chr(140),chr(141),chr(142),chr(143),chr(144),chr(145),chr(146),chr(147),chr(148),chr(149),chr(150),chr(151),chr(152),chr(153),chr(154),chr(155),chr(156),chr(157),chr(158),chr(159),chr(160),chr(161),chr(162),chr(163),chr(164),chr(165),chr(166),chr(167),chr(168),chr(169),chr(170),chr(171),chr(172),chr(173),chr(174),chr(175),chr(181),chr(182),chr(183),chr(184),chr(189),chr(190),chr(198),chr(199),chr(207),chr(208),chr(209),chr(210),chr(211),chr(212),chr(213),chr(214),chr(215),chr(216),chr(221),chr(222),chr(224),chr(225),chr(226),chr(227),chr(228),chr(229),chr(230),chr(231),chr(232),chr(233),chr(234),chr(235),chr(236),chr(237),chr(238),chr(239),chr(240),chr(241),chr(242),chr(243),chr(244),chr(245),chr(246),chr(247),chr(248),chr(249),chr(250),chr(252),chr(253),chr(255));
				$text = strtr($text,join("",$in),join("",$out));
				break;
			default:
		}
		return $text;
	}

	/**
	 * @ignore
	 */
	static function _TO_utf8(&$text,$from_cp){
		static $TR_TABLES = array();

		switch ($from_cp){
			case "iso-8859-2":
			case "iso-8859-1":
			case "windows-1250":
			case "windows-1252":
				if(!isset($TR_TABLES[$from_cp])){
					require(dirname(__FILE__)."/tr_tables/to_utf8/$from_cp.php");
				}
				return strtr($text,$TR_TABLES["$from_cp"]);
				break;
			default:
				return $text;
		}
	}

	/**
	 * @ignore
	 */
	static function _TO_ascii(&$text,$from_cp){
		static $TR_TABLES = array();

		switch ($from_cp){
			case "iso-8859-2":
			case "iso-8859-1":
			case "windows-1250":
			case "windows-1252":
				if(!isset($TR_TABLES[$from_cp])){
					require(dirname(__FILE__)."/tr_tables/to_ascii/$from_cp.php");
				}
				$text = strtr($text,$TR_TABLES["$from_cp"]);
				break;
			case "utf8":
				$text = self::_Transliteration($text);
				$text = self::_RemoveUtf8Chars($text);
				break;
		}

		return $text;	
	}

	/**
	 * Checks if input string is in given charset.
	 *
	 * Method can distinguish some characters (or even whole sequencies) that can not appear in the string.
	 * When they appear method returns false
	 *
	 * ```
	 * self::CheckEncoding($text, "utf-8", array(".",";","{","}","HUSAK"));
	 * ```
	 *
	 * @param string|array $text string or array of strings
	 * @param string $charset
	 * @param array $disallowed_char_sequencies		forbidden chars or strings
	 * @return bool
	 * - true -> text is in given charset
	 * - false -> text is not in given charset or contains a character or sequence from array $disallowed_char_sequencies
	 */
	static function CheckEncoding($text,$charset,$disallowed_char_sequencies = array()){
		if(is_array($text)){
			foreach($text as $_key => $_value){
				$_stat_key = self::CheckEncoding($_key,$charset,$disallowed_char_sequencies);
				$_stat_value = self::CheckEncoding($_value,$charset,$disallowed_char_sequencies);
				if(!$_stat_key || !$_stat_value){
					return false;
				}
			}
			return true;
		}

		settype($text,"string");
		settype($charset,"string");
		settype($disallowed_char_sequencies,"array");
		$charset = self::_GetCharsetByName($charset);
		$out = true;
		switch($charset){
			case "utf8":
				$out = self::_CheckEncodingUtf8($text);
				break;
			case "ascii":
				$out = self::_CheckEncodingAscii($text);
				break;
		}

		if($out && sizeof($disallowed_char_sequencies)>0){
			for($i=0;$i<sizeof($disallowed_char_sequencies);$i++){
				if(is_int(strpos($text,$disallowed_char_sequencies[$i]))){
					$out = false;
					break;
				}
			}
		}

		return $out;
	}

	/**
	 * @ignore
	 */
	static function _CheckEncodingUtf8(&$text){

		$_11111110 = bindec("11111110");
		$_11111100 = bindec("11111100");
		$_11111000 = bindec("11111000");
		$_11110000 = bindec("11110000");
		$_11100000 = bindec("11100000");
		$_11000000 = bindec("11000000");
		$_10000000 = bindec("10000000");
		
		$_len = strlen($text);

		$utf8_counter = 0;
		for($i=0;$i<$_len;$i++){
			$code = ord($text[$i]);
			settype($code,"integer");
			
			if(($code&$_10000000) == 0){
				if($utf8_counter!=0){
					return false;
				}
				continue;
			}
			if($utf8_counter==0){

				if(($code&$_11111110)==$_11111100){
					$utf8_counter = 6;
				}elseif(($code&$_11111100)==$_11111000){
					$utf8_counter = 5;
				}elseif(($code&$_11111000)==$_11110000){
					$utf8_counter = 4;
				}elseif(($code&$_11110000)==$_11100000){
					$utf8_counter = 3;
				}elseif(($code&$_11100000)==$_11000000){
					$utf8_counter = 2;
				}else{
					return false;
				}
				//tento znak
				$utf8_counter--;
				continue;
			}
			if($utf8_counter>0){
				if((($code&$_11000000)==$_10000000) || (($code&$_11000000)==$_11000000)){
					$utf8_counter--;
				}else{
					return false;
				}
				continue;
			}
			return false;
		}
		if($utf8_counter>0){
			return false;
		}
		return true;
	}

	/**
	 * @ignore
	 */
	static function _CheckEncodingAscii(&$text){
		$_len = strlen($text);

		for($i=0;$i<$_len;$i++){
			$code = ord($text[$i]);
			settype($code,"integer");
			if($code>=128){
				return false;
			}
		}

		return true;
	}

	/**
	 * Counts length of a string.
	 *
	 * @param string $text
	 * @param string $charset
	 * @return integer
	 */
	static function Length(&$text,$charset){
		$charset = self::_GetCharsetByName($charset);
		switch($charset){
			case "utf8":
				return self::_LengthUtf8($text);
			default:
				return strlen($text);
		}
	}

	/**
	 * @ignore
	 */
	static function _LengthUtf8(&$str){
		$i = 0;
		$count = 0;
		$len = strlen($str);
		while ($i < $len) {
			$chr = ord ($str[$i]);
			$count++;
			$i++;
			if ($i >= $len){ break; }

			if ($chr & 0x80){
				$chr <<= 1;
				while ($chr & 0x80) {
					$i++;
					$chr <<= 1;
				}
			}
		}
		return $count;
	}
}
