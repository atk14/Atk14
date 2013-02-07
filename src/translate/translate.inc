<?php
/**
 * Class for converting strings between charsets.
 *
 * @package Atk14
 * @subpackage Translate
 * @filesource
 */

/**
 * This class converts string between charsets.
 *
 * It's important to search the code to discover the charset support.
 *
 * The class uses phps' iconv extension.
 * When it is not installed, translate class uses its own translation methods for some charsets.
 *
 * Usage:
 * <code>
 * $output_text = translate::trans($input_text,$from_charset,$to_charset);
 * </code>
 * When it is impossible to convert the string the trans() method returns the input string. 
 *
 * The translate class is able to change the string to lowercase or uppercase
 * Example:
 * <code>
 * $output_text = translate::lower($input_text,"windows-1250");
 * $output_text = translate::upper($input_text,"windows-1250");
 * </code>
 *
 * Checking that string is in selected charset.
 * <code>
 * $output_text = translate::check_encoding($input_text,"utf-8");
 * </code>
 *
 * Checking length of a UTF-8 string:
 * <code>
 * $length = translate::length($str,"UTF-8");
 * </code>
 *
 * @package Atk14
 * @subpackage Translate
 * @filesource
 */

/*
*	updaty
*	3.12.2003 - pridana funkce _TO_windows_1250
*	4.12.2003 - pridana funkce _TO_ascii pro osmibitove kodovani
* 15.12.2003 - pridana funkce _check_encoding_ascii a _check_encoding_utf8
* 13.3.2006 - opravena chyba pri translate::lower($neco,"windows-1250") a translate::upper($neco,"windows-1250")
* 13.3.2006 - do metod lower a upper pridano kodovani iso-8859-2
* 16.6.2006 - konvert z windows-1250 do 852 a naopak
* 26.9.2007 - konvert z utf-8 do ascii (jen ceske znaky)
* 22.10.2007 - parametrem pro prekodovani ted muze byt i pole
* 29.11.2007 - doplneno urcovani delky retyezce
* 
*/

class translate{

	/**
	 * Converts string from a charset to another.
	 *
	 * Parameter can be an array of string and the method also returns array with all converted strings.
	 *
	 * Volitelne parametry v poli $options:
	 *		"recode_array_keys" ---> pokud je $text pole, budou prekodovany i klice tohoto pole
	 * 
	 * @param string|array $text
	 * @param string $from_charset input charset
	 * @param string $to_charset output charset
	 * @param array $options
	 * - recode_array_keys usable only when $text is array; then also keys will be converted
	 * @return string|array
	 */
	static function trans($text,$from_charset,$to_charset,$options = array()){
		$from_charset = translate::_get_charset_by_name($from_charset);
		$to_charset = translate::_get_charset_by_name($to_charset);
		if($from_charset==$to_charset){
			return $text;
		}
		return translate::_trans($text,$from_charset,$to_charset,$options);
	}

	/**
	 * @ignore
	 */
	static function _trans($text,$from_charset,$to_charset,$options = array()){
		if(is_array($text)){
			$out = array();
			foreach($text as $key => $value){
				$_key = $key;
				if(isset($options["recode_array_keys"]) && $options["recode_array_keys"]){
					$_key = translate::_trans($key,$from_charset,$to_charset);
				}
				$out[$_key] = (is_string($text[$key]) || is_array($text[$key])) ? translate::_trans($text[$key],$from_charset,$to_charset) : $text[$key];
			}
			return $out;
		}

		if($from_charset=="utf8" && $to_charset!="utf8"){
			$text = translate::_remove_utf8_headaches($text);
		}

    if(function_exists('iconv')){
       $success=true;
       $out=@iconv($from_charset, $to_charset.'//TRANSLIT', $text) or $success=false;
       if($out!==false && $success)
          return $out;
       }
    
    switch($to_charset){
			case "iso-8859-2":
				return translate::_TO_iso_8859_2($text,$from_charset);
				break;
			case "utf8":
				return translate::_TO_utf8($text,$from_charset);
				break;
			case "windows-1250":
			  return translate::_TO_windows_1250($text,$from_charset);
				break;
			case "852":
				return translate::_TO_852($text,$from_charset);
				break;			
			case "ascii":
				return translate::_TO_ascii($text,$from_charset);
				break;
			case "HTML entities":
				return translate::_TO_HTML_entitites($text,$from_charset);
				break;
			default: 
				return $text;
				break;
		}
		return $text;
	}

	/**
	 * Removes special chars which don't have substitution in 8bit charsets.
	 *
	 * @ignore
	 */
	static function _remove_utf8_headaches($text){
		return strtr($text,array(
			chr(0xE2).chr(0x80).chr(0x93) => "-",
		));
	}

	/**
	 * Converts string to lowercase.
	 *
	 * @param string $text
	 * @param string $charset
	 * @return string
	 */
	static function lower($text,$charset){
		$charset = translate::_get_charset_by_name($charset);
		switch($charset){
			case "windows-1250":
				$mala = chr(97).chr(98).chr(99).chr(100).chr(101).chr(102).chr(103).chr(104).chr(105).chr(106).chr(107).chr(108).chr(109).chr(110).chr(111).chr(112).chr(113).chr(114).chr(115).chr(116).chr(117).chr(118).chr(119).chr(120).chr(121).chr(122).chr(154).chr(156).chr(157).chr(158).chr(159).chr(179).chr(185).chr(186).chr(190).chr(191).chr(224).chr(225).chr(226).chr(227).chr(228).chr(229).chr(230).chr(231).chr(232).chr(233).chr(234).chr(235).chr(236).chr(237).chr(238).chr(239).chr(240).chr(241).chr(242).chr(243).chr(244).chr(245).chr(246).chr(248).chr(249).chr(250).chr(251).chr(252).chr(253).chr(254);
				$velka = chr(65).chr(66).chr(67).chr(68).chr(69).chr(70).chr(71).chr(72).chr(73).chr(74).chr(75).chr(76).chr(77).chr(78).chr(79).chr(80).chr(81).chr(82).chr(83).chr(84).chr(85).chr(86).chr(87).chr(88).chr(89).chr(90).chr(138).chr(140).chr(141).chr(142).chr(143).chr(163).chr(165).chr(170).chr(188).chr(175).chr(192).chr(193).chr(194).chr(195).chr(196).chr(197).chr(198).chr(199).chr(200).chr(201).chr(202).chr(203).chr(204).chr(205).chr(206).chr(207).chr(208).chr(209).chr(210).chr(211).chr(212).chr(213).chr(214).chr(216).chr(217).chr(218).chr(219).chr(220).chr(221).chr(222);
				return strtr($text,$velka,$mala);
				break;
			case "iso-8859-2":
				$mala = chr(97).chr(98).chr(99).chr(100).chr(101).chr(102).chr(103).chr(104).chr(105).chr(106).chr(107).chr(108).chr(109).chr(110).chr(111).chr(112).chr(113).chr(114).chr(115).chr(116).chr(117).chr(118).chr(119).chr(120).chr(121).chr(122).chr(177).chr(179).chr(181).chr(182).chr(185).chr(186).chr(187).chr(188).chr(190).chr(191).chr(224).chr(225).chr(226).chr(227).chr(228).chr(229).chr(230).chr(231).chr(232).chr(233).chr(234).chr(235).chr(236).chr(237).chr(238).chr(239).chr(240).chr(241).chr(242).chr(243).chr(244).chr(245).chr(246).chr(248).chr(249).chr(250).chr(251).chr(252).chr(253).chr(254);
				$velka = chr(65).chr(66).chr(67).chr(68).chr(69).chr(70).chr(71).chr(72).chr(73).chr(74).chr(75).chr(76).chr(77).chr(78).chr(79).chr(80).chr(81).chr(82).chr(83).chr(84).chr(85).chr(86).chr(87).chr(88).chr(89).chr(90).chr(161).chr(163).chr(165).chr(166).chr(169).chr(170).chr(171).chr(172).chr(174).chr(175).chr(192).chr(193).chr(194).chr(195).chr(196).chr(197).chr(198).chr(199).chr(200).chr(201).chr(202).chr(203).chr(204).chr(205).chr(206).chr(207).chr(208).chr(209).chr(210).chr(211).chr(212).chr(213).chr(214).chr(216).chr(217).chr(218).chr(219).chr(220).chr(221).chr(222);
				return strtr($text,$velka,$mala);
				break;
			case "ascii":
			default: 
				return strtolower($text);
				break;
		}
	}

	/**
	 * Converts string to uppercase.
	 *
	 * @param string $text
	 * @param string $charset
	 * @return string
	 */
	static function upper($text,$charset){
		$charset = translate::_get_charset_by_name($charset);
		switch($charset){
			case "windows-1250":
				$mala = chr(97).chr(98).chr(99).chr(100).chr(101).chr(102).chr(103).chr(104).chr(105).chr(106).chr(107).chr(108).chr(109).chr(110).chr(111).chr(112).chr(113).chr(114).chr(115).chr(116).chr(117).chr(118).chr(119).chr(120).chr(121).chr(122).chr(154).chr(156).chr(157).chr(158).chr(159).chr(179).chr(185).chr(186).chr(190).chr(191).chr(224).chr(225).chr(226).chr(227).chr(228).chr(229).chr(230).chr(231).chr(232).chr(233).chr(234).chr(235).chr(236).chr(237).chr(238).chr(239).chr(240).chr(241).chr(242).chr(243).chr(244).chr(245).chr(246).chr(248).chr(249).chr(250).chr(251).chr(252).chr(253).chr(254);
				$velka = chr(65).chr(66).chr(67).chr(68).chr(69).chr(70).chr(71).chr(72).chr(73).chr(74).chr(75).chr(76).chr(77).chr(78).chr(79).chr(80).chr(81).chr(82).chr(83).chr(84).chr(85).chr(86).chr(87).chr(88).chr(89).chr(90).chr(138).chr(140).chr(141).chr(142).chr(143).chr(163).chr(165).chr(170).chr(188).chr(175).chr(192).chr(193).chr(194).chr(195).chr(196).chr(197).chr(198).chr(199).chr(200).chr(201).chr(202).chr(203).chr(204).chr(205).chr(206).chr(207).chr(208).chr(209).chr(210).chr(211).chr(212).chr(213).chr(214).chr(216).chr(217).chr(218).chr(219).chr(220).chr(221).chr(222);
				return strtr($text,$mala,$velka);
				break;
			case "iso-8859-2":
				$mala = chr(97).chr(98).chr(99).chr(100).chr(101).chr(102).chr(103).chr(104).chr(105).chr(106).chr(107).chr(108).chr(109).chr(110).chr(111).chr(112).chr(113).chr(114).chr(115).chr(116).chr(117).chr(118).chr(119).chr(120).chr(121).chr(122).chr(177).chr(179).chr(181).chr(182).chr(185).chr(186).chr(187).chr(188).chr(190).chr(191).chr(224).chr(225).chr(226).chr(227).chr(228).chr(229).chr(230).chr(231).chr(232).chr(233).chr(234).chr(235).chr(236).chr(237).chr(238).chr(239).chr(240).chr(241).chr(242).chr(243).chr(244).chr(245).chr(246).chr(248).chr(249).chr(250).chr(251).chr(252).chr(253).chr(254);
				$velka = chr(65).chr(66).chr(67).chr(68).chr(69).chr(70).chr(71).chr(72).chr(73).chr(74).chr(75).chr(76).chr(77).chr(78).chr(79).chr(80).chr(81).chr(82).chr(83).chr(84).chr(85).chr(86).chr(87).chr(88).chr(89).chr(90).chr(161).chr(163).chr(165).chr(166).chr(169).chr(170).chr(171).chr(172).chr(174).chr(175).chr(192).chr(193).chr(194).chr(195).chr(196).chr(197).chr(198).chr(199).chr(200).chr(201).chr(202).chr(203).chr(204).chr(205).chr(206).chr(207).chr(208).chr(209).chr(210).chr(211).chr(212).chr(213).chr(214).chr(216).chr(217).chr(218).chr(219).chr(220).chr(221).chr(222);
				return strtr($text,$mala,$velka);
				break;
			case "ascii":
			default: 
				return strtoupper($text);
				break;
		}
	}

