<?
/**
* Pocet vterin, po niz, pokud nebylo k session pristoupeno, dojde k jejimu smazani.
*/
if(!defined("SESSION_STORER_SESSION_MAX_LIFETIME")){
	define("SESSION_STORER_SESSION_MAX_LIFETIME",60 * 60 * 24 * 1);  // 1 den
}
/**
* Jmeno cookie pro ulozeni session_id 
*/
if(!defined("SESSION_STORER_COOKIE_NAME_SESSION")){
	define("SESSION_STORER_COOKIE_NAME_SESSION","_ses_");
}
/**
* Jmeno cookie pro testovani, zda ma uzivak aktivni cookies. 
*/
if(!defined("SESSION_STORER_COOKIE_NAME_CHECK")){
	define("SESSION_STORER_COOKIE_NAME_CHECK","_chk_");
}

/**
* Trida pro ukladani a nacitani promennych do session.
* Mechanismus funguje za predpokladu, ze ma uzivatel aktivovane cookies.
* Do session lze ukladat promenne libovolneho typu.
*
* Priklad pouziti:
*		$session = new SessionStorer();
*		if(!$session->cookiesEnabled()){
*			echo "CHYBA: aktivujte si prosim cookies";
*		}
*		$session->WriteValue("heslo_overeno",true);
*		//...
*		if($session->ReadValue("heslo_overeno") === true){
*			//...
*		}
*
* Potrebne jsou tyto dve tabulky:
*			CREATE SEQUENCE seq_sessions;
*			CREATE TABLE sessions(
*				id INT NOT NULL PRIMARY KEY DEFAULT NEXTVAL('seq_sessions'),
*				security VARCHAR(32) NOT NULL CHECK (security ~ '^[a-zA-Z0-9]{32}$'),
*				--
*				remote_addr VARCHAR(255) DEFAULT '' NOT NULL,
*				--
*				created TIMESTAMP DEFAULT NOW() NOT NULL,
*				last_access TIMESTAMP DEFAULT NOW() NOT NULL
*			);
*			CREATE INDEX in_sessions_lastaccess ON sessions (last_access);
*			
*			CREATE SEQUENCE seq_session_values;
*			CREATE TABLE session_values(
*				id INT NOT NULL PRIMARY KEY DEFAULT NEXTVAL('seq_session_values'),
*				session_id INT NOT NULL,
*				session_name VARCHAR(64) NOT NULL CHECK(LENGTH(session_name)>0),
*				--
*				key VARCHAR(128) NOT NULL CHECK(LENGTH(key)>0),
*				value TEXT DEFAULT '' NOT NULL,
*				expiration TIMESTAMP DEFAULT NULL,
*				--
*				CONSTRAINT unq_sessionvalues UNIQUE(session_id,session_name,key),
*				CONSTRAINT fk_sessionvalues_sessions FOREIGN KEY (session_id) REFERENCES sessions(id) ON DELETE CASCADE
*			);
*			CREATE INDEX in_sessionvalues_sessionid ON session_values(session_id);
*			CREATE INDEX in_sessionvalues_expiration ON session_values(expiration);
*
*/
class SessionStorer{
	/**
	* Jmeno session.
	* Nastavuje se v konstruktoru.
	* @var string
	*/
	var $_SessionName = "default";

	/**
	* Identifikator session.
	* @var integer
	*/
	var $_SessionId = null;

	/**
	*	Bezpecnostni nahodny retezec pro identifikaci session. 
	* @var string
	*/
	var $_SessionSecurity = null;

	/**
	* Do tohoto pole budou nacteny veskere hodnoty z databaze.
	* Pri cteni jsou hodnoty cteny z tohoto pole.
	* @var array
	*/
	var $_ValuesStore = array();

	var $_Initialized = false;

