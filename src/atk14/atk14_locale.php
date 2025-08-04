<?php
/**
 * Class for managing locale based resources.
 *
 * Converts dates and time formats between different locales
 *
 * @package Atk14
 * @subpackage Core
 * @filesource
 */

/**
 * Class for managing locale based resources.
 *
 * Converts dates and time formats between different locales
 *
 * @package Atk14
 * @subpackage Core
 *
 */
class Atk14Locale{

	/**
	 * Initializes locale
	 *
	 * <code>
	 * $new_lang = "cs";
	 * $prev_lang = Atk14Locale::Initialize($new_lang);
	 *
	 * // or just...
	 * Atk14Locale::Initialize();
	 * </code>
	 */
	static function Initialize(&$lang = null){
		global $ATK14_GLOBAL;

		$lang = (string)$lang; // $lang may be a object or something... :)
		if(!$lang){
			$lang = $ATK14_GLOBAL->getDefaultLang();
		}

		$previous_lang = $ATK14_GLOBAL->getLang();

		if(function_exists("atk14_initialize_locale")){
			atk14_initialize_locale($lang);
		}else{
			i18n::init_translation($lang);
		}
		$ATK14_GLOBAL->setValue("lang",$lang);

		return $previous_lang;
	}

	/**
	 * Formats Date
	 * 
	 * <code>
	 * Atk14Locale::FormatDate("1982-12-31"); // "31.12.1982", according to the currently set language
	 * Atk14Locale::FormatDate("1982-12-31","j.n."); // "31.12."
	 * </code>
	 *
	 * @param string $iso_date date in ISO format
	 * @return string date in localized format
	 * @static
	 */
	static function FormatDate($iso_date,$pattern = ""){
		$iso_date = (string)$iso_date;
		if(strlen($iso_date)==0){ return ""; }

		if(!strlen($pattern)){
			$pattern = self::_GetDatePattern();
		}

		return date($pattern,strtotime($iso_date));
	}

	/**
	 * Parses date in localized format and converts it to ISO format
	 *
	 * <code>
	 * Atk14Locale::ParseDate("31.12.1982"); // "1982-12-31"
	 * </code>
	 *
	 * @param string $localized_date
	 * @return string date in ISO format
	 * @static
	 *
	 */
	static function ParseDate($localized_date){
		$localized_date = (string)$localized_date;
		$pattern = self::_GetParseDatePattern();

		if(
			preg_match($pattern,$localized_date,$matches) &&
			($date = Date::ByDate(array(
				"year" => $matches["year"],
				"month" => $matches["month"],
				"day" => $matches["day"]
			)
		))){
			return $date->toString();
		}
		return null;
	}

	/**
	 * Formats datetime in ISO format to localized format.
	 *
	 * NOTE: Does not convert seconds!!!
	 * Zformatuje iso datetime az na hodiny a mninuty.
	 *
	 * <code>
	 * Atk14Locale::FormatDateTime("1982-12-31 12:33:00");
	 * </code>
	 * outputs "31.12.1982 12:33"
	 *
	 * @param string $iso_datetime datetime in ISO format
	 * @return string datetime in localized format
	 * @static
	 */
	static function FormatDateTime($iso_datetime){
		$iso_datetime = (string)$iso_datetime;
		if(strlen($iso_datetime)==0){ return ""; }

		$pattern = self::_GetDateTimePattern();

		return date($pattern,strtotime($iso_datetime));	
	}

	/**
	* Pokud rozpoznani datumu s casem selze, bude volano Atk14Locale::ParseDate().
	* 
	* Atk14Locale::ParseDateTime("31.12.2010 12:30"); // "2010-12-31 12:30:00"
	* Atk14Locale::ParseDateTime("31.12.2010"); // "2010-12-31 00:00:00"
	*/
	static function ParseDateTime($localized_datetime){
		$localized_datetime = (string)$localized_datetime;
		$pattern = self::_GetParseDateTimePattern();

		if(!$out = Atk14Locale::_ParseDateTime($localized_datetime,$pattern)){
			$out = Atk14Locale::ParseDate($localized_datetime);
			if($out){ $out .= " 00:00:00"; }
	 	}
		return $out;
	}

	static function FormatDateTimeWithSeconds($iso_datetime){
		$iso_datetime = (string)$iso_datetime;
		if(strlen($iso_datetime)==0){ return ""; }

		$pattern = self::_GetDateTimeWithSecondsPattern();

		return date($pattern,strtotime($iso_datetime));
	}

	/**
	* Atk14Locale::ParseDateTimeWithSeconds("31.12.2010 12:30:22"); // "2010-12-31 12:30:22"
	* Atk14Locale::ParseDateTimeWithSeconds("31.12.2010 12:30"); // "2010-12-31 12:30:00"
	* Atk14Locale::ParseDateTimeWithSeconds("31.12.2010"); // "2010-12-31 00:00:00"
	*/
	static function ParseDateTimeWithSeconds($localized_datetime){
		$localized_datetime = (string)$localized_datetime;
		$pattern = self::_GetParseDateTimeWithSecondsPattern();

		if(!$out = Atk14Locale::_ParseDateTime($localized_datetime,$pattern)){
			$out = Atk14Locale::ParseDateTime($localized_datetime);
		}
		return $out;
	}

	/**
	 * Returns decimal point for number formatting
	 *
	 * <code>
	 * echo Atk14Locale::DecimalPoint(); // e.g. "."
	 * </code>
	 */
	static function DecimalPoint(){
		$decimal_point = _("atk14.number_format.decimal_point");
		if($decimal_point == "atk14.number_format.decimal_point"){
			$decimal_point = ","; 
		}
		return $decimal_point;
	}