	/**
	 *
	 * @ignore
	 * @param string $charset unified chrset name
	 * @return string
	 */
	function _get_charset_by_name($charset){
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
		switch($from_cp){
			case "windows-1250":
				return strtr($text, array(chr(130)=>chr(39),chr(132)=>chr(34),chr(133)=>chr(46).chr(46).chr(46),chr(134)=>chr(124),chr(135)=>chr(124),chr(137)=>chr(111).chr(47).chr(111).chr(111),chr(138)=>chr(169),chr(139)=>chr(60),chr(140)=>chr(166),chr(141)=>chr(171),chr(142)=>chr(174),chr(143)=>chr(172),chr(145)=>chr(96),chr(146)=>chr(39),chr(147)=>chr(34),chr(148)=>chr(34),chr(149)=>chr(42),chr(150)=>chr(45).chr(45),chr(151)=>chr(45).chr(45).chr(45),chr(153)=>chr(40).chr(84).chr(77).chr(41),chr(154)=>chr(185),chr(155)=>chr(62),chr(156)=>chr(182),chr(157)=>chr(187),chr(158)=>chr(190),chr(159)=>chr(188),chr(161)=>chr(183),chr(165)=>chr(161),chr(166)=>chr(124),chr(169)=>chr(40).chr(99).chr(41),chr(171)=>chr(62).chr(62),chr(172)=>chr(110).chr(111).chr(116),chr(174)=>chr(40).chr(114).chr(41),chr(177)=>chr(43).chr(47).chr(45),chr(181)=>chr(117),chr(183)=>chr(255),chr(185)=>chr(177),chr(187)=>chr(60).chr(60),chr(188)=>chr(165),chr(190)=>chr(181)));
				break;
			case "windows-1252":
				return strtr($text, array(chr(130)=>chr(39),chr(132)=>chr(34),chr(133)=>chr(46).chr(46).chr(46),chr(134)=>chr(124),chr(135)=>chr(124),chr(137)=>chr(111).chr(47).chr(111).chr(111),chr(138)=>chr(169),chr(139)=>chr(60),chr(140)=>chr(79).chr(69),chr(145)=>chr(96),chr(146)=>chr(39),chr(147)=>chr(34),chr(148)=>chr(34),chr(149)=>chr(42),chr(150)=>chr(45).chr(45),chr(151)=>chr(45).chr(45).chr(45),chr(152)=>chr(189),chr(153)=>chr(40).chr(84).chr(77).chr(41),chr(154)=>chr(185),chr(155)=>chr(62),chr(156)=>chr(111).chr(101),chr(159)=>chr(89),chr(161)=>chr(33),chr(162)=>chr(99),chr(163)=>chr(76),chr(165)=>chr(121),chr(166)=>chr(124),chr(169)=>chr(40).chr(99).chr(41),chr(171)=>chr(62).chr(62),chr(172)=>chr(110).chr(111).chr(116),chr(174)=>chr(40).chr(114).chr(41),chr(177)=>chr(43).chr(47).chr(45),chr(181)=>chr(117),chr(183)=>chr(255),chr(187)=>chr(60).chr(60),chr(188)=>chr(49).chr(47).chr(52),chr(189)=>chr(49).chr(47).chr(50),chr(190)=>chr(51).chr(47).chr(52),chr(191)=>chr(63),chr(192)=>chr(65),chr(195)=>chr(65),chr(197)=>chr(65),chr(198)=>chr(65).chr(69),chr(200)=>chr(69),chr(202)=>chr(69),chr(204)=>chr(73),chr(207)=>chr(73),chr(209)=>chr(78),chr(210)=>chr(79),chr(213)=>chr(79),chr(216)=>chr(79),chr(217)=>chr(85),chr(219)=>chr(85),chr(224)=>chr(97),chr(227)=>chr(97),chr(229)=>chr(97),chr(230)=>chr(97).chr(101),chr(232)=>chr(101),chr(234)=>chr(101),chr(236)=>chr(105),chr(239)=>chr(105),chr(241)=>chr(110),chr(242)=>chr(111),chr(245)=>chr(111),chr(248)=>chr(111),chr(249)=>chr(117),chr(251)=>chr(117),chr(255)=>chr(121)));	
				break;
			case "iso-8859-1":
				return strtr($text, array(chr(161)=>chr(33),chr(162)=>chr(99),chr(163)=>chr(76),chr(165)=>chr(121),chr(166)=>chr(124),chr(169)=>chr(40).chr(99).chr(41),chr(171)=>chr(62).chr(62),chr(172)=>chr(110).chr(111).chr(116),chr(174)=>chr(40).chr(114).chr(41),chr(177)=>chr(43).chr(47).chr(45),chr(181)=>chr(117),chr(183)=>chr(46),chr(187)=>chr(60).chr(60),chr(188)=>chr(49).chr(47).chr(52),chr(189)=>chr(49).chr(47).chr(50),chr(190)=>chr(51).chr(47).chr(52),chr(191)=>chr(63),chr(192)=>chr(65),chr(195)=>chr(65),chr(197)=>chr(65),chr(198)=>chr(65).chr(69),chr(200)=>chr(69),chr(202)=>chr(69),chr(204)=>chr(73),chr(207)=>chr(73),chr(209)=>chr(78),chr(210)=>chr(79),chr(213)=>chr(79),chr(216)=>chr(79),chr(217)=>chr(85),chr(219)=>chr(85),chr(224)=>chr(97),chr(227)=>chr(97),chr(229)=>chr(97),chr(230)=>chr(97).chr(101),chr(232)=>chr(101),chr(234)=>chr(101),chr(236)=>chr(105),chr(239)=>chr(105),chr(241)=>chr(110),chr(242)=>chr(111),chr(245)=>chr(111),chr(248)=>chr(111),chr(249)=>chr(117),chr(251)=>chr(117),chr(255)=>chr(121)));
				break;
			case "kam":
				return strtr($text, array(chr(128)=>chr(200),chr(129)=>chr(252),chr(130)=>chr(233),chr(131)=>chr(239),chr(132)=>chr(228),chr(133)=>chr(207),chr(134)=>chr(171),chr(135)=>chr(232),chr(136)=>chr(236),chr(137)=>chr(204),chr(138)=>chr(197),chr(139)=>chr(205),chr(140)=>chr(181),chr(141)=>chr(229),chr(142)=>chr(196),chr(143)=>chr(193),chr(144)=>chr(201),chr(145)=>chr(190),chr(146)=>chr(174),chr(147)=>chr(244),chr(148)=>chr(246),chr(149)=>chr(211),chr(150)=>chr(249),chr(151)=>chr(218),chr(152)=>chr(253),chr(153)=>chr(214),chr(154)=>chr(220),chr(155)=>chr(169),chr(156)=>chr(165),chr(157)=>chr(221),chr(158)=>chr(216),chr(159)=>chr(187),chr(160)=>chr(225),chr(161)=>chr(237),chr(162)=>chr(243),chr(163)=>chr(250),chr(164)=>chr(242),chr(165)=>chr(210),chr(166)=>chr(217),chr(167)=>chr(212),chr(168)=>chr(185),chr(169)=>chr(248),chr(170)=>chr(224),chr(171)=>chr(192),chr(172)=>chr(49).chr(47).chr(52),chr(173)=>chr(167),chr(174)=>chr(60).chr(60),chr(175)=>chr(62).chr(62),chr(230)=>chr(117),chr(241)=>chr(43).chr(47).chr(45),chr(242)=>chr(62).chr(61),chr(243)=>chr(60).chr(61),chr(246)=>chr(247),chr(248)=>chr(176),chr(250)=>chr(46)));
				break;
			case "koi8":
				return strtr($text, array(chr(193)=>chr(225),chr(195)=>chr(232),chr(196)=>chr(239),chr(197)=>chr(236),chr(198)=>chr(224),chr(199)=>chr(99),chr(200)=>chr(252),chr(201)=>chr(237),chr(202)=>chr(249),chr(203)=>chr(229),chr(204)=>chr(181),chr(205)=>chr(246),chr(206)=>chr(242),chr(207)=>chr(243),chr(208)=>chr(244),chr(209)=>chr(228),chr(210)=>chr(248),chr(211)=>chr(185),chr(212)=>chr(187),chr(213)=>chr(250),chr(215)=>chr(233),chr(216)=>chr(97),chr(217)=>chr(253),chr(218)=>chr(190),chr(220)=>chr(183),chr(224)=>chr(180),chr(225)=>chr(193),chr(227)=>chr(200),chr(228)=>chr(207),chr(229)=>chr(204),chr(230)=>chr(192),chr(231)=>chr(67).chr(72),chr(232)=>chr(220),chr(233)=>chr(205),chr(234)=>chr(217),chr(235)=>chr(197),chr(236)=>chr(165),chr(237)=>chr(214),chr(238)=>chr(210),chr(239)=>chr(211),chr(240)=>chr(212),chr(241)=>chr(196),chr(242)=>chr(216),chr(243)=>chr(169),chr(244)=>chr(171),chr(245)=>chr(218),chr(247)=>chr(201),chr(248)=>chr(195),chr(249)=>chr(221),chr(250)=>chr(174),chr(254)=>chr(176)));
				break;
			case "mac":
				return strtr($text, array(chr(128)=>chr(196),chr(129)=>chr(65),chr(130)=>chr(199),chr(131)=>chr(201),chr(132)=>chr(78),chr(133)=>chr(214),chr(134)=>chr(220),chr(135)=>chr(225),chr(136)=>chr(97),chr(137)=>chr(226),chr(138)=>chr(228),chr(139)=>chr(97),chr(140)=>chr(97),chr(141)=>chr(231),chr(142)=>chr(233),chr(143)=>chr(101),chr(144)=>chr(101),chr(145)=>chr(235),chr(146)=>chr(237),chr(147)=>chr(105),chr(148)=>chr(238),chr(149)=>chr(105),chr(150)=>chr(110),chr(151)=>chr(243),chr(152)=>chr(111),chr(153)=>chr(244),chr(154)=>chr(246),chr(155)=>chr(111),chr(156)=>chr(250),chr(157)=>chr(117),chr(158)=>chr(117),chr(159)=>chr(252),chr(160)=>chr(124),chr(161)=>chr(176),chr(162)=>chr(99),chr(164)=>chr(167),chr(165)=>chr(42),chr(167)=>chr(223),chr(168)=>chr(40).chr(114).chr(41),chr(169)=>chr(40).chr(99).chr(41),chr(170)=>chr(40).chr(84).chr(77).chr(41),chr(171)=>chr(180),chr(172)=>chr(168),chr(173)=>chr(33).chr(61),chr(174)=>chr(65).chr(69),chr(175)=>chr(79),chr(177)=>chr(43).chr(47).chr(45),chr(178)=>chr(60).chr(61),chr(179)=>chr(62).chr(61),chr(180)=>chr(121),chr(181)=>chr(117),chr(182)=>chr(100),chr(190)=>chr(97).chr(101),chr(192)=>chr(63),chr(193)=>chr(33),chr(194)=>chr(110).chr(111).chr(116),chr(201)=>chr(46).chr(46).chr(46),chr(202)=>chr(160),chr(203)=>chr(65),chr(204)=>chr(65),chr(205)=>chr(79),chr(206)=>chr(79).chr(69),chr(207)=>chr(111).chr(101),chr(208)=>chr(45).chr(45).chr(45),chr(209)=>chr(45).chr(45),chr(210)=>chr(34),chr(211)=>chr(34),chr(212)=>chr(96),chr(213)=>chr(39),chr(214)=>chr(247),chr(216)=>chr(121),chr(217)=>chr(89),chr(218)=>chr(47),chr(219)=>chr(164),chr(220)=>chr(60),chr(221)=>chr(62),chr(222)=>chr(102).chr(105),chr(223)=>chr(102).chr(108),chr(224)=>chr(124),chr(225)=>chr(46),chr(226)=>chr(39),chr(227)=>chr(34),chr(228)=>chr(111).chr(47).chr(111).chr(111),chr(229)=>chr(194),chr(230)=>chr(69),chr(231)=>chr(193),chr(232)=>chr(203),chr(233)=>chr(69),chr(234)=>chr(205),chr(235)=>chr(206),chr(236)=>chr(73),chr(237)=>chr(73),chr(238)=>chr(211),chr(239)=>chr(212),chr(241)=>chr(79),chr(242)=>chr(218),chr(243)=>chr(85),chr(244)=>chr(85),chr(245)=>chr(105),chr(247)=>chr(126),chr(249)=>chr(162),chr(250)=>chr(255),chr(252)=>chr(184),chr(253)=>chr(189),chr(254)=>chr(178),chr(255)=>chr(183)));
				break;
			case "macce":
				return strtr($text, array(chr(128)=>chr(196),chr(129)=>chr(65),chr(130)=>chr(97),chr(131)=>chr(201),chr(132)=>chr(161),chr(133)=>chr(214),chr(134)=>chr(220),chr(135)=>chr(225),chr(136)=>chr(177),chr(137)=>chr(200),chr(138)=>chr(228),chr(139)=>chr(232),chr(140)=>chr(198),chr(141)=>chr(230),chr(142)=>chr(233),chr(143)=>chr(172),chr(144)=>chr(188),chr(145)=>chr(207),chr(146)=>chr(237),chr(147)=>chr(239),chr(148)=>chr(69),chr(149)=>chr(101),chr(150)=>chr(69),chr(151)=>chr(243),chr(152)=>chr(101),chr(153)=>chr(244),chr(154)=>chr(246),chr(155)=>chr(111),chr(156)=>chr(250),chr(157)=>chr(204),chr(158)=>chr(236),chr(159)=>chr(252),chr(160)=>chr(124),chr(161)=>chr(176),chr(162)=>chr(202),chr(164)=>chr(167),chr(165)=>chr(42),chr(167)=>chr(223),chr(168)=>chr(40).chr(114).chr(41),chr(169)=>chr(40).chr(99).chr(41),chr(170)=>chr(40).chr(84).chr(77).chr(41),chr(171)=>chr(234),chr(172)=>chr(168),chr(173)=>chr(33).chr(61),chr(174)=>chr(103),chr(175)=>chr(73),chr(176)=>chr(105),chr(177)=>chr(73),chr(178)=>chr(60).chr(61),chr(179)=>chr(62).chr(61),chr(180)=>chr(105),chr(181)=>chr(75),chr(182)=>chr(100),chr(184)=>chr(179),chr(185)=>chr(76),chr(186)=>chr(108),chr(187)=>chr(165),chr(188)=>chr(181),chr(189)=>chr(197),chr(190)=>chr(229),chr(191)=>chr(78),chr(192)=>chr(110),chr(193)=>chr(209),chr(194)=>chr(110).chr(111).chr(116),chr(196)=>chr(241),chr(197)=>chr(210),chr(201)=>chr(46).chr(46).chr(46),chr(202)=>chr(160),chr(203)=>chr(242),chr(204)=>chr(213),chr(205)=>chr(79),chr(206)=>chr(245),chr(207)=>chr(79),chr(208)=>chr(45).chr(45).chr(45),chr(209)=>chr(45).chr(45),chr(210)=>chr(34),chr(211)=>chr(34),chr(212)=>chr(96),chr(213)=>chr(39),chr(214)=>chr(247),chr(216)=>chr(111),chr(217)=>chr(192),chr(218)=>chr(224),chr(219)=>chr(216),chr(220)=>chr(60),chr(221)=>chr(62),chr(222)=>chr(248),chr(223)=>chr(82),chr(224)=>chr(114),chr(225)=>chr(169),chr(226)=>chr(39),chr(227)=>chr(34),chr(228)=>chr(185),chr(229)=>chr(166),chr(230)=>chr(182),chr(231)=>chr(193),chr(232)=>chr(171),chr(233)=>chr(187),chr(234)=>chr(205),chr(235)=>chr(174),chr(236)=>chr(190),chr(237)=>chr(85),chr(238)=>chr(211),chr(239)=>chr(212),chr(240)=>chr(117),chr(241)=>chr(217),chr(242)=>chr(218),chr(243)=>chr(249),chr(244)=>chr(219),chr(245)=>chr(251),chr(246)=>chr(85),chr(247)=>chr(117),chr(248)=>chr(221),chr(249)=>chr(253),chr(250)=>chr(107),chr(251)=>chr(175),chr(252)=>chr(163),chr(253)=>chr(191),chr(254)=>chr(71),chr(255)=>chr(183)));
				break;
			case "pc2":
				return strtr($text, array(chr(128)=>chr(199),chr(129)=>chr(252),chr(130)=>chr(233),chr(131)=>chr(226),chr(132)=>chr(228),chr(133)=>chr(249),chr(134)=>chr(230),chr(135)=>chr(231),chr(136)=>chr(179),chr(137)=>chr(235),chr(138)=>chr(213),chr(139)=>chr(245),chr(140)=>chr(238),chr(141)=>chr(172),chr(142)=>chr(196),chr(143)=>chr(198),chr(144)=>chr(201),chr(145)=>chr(197),chr(146)=>chr(229),chr(147)=>chr(244),chr(148)=>chr(246),chr(149)=>chr(165),chr(150)=>chr(181),chr(151)=>chr(166),chr(152)=>chr(182),chr(153)=>chr(214),chr(154)=>chr(220),chr(155)=>chr(171),chr(156)=>chr(187),chr(157)=>chr(163),chr(158)=>chr(215),chr(159)=>chr(232),chr(160)=>chr(225),chr(161)=>chr(237),chr(162)=>chr(243),chr(163)=>chr(250),chr(164)=>chr(161),chr(165)=>chr(177),chr(166)=>chr(174),chr(167)=>chr(190),chr(168)=>chr(202),chr(169)=>chr(234),chr(171)=>chr(188),chr(172)=>chr(200),chr(173)=>chr(186),chr(174)=>chr(60).chr(60),chr(175)=>chr(62).chr(62),chr(181)=>chr(193),chr(182)=>chr(194),chr(183)=>chr(204),chr(184)=>chr(170),chr(189)=>chr(175),chr(190)=>chr(191),chr(198)=>chr(195),chr(199)=>chr(227),chr(207)=>chr(164),chr(208)=>chr(240),chr(209)=>chr(208),chr(210)=>chr(207),chr(211)=>chr(203),chr(212)=>chr(239),chr(213)=>chr(210),chr(214)=>chr(205),chr(215)=>chr(206),chr(216)=>chr(236),chr(221)=>chr(222),chr(222)=>chr(217),chr(224)=>chr(211),chr(225)=>chr(223),chr(226)=>chr(212),chr(227)=>chr(209),chr(228)=>chr(241),chr(229)=>chr(242),chr(230)=>chr(169),chr(231)=>chr(185),chr(232)=>chr(192),chr(233)=>chr(218),chr(234)=>chr(224),chr(235)=>chr(219),chr(236)=>chr(253),chr(237)=>chr(221),chr(238)=>chr(254),chr(239)=>chr(180),chr(241)=>chr(189),chr(242)=>chr(178),chr(243)=>chr(183),chr(244)=>chr(162),chr(245)=>chr(167),chr(246)=>chr(247),chr(247)=>chr(184),chr(249)=>chr(168),chr(250)=>chr(255),chr(252)=>chr(216),chr(253)=>chr(248)));
				break;
			case "pc2a":
				return strtr($text, array(chr(128)=>chr(199),chr(129)=>chr(252),chr(130)=>chr(233),chr(131)=>chr(226),chr(132)=>chr(228),chr(133)=>chr(249),chr(134)=>chr(230),chr(135)=>chr(231),chr(136)=>chr(179),chr(137)=>chr(235),chr(138)=>chr(213),chr(139)=>chr(245),chr(140)=>chr(238),chr(141)=>chr(172),chr(142)=>chr(196),chr(143)=>chr(198),chr(144)=>chr(201),chr(145)=>chr(197),chr(146)=>chr(229),chr(147)=>chr(244),chr(148)=>chr(246),chr(149)=>chr(165),chr(150)=>chr(181),chr(151)=>chr(166),chr(152)=>chr(182),chr(153)=>chr(214),chr(154)=>chr(220),chr(155)=>chr(171),chr(156)=>chr(187),chr(157)=>chr(163),chr(158)=>chr(215),chr(159)=>chr(232),chr(160)=>chr(225),chr(161)=>chr(237),chr(162)=>chr(243),chr(163)=>chr(250),chr(164)=>chr(161),chr(165)=>chr(177),chr(166)=>chr(174),chr(167)=>chr(190),chr(168)=>chr(202),chr(169)=>chr(234),chr(171)=>chr(188),chr(172)=>chr(200),chr(173)=>chr(186),chr(174)=>chr(60).chr(60),chr(175)=>chr(62).chr(62),chr(179)=>chr(124),chr(180)=>chr(43),chr(181)=>chr(193),chr(182)=>chr(194),chr(183)=>chr(204),chr(184)=>chr(170),chr(185)=>chr(43),chr(186)=>chr(124),chr(187)=>chr(43),chr(188)=>chr(43),chr(189)=>chr(175),chr(190)=>chr(191),chr(191)=>chr(43),chr(192)=>chr(43),chr(193)=>chr(43),chr(194)=>chr(43),chr(195)=>chr(43),chr(196)=>chr(45),chr(197)=>chr(43),chr(198)=>chr(195),chr(199)=>chr(227),chr(200)=>chr(43),chr(201)=>chr(43),chr(202)=>chr(43),chr(203)=>chr(43),chr(204)=>chr(43),chr(205)=>chr(45),chr(206)=>chr(43),chr(207)=>chr(164),chr(208)=>chr(240),chr(209)=>chr(208),chr(210)=>chr(207),chr(211)=>chr(203),chr(212)=>chr(239),chr(213)=>chr(210),chr(214)=>chr(205),chr(215)=>chr(206),chr(216)=>chr(236),chr(217)=>chr(43),chr(218)=>chr(43),chr(221)=>chr(222),chr(222)=>chr(217),chr(224)=>chr(211),chr(225)=>chr(223),chr(226)=>chr(212),chr(227)=>chr(209),chr(228)=>chr(241),chr(229)=>chr(242),chr(230)=>chr(169),chr(231)=>chr(185),chr(232)=>chr(192),chr(233)=>chr(218),chr(234)=>chr(224),chr(235)=>chr(219),chr(236)=>chr(253),chr(237)=>chr(221),chr(238)=>chr(254),chr(239)=>chr(180),chr(241)=>chr(189),chr(242)=>chr(178),chr(243)=>chr(183),chr(244)=>chr(162),chr(245)=>chr(167),chr(246)=>chr(247),chr(247)=>chr(184),chr(249)=>chr(168),chr(250)=>chr(255),chr(252)=>chr(216),chr(253)=>chr(248)));
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
			//utf-16 je podmnozina kodovani utf-8	s nejakyma dalsim vyfikundacema.
			//mela by byt plne kompatibilni ve spodnich dvou planech.
			//znaky schopne konverze to iso-8859-2 by mely v techto planech.
			case "utf16":	
			case "utf8":
				return strtr($text, array(chr(194).chr(160)=>chr(160),chr(196).chr(132)=>chr(161),chr(203).chr(152)=>chr(162),chr(197).chr(129)=>chr(163),chr(194).chr(164)=>chr(164),chr(196).chr(189)=>chr(165),chr(197).chr(154)=>chr(166),chr(194).chr(167)=>chr(167),chr(194).chr(168)=>chr(168),chr(197).chr(160)=>chr(169),chr(197).chr(158)=>chr(170),chr(197).chr(164)=>chr(171),chr(197).chr(185)=>chr(172),chr(194).chr(173)=>chr(173),chr(197).chr(189)=>chr(174),chr(197).chr(187)=>chr(175),chr(194).chr(176)=>chr(176),chr(196).chr(133)=>chr(177),chr(203).chr(155)=>chr(178),chr(197).chr(130)=>chr(179),chr(194).chr(180)=>chr(180),chr(196).chr(190)=>chr(181),chr(197).chr(155)=>chr(182),chr(203).chr(135)=>chr(183),chr(194).chr(184)=>chr(184),chr(197).chr(161)=>chr(185),chr(197).chr(159)=>chr(186),chr(197).chr(165)=>chr(187),chr(197).chr(186)=>chr(188),chr(203).chr(157)=>chr(189),chr(197).chr(190)=>chr(190),chr(197).chr(188)=>chr(191),chr(197).chr(148)=>chr(192),chr(195).chr(129)=>chr(193),chr(195).chr(130)=>chr(194),chr(196).chr(130)=>chr(195),chr(195).chr(132)=>chr(196),chr(196).chr(185)=>chr(197),chr(196).chr(134)=>chr(198),chr(195).chr(135)=>chr(199),chr(196).chr(140)=>chr(200),chr(195).chr(137)=>chr(201),chr(196).chr(152)=>chr(202),chr(195).chr(139)=>chr(203),chr(196).chr(154)=>chr(204),chr(195).chr(141)=>chr(205),chr(195).chr(142)=>chr(206),chr(196).chr(142)=>chr(207),chr(195).chr(144)=>chr(208),chr(197).chr(131)=>chr(209),chr(197).chr(135)=>chr(210),chr(195).chr(147)=>chr(211),chr(195).chr(148)=>chr(212),chr(197).chr(144)=>chr(213),chr(195).chr(150)=>chr(214),chr(195).chr(151)=>chr(215),chr(197).chr(152)=>chr(216),chr(197).chr(174)=>chr(217),chr(195).chr(154)=>chr(218),chr(197).chr(176)=>chr(219),chr(195).chr(156)=>chr(220),chr(195).chr(157)=>chr(221),chr(197).chr(162)=>chr(222),chr(195).chr(159)=>chr(223),chr(197).chr(149)=>chr(224),chr(195).chr(161)=>chr(225),chr(195).chr(162)=>chr(226),chr(196).chr(131)=>chr(227),chr(195).chr(164)=>chr(228),chr(196).chr(186)=>chr(229),chr(196).chr(135)=>chr(230),chr(195).chr(167)=>chr(231),chr(196).chr(141)=>chr(232),chr(195).chr(169)=>chr(233),chr(196).chr(153)=>chr(234),chr(195).chr(171)=>chr(235),chr(196).chr(155)=>chr(236),chr(195).chr(173)=>chr(237),chr(195).chr(174)=>chr(238),chr(196).chr(143)=>chr(239),chr(195).chr(176)=>chr(240),chr(197).chr(132)=>chr(241),chr(197).chr(136)=>chr(242),chr(195).chr(179)=>chr(243),chr(195).chr(180)=>chr(244),chr(197).chr(145)=>chr(245),chr(195).chr(182)=>chr(246),chr(195).chr(183)=>chr(247),chr(197).chr(153)=>chr(248),chr(197).chr(175)=>chr(249),chr(195).chr(186)=>chr(250),chr(197).chr(177)=>chr(251),chr(195).chr(188)=>chr(252),chr(195).chr(189)=>chr(253),chr(197).chr(163)=>chr(254),chr(203).chr(153)=>chr(255)));
				break;
			case "vga":
				return strtr($text, array(chr(128)=>chr(199),chr(129)=>chr(252),chr(130)=>chr(233),chr(131)=>chr(226),chr(132)=>chr(228),chr(133)=>chr(97),chr(134)=>chr(97),chr(135)=>chr(231),chr(136)=>chr(101),chr(137)=>chr(235),chr(138)=>chr(101),chr(139)=>chr(105),chr(140)=>chr(238),chr(141)=>chr(105),chr(142)=>chr(196),chr(143)=>chr(65),chr(144)=>chr(201),chr(145)=>chr(97).chr(101),chr(146)=>chr(65).chr(69),chr(147)=>chr(244),chr(148)=>chr(246),chr(149)=>chr(111),chr(150)=>chr(117),chr(151)=>chr(117),chr(152)=>chr(121),chr(153)=>chr(214),chr(154)=>chr(220),chr(155)=>chr(99),chr(156)=>chr(76),chr(157)=>chr(121),chr(158)=>chr(80).chr(116),chr(159)=>chr(102),chr(160)=>chr(225),chr(161)=>chr(237),chr(162)=>chr(243),chr(163)=>chr(250),chr(164)=>chr(110),chr(165)=>chr(78),chr(166)=>chr(97),chr(167)=>chr(111),chr(168)=>chr(63),chr(170)=>chr(110).chr(111).chr(116),chr(171)=>chr(49).chr(47).chr(50),chr(172)=>chr(49).chr(47).chr(52),chr(173)=>chr(33),chr(174)=>chr(60).chr(60),chr(175)=>chr(62).chr(62),chr(230)=>chr(117),chr(241)=>chr(43).chr(47).chr(45),chr(242)=>chr(62).chr(61),chr(243)=>chr(60).chr(61),chr(246)=>chr(247),chr(248)=>chr(176),chr(250)=>chr(46)));
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
		switch($from_cp){
			case "iso-8859-2":
				return strtr($text, array(chr(161)=>chr(165),chr(165)=>chr(188),chr(166)=>chr(140),chr(169)=>chr(138),chr(171)=>chr(141),chr(172)=>chr(143),chr(174)=>chr(142),chr(177)=>chr(185),chr(181)=>chr(190),chr(182)=>chr(156),chr(183)=>chr(161),chr(185)=>chr(154),chr(187)=>chr(157),chr(188)=>chr(159),chr(190)=>chr(158),chr(255)=>chr(183)));
				break;
			case "iso-8859-1":
				return strtr($text, array(chr(161)=>chr(33),chr(162)=>chr(99),chr(163)=>chr(76),chr(165)=>chr(121),chr(183)=>chr(46),chr(188)=>chr(49).chr(47).chr(52),chr(189)=>chr(49).chr(47).chr(50),chr(190)=>chr(51).chr(47).chr(52),chr(191)=>chr(63),chr(192)=>chr(65),chr(195)=>chr(65),chr(197)=>chr(65),chr(198)=>chr(65).chr(69),chr(200)=>chr(69),chr(202)=>chr(69),chr(204)=>chr(73),chr(207)=>chr(73),chr(209)=>chr(78),chr(210)=>chr(79),chr(213)=>chr(79),chr(216)=>chr(79),chr(217)=>chr(85),chr(219)=>chr(85),chr(224)=>chr(97),chr(227)=>chr(97),chr(229)=>chr(97),chr(230)=>chr(97).chr(101),chr(232)=>chr(101),chr(234)=>chr(101),chr(236)=>chr(105),chr(239)=>chr(105),chr(241)=>chr(110),chr(242)=>chr(111),chr(245)=>chr(111),chr(248)=>chr(111),chr(249)=>chr(117),chr(251)=>chr(117),chr(255)=>chr(121)));
				break;
			case "windows-1252":
				return strtr($text, array(chr(140)=>chr(79).chr(69),chr(145)=>chr(96),chr(146)=>chr(39),chr(148)=>chr(34),chr(152)=>chr(189),chr(156)=>chr(111).chr(101),chr(159)=>chr(89),chr(161)=>chr(33),chr(162)=>chr(99),chr(163)=>chr(76),chr(165)=>chr(121),chr(188)=>chr(49).chr(47).chr(52),chr(189)=>chr(49).chr(47).chr(50),chr(190)=>chr(51).chr(47).chr(52),chr(191)=>chr(63),chr(192)=>chr(65),chr(195)=>chr(65),chr(197)=>chr(65),chr(198)=>chr(65).chr(69),chr(200)=>chr(69),chr(202)=>chr(69),chr(204)=>chr(73),chr(207)=>chr(73),chr(209)=>chr(78),chr(210)=>chr(79),chr(213)=>chr(79),chr(216)=>chr(79),chr(217)=>chr(85),chr(219)=>chr(85),chr(224)=>chr(97),chr(227)=>chr(97),chr(229)=>chr(97),chr(230)=>chr(97).chr(101),chr(232)=>chr(101),chr(234)=>chr(101),chr(236)=>chr(105),chr(239)=>chr(105),chr(241)=>chr(110),chr(242)=>chr(111),chr(245)=>chr(111),chr(248)=>chr(111),chr(249)=>chr(117),chr(251)=>chr(117),chr(255)=>chr(121)));
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
			case "utf16":	
			case "utf8":
				return strtr($text,
					 array(
					 //chr(39)=>chr(130),
					/*chr(34)=>chr(132),*/
					/*chr(46).chr(46).chr(46)=>chr(133),*/
					//chr(124)=>chr(134),
					//chr(124)=>chr(135),
					chr(111).chr(47).chr(111).chr(111)=>chr(137),
					chr(197).chr(160)=>chr(138),
					//chr(60)=>chr(139),
					chr(197).chr(154)=>chr(140),
					chr(197).chr(164)=>chr(141),
					chr(197).chr(189)=>chr(142),
					chr(197).chr(185)=>chr(143),
					//chr(96)=>chr(145),
					//chr(39)=>chr(146),
					/*chr(34)=>chr(147),*/
					/*chr(34)=>chr(148),*/
					//chr(42)=>chr(149),
					/*chr(45).chr(45)=>chr(150),*/
					/*chr(45).chr(45).chr(45)=>chr(151),*/
					chr(40).chr(84).chr(77).chr(41)=>chr(153),
					chr(197).chr(161)=>chr(154),
					//chr(62)=>chr(155),
					chr(197).chr(155)=>chr(156),
					chr(197).chr(165)=>chr(157),
					chr(197).chr(190)=>chr(158),
					chr(197).chr(186)=>chr(159),
					chr(194).chr(160)=>chr(160),
					chr(203).chr(135)=>chr(161),
					chr(203).chr(152)=>chr(162),
					chr(197).chr(129)=>chr(163),
					chr(194).chr(164)=>chr(164),
					chr(196).chr(132)=>chr(165),
					chr(194).chr(166)=>chr(166),
					chr(194).chr(167)=>chr(167),
					chr(194).chr(168)=>chr(168),
					chr(194).chr(169)=>chr(169),
					chr(197).chr(158)=>chr(170),
					chr(194).chr(171)=>chr(171),
					chr(194).chr(172)=>chr(172),
					chr(194).chr(173)=>chr(173),
					chr(194).chr(174)=>chr(174),
					chr(197).chr(187)=>chr(175),
					chr(194).chr(176)=>chr(176),
					chr(194).chr(177)=>chr(177),
					chr(203).chr(155)=>chr(178),
					chr(197).chr(130)=>chr(179),
					chr(194).chr(180)=>chr(180),
					chr(194).chr(181)=>chr(181),
					chr(203).chr(153)=>chr(183),
					chr(194).chr(184)=>chr(184),
					chr(196).chr(133)=>chr(185),
					chr(197).chr(159)=>chr(186),
					chr(194).chr(187)=>chr(187),
					chr(196).chr(189)=>chr(188),
					chr(203).chr(157)=>chr(189),
					chr(196).chr(190)=>chr(190),
					chr(197).chr(188)=>chr(191),
					chr(197).chr(148)=>chr(192),
					chr(195).chr(129)=>chr(193),
					chr(195).chr(130)=>chr(194),
					chr(196).chr(130)=>chr(195),
					chr(195).chr(132)=>chr(196),
					chr(196).chr(185)=>chr(197),
					chr(196).chr(134)=>chr(198),
					chr(195).chr(135)=>chr(199),
					chr(196).chr(140)=>chr(200),
					chr(195).chr(137)=>chr(201),
					chr(196).chr(152)=>chr(202),
					chr(195).chr(139)=>chr(203),
					chr(196).chr(154)=>chr(204),
					chr(195).chr(141)=>chr(205),
					chr(195).chr(142)=>chr(206),
					chr(196).chr(142)=>chr(207),
					chr(195).chr(144)=>chr(208),
					chr(197).chr(131)=>chr(209),
					chr(197).chr(135)=>chr(210),
					chr(195).chr(147)=>chr(211),
					chr(195).chr(148)=>chr(212),
					chr(197).chr(144)=>chr(213),
					chr(195).chr(150)=>chr(214),
					chr(195).chr(151)=>chr(215),
					chr(197).chr(152)=>chr(216),
					chr(197).chr(174)=>chr(217),
					chr(195).chr(154)=>chr(218),
					chr(197).chr(176)=>chr(219),
					chr(195).chr(156)=>chr(220),
					chr(195).chr(157)=>chr(221),
					chr(197).chr(162)=>chr(222),
					chr(195).chr(159)=>chr(223),
					chr(197).chr(149)=>chr(224),
					chr(195).chr(161)=>chr(225),
					chr(195).chr(162)=>chr(226),
					chr(196).chr(131)=>chr(227),
					chr(195).chr(164)=>chr(228),
					chr(196).chr(186)=>chr(229),
					chr(196).chr(135)=>chr(230),
					chr(195).chr(167)=>chr(231),
					chr(196).chr(141)=>chr(232),
					chr(195).chr(169)=>chr(233),
					chr(196).chr(153)=>chr(234),
					chr(195).chr(171)=>chr(235),
					chr(196).chr(155)=>chr(236),
					chr(195).chr(173)=>chr(237),
					chr(195).chr(174)=>chr(238),
					chr(196).chr(143)=>chr(239),
					chr(195).chr(176)=>chr(240),
					chr(197).chr(132)=>chr(241),
					chr(197).chr(136)=>chr(242),
					chr(195).chr(179)=>chr(243),
					chr(195).chr(180)=>chr(244),
					chr(197).chr(145)=>chr(245),
					chr(195).chr(182)=>chr(246),
					chr(195).chr(183)=>chr(247),
					chr(197).chr(153)=>chr(248),
					chr(197).chr(175)=>chr(249),
					chr(195).chr(186)=>chr(250),
					chr(197).chr(177)=>chr(251),
					chr(195).chr(188)=>chr(252),
					chr(195).chr(189)=>chr(253),
					chr(197).chr(163)=>chr(254),
					chr(203).chr(153)=>chr(255)));
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
				$text = translate::trans($text,"utf8","windows-1250");
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
		switch ($from_cp){
			case "iso-8859-2":
				return strtr($text,array(chr(160)=>chr(194).chr(160),chr(161)=>chr(196).chr(132),chr(162)=>chr(203).chr(152),chr(163)=>chr(197).chr(129),chr(164)=>chr(194).chr(164),chr(165)=>chr(196).chr(189),chr(166)=>chr(197).chr(154),chr(167)=>chr(194).chr(167),chr(168)=>chr(194).chr(168),chr(169)=>chr(197).chr(160),chr(170)=>chr(197).chr(158),chr(171)=>chr(197).chr(164),chr(172)=>chr(197).chr(185),chr(173)=>chr(194).chr(173),chr(174)=>chr(197).chr(189),chr(175)=>chr(197).chr(187),chr(176)=>chr(194).chr(176),chr(177)=>chr(196).chr(133),chr(178)=>chr(203).chr(155),chr(179)=>chr(197).chr(130),chr(180)=>chr(194).chr(180),chr(181)=>chr(196).chr(190),chr(182)=>chr(197).chr(155),chr(183)=>chr(203).chr(135),chr(184)=>chr(194).chr(184),chr(185)=>chr(197).chr(161),chr(186)=>chr(197).chr(159),chr(187)=>chr(197).chr(165),chr(188)=>chr(197).chr(186),chr(189)=>chr(203).chr(157),chr(190)=>chr(197).chr(190),chr(191)=>chr(197).chr(188),chr(192)=>chr(197).chr(148),chr(193)=>chr(195).chr(129),chr(194)=>chr(195).chr(130),chr(195)=>chr(196).chr(130),chr(196)=>chr(195).chr(132),chr(197)=>chr(196).chr(185),chr(198)=>chr(196).chr(134),chr(199)=>chr(195).chr(135),chr(200)=>chr(196).chr(140),chr(201)=>chr(195).chr(137),chr(202)=>chr(196).chr(152),chr(203)=>chr(195).chr(139),chr(204)=>chr(196).chr(154),chr(205)=>chr(195).chr(141),chr(206)=>chr(195).chr(142),chr(207)=>chr(196).chr(142),chr(208)=>chr(195).chr(144),chr(209)=>chr(197).chr(131),chr(210)=>chr(197).chr(135),chr(211)=>chr(195).chr(147),chr(212)=>chr(195).chr(148),chr(213)=>chr(197).chr(144),chr(214)=>chr(195).chr(150),chr(215)=>chr(195).chr(151),chr(216)=>chr(197).chr(152),chr(217)=>chr(197).chr(174),chr(218)=>chr(195).chr(154),chr(219)=>chr(197).chr(176),chr(220)=>chr(195).chr(156),chr(221)=>chr(195).chr(157),chr(222)=>chr(197).chr(162),chr(223)=>chr(195).chr(159),chr(224)=>chr(197).chr(149),chr(225)=>chr(195).chr(161),chr(226)=>chr(195).chr(162),chr(227)=>chr(196).chr(131),chr(228)=>chr(195).chr(164),chr(229)=>chr(196).chr(186),chr(230)=>chr(196).chr(135),chr(231)=>chr(195).chr(167),chr(232)=>chr(196).chr(141),chr(233)=>chr(195).chr(169),chr(234)=>chr(196).chr(153),chr(235)=>chr(195).chr(171),chr(236)=>chr(196).chr(155),chr(237)=>chr(195).chr(173),chr(238)=>chr(195).chr(174),chr(239)=>chr(196).chr(143),chr(240)=>chr(195).chr(176),chr(241)=>chr(197).chr(132),chr(242)=>chr(197).chr(136),chr(243)=>chr(195).chr(179),chr(244)=>chr(195).chr(180),chr(245)=>chr(197).chr(145),chr(246)=>chr(195).chr(182),chr(247)=>chr(195).chr(183),chr(248)=>chr(197).chr(153),chr(249)=>chr(197).chr(175),chr(250)=>chr(195).chr(186),chr(251)=>chr(197).chr(177),chr(252)=>chr(195).chr(188),chr(253)=>chr(195).chr(189),chr(254)=>chr(197).chr(163),chr(255)=>chr(203).chr(153)));
				break;
			case "iso-8859-1":
				return strtr($text,array(chr(160)=>chr(194).chr(160),chr(161)=>chr(194).chr(161),chr(162)=>chr(194).chr(162),chr(163)=>chr(194).chr(163),chr(164)=>chr(194).chr(164),chr(165)=>chr(194).chr(165),chr(166)=>chr(194).chr(166),chr(167)=>chr(194).chr(167),chr(168)=>chr(194).chr(168),chr(169)=>chr(194).chr(169),chr(170)=>chr(194).chr(170),chr(171)=>chr(194).chr(171),chr(172)=>chr(194).chr(172),chr(173)=>chr(194).chr(173),chr(174)=>chr(194).chr(174),chr(175)=>chr(194).chr(175),chr(176)=>chr(194).chr(176),chr(177)=>chr(194).chr(177),chr(178)=>chr(194).chr(178),chr(179)=>chr(194).chr(179),chr(180)=>chr(194).chr(180),chr(181)=>chr(194).chr(181),chr(182)=>chr(194).chr(182),chr(183)=>chr(194).chr(183),chr(184)=>chr(194).chr(184),chr(186)=>chr(194).chr(186),chr(187)=>chr(194).chr(187),chr(188)=>chr(194).chr(188),chr(189)=>chr(194).chr(189),chr(190)=>chr(194).chr(190),chr(191)=>chr(194).chr(191),chr(192)=>chr(195).chr(128),chr(193)=>chr(195).chr(129),chr(194)=>chr(195).chr(130),chr(195)=>chr(195).chr(131),chr(196)=>chr(195).chr(132),chr(197)=>chr(195).chr(133),chr(198)=>chr(195).chr(134),chr(199)=>chr(195).chr(135),chr(200)=>chr(195).chr(136),chr(201)=>chr(195).chr(137),chr(202)=>chr(195).chr(138),chr(203)=>chr(195).chr(139),chr(204)=>chr(195).chr(140),chr(205)=>chr(195).chr(141),chr(206)=>chr(195).chr(142),chr(207)=>chr(195).chr(143),chr(208)=>chr(195).chr(144),chr(209)=>chr(195).chr(145),chr(210)=>chr(195).chr(146),chr(211)=>chr(195).chr(147),chr(212)=>chr(195).chr(148),chr(213)=>chr(195).chr(149),chr(214)=>chr(195).chr(150),chr(215)=>chr(195).chr(151),chr(216)=>chr(195).chr(152),chr(217)=>chr(195).chr(153),chr(218)=>chr(195).chr(154),chr(219)=>chr(195).chr(155),chr(220)=>chr(195).chr(156),chr(221)=>chr(195).chr(157),chr(222)=>chr(195).chr(158),chr(223)=>chr(195).chr(159),chr(224)=>chr(195).chr(160),chr(225)=>chr(195).chr(161),chr(226)=>chr(195).chr(162),chr(227)=>chr(195).chr(163),chr(228)=>chr(195).chr(164),chr(229)=>chr(195).chr(165),chr(230)=>chr(195).chr(166),chr(231)=>chr(195).chr(167),chr(232)=>chr(195).chr(168),chr(233)=>chr(195).chr(169),chr(234)=>chr(195).chr(170),chr(235)=>chr(195).chr(171),chr(236)=>chr(195).chr(172),chr(237)=>chr(195).chr(173),chr(238)=>chr(195).chr(174),chr(239)=>chr(195).chr(175),chr(240)=>chr(195).chr(176),chr(241)=>chr(195).chr(177),chr(242)=>chr(195).chr(178),chr(243)=>chr(195).chr(179),chr(244)=>chr(195).chr(180),chr(245)=>chr(195).chr(181),chr(246)=>chr(195).chr(182),chr(247)=>chr(195).chr(183),chr(248)=>chr(195).chr(184),chr(249)=>chr(195).chr(185),chr(250)=>chr(195).chr(186),chr(251)=>chr(195).chr(187),chr(252)=>chr(195).chr(188),chr(253)=>chr(195).chr(189),chr(254)=>chr(195).chr(190),chr(255)=>chr(195).chr(191)));
				break;
			case "windows-1250":
			  return strtr($text,array(chr(130)=>chr(39),chr(132)=>chr(34),chr(133)=>chr(46).chr(46).chr(46),chr(134)=>chr(124),chr(135)=>chr(124),chr(137)=>chr(111).chr(47).chr(111).chr(111),chr(138)=>chr(197).chr(160),chr(139)=>chr(60),chr(140)=>chr(197).chr(154),chr(141)=>chr(197).chr(164),chr(142)=>chr(197).chr(189),chr(143)=>chr(197).chr(185),chr(145)=>chr(96),chr(146)=>chr(39),chr(147)=>chr(34),chr(148)=>chr(34),chr(149)=>chr(42),chr(150)=>chr(45).chr(45),chr(151)=>chr(45).chr(45).chr(45),chr(153)=>chr(40).chr(84).chr(77).chr(41),chr(154)=>chr(197).chr(161),chr(155)=>chr(62),chr(156)=>chr(197).chr(155),chr(157)=>chr(197).chr(165),chr(158)=>chr(197).chr(190),chr(159)=>chr(197).chr(186),chr(160)=>chr(194).chr(160),chr(161)=>chr(203).chr(135),chr(162)=>chr(203).chr(152),chr(163)=>chr(197).chr(129),chr(164)=>chr(194).chr(164),chr(165)=>chr(196).chr(132),chr(166)=>chr(194).chr(166),chr(167)=>chr(194).chr(167),chr(168)=>chr(194).chr(168),chr(169)=>chr(194).chr(169),chr(170)=>chr(197).chr(158),chr(171)=>chr(194).chr(171),chr(172)=>chr(194).chr(172),chr(173)=>chr(194).chr(173),chr(174)=>chr(194).chr(174),chr(175)=>chr(197).chr(187),chr(176)=>chr(194).chr(176),chr(177)=>chr(194).chr(177),chr(178)=>chr(203).chr(155),chr(179)=>chr(197).chr(130),chr(180)=>chr(194).chr(180),chr(181)=>chr(194).chr(181),chr(183)=>chr(203).chr(153),chr(184)=>chr(194).chr(184),chr(185)=>chr(196).chr(133),chr(186)=>chr(197).chr(159),chr(187)=>chr(194).chr(187),chr(188)=>chr(196).chr(189),chr(189)=>chr(203).chr(157),chr(190)=>chr(196).chr(190),chr(191)=>chr(197).chr(188),chr(192)=>chr(197).chr(148),chr(193)=>chr(195).chr(129),chr(194)=>chr(195).chr(130),chr(195)=>chr(196).chr(130),chr(196)=>chr(195).chr(132),chr(197)=>chr(196).chr(185),chr(198)=>chr(196).chr(134),chr(199)=>chr(195).chr(135),chr(200)=>chr(196).chr(140),chr(201)=>chr(195).chr(137),chr(202)=>chr(196).chr(152),chr(203)=>chr(195).chr(139),chr(204)=>chr(196).chr(154),chr(205)=>chr(195).chr(141),chr(206)=>chr(195).chr(142),chr(207)=>chr(196).chr(142),chr(208)=>chr(195).chr(144),chr(209)=>chr(197).chr(131),chr(210)=>chr(197).chr(135),chr(211)=>chr(195).chr(147),chr(212)=>chr(195).chr(148),chr(213)=>chr(197).chr(144),chr(214)=>chr(195).chr(150),chr(215)=>chr(195).chr(151),chr(216)=>chr(197).chr(152),chr(217)=>chr(197).chr(174),chr(218)=>chr(195).chr(154),chr(219)=>chr(197).chr(176),chr(220)=>chr(195).chr(156),chr(221)=>chr(195).chr(157),chr(222)=>chr(197).chr(162),chr(223)=>chr(195).chr(159),chr(224)=>chr(197).chr(149),chr(225)=>chr(195).chr(161),chr(226)=>chr(195).chr(162),chr(227)=>chr(196).chr(131),chr(228)=>chr(195).chr(164),chr(229)=>chr(196).chr(186),chr(230)=>chr(196).chr(135),chr(231)=>chr(195).chr(167),chr(232)=>chr(196).chr(141),chr(233)=>chr(195).chr(169),chr(234)=>chr(196).chr(153),chr(235)=>chr(195).chr(171),chr(236)=>chr(196).chr(155),chr(237)=>chr(195).chr(173),chr(238)=>chr(195).chr(174),chr(239)=>chr(196).chr(143),chr(240)=>chr(195).chr(176),chr(241)=>chr(197).chr(132),chr(242)=>chr(197).chr(136),chr(243)=>chr(195).chr(179),chr(244)=>chr(195).chr(180),chr(245)=>chr(197).chr(145),chr(246)=>chr(195).chr(182),chr(247)=>chr(195).chr(183),chr(248)=>chr(197).chr(153),chr(249)=>chr(197).chr(175),chr(250)=>chr(195).chr(186),chr(251)=>chr(197).chr(177),chr(252)=>chr(195).chr(188),chr(253)=>chr(195).chr(189),chr(254)=>chr(197).chr(163),chr(255)=>chr(203).chr(153)));
				break;
			case "windows-1252":
				return strtr($text,array(chr(130)=>chr(39),chr(132)=>chr(34),chr(133)=>chr(46).chr(46).chr(46),chr(134)=>chr(124),chr(135)=>chr(124),chr(137)=>chr(111).chr(47).chr(111).chr(111),chr(138)=>chr(197).chr(160),chr(139)=>chr(60),chr(140)=>chr(79).chr(69),chr(145)=>chr(96),chr(146)=>chr(39),chr(147)=>chr(34),chr(148)=>chr(34),chr(149)=>chr(42),chr(150)=>chr(45).chr(45),chr(151)=>chr(45).chr(45).chr(45),chr(152)=>chr(203).chr(157),chr(153)=>chr(40).chr(84).chr(77).chr(41),chr(154)=>chr(197).chr(161),chr(155)=>chr(62),chr(156)=>chr(111).chr(101),chr(159)=>chr(89),chr(160)=>chr(194).chr(160),chr(161)=>chr(194).chr(161),chr(162)=>chr(194).chr(162),chr(163)=>chr(194).chr(163),chr(164)=>chr(194).chr(164),chr(165)=>chr(194).chr(165),chr(166)=>chr(194).chr(166),chr(167)=>chr(194).chr(167),chr(168)=>chr(194).chr(168),chr(169)=>chr(194).chr(169),chr(170)=>chr(194).chr(170),chr(171)=>chr(194).chr(171),chr(172)=>chr(194).chr(172),chr(173)=>chr(194).chr(173),chr(174)=>chr(194).chr(174),chr(175)=>chr(194).chr(175),chr(176)=>chr(194).chr(176),chr(177)=>chr(194).chr(177),chr(178)=>chr(194).chr(178),chr(179)=>chr(194).chr(179),chr(180)=>chr(194).chr(180),chr(181)=>chr(194).chr(181),chr(182)=>chr(194).chr(182),chr(183)=>chr(203).chr(153),chr(184)=>chr(194).chr(184),chr(185)=>chr(194).chr(185),chr(186)=>chr(194).chr(186),chr(187)=>chr(194).chr(187),chr(188)=>chr(194).chr(188),chr(189)=>chr(194).chr(189),chr(190)=>chr(194).chr(190),chr(191)=>chr(194).chr(191),chr(192)=>chr(195).chr(128),chr(193)=>chr(195).chr(129),chr(194)=>chr(195).chr(130),chr(195)=>chr(195).chr(131),chr(196)=>chr(195).chr(132),chr(197)=>chr(195).chr(133),chr(198)=>chr(195).chr(134),chr(199)=>chr(195).chr(135),chr(200)=>chr(195).chr(136),chr(201)=>chr(195).chr(137),chr(202)=>chr(195).chr(138),chr(203)=>chr(195).chr(139),chr(204)=>chr(195).chr(140),chr(205)=>chr(195).chr(141),chr(206)=>chr(195).chr(142),chr(207)=>chr(195).chr(143),chr(208)=>chr(195).chr(144),chr(209)=>chr(195).chr(145),chr(210)=>chr(195).chr(146),chr(211)=>chr(195).chr(147),chr(212)=>chr(195).chr(148),chr(213)=>chr(195).chr(149),chr(214)=>chr(195).chr(150),chr(215)=>chr(195).chr(151),chr(216)=>chr(79),chr(217)=>chr(195).chr(153),chr(218)=>chr(195).chr(154),chr(219)=>chr(195).chr(155),chr(220)=>chr(195).chr(156),chr(221)=>chr(195).chr(157),chr(222)=>chr(195).chr(158),chr(223)=>chr(195).chr(159),chr(224)=>chr(195).chr(160),chr(225)=>chr(195).chr(161),chr(226)=>chr(195).chr(162),chr(227)=>chr(195).chr(163),chr(228)=>chr(195).chr(164),chr(229)=>chr(195).chr(165),chr(230)=>chr(195).chr(166),chr(231)=>chr(195).chr(167),chr(232)=>chr(195).chr(168),chr(233)=>chr(195).chr(169),chr(234)=>chr(195).chr(170),chr(235)=>chr(195).chr(171),chr(236)=>chr(195).chr(172),chr(237)=>chr(195).chr(173),chr(238)=>chr(195).chr(174),chr(239)=>chr(195).chr(175),chr(240)=>chr(195).chr(176),chr(241)=>chr(195).chr(177),chr(242)=>chr(195).chr(178),chr(243)=>chr(195).chr(179),chr(244)=>chr(195).chr(180),chr(245)=>chr(195).chr(181),chr(246)=>chr(195).chr(182),chr(247)=>chr(195).chr(183),chr(248)=>chr(195).chr(184),chr(249)=>chr(195).chr(185),chr(250)=>chr(195).chr(186),chr(251)=>chr(195).chr(187),chr(252)=>chr(195).chr(188),chr(253)=>chr(195).chr(189),chr(254)=>chr(195).chr(190),chr(255)=>chr(195).chr(191)));
				break;			
			default:
				return $text;
		}
	}

	/**
	 * @ignore
	 */
	static function _TO_ascii(&$text,$from_cp){
		switch ($from_cp){
			case "iso-8859-2":
				return strtr($text,array(chr(161)=>chr(65),chr(163)=>chr(76),chr(165)=>chr(76),chr(166)=>chr(83),chr(168)=>chr(34),chr(169)=>chr(83),chr(170)=>chr(83),chr(171)=>chr(84),chr(172)=>chr(90),chr(173)=>chr(45),chr(174)=>chr(90),chr(175)=>chr(90),chr(177)=>chr(97),chr(179)=>chr(108),chr(180)=>chr(39),chr(181)=>chr(108),chr(182)=>chr(115),chr(185)=>chr(115),chr(186)=>chr(115),chr(187)=>chr(116),chr(188)=>chr(122),chr(189)=>chr(34),chr(190)=>chr(122),chr(191)=>chr(122),chr(192)=>chr(82),chr(193)=>chr(65),chr(194)=>chr(65),chr(195)=>chr(65),chr(196)=>chr(65),chr(197)=>chr(76),chr(198)=>chr(67),chr(199)=>chr(67),chr(200)=>chr(67),chr(201)=>chr(69),chr(202)=>chr(69),chr(203)=>chr(69),chr(204)=>chr(69),chr(205)=>chr(73),chr(206)=>chr(73),chr(207)=>chr(68),chr(208)=>chr(68),chr(209)=>chr(78),chr(210)=>chr(78),chr(211)=>chr(79),chr(212)=>chr(79),chr(213)=>chr(79),chr(214)=>chr(79),chr(215)=>chr(46),chr(216)=>chr(82),chr(217)=>chr(85),chr(218)=>chr(85),chr(219)=>chr(85),chr(220)=>chr(85),chr(221)=>chr(89),chr(222)=>chr(84),chr(223)=>chr(115).chr(115),chr(224)=>chr(114),chr(225)=>chr(97),chr(226)=>chr(97),chr(227)=>chr(97),chr(228)=>chr(97),chr(229)=>chr(108),chr(230)=>chr(99),chr(231)=>chr(99),chr(232)=>chr(99),chr(233)=>chr(101),chr(234)=>chr(101),chr(235)=>chr(101),chr(236)=>chr(101),chr(237)=>chr(105),chr(238)=>chr(105),chr(239)=>chr(100),chr(240)=>chr(100),chr(241)=>chr(110),chr(242)=>chr(110),chr(243)=>chr(111),chr(244)=>chr(111),chr(245)=>chr(111),chr(246)=>chr(111),chr(247)=>chr(47),chr(248)=>chr(114),chr(249)=>chr(117),chr(250)=>chr(117),chr(251)=>chr(117),chr(252)=>chr(117),chr(253)=>chr(121),chr(254)=>chr(116)));
				break;
			case "iso-8859-1":
				return strtr($text,array(chr(161)=>chr(33),chr(162)=>chr(99),chr(163)=>chr(76),chr(165)=>chr(121),chr(166)=>chr(124),chr(168)=>chr(34),chr(169)=>chr(40).chr(99).chr(41),chr(171)=>chr(62).chr(62),chr(172)=>chr(110).chr(111).chr(116),chr(173)=>chr(45),chr(174)=>chr(40).chr(114).chr(41),chr(177)=>chr(43).chr(47).chr(45),chr(180)=>chr(39),chr(181)=>chr(117),chr(183)=>chr(46),chr(187)=>chr(60).chr(60),chr(188)=>chr(49).chr(47).chr(52),chr(189)=>chr(49).chr(47).chr(50),chr(190)=>chr(51).chr(47).chr(52),chr(191)=>chr(63),chr(192)=>chr(65),chr(193)=>chr(65),chr(194)=>chr(65),chr(195)=>chr(65),chr(196)=>chr(65),chr(197)=>chr(65),chr(198)=>chr(65).chr(69),chr(199)=>chr(67),chr(200)=>chr(69),chr(201)=>chr(69),chr(202)=>chr(69),chr(203)=>chr(69),chr(204)=>chr(73),chr(205)=>chr(73),chr(206)=>chr(73),chr(207)=>chr(73),chr(208)=>chr(68),chr(209)=>chr(78),chr(210)=>chr(79),chr(211)=>chr(79),chr(212)=>chr(79),chr(213)=>chr(79),chr(214)=>chr(79),chr(215)=>chr(46),chr(216)=>chr(79),chr(217)=>chr(85),chr(218)=>chr(85),chr(219)=>chr(85),chr(220)=>chr(85),chr(221)=>chr(89),chr(223)=>chr(115).chr(115),chr(224)=>chr(97),chr(225)=>chr(97),chr(226)=>chr(97),chr(227)=>chr(97),chr(228)=>chr(97),chr(229)=>chr(97),chr(230)=>chr(97).chr(101),chr(231)=>chr(99),chr(232)=>chr(101),chr(233)=>chr(101),chr(234)=>chr(101),chr(235)=>chr(101),chr(236)=>chr(105),chr(237)=>chr(105),chr(238)=>chr(105),chr(239)=>chr(105),chr(240)=>chr(100),chr(241)=>chr(110),chr(242)=>chr(111),chr(243)=>chr(111),chr(244)=>chr(111),chr(245)=>chr(111),chr(246)=>chr(111),chr(247)=>chr(47),chr(248)=>chr(111),chr(249)=>chr(117),chr(250)=>chr(117),chr(251)=>chr(117),chr(252)=>chr(117),chr(253)=>chr(121),chr(255)=>chr(121)));
				break;
			case "windows-1250":
				return strtr($text,array(chr(130)=>chr(39),chr(132)=>chr(34),chr(133)=>chr(46).chr(46).chr(46),chr(134)=>chr(124),chr(135)=>chr(124),chr(137)=>chr(111).chr(47).chr(111).chr(111),chr(138)=>chr(83),chr(139)=>chr(60),chr(140)=>chr(83),chr(141)=>chr(84),chr(142)=>chr(90),chr(143)=>chr(90),chr(145)=>chr(96),chr(146)=>chr(39),chr(147)=>chr(34),chr(148)=>chr(34),chr(149)=>chr(42),chr(150)=>chr(45).chr(45),chr(151)=>chr(45).chr(45).chr(45),chr(153)=>chr(40).chr(84).chr(77).chr(41),chr(154)=>chr(115),chr(155)=>chr(62),chr(156)=>chr(115),chr(157)=>chr(116),chr(158)=>chr(122),chr(159)=>chr(122),chr(163)=>chr(76),chr(165)=>chr(65),chr(166)=>chr(124),chr(168)=>chr(34),chr(169)=>chr(40).chr(99).chr(41),chr(170)=>chr(83),chr(171)=>chr(62).chr(62),chr(172)=>chr(110).chr(111).chr(116),chr(173)=>chr(45),chr(174)=>chr(40).chr(114).chr(41),chr(175)=>chr(90),chr(177)=>chr(43).chr(47).chr(45),chr(179)=>chr(108),chr(180)=>chr(39),chr(181)=>chr(117),chr(185)=>chr(97),chr(186)=>chr(115),chr(187)=>chr(60).chr(60),chr(188)=>chr(76),chr(189)=>chr(34),chr(190)=>chr(108),chr(191)=>chr(122),chr(192)=>chr(82),chr(193)=>chr(65),chr(194)=>chr(65),chr(195)=>chr(65),chr(196)=>chr(65),chr(197)=>chr(76),chr(198)=>chr(67),chr(199)=>chr(67),chr(200)=>chr(67),chr(201)=>chr(69),chr(202)=>chr(69),chr(203)=>chr(69),chr(204)=>chr(69),chr(205)=>chr(73),chr(206)=>chr(73),chr(207)=>chr(68),chr(208)=>chr(68),chr(209)=>chr(78),chr(210)=>chr(78),chr(211)=>chr(79),chr(212)=>chr(79),chr(213)=>chr(79),chr(214)=>chr(79),chr(215)=>chr(46),chr(216)=>chr(82),chr(217)=>chr(85),chr(218)=>chr(85),chr(219)=>chr(85),chr(220)=>chr(85),chr(221)=>chr(89),chr(222)=>chr(84),chr(223)=>chr(115).chr(115),chr(224)=>chr(114),chr(225)=>chr(97),chr(226)=>chr(97),chr(227)=>chr(97),chr(228)=>chr(97),chr(229)=>chr(108),chr(230)=>chr(99),chr(231)=>chr(99),chr(232)=>chr(99),chr(233)=>chr(101),chr(234)=>chr(101),chr(235)=>chr(101),chr(236)=>chr(101),chr(237)=>chr(105),chr(238)=>chr(105),chr(239)=>chr(100),chr(240)=>chr(100),chr(241)=>chr(110),chr(242)=>chr(110),chr(243)=>chr(111),chr(244)=>chr(111),chr(245)=>chr(111),chr(246)=>chr(111),chr(247)=>chr(47),chr(248)=>chr(114),chr(249)=>chr(117),chr(250)=>chr(117),chr(251)=>chr(117),chr(252)=>chr(117),chr(253)=>chr(121),chr(254)=>chr(116)));
				break;
			case "windows-1252":
				return strtr($text,array(chr(130)=>chr(39),chr(132)=>chr(34),chr(133)=>chr(46).chr(46).chr(46),chr(134)=>chr(124),chr(135)=>chr(124),chr(137)=>chr(111).chr(47).chr(111).chr(111),chr(138)=>chr(83),chr(139)=>chr(60),chr(140)=>chr(79).chr(69),chr(145)=>chr(96),chr(146)=>chr(39),chr(147)=>chr(34),chr(148)=>chr(34),chr(149)=>chr(42),chr(150)=>chr(45).chr(45),chr(151)=>chr(45).chr(45).chr(45),chr(152)=>chr(34),chr(153)=>chr(40).chr(84).chr(77).chr(41),chr(154)=>chr(115),chr(155)=>chr(62),chr(156)=>chr(111).chr(101),chr(159)=>chr(89),chr(161)=>chr(33),chr(162)=>chr(99),chr(163)=>chr(76),chr(165)=>chr(121),chr(166)=>chr(124),chr(168)=>chr(34),chr(169)=>chr(40).chr(99).chr(41),chr(171)=>chr(62).chr(62),chr(172)=>chr(110).chr(111).chr(116),chr(173)=>chr(45),chr(174)=>chr(40).chr(114).chr(41),chr(177)=>chr(43).chr(47).chr(45),chr(180)=>chr(39),chr(181)=>chr(117),chr(187)=>chr(60).chr(60),chr(188)=>chr(49).chr(47).chr(52),chr(189)=>chr(49).chr(47).chr(50),chr(190)=>chr(51).chr(47).chr(52),chr(191)=>chr(63),chr(192)=>chr(65),chr(193)=>chr(65),chr(194)=>chr(65),chr(195)=>chr(65),chr(196)=>chr(65),chr(197)=>chr(65),chr(198)=>chr(65).chr(69),chr(199)=>chr(67),chr(200)=>chr(69),chr(201)=>chr(69),chr(202)=>chr(69),chr(203)=>chr(69),chr(204)=>chr(73),chr(205)=>chr(73),chr(206)=>chr(73),chr(207)=>chr(73),chr(208)=>chr(68),chr(209)=>chr(78),chr(210)=>chr(79),chr(211)=>chr(79),chr(212)=>chr(79),chr(213)=>chr(79),chr(214)=>chr(79),chr(215)=>chr(46),chr(216)=>chr(79),chr(217)=>chr(85),chr(218)=>chr(85),chr(219)=>chr(85),chr(220)=>chr(85),chr(221)=>chr(89),chr(223)=>chr(115).chr(115),chr(224)=>chr(97),chr(225)=>chr(97),chr(226)=>chr(97),chr(227)=>chr(97),chr(228)=>chr(97),chr(229)=>chr(97),chr(230)=>chr(97).chr(101),chr(231)=>chr(99),chr(232)=>chr(101),chr(233)=>chr(101),chr(234)=>chr(101),chr(235)=>chr(101),chr(236)=>chr(105),chr(237)=>chr(105),chr(238)=>chr(105),chr(239)=>chr(105),chr(240)=>chr(100),chr(241)=>chr(110),chr(242)=>chr(111),chr(243)=>chr(111),chr(244)=>chr(111),chr(245)=>chr(111),chr(246)=>chr(111),chr(247)=>chr(47),chr(248)=>chr(111),chr(249)=>chr(117),chr(250)=>chr(117),chr(251)=>chr(117),chr(252)=>chr(117),chr(253)=>chr(121),chr(255)=>chr(121)));
				break;
			case "utf8":
				$out = strtr($text,array(
					chr(0xC2).chr(0xA0) => " ", // Non-breaking space
				));
				// nasledujicim hackem se prevedou do ascii jen ceske znaky z celeho utf8
				$out = translate::trans($out,"utf8","iso-8859-2");
				return translate::trans($out,"iso-8859-2","ascii");
				break;
			default:
				return $text;	
		}
	}

	/**
	 * Checks if input string is in given charset.
	 *
	 * Method can distinguish some characters (or even whole sequencies) that can not appear in the string.
	 * When they appear method returns false
	 *
	 * <code>
	 * translate::check_encoding($text, "utf-8", array(".",";","{","}","HUSAK"));
	 * </code>
	 *
	 * @param string|array $text string or array of strings
	 * @param string $charset
	 * @param array $disallowed_char_sequencies		forbidden chars or strings
	 * @return bool																true -> text is in given charset
	 *																						false -> text is not in given charset or contains a character or sequence from array $disallowed_char_sequencies
	 */
	static function check_encoding($text,$charset,$disallowed_char_sequencies = array()){
		if(is_array($text)){
			reset($text);
			while(list($_key,$_value) = each($text)){
				$_stat_key = translate::check_encoding($_key,$charset,$disallowed_char_sequencies);
				$_stat_value = translate::check_encoding($_value,$charset,$disallowed_char_sequencies);
				if(!$_stat_key || !$_stat_value){
					return false;
				}
			}
			return true;
		}

		settype($charset,"string");
		settype($disallowed_char_sequencies,"array");
		$charset = translate::_get_charset_by_name($charset);
		$out = true;
		switch($charset){
			case "utf8":
				$out = translate::_check_encoding_utf8($text);
				break;
			case "ascii":
				$out = translate::_check_encoding_ascii($text);
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
	static function _check_encoding_utf8(&$text){

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
	static function _check_encoding_ascii(&$text){
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
	static function length(&$text,$charset){
		$charset = translate::_get_charset_by_name($charset);
		switch($charset){
			case "utf8":
				return translate::_length_utf8($text);
			default:
				return strlen($text);
		}
	}

	/**
	 * @ignore
	 */
	static function _length_utf8(&$str){
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
