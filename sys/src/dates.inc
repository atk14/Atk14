<?php
/**
* Trida dates nabizi staticke metody pro praci s datumy.
*
*	Datum je reprezentovan stringem. Format musi byt iso (YYYY-mm-dd).
*
* Priklady pouziti:
* 
*	 //overeni spravnosti datumu
*	 if(!dates::check_date("2004-01-01")){
*			echo "spatny datum";
*	 }
* 
*	 //pridani dnu k datumu
*  $new_date = dates::add_days("2004-01-01",10);
*	 $new_date = dates::add_days("2004-01-01",-10);
*
*	 //spocitani dnu
*	 $days = dates::count_days("2004-01-31","2004-02-10"); //vrati 11
*
* @changelog
* 	2006-05-30: pridany metody get_last_date() a get_first_date()
*		2007-03-01: pridany metody get_last_date_by_date() a get_first_date_by_date()
* 
*/
class dates{
	
	/**
	* 
	* @access public
	* @static
	*/
	function now(){
		return date("Y-m-d");
	}

	/**
	*
	* @access public
	* @static
	* @param int $year
	* @param int $month
	* @param int $day
	* @return string
	*/
	function make_date($year,$month,$day){
		settype($year,"integer");
		settype($month,"integer");
		settype($day,"integer");

		$_year = "$year";
		$_month = strlen($month)==1 ? "0$month" : "$month";
		$_day = strlen($day)==1 ? "0$day" : "$day";

		$date = "$_year-$_month-$_day";

		if(!dates::check_date($date)){ return ""; }

		return $date;
	}

	/**
	* Overi spravnost datumu.
	*
	* @access	public
	* @static
	* @param string $date					datum
	* @return bool								true -> datum ma spravny format spravny a den existuje
	*															false -> datum ma spatny format nebo takovy datum neexistuje
	*/
	function check_date($date){
		settype($date,"string");
		
		$out = false;

		if(preg_match('/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/',$date,$matches)){
			$_year = $matches[1];
			$_month = $matches[2];
			$_day = $matches[3];
			
			$_stat = checkdate($_month,$_day,$_year);

			if($_stat){
				$out = true;
			}
		}
		
		return $out;
	}

	/**
	* Prida k datumu pocet dni.
	*  
	* @access	public
	* @static
	* @param string $date					datum
	* @param int $days						pocet dni, ktery ma byt k datu pridan
	* @return string							true -> datum ma spravny format spravny a den existuje
	*															false -> datum ma spatny format nebo takovy datum neexistuje
	*/
	function add_days($date,$days){
		settype($date,"string");
		settype($days,"integer");

		if(!dates::check_date($date)){
			return false;
		}

		if($days==0){
			return $date;
		}

		$int_date = dates::_to_int($date);

		$int_date = dates::_add_days($int_date,$days);
		if(is_int($int_date)){
			return false;
		}

		return dates::_to_string($int_date);
	}

	function add_years($date,$years){
		settype($date,"string");
		settype($years,"integer");

		if(!dates::check_date($date)){
			return false;
		}
		
		$a = dates::_to_ar($date);
		($date = dates::make_date($a["y"]+$years,$a["m"],$a["d"])) ||
		($date = dates::make_date($a["y"]+$years,$a["m"],$a["d"]-1)); // prestupny rok a napr. resime 2008-02-29 + 1 rok -> 2009-02-29
		return $date;
	}

	function add_months($date,$months){
		settype($date,"string");
		settype($months,"integer");

		if(!dates::check_date($date)){
			return false;
		}
		
		$a = dates::_to_ar($date);

		$a["m"] += $months;
		$a["y"] += floor($a["m"] / 12);
		$a["m"] = $a["m"] - (floor($a["m"] / 12)*12);
		if($a["m"]<=0){ $a["m"] = 12 + $a["m"]; $a["y"]--; }
		
		($date = dates::make_date($a["y"],$a["m"],$a["d"])) ||
		($date = dates::make_date($a["y"],$a["m"],$a["d"]-1)) ||
		($date = dates::make_date($a["y"],$a["m"],$a["d"]-2)) ||
		($date = dates::make_date($a["y"],$a["m"],$a["d"]-3));
		return $date;
	}
	
	/**
	* Spocita rozdil mezi datumy ve dnech.
	* Pokud jsou datumy stejne, vrati 0.
	*
	* @static
	* @access public
	* @return string 	$date_from	datum od)
	* @return string 	$date_to		datum do
	* @return int									pocet dni; nebo (bool) false v pripade, ze nejaky datum je zadan spatne
	*/
	function get_difference($date_from,$date_to){
		settype($date_from,"string");
		settype($date_to,"string");

		$_stat = dates::count_days($date_from,$date_to);
		if(is_bool($_stat)){
			return false;
		}

		if($_stat>0){
			$_stat--;
		}
		if($_stat<0){
			$_stat++;
		}
		return $_stat;
	}
	
