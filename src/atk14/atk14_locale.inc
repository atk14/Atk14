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
	 * Formats Date
	 * 
	 * <code>
	 * Atk14Locale::FormatDate("1982-12-31"); // "31.12.1982"
	 * </code>
	 *
	 * @param string $iso_date date in ISO format
	 * @return string date in localized format
	 * @static
	 */
	static function FormatDate($iso_date){
		if(strlen($iso_date)==0){ return ""; }

		$pattern = _("atk14.date_format");
		if($pattern == "atk14.date_format"){ $pattern = "j.n.Y"; }

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
		$pattern = _("atk14.parse_date_pattern");
		if($pattern == "atk14.parse_date_pattern"){ $pattern = "/^(?<day>[0-9]{1,2})\\.(?<month>[0-9]{1,2})\\.(?<year>[0-9]{4})$/"; }

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
		if(strlen($iso_datetime)==0){ return ""; }

		$pattern = _("atk14.datetime_format");
		if($pattern == "atk14.datetime_format"){ $pattern = "j.n.Y H:i"; }

		return date($pattern,strtotime($iso_datetime));	
	}

	/**
	* Pokud rozpoznani datumu s casem selze, bude volano Atk14Locale::ParseDate().
	* 
	* Atk14Locale::ParseDateTime("31.12.2010 12:30"); // "2010-12-31 12:30:00"
	* Atk14Locale::ParseDateTime("31.12.2010"); // "2010-12-31 00:00:00"
	*/
	static function ParseDateTime($localized_datetime){
		$pattern = _("atk14.parse_datetime_pattern");
		if($pattern == "atk14.parse_datetime_pattern"){ $pattern = "/^(?<day>[0-9]{1,2})\\.(?<month>[0-9]{1,2})\\.(?<year>[0-9]{4}) (?<hours>[0-9]{2}):(?<minutes>[0-9]{2})$/"; }

		if(!$out = Atk14Locale::_ParseDateTime($localized_datetime,$pattern)){
			$out = Atk14Locale::ParseDate($localized_datetime);
			if($out){ $out .= " 00:00:00"; }
	 	}
		return $out;
	}

	static function FormatDateTimeWithSeconds($iso_datetime){
		if(strlen($iso_datetime)==0){ return ""; }

		$pattern = _("atk14.datetime_with_seconds_format");
		if($pattern == "atk14.datetime_with_seconds_format"){ $pattern = "j.n.Y H:i:s"; }

		return date($pattern,strtotime($iso_datetime));
	}

	/**
	* Atk14Locale::ParseDateTimeWithSeconds("31.12.2010 12:30:22"); // "2010-12-31 12:30:22"
	* Atk14Locale::ParseDateTimeWithSeconds("31.12.2010 12:30"); // "2010-12-31 12:30:00"
	* Atk14Locale::ParseDateTimeWithSeconds("31.12.2010"); // "2010-12-31 00:00:00"
	*/
	static function ParseDateTimeWithSeconds($localized_datetime){
		$pattern = _("atk14.parse_datetime_with_seconds_pattern");
		if($pattern == "atk14.parse_datetime_with_seconds_pattern"){ $pattern = "/^(?<day>[0-9]{1,2})\\.(?<month>[0-9]{1,2})\\.(?<year>[0-9]{4}) (?<hours>[0-9]{2}):(?<minutes>[0-9]{2}):(?<seconds>[0-9]{2})$/"; }

		if(!$out = Atk14Locale::_ParseDateTime($localized_datetime,$pattern)){
			$out = Atk14Locale::ParseDateTime($localized_datetime);
		}
		return $out;
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
