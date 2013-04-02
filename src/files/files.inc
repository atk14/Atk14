<?php
/**
 * Class for basic file management.
 *
 * Provides static methods for operations on files.
 *
 * @package Atk14
 * @subpackage InternalLibraries
 * @filesource
 * @author Jaromir Tomek
 */
/**
 * Class for basic file management.
 *
 * Provides static methods for operations on files.
 *
 * @package Atk14
 * @subpackage InternalLibraries
 * @filesource
 *
 */
class files{

	/**
	 * Creates a directory.
	 *
	 * Also creates parent directories when they don't exist.
	 * Return number of created directories.
	 * When the requested directory exists method returns 0.
	 * Newly created directories have permissions set to 0777.
	 *
	 * @static
	 * @param string $dirname	Name of directory to be created.
	 * @param boolean &$error Error flag
	 * @param string	 &$error_str Error description
	 * @return int Number of directories created
	 */
	static function mkdir($dirname,&$error = null,&$error_str = null){
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
	 * Creates a copy of a file.
	 *
	 * When the target file does not exist, it is created with permissions 0666.
	 *
	 * @static
	 * @param string 	$from_file Source file
	 * @param string 	$to_file Target file
	 * @param boolean 	&$error Error flag
	 * @param string 	&$error_str Error message
	 * @return int Number of copied bytes
	 *
	 */
	static function copy_file($from_file,$to_file,&$error = null,&$error_str = null){
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
	 * Writes a string to a file.
	 *
	 * When the target file does not exist it is created with permissions 0666.
	 *
	 * @static
	 * @param string 	$file	Name of a file
	 * @param string 	$content String to write
	 * @param boolean 	&$error Error flag
	 * @param string 	&$error_str Error description
	 * @return int Number of written bytes
	 */
	static function write_to_file($file,$content,&$error = null,&$error_str = null){
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
	 * Checks if a file was uploaded.
	 *
	 * @static
	 * @param string 	$filename Name of a file
	 * @return bool	true => file was securely uploaded; false => file was not uploaded
	 *
	 */
	static function is_uploaded_file($filename){
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
	 * Moves a file
	 *
	 * In fact the method copies the file first and then removes the source file.
	 *
	 *
	 * @static
	 * @param string 	$from_file Source file
	 * @param string 	$to_file Target file
	 * @param boolean 	&$error Error flag
	 * @param string 	&$error_str Error description
	 * @return int	Number of moved files; ie. on success return 1
	 * @see files::unlink()
	 */
	static function move_file($from_file,$to_file,&$error = null,&$error_str = null){
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
	 * Removes a file from filesystem.
	 *
	 * @static
	 * @param string $file Name of a file
	 * @param boolean &$error Error flag
	 * @param string 	&$error_str Error description
	 * @return int Number of deleted files; on success returns 1; otherwise 0
	 */
	static function unlink($file,&$error = null,&$error_str = null){
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
	 * Removes a directory recursively.
	 *
	 * Removes a directory with its content.
	 *
	 * @static
	 * @param string 	$dir Directory name
	 * @param boolean 	&$error Error flag
	 * @param string 	&$error_str Error description
	 * @return int Number of deleted directories and files
	 */
	static function recursive_unlink_dir($dir,&$error = null,&$error_str = null){
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
	 *
	 */
	static function _recursive_unlink_dir($dir,&$error,&$error_str){
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
	 * Reads content of a file.
	 *
	 * @static
	 * @param string 	$filename Name of a file
	 * @param boolean 	&$error Error flag
	 * @param string 	&$error_str Error description
	 * @return string Content of a file
	 */
	static function get_file_content($filename,&$error = null,&$error_str = null){
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
	 * Checks if a file is both readable and writable.
	 *
	 * @static
	 * @param string 	$filename Name of a file
	 * @param boolean 	&$error Error flag
	 * @param string 	&$error_str Error description
	 * @return int	1 - is readable and writable; 0 - is not
	 */
	static function is_readable_and_writable($filename,&$error = null,&$error_str = null){
		$error = false;
		$error_str = "";

		settype($filename,"string");

		if(!file_exists($filename)){
			$error = true;
			$error_str = "file does't exist";
			return 0;
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
	 * Determines width and height of an image in parameter.
	 *
	 * <code>
	 * list($width,$height) = files::get_image_size($image_content,$err,$err_str);
	 * </code>
	 * 
	 * @param string $image_content Binary image data
	 * @param boolean $error Error flag
	 * @param string $error_str Error description
	 * @return array Image dimensions
	 *
	 */
	static function get_image_size($image_content,&$error = null,&$error_str = null){
		$temp = defined("TEMP") ? TEMP : "/tmp";
		$filename = $temp."/get_image_filename_".posix_getpid();
		if(!files::write_to_file($filename,$image_content,$error,$error_str)){ return null; }
		$out = getimagesize($filename);
		files::unlink($filename,$error,$error_str);
		if(!is_array($out)){ $out = null; }
		return $out;
	}

	/**
	 * Write a content to a temporary file.
	 *
	 * $tmp_filename = files::write_to_temp($hot_content);
	 * // ... do some work with $tmp_filename
	 * files::unlink($tmp_filename);
	 */
	static function write_to_temp($content, &$error = null, &$error_str = null){
		$temp_filename = defined("TEMP") ? TEMP : "/tmp";
		$temp_filename .= "/files_tmp_".uniqid("",true)."";
		
		$out = files::write_to_file($temp_filename,$content,$error,$error_str);
		if(!$error){
			return $temp_filename;
		}
	}

	/**
	 * Determines the mime type of the file.
	 * 
	 * !! Note that it actualy runs the shell command file.
	 * If it is unable to run the command, 'application/octet-string' is returned.
	 */
	static function determine_file_type($filename, &$error = null, &$error_str  = null){
		$command = "file -i ".escapeshellarg($filename);
		$out = `$command`;
		// /tmp/xxsEEws: text/plain charset=us-ascii
		// -> text/plain
		// ya.gif: image/gif; charset=binary
		// -> image/gif
		if(preg_match("/^.*?:\\s*([^\\s]+\\/[^\\s;]+)/",$out,$matches)){
			$mime_type = $matches[1];
		}else{
			$mime_type = "application/octet-stream";
		}
		return $mime_type;
	}
}