	/**
	*	Konstruktor.
	*
	*	@access public 
	*/
	function SessionStorer($session_name = "default"){
		settype($session_name,"string");
		$this->_SessionName = $session_name;

		if(isset($GLOBALS["dbmole"])){
			$this->_dbmole = &$GLOBALS["dbmole"];
		}elseif(class_exists("PgMole")){
			$this->_dbmole = &PgMole::GetInstance();
		}elseif(class_exists("OracleMole")){
			$this->_dbmole = &OracleMole::GetInstance();
		}
		setcookie(SESSION_STORER_COOKIE_NAME_CHECK,"1",time()+60*60*24*365*5,$this->_getWebDocumentRoot());
	}

	/**
	* Zde se zacne hrabat do databaze.
	*
	*/
	function _initialize(){
		if($this->_Initialized){ return; }
		if($this->cookiesEnabled()){
			list($id,$security) = $this->_ReadSessionCookie();
			if(!$this->_CheckSession($id,$security)){
				$this->_CreateNewSession();
			}
			$this->_GarbageCollection();

			$this->_ReadAllValues();
			$this->_SetSessionCookie();
		}
		$this->_Initialized = true;
	}

	/**
	* Nacte hodnotu id a security ze session cookie.
	* 
	* Pokud neni session cookie nastaven, vrati array(null,null)
	* 
	* @access private
	* @return array												array($id,$security)
	*/
	function _ReadSessionCookie(){
		$id = null;
		$security = null;

		if(isset($GLOBALS["_COOKIE"][SESSION_STORER_COOKIE_NAME_SESSION])){
			$_cookie = $GLOBALS["_COOKIE"][SESSION_STORER_COOKIE_NAME_SESSION];
			settype($_cookie,"string");
			$_ar = explode(".",$_cookie);
			if(
				preg_match("/^[0-9]{1,}$/",$_ar[0]) &&
				preg_match("/^[a-zA-Z0-9]{32}$/",$_ar[1])
			){
				settype($_ar[0],"integer");
				$id = $_ar[0];
				$security = $_ar[1];
			}
		}

		return array($id,$security);
	}

	/**
	* Overi, ze daba kombinace $id a $security existuje.
	* 
	* @access public
	* @return bool					true -> dana kombinace $id a $security je ok - tato session existuje
	*												false -> session neexistuje
	*/
	function _CheckSession($id,$security){
		if(!isset($id) || !isset($security)){ return false; }

		settype($id,"integer");
		settype($security,"string");

		$session_ok = false;
		$rec_security = $this->_dbmole->SelectSingleValue("
			SELECT
				security
			FROM
				sessions
			WHERE
				id=:id
		",array(":id" => $id));
		if(isset($rec_security) && $rec_security==$security){
			$session_ok = true;
			$this->_dbmole->doQuery("UPDATE sessions SET last_access=:now WHERE id=:id AND last_access<=:before_5_minutes",array(
				":id" => $id,
				":now" => $this->_getNow(),
				":before_5_minutes" => $this->_getIsoDate($this->_getCurrentTime()-60*5), // aktualizujeme last_access nejvyse 1x za 5 minut
			));
		}

		if($session_ok){
			$this->_SessionId = $id;
			$this->_SessionSecurity = $security;
		}

		return $session_ok;
	}

	/**
	* Vytvori novy zaznam v tabulce sessions.
	* Nastavi vlastni $this->_SessionId a $this->_SessionSecurity.
	*
	* @access private
	* @return bool					true -> ok, zaznam vytvoren
	* 											false -> doslo k chybe pri vykonavani SQL
	*/
	function _CreateNewSession(){
		$id = null;
		$security = SessionStorer::_RandomString();

		$id = $this->_dbmole->SelectSequenceNextval("seq_sessions");

		$this->_dbmole->doQuery("
			INSERT INTO sessions(
				id,
				security,
				remote_addr
			) VALUES(
				:id,
				:security,
				:remote_addr
			)
		",array(
			":id" => $id,
			":security" => $security,
			":remote_addr" => $GLOBALS["_SERVER"]["REMOTE_ADDR"]
		));

		$this->_SessionId = $id;
		$this->_SessionSecurity = $security;

		return true;
	}