	/**
	 * Returns thousands separator for number formatting
	 *
	 * <code>
	 * echo Atk14Locale::ThousandsSeparator(); // e.g. " "
	 * </code>
	 */
	static function ThousandsSeparator(){
		$thousands_separator = _("atk14.number_format.thousands_separator");
		if($thousands_separator == "atk14.number_format.thousands_separator"){
			$thousands_separator = " "; 
		}
		return $thousands_separator;
	}

	/**
	 * Format number according to the current locale
	 *
	 * <code>
	 * echo Atk14Locale::FormatNumber(33); // "33"
	 * echo Atk14Locale::FormatNumber(-1234.56); // "-1 234,56"
	 *
	 * // setting decimal places
	 * echo Atk14Locale::FormatNumber(33, 2); // "33,00"
	 * echo Atk14Locale::FormatNumber(33.7777, 2); // "33,78"
	 * echo Atk14Locale::FormatNumber(33.7777, 0); // "34"
	 * </code>
	 */
	static function FormatNumber($number,$decimal_places = null){
		if(!strlen("$number")){ return; }

		if(strlen((string)$decimal_places)==0){ // null, "", false...
			$decimal_places = 0;
			if(preg_match('/\.(\d*)$/',"$number",$matches)){
				$decimal_places = strlen($matches[1]);
			}
		}

		$out = number_format($number,$decimal_places,self::DecimalPoint(),self::ThousandsSeparator());

		return $out;
	}

	static function _GetDatePattern(){
		$pattern = _("atk14.date_format");
		if(!$pattern || $pattern=="atk14.date_format"){ $pattern = "j.n.Y"; }
		return $pattern;
	}

	static function _GetDateTimePattern(){
		$pattern = _("atk14.datetime_format");
		if(!$pattern || $pattern=="atk14.datetime_format"){
			$pattern = self::_GetDatePattern();
			$pattern .= " H:i";
		}
		return $pattern;
	}

	static function _GetDateTimeWithSecondsPattern(){
		$pattern = _("atk14.datetime_with_seconds_format");
		if(!$pattern || $pattern=="atk14.datetime_with_seconds_format"){
			$pattern = self::_GetDatePattern();
			$pattern .= " H:i:s";
		}
		return $pattern;
	}

	static function _GetParseDatePattern(){
		$pattern = _("atk14.parse_date_pattern");
		if(!$pattern || $pattern=="atk14.parse_date_pattern"){
			$format_pattern = self::_GetDatePattern(); // "j.n.Y"
			$pattern = self::_ConvertFormatPatternToParsePattern($format_pattern);
		}

		return $pattern;
	}

	static function _GetParseDateTimePattern(){
		$pattern = _("atk14.parse_datetime_pattern");
		if(!$pattern || $pattern=="atk14.parse_datetime_pattern"){
			$format_pattern = self::_GetDateTimePattern(); // "j.n.Y H:i"
			$pattern = self::_ConvertFormatPatternToParsePattern($format_pattern);
		}

		return $pattern;
	}

	static function _GetParseDateTimeWithSecondsPattern(){
		$pattern = _("atk14.parse_datetime_with_seconds_pattern");
		if(!$pattern || $pattern=="atk14.parse_datetime_with_seconds_pattern"){
			$format_pattern = self::_GetDateTimeWithSecondsPattern(); // "j.n.Y H:i:s"
			$pattern = self::_ConvertFormatPatternToParsePattern($format_pattern);
		}

		return $pattern;
	}

	static function _ConvertFormatPatternToParsePattern($format_pattern){
		static $cache = [];
		if(!isset($cache[$format_pattern])){
			$pattern = preg_quote($format_pattern);
			$pattern = strtr($pattern,[
				" " => '\s+',
				"j" => '\s*(?<day>[0-9]{1,2})\s*',
				"d" => '\s*(?<day>[0-9]{1,2})\s*',
				"n" => '\s*(?<month>[0-9]{1,2})\s*',
				"m" => '\s*(?<month>[0-9]{1,2})\s*',
				"Y" => '\s*(?<year>[0-9]{4})\s*',
				"H" => '\s*(?<hours>[0-9]{1,2})\s*',
				"i" => '\s*(?<minutes>[0-9]{1,2})\s*',
				"s" => '\s*(?<seconds>[0-9]{1,2})\s*',
			]);
			$pattern = "/^$pattern$/";
			$cache[$format_pattern] = $pattern;
		}
		return $cache[$format_pattern];
	}

	static function _ParseDateTime($localized_datetime,$pattern){
		if(
			preg_match($pattern,$localized_datetime,$matches) &&
			($date = Date::ByDate(array(
				"year" => $matches["year"],
				"month" => $matches["month"],
				"day" => $matches["day"]
			))) &&
			($time = Atk14Locale::_ExtractTime($matches))
		){
			return $date->toString()." ".$time;
		}

		return null;
	}

	static function _ExtractTime($matches){
		$hours = (int)$matches["hours"];
		$minutes = (int)$matches["minutes"];
		$seconds = isset($matches["seconds"]) ? (int)$matches["seconds"] : 0;

		if(
			$hours>60 || $hours<0 ||
			$minutes>60 || $minutes<0 ||
			$seconds>60 || $seconds<0
		){
			return null;
		}

		return sprintf("%02d:%02d:%02s",$hours,$minutes,$seconds);
	}
}