	/**
	* Spocita pocet dnu od mezi datumy.
	* Pokud jsou datumy stejne, vrati 1.
	*
	* Funkce dates::get_difference() spocita rovnez dny mezi datumy,
	*	ale vrati 0 v pripade, ze se datumy sobe rovnaji.
	*
	* @static
	* @access public
	* @return string 	$date_from	datum od
	* @return string 	$date_to		datum do
	* @return int									pocet dni; nebo (bool) false v pripade, ze nejaky datum je zadan spatne
	*/
	function count_days($date_from,$date_to){
		settype($date_from,"string");
		settype($date_to,"string");

		$out = false;

		if(!dates::check_date($date_from) || !dates::check_date($date_to)){
			return false;
		}

		$_from = dates::_to_int($date_from);
		$_to = dates::_to_int($date_to);

		if($_from==$_to){
			return 1;
		}

		$_sign = 1;
		if($_from>$_to){
			$_sign = -1;
		}

		$_days = 1;
		$_current = $_from;
		while(1){
			if($_current==$_to){
				break;
			}
			$_days++;
			$_current = dates::_add_days($_current,$_sign * 1);
		}

		$out = $_sign * $_days;
		return $out;
	}

	/**
	* Spocita roky mezi datumy.
	* Velmi uzitecne, pokud se zjistuje, kolik roku ma ten ktery clovek podle data narozeni.
	*
	* TODO: pokud se tam zamota prestupny rok, vysledek nemusi byt presny
	* var_dump(dates::count_years("2000-03-01","2001-03-01")); -> 0.997260273973
	* 
	*
	* @param string $date_from			"1977-07-25"
	* @param string $date_to				"2007-10-23"
	* @return float									30.2465753425
	*/
	function count_years($date_from,$date_to){
		settype($date_from,"string");
		settype($date_to,"string");

		if(!dates::check_date($date_from) || !dates::check_date($date_to)){
			return null;
		}

		$from = dates::_to_ar($date_from);
		$to = dates::_to_ar($date_to);

		$celych_let = ($date_to - $date_from);

		$_days1 = (float)dates::count_days("$from[y]-01-01",$date_from);
		$_days2 = (float)dates::count_days("$to[y]-01-01",$date_to);

		// drobny hack pro prestupne roky
		if($_days1==366){ $_days1 = 365; }
		if($_days2==366){ $_days2 = 365; }

		$desetinna_cast = (float)(1.0 - ((365.0 - ($_days2 - $_days1))/365.0));

		return $celych_let + $desetinna_cast;
	}


	/**
	* Porovna datumy.
	*
	* @access public
	* @static
	* @param string $first_date					prvni datum
	* @param string $second_date				druhy datum
	* @return int 											1 -> prvni datum je novejsi nez druhy
	* 																	0 -> oba datumy jsou stejne
	*																		-1 -> druhy datum je novejsi nez prvni
	*																		(bool) false -> v pripade spatneho datumu na vstupu
	*/
	function compare($first_date,$second_date){
		settype($first_date,"string");
		settype($second_date,"string");

		if(!dates::check_date($first_date) || !dates::check_date($second_date)){
			return false;
		}

		$_first_date = dates::_to_int($first_date);
		$_second_date = dates::_to_int($second_date);

		if($_first_date>$_second_date){
			return 1;
		}elseif($_first_date==$_second_date){
			return 0;
		}elseif($_first_date<$_second_date){
			return -1;
		}else{
			return false;
		}
	}

	/**
	* Vrati posledni den v danem mesici a roce.
	* 
	* @public public
	* @static
	* @param int $month					mesic (1-12)
	* @param int $year					rok (2005)
	* @return int								posledni den v mesici (1-31)
	*														nebo null, pokud je neco spatneho na vstupu
	*/
	function get_last_day($month,$year){
		settype($month,"integer");
		settype($year,"integer");
		
		if($month<1 || $month>12){
			return null;
		}
		if($year<1000 || $year>9999){
			return null;
		}
		
		switch($month){
			case 1:
				return 31;
			case 2:
				if(dates::check_date("$year-02-29")){
					return 29;
				}
				return 28;
			case 3:
				return 31;
			case 4:
				return 30;
			case 5:
				return 31;
			case 6:
				return 30;
			case 7:
				return 31;
			case 8:
				return 31;
			case 9:
				return 30;
			case 10:
				return 31;
			case 11:
				return 30;
			case 12:
				return 31;						
		}
		return null;
	}

	/**
	* Vrati datum posledniho dne v mesici.
	*
	* @static
	* @access pulic
	* @param integer $month
	* @param integer $year
	* @return string								iso datum, nebo null
	*/
	function get_last_date($month,$year){
		settype($month,"integer");
		settype($year,"integer");

		$day = dates::get_last_day($month,$year);
		if(!isset($day)){
			return null;
		}
		
		$_year = "$year";
		$_month = "$month";
		$_day = "$day";

		if(strlen($_month)==1){ $_month = "0$_month"; }
		if(strlen($_day)==1){ $_day = "0$_day"; }

		$date = "$_year-$_month-$_day";
		return $date;
	}