	function _RandomString($length = 32){
		settype($length,"integer");
		srand ((double) microtime() * 1000000);
		$temp = array("a","i","o","s","t","u","v","3","4","5","8","B","C","D","E","F","7","G","H","I","J","K","L","M","N","O","j","k","l","6","P","Q","W","b","c","d","e","f","g","h","p","q","r","x","y","z","0","1","S","T","U","w","2","9","A","R","V","m","n");
		$rand = array_rand($temp,$length);
		$out = '';
		for($i=0;$i<sizeof($rand);$i++){
			$out .= $temp[$rand[$i]];
		}
		return $out;
	}

	/**
	* Nastavi session cookie.
	*
	* @access private
	*/
	function _SetSessionCookie(){
		$_expire_time = 0; // session cookie se nastavi do zavreni prohlizece
		$_cokie_value = "$this->_SessionId.$this->_SessionSecurity";
		setcookie(SESSION_STORER_COOKIE_NAME_SESSION,$_cokie_value,$_expire_time,$this->_getWebDocumentRoot());
	}

	/**
	* Smaze session cookie.
	*
	* @access private
	*/
	function _ClearSessionCookie(){
		$_expire_time = $this->_getCurrentTime() - 60 * 60 * 24 * 365;
		setcookie(SESSION_STORER_COOKIE_NAME_SESSION,"",$_expire_time,$this->_getWebDocumentRoot());
	}

