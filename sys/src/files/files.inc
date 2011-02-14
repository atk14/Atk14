<?
/**
* Trida poskytuje staticke funkce pro praci se soubory.
*
* @changelog
*		2005-10-21: oprava v metode _recursive_unlink_dir() 
*
* @package  extra_classes
* @author		Tomek
* @access   public
* @version 	20050215
*/
class files{

	/**
	* Vytvori adresar.
	*
	*	Vytvori rovnez vsechny nadadresare, pokud neexistuji.
	* Vrati pocet takto vytvorenych adresaru.
	* Pokud adresar jiz existuje, je vracen int 0.
	* Adresare jsou vytvoreny s pravy 0777.
	*
	* @access public
	* @static
	* @param string $dirname				jmeno adresare
	* @param boolean &$error 				priznak chyby
	* @param string	 &$error_str 		popis chyby
	* @return int										pocet adresaru, ktere byly vytvoreny
	*/
	function mkdir($dirname,&$error,&$error_str){
		$out = 0;
		$error = false;
		$error_str = "";
		$old_umask = umask(0);
		$ar = explode("/",$dirname);
		$current_dir = "";
		for($i=0;$i<sizeof($ar);$i++){
			if($i!=0){ $current_dir .= "/";}
			$current_dir .= $ar[$i];
			if($ar[$i]=="." || $ar[$i]==".." || $ar[$i]==""){
				continue;
			}
			if(!file_exists($current_dir)){
				$_old_umask = umask(0);
				$stat = mkdir($current_dir,0777);
				umask($_old_umask);
				if(!$stat){
					$out = 0;
					break;
				}
				$out ++;
			}
		}
		umask($old_umask);
		return $out;
	}

	/**
	* Zkopiruje soubor.
	*
	* V pripade, ze je cilovy soubor nove vytvaren, je vytvoren s pravy 0666.
	*
	* @access public
	* @static
	* @param string 	$from_file			zdrojovy soubor
	* @param string 	$to_file				cilovy soubor
	* @param boolean 	&$error 				priznak chyby
	* @param string 	&$error_str 		popis chyby
	* @return int											pocet zkopirovanych bytu
	*/
	function copy_file($from_file,$to_file,&$error,&$error_str){
		$bytes = 0;
		$error = false;
		$error_str = "";

		settype($from_file,"string");
		settype($to_file,"string");
		
		$in = fopen($from_file,"r");
		if(!$in){
			$error = true;
			$error_str = "can't open input file for reading";
			return $bytes;
		}
		$__target_file_exists = false;
		if(file_exists($to_file)){
			$__target_file_exists = true;
		}
		$out = fopen($to_file,"w");
		if(!$out){
			$error = true;
			$error_str = "can't open output file for writing";
			return $bytes;
		}

		$buffer = "";
		while(!feof($in) && $in){
			$buffer = fread($in,4096);
			fwrite($out,$buffer,strlen($buffer));
			$bytes += strlen($buffer);
		}

		
		fclose($in);
		fclose($out);
		
		//menit modsouboru, jenom, kdyz soubor drive neexistoval
		if(!$__target_file_exists){
			$_old_umask = umask(0);
			$_stat = chmod($to_file,0666);
			umask($_old_umask);

			if(!$_stat && $error==false){
				$error = true;
				$error_str = "failed to do chmod on $to_file";
				return $bytes;
			}
		}

		return $bytes;
	}

	/**
	* Zapise obsah retezce do souboru.
	*
	* V pripade, ze je cilovy soubor nove vytvaren, je vytvoren s pravy 0666.
	*
	* @access public
	* @static
	* @param string 	$file						jmeno souboru
	* @param string 	$content				retezec, ktery ma byt zapsan
	* @param boolean 	&$error 				priznak chyby
	* @param string 	&$error_str 		popis chyby
	* @return int											pocet zkopirovanych bytu
	*/
	function write_to_file($file,$content,&$error,&$error_str){
		$bytes = 0;
		$error = false;
		$error_str = "";

		settype($file,"string");
		settype($content,"string");

		$_file_exists = false;
		if(file_exists($file)){
			$_file_exists = true;
		}

		if($_file_exists){
			if(is_dir($file)){
				$error = true;
				$error_str = "$file is a directory";
				return 0;
			}
		}

		$f = fopen($file,"w");
		if(!$f){
			$error = true;
			$error_str = "failed to open file for writing";
			return 0;
		}
		$bytes = fwrite($f,$content,strlen($content));
		if($bytes!=strlen($content)){
			$error = true;
			$error_str = "failed to write ".strlen($content)." bytes; writen ".$bytes;
			return $bytes;
		}
		fclose($f);

		//menit modsouboru, jenom, kdyz soubor drive neexistoval
		if(!$_file_exists){
			$_old_umask = umask(0);
			$_stat = chmod($file,0666);
			umask($_old_umask);
	
			if(!$_stat && $error==false){
				$error = true;
				$error_str = "failed to do chmod on $file";
				return $bytes;
			}
		}


		return $bytes;
	}