	/**
	* Vrati datum posledniho dne v mesici podle daneho datumu.
	* 
	* @static
	* @access public
	* @param string $date					iso datum (napr. "2008-02-15")
	* @return string 							iso datum, nebo null (napr. "2008-02-29")
	*/
	function get_last_date_by_date($date){
		settype($date,"string");
		
		if(!dates::check_date($date)){
			return null;
		}

		$year = (int)substr($date,0,4);
		$month = (int)substr($date,5,2);

		return dates::get_last_date($month,$year);
	}

	/**
	* Vrati datum prvniho dne v mesici.
	* 
	* @static
	* @access pulic
	* @return string								iso datum, nebo null
	*/
	function get_first_date($month,$year){

		settype($month,"integer");
		settype($year,"integer");

		$_year = "$year";
		$_month = "$month";
		$_day = "01";

		if(strlen($_month)==1){ $_month = "0$_month"; }
		
		$date = "$_year-$_month-$_day";
		if(!dates::check_date($date)){
			$date = null;
		}

		return $date;
	}

	/**
	* Vrati datum prvniho dne v mesici podle daneho datumu.
	* 
	* @static
	* @access public
	* @param string $date					iso datum (napr. "2008-02-15")
	* @return string 							iso datum, nebo null (napr. "2008-02-01")
	*/
	function get_first_date_by_date($date){
		settype($date,"string");
		
		if(!dates::check_date($date)){
			return null;
		}

		$year = (int)substr($date,0,4);
		$month = (int)substr($date,5,2);

		return dates::get_first_date($month,$year);
	}

	/**
	* Prevede datum pro vnitrni reprezentaci na integer.
	*
	* @access private
	* @static
	* @param string $date					datum
	* @return int									datum prevedeny na int
	*/
	function _to_int($date){
		$ar = dates::_to_ar($date);
		return $ar["y"]*10000 + $ar["m"]*100 + $ar["d"];
	}

	function _to_ar($date){
		settype($date,"string");
		$ar = split("-",$date);
	
		$_year = (int)$ar[0];
		$_month = (int)$ar[1];
		$_day = (int)$ar[2];

		return array(
			"y" => $_year,
			"m" => $_month,
			"d" => $_day
		);
	}

	/**
	* Prevede integer opet na datum reprezentovany stringem.
	*
	* @access private
	* @static
	* @param mixed $in_date			datum reprezentovany integerem nebo polem
	* @return string							datum reprezentovany stringem v iso formatu
	*/
	function _to_string($in_date){
		if(is_array($in_date)){
			$_day = $in_date["d"];
			$_month = $in_date["m"];
			$_year = $in_date["y"];
		}else{
			settype($in_date,"integer");
			$_day = $in_date%100;
			$_month = floor(($in_date%10000)/100);
			$_year = floor(($in_date%100000000)/10000);
		}

		settype($_day,"string");
		settype($_month,"string");
		settype($_year,"string");

		if(strlen($_day)==1){ $_day = "0$_day";}
		if(strlen($_month)==1){ $_month = "0$_month";}
		
		return "$_year-$_month-$_day";
	}

	/**
	* Prida k datumu v integer formatu dny.
	*
	* @access private
	* @static
	* @param int $int_date					datum ve formatu integer
	* @param int $days 							pocet dni
	* @return int 									vysledny datum ve formatu integer
	* 															(bool) false -> pokud je na vstupu nesmyslne datum	
	*/
	function _add_days($int_date,$days){
		settype($int_date,"integer");
		settype($days,"integer");

		if($days==0){
			return $int_date;
		}

		$_sign = 1;
		if($days<0){
			$_sign = -1;
		}
		$_days = abs($days);
		
		$_day = $int_date%100;
		$_month = floor(($int_date%10000)/100);
		$_year = floor(($int_date%100000000)/10000);

		for($i=1;$i<=$_days;$i++){
			$_new_day = $_day;
			$_new_month = $_month;
			$_new_year = $_year;

			//pricitani
			if($_sign==1){
				$_new_day++;
				//zkontrolujeme datum, pokud je den vetsi nez 28
				//pokud kontrola dopadne spatne, nastavime den na 1 a zvysime mesic
				if($_new_day>28){
					if(!checkdate($_new_month,$_new_day,$_new_year)){
						$_new_day = 1;
						$_new_month++;
						if(!checkdate($_new_month,$_new_day,$_new_year)){
							$_new_month = 1;
							$_new_year++;
							if(!checkdate($_new_month,$_new_day,$_new_year)){
								return false;
							}
						}
					}
				}
			}

			//odecitani
			if($_sign==-1){
				$_new_day--;
				if($_new_day==0){
					$_new_day = 31;
					$_new_month--;
					if($_new_month==0){
						$_new_month = 12;
						$_new_year--;
					}
					//pohybovalo se s dnem, overime platnost data
					while(true){
						if(checkdate($_new_month,$_new_day,$_new_year)){
							break;
						}
						$_new_day--;
						if($_new_day<28){
							return false;
						}
					}
				}
			}

			$_day = $_new_day;
			$_month = $_new_month;
			$_year = $_new_year;

		}
		return $_year*10000 + $_month*100 + $_day;
	}
}
?>