	/**
	* Nacte vsechny hodnoty ulozene v aktualni session do $this->_ValuesStore.
	*
	* @access private
	*/
	function _ReadAllValues(){
		if(!isset($this->_SessionId)){
			return;
		}
		$rows = $this->_dbmole->SelectRows("
				SELECT
					key,
					value,
					expiration
				FROM
					session_values
				WHERE
					session_id=:session_id AND
					session_name=:session_name
			",array(
				":session_id" => $this->_SessionId,
				":session_name" => $this->_SessionName
		));
		while(list(,$row) = each($rows)){
			$_expiration = null;
			if(isset($row["expiration"])){
				$_expiration = strtotime($row["expiration"]);
			}
			$this->_ValuesStore[$row["key"]] = array(
				"packed_value" => $row["value"],
				"expiration" => $_expiration
			);
		}
	}

	/**
	* Provede Garbage Collection.
	* 
	* @access private
	*	@return null
	*/
	function _GarbageCollection(){
		$_seconds = SESSION_STORER_SESSION_MAX_LIFETIME;
		settype($_seconds,"integer");
		$this->_dbmole->doQuery("
			DELETE FROM sessions WHERE
				last_access<:last_access
		",array(
			":last_access" => $this->_getIsoDate($this->_getCurrentTime() - $_seconds)
		));

		$this->_dbmole->doQuery("
			DELETE FROM session_values WHERE
				expiration<:now
		",array(
			":now" => $this->_getNow()
		));
	}


	/**
	* Zjisti, zda ma klient aktivni cookies.
	* Jen pokud jsou aktivni cookies, budou session hodnoty ulozeny do databaze.
	* V opacnem pripade budou po skonceni HTTP requestu ztraceny.
	* 
	* @access public
	* @return bool
	*/
	function cookiesEnabled(){
		return isset($GLOBALS["_COOKIE"][SESSION_STORER_COOKIE_NAME_CHECK]);
	}

	/**
	* Precte drive ulozenou hodnotu ze session.
	* Popkud hodnota nebyla nebude nalezena, bude vracen null.
	*
	* @access public
	* @param string $key
	* @return mixed								bude vraceno ten typ promenne, ktery byl ulozen
	*															null v pripade, ze hodnota neni ulozena
	*/
	function ReadValue($key){
		settype($key,"string");

		$this->_initialize();
		
		if(isset($this->_ValuesStore[$key]) && (!isset($this->_ValuesStore[$key]["expiration"]) || $this->_ValuesStore[$key]["expiration"]>$this->_getCurrentTime())){
			return $this->_UnpackValue($this->_ValuesStore[$key]["packed_value"]);
		}

		return null;
	}

	/**
	* Zapise hodnotu do session.
	*
	* @param string $key
	* @param mixed $value					muze byt string, pole, int...
	* @param int $expiration			pocet vterin, po kterou ma hodnota platit
	*/
	function WriteValue($key,$value,$expiration = null){
		settype($key,"string");
		if(isset($expiration)){
			settype($expiration,"integer");
		}

		$this->_initialize();

		$packed_value = $this->_PackValue($value);

	
		if(!isset($value) && isset($this->_SessionId)){
			$this->_dbmole->doQuery("
				DELETE FROM session_values WHERE
					session_id=:session_id AND
					session_name=:session_name AND
					key=:key
			",array(
				":session_id" => $this->_SessionId,
				":session_name" => $this->_SessionName,
				":key" => $key
			));
		}elseif(isset($this->_SessionId)){

			$id = $this->_dbmole->SelectSingleValue("
				SELECT id FROM session_values WHERE
					session_id=:session_id AND
					session_name=:session_name AND
					key=:key
			",array(
				":session_id" => $this->_SessionId,
				":session_name" => $this->_SessionName,
				":key" => $key
			));

			if(isset($id)){ $this->_dbmole->doQuery("DELETE FROM session_values WHERE id=:id",array(":id" => $id)); }


			$_expiration_sql = null;
			if(isset($expiration)){
				$_expiration_sql = $this->_getIsoDate($this->_getCurrentTime()+$expiration);
			}

			$options = array();
			if($this->_dbmole->getDatabaseType()=="oracle"){ $options["clobs"] = array("value"); }

			$this->_dbmole->InsertIntoTable("session_values",array(
				"id" => 						$this->_dbmole->SelectSequenceNextval("seq_session_values"),
				"session_id" => 		$this->_SessionId,
				"session_name" => 	$this->_SessionName,
				"key" => 						$key,
				"value" => 					$packed_value,
				"expiration" => 		$_expiration_sql
			),$options);
		}

		if(isset($value)){
			$_expiration = null;
			if(isset($expiration)){
				$_expiration = $this->_getCurrentTime() + $expiration;
			}
			$this->_ValuesStore[$key] = array(
				"packed_value" => $packed_value,
				"expiration" => $_expiration
			);

		}else{

			unset($this->_ValuesStore[$key]);

		}
	}

	/**
	* Cleares all values.
	*/
	function clear(){
		$this->_initialize();
		foreach($this->_ValuesStore as $k => $v){ $this->WriteValue($k,null); }
	}


	/**
	* Sbali hodnotu do stringu.
	* Provadi PHP serializaci a prekodovani do base64.
	* Vysledek muze by s uspechem ulozen do databaze.
	*
	* @access private
	* @param mixed $value
	*	@return string
	*/
	function _PackValue($value){
		if(!isset($value)){
			return "";
		}
		return chunk_split(base64_encode(serialize($value)),74);
	}

	/**
	* Provede rozbaleni hodnoty z drive zabaleneho stringu.
	*
	*	@access private
	*	@param string $packed_value
	* @return mixed
	*/
	function _UnpackValue($packed_value){
		settype($packed_value,"string");
		if(strlen($packed_value)==0){
			return null;
		}
		return unserialize(base64_decode($packed_value));
	}

	function _getCurrentTime(){
		return defined("CURRENT_TIME") ? CURRENT_TIME : time();
	}

	function _getNow(){
		return $this->_getIsoDate($this->_getCurrentTime());
	}

	function _getIsoDate($time){
		return date("Y-m-d H:i:s",$time);
	}

	function _getWebDocumentRoot(){
		global $ATK14_GLOBAL;	

		if(isset($ATK14_GLOBAL)){
			return $ATK14_GLOBAL->getBaseHref();
		}
	  if(defined("WEB_DOCUMENT_ROOT")){
			return WEB_DOCUMENT_ROOT;
		}
		return "/";
	}
}
?>