	/**
	* Zjisti, zda je soubor uploadnut.
	*
	* @access public
	* @static
	* @param string 	$filename				jmeno souboru
	* @return bool										true => soubor je bezpecne uploadnut; false => soubor neni uploadnut
	*/
	function is_uploaded_file($filename){
		settype($filename,"string");
		if(!file_exists($filename)){
			return false;
		}
		if(is_dir($filename)){
			return false;
		}
		if(!is_uploaded_file($filename)){
			return false;
		}
		
		if(fileowner($filename)!=posix_getuid() && !fileowner($filename)){
			return false;
		}
		// nasl. podminka byla vyhozena - uzivatel prece muze uploadnout prazdny soubor...
		//if(filesize($filename)==0){
		//	return false;
		//}
		return true;
	}

	/**
	* Presune soubor. 
	*
	* Ve skutecnosti to provadi prekopirovani a pak smazani zdrojoveho souboru.
	*
	* @access public
	* @static
	* @param string 	$from_file			zdrojovy soubor
	* @param string 	$to_file				cilovy soubor
	* @param boolean 	&$error 				priznak chyby
	* @param string 	&$error_str 		popis chyby
	* @return int											pocet presunutych souboru; tedy v pripade uspechu bude vracena 1
	* @see files::unlink()
	*/
	function move_file($from_file,$to_file,&$error,&$error_str){
		$error = false;
		$error_str = "";

		settype($from_file,"string");
		settype($to_file,"string");

		if($from_file==$to_file){
			$error = true;
			$error_str = "from_file and to_file are the same files";
			return 0;
		}

		$_stat = rename($from_file,$to_file);
		if(!$_stat){
			$error = true;
			$error_str = "can't rename $from_file to $to_file";
			return 0;
		}	

		return 1;

		/*
		files::copy_file($from_file,$to_file,$error,$error_str);

		if(!$error){
			files::unlink($from_file,$error,$error_str);
		}

		if($error){
			return 0;
		}

		return 1;
		*/
	}

	/**
	* Smaze soubor. 
	*
	* @access public
	* @static
	* @param string 	$file						jmeno souboru
	* @param boolean 	&$error 				priznak chyby
	* @param string 	&$error_str 		popis chyby
	* @return int											pocet smazanych souboru; v pripade uspechun bude vraceno vraci 0 nebo 1, jinak 0
	*/
	function unlink($file,&$error,&$error_str){
		$error = false;
		$error_str = "";

		if(!file_exists($file)){
			return 0;
		}

		$stat = unlink($file);

		if(!$stat){
			$error = true;
			$error_str = "cannot unlink $file";
			return 0;
		}

		return 1;
	}

	/**
	* Smaze rekursivne adresar.
	*
	* Smaze dany adresar vcetne jeho obsahu - obsah je mazan rekursivne.
	*
	* @access public
	* @static
	* @param string 	$dir						jmeno adresare
	* @param boolean 	&$error 				priznak chyby
	* @param string 	&$error_str 		popis chyby
	* @return int											pocet smazanych souboru a adresaru
	*/
	function recursive_unlink_dir($dir,&$error,&$error_str){
		$error = false;
		$error_str = "";
		settype($dir,"string");
		return files::_recursive_unlink_dir($dir,$error,$error_str);
	}

