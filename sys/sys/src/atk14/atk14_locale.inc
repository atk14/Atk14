<?
class Atk14Locale{

	/**
	* Atk14Locale::FormatDate("1982-12-31"); // "31.12.1982"
	*/
	function FormatDate($iso_date){
		if(strlen($iso_date)==0){ return ""; }

		$pattern = _("atk14.date_format");
		if($pattern == "atk14.date_format"){ $pattern = "j.n.Y"; }

		return date($pattern,strtotime($iso_date));
	}

	/**
	* Atk14Locale::ParseDate("31.12.1982"); // "1982-12-31"
	*/
	function ParseDate($localized_date){
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
	* Zformatuje iso datetime az na hodiny a mninuty.
	* Nikoli vteriny!!!
	*
	* Atk14Locale::FormatDateTime("1982-12-31 12:33:00"); // "31.12.1982 12:33"
	*/
	function FormatDateTime($iso_datetime){
		if(strlen($iso_datetime)==0){ return ""; }

		$pattern = _("atk14.datetime_format");
		if($pattern == "atk14.datetime_format"){ $pattern = "j.n.Y H:i"; }

		return date($pattern,strtotime($iso_datetime));	
	}

	function ParseDateTime($localized_datetime){
		$pattern = _("atk14.parse_datetime_pattern");
		if($pattern == "atk14.parse_datetime_pattern"){ $pattern = "/^(?<day>[0-9]{1,2})\\.(?<month>[0-9]{1,2})\\.(?<year>[0-9]{4}) (?<hours>[0-9]{2}):(?<minutes>[0-9]{2})$/"; }

		return Atk14Locale::_ParseDateTime($localized_datetime,$pattern);
	}

	function FormatDateTimeWithSeconds($iso_datetime){
		if(strlen($iso_datetime)==0){ return ""; }

		$pattern = _("atk14.datetime_with_seconds_format");
		if($pattern == "atk14.datetime_with_seconds_format"){ $pattern = "j.n.Y H:i:s"; }

		return date($pattern,strtotime($iso_datetime));
	}

	function ParseDateTimeWithSeconds($localized_datetime){
		$pattern = _("atk14.parse_datetime_with_seconds_pattern");
		if($pattern == "atk14.parse_datetime_with_seconds_pattern"){ $pattern = "/^(?<day>[0-9]{1,2})\\.(?<month>[0-9]{1,2})\\.(?<year>[0-9]{4}) (?<hours>[0-9]{2}):(?<minutes>[0-9]{2}):(?<seconds>[0-9]{2})$/"; }

		return Atk14Locale::_ParseDateTime($localized_datetime,$pattern);
	}

	function _ParseDateTime($localized_datetime,$pattern){
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

	function _ExtractTime($matches){
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
?>