	/**
	* Smaze rekursivne adresar.
	*
	* Metoda je urcena pro vnitrni rekursivni volani.
	*
	* @access private
	* @static
	* @param string 	$dir						jmeno adresare
	* @param boolean 	&$error 				priznak chyby
	* @param string 	&$error_str 		popis chyby
	* @return int											pocet smazanych souboru a adresaru
	* @see file::recursive_unlink_dir()
	*/
	function _recursive_unlink_dir($dir,&$error,&$error_str){
		settype($dir,"string");
		
		$out = 0;

		if($error){
			return $out;
		}

		if($dir==""){ return; }

		if($dir[strlen($dir)-1]=="/"){
			$dir = preg_replace('/\/$/','',$dir);
		}

		if($dir==""){ return; }

		if(!file_exists($dir)){
			return 0;
		}

		$dir .= "/";
		$dir_handle = opendir($dir);
		while($item = readdir($dir_handle)){
			if($item=="." || $item==".." || $item==""){
				continue;
			}
			if(is_dir($dir.$item)){
				$out += files::_recursive_unlink_dir($dir.$item,$error,$error_str);
				//2005-10-21: nasledujici continue tady chybel, skript se proto chybne pokousel volat fci unlink na adresar
				continue;
			}
			if($error){ break; }
			//going to unlink file: $dir$item
			$stat = unlink("$dir$item");
			if(!$stat){
				$error = true;
				$error_str = "cannot unlink $dir$item";
			}else{
				$out++;
			}
		}
		
		closedir($dir_handle);
		if($error){ return; }
		//going to unlink dir: $dir$item
		$stat = rmdir($dir);
		if(!$stat){
			$error = true;
			$error_str = "cannot unlink $dir";
		}else{
			$out++;
		}
		return $out;
	}

	/**
	* Nacte obsah souboru.
	*
	* @access public
	* @static
	* @param string 	$filename				jmeno souboru
	* @param boolean 	&$error 				priznak chyby
	* @param string 	&$error_str 		popis chyby
	* @return string									obsah souboru
	* @see file::recursive_unlink_dir()
	*/
	function get_file_content($filename,&$error,&$error_str){
		$error = false;
		$error_str = "";

		$out = "";
		
		settype($filename,"string");

		if(!is_file($filename)){
			$error = true;
			$error_str = "$filename is not a file";
			return null;		
		}

		$filesize = filesize($filename);
		if($filesize==0){ return ""; }

		$f = fopen($filename,"r");
		if(!$f){
			$error = false;
			$error_str = "can't open file $filename for writing";
			return $out;
		}
		$out = fread($f,$filesize);
		fclose($f);

		return $out;
	}

	/**
	* Zjisti, zda je soubor ke cteni a zapisu.
	*
	* @access public
	* @static
	* @param string 	$filename				jmeno souboru
	* @param boolean 	&$error 				priznak chyby
	* @param string 	&$error_str 		popis chyby
	* @return int											0 => neni; 1 => je; null => chyba
	*/
	function is_readable_and_writable($filename,&$error,&$error_str){
		$error = false;
		$error_str = "";

		settype($filename,"string");

		if(!file_exists($filename)){
			$error = true;
			$error_str = "file does't exist";
			return null;
		}

		$_UID_ = posix_getuid();
		$_FILE_OWNER = fileowner($filename);
		$_FILE_PERMS = fileperms($filename);
		if(!(
			(($_FILE_OWNER!=$_UID_) && (((int)$_FILE_PERMS&(int)bindec("110")))==(int)bindec("110")) ||
			(($_FILE_OWNER==$_UID_) && (((int)$_FILE_PERMS&(int)bindec("110000000"))==(int)bindec("110000000")))
		)){
			return 0;
		}
		return 1;
	}

	/**
	* Urci velikost obrazu z obsahu v parametru.
	* 
	* list($width,$height) = files::get_image_size($image_content,$err,$err_str);
	*/
	function get_image_size($image_content,&$error,&$error_str){
		$temp = defined("TEMP") ? TEMP : "/tmp";
		$filename = $temp."/get_image_filename_".posix_getpid();
		if(!files::write_to_file($filename,$image_content,$error,$error_str)){ return null; }
		$out = getimagesize($filename);
		files::unlink($filename,$error,$error_str);
		if(!is_array($out)){ $out = null; }
		return $out;
	}
}
?>
