<?php
/**
 * Class for basic file management.
 *
 * Provides static methods for operations on files.
 *
 * @filesource
 */

defined("FILES_DEFAULT_FILE_PERMS") || define("FILES_DEFAULT_FILE_PERMS",0666);
defined("FILES_DEFAULT_DIR_PERMS") || define("FILES_DEFAULT_DIR_PERMS",0777);

/**
 * Class for basic file management.
 *
 * Provides static methods for operations on files.
 *
 * @package Atk14\InternalLibraries
 * @filesource
 *
 */
class Files{

	const VERSION = "1.4";

	static protected $_DefaultFilePerms = FILES_DEFAULT_FILE_PERMS;

	static protected $_DefaultDirPerms = FILES_DEFAULT_DIR_PERMS;

	/**
	 *
	 *
	 *	echo decoct(Files::GetDefaultFilePerms()); // e.g. "666"
	 */
	static function GetDefaultFilePerms(){
		return self::$_DefaultFilePerms;
	}

	/**
	 *
	 *
	 *	$prev_perms = Files::SetDefaultFilePerms(0640);
	 */
	static function SetDefaultFilePerms($perms){
		$perms = (int)$perms;

		$prev = self::$_DefaultFilePerms;
		self::$_DefaultFilePerms = $perms;
		return $prev;
	}

	/**
	 *
	 *
	 *	echo decoct(Files::GetDefaultDirPerms()); // e.g. "777"
	 */
	static function GetDefaultDirPerms(){
		return self::$_DefaultDirPerms;
	}

	/**
	 *
	 *
	 *	$prev_perms = Files::SetDefaultDirPerms(0750);
	 */
	static function SetDefaultDirPerms($perms){
		$perms = (int)$perms;

		$prev = self::$_DefaultDirPerms;
		self::$_DefaultDirPerms = $perms;
		return $prev;
	}

	/**
	 * Normalizes permissions of a file or a directory according the default perms
	 *
	 *	Files::NormalizeFilePerms("/path/to/a/file");
	 *	Files::NormalizeFilePerms("/path/to/a/directory");
	 */
	static function NormalizeFilePerms($filename,&$error = null,&$error_str = null){
		$error = false;
		$error_str = "";

		$perms = is_dir($filename) ? self::GetDefaultDirPerms() : self::GetDefaultFilePerms();
		
		$_old_umask = umask(0);
		$_stat = chmod($filename,$perms);
		umask($_old_umask);

		if(!$_stat){
			$error = true;
			$error_str = "failed to do chmod on $filename";
			return false;
		}
		return true;
	}

	/**
	 * Creates a directory.
	 *
	 * Also creates parent directories when they don't exist.
	 * Return number of created directories.
	 * When the requested directory exists method returns 0.
	 * Newly created directories have permissions set to 0777.
	 *
	 * @param string $dirname	Name of directory to be created.
	 * @param boolean &$error Error flag
	 * @param string	 &$error_str Error description
	 * @return int Number of directories created - Not true! See the code.
	 */
	static function Mkdir($dirname,&$error = null,&$error_str = null){
		$out = 0;
		$error = false;
		$error_str = "";

		$dirname = self::_NormalizeFilename($dirname);

		if(is_dir($dirname)){ return $out; }

		/*
		// this obsolete code doesn't work well when open_basedir restriction is used
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
		*/

		// this is a temporary workaround
		$old_umask = umask(0);
		if(mkdir($dirname,self::GetDefaultDirPerms(),true)){
			$out = 1;
		}else{
			if(preg_match('/^5\.3\./',phpversion())){
				return 1; // HACK for PHP5.3 - to be removed
			}
			$error = true;
			$error_str = "can't create directory $dirname";
		}
		umask($old_umask);

		return $out;
	}

	/**
	 * Created a directory for the given filename.
	 *
	 *
	 * ```
	 *	Files::MkdirForFile("/path/to/a/file.dat");
	 * ```
	 */
	static function MkdirForFile($filename,&$error = null,&$error_str = null){
		$directory = preg_replace('/[^\/]+$/','',$filename);
		if(!strlen($directory)){
			$error = true;
			$error_str = "can't determine directory name";
			return;
		}
		return self::Mkdir($directory,$error,$error_str);
	}

	/**
	 * Creates a copy of a file.
	 *
	 * When the target file does not exist, it is created with permissions 0666.
	 *
	 * @param string $from_file Source file
	 * @param string $to_file Target file
	 * @param boolean &$error Error flag
	 * @param string &$error_str Error message
	 * @return int Number of copied bytes
	 *
	 */
	static function CopyFile($from_file,$to_file,&$error = null,&$error_str = null){
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
		
		//menit mod souboru, jenom, kdyz soubor drive neexistoval
		if(!$__target_file_exists){
			$_old_umask = umask(0);
			$_stat = chmod($to_file,self::GetDefaultFilePerms());
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
	 * @param string $file Name of a file
	 * @param string $content String to write
	 * @param boolean &$error Error flag
	 * @param string &$error_str Error description
	 * @param array $options
	 * - file_open_mode see PHPs fopen manual
	 * @return int Number of written bytes
	 */
	static function WriteToFile($file,$content,&$error = null,&$error_str = null,$options = array()){
		$options += array(
			"file_open_mode" => "w",
		);

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

		$f = fopen($file,$options["file_open_mode"]);
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

		//menit mod souboru, jenom, kdyz soubor drive neexistoval
		if(!$_file_exists){
			$_old_umask = umask(0);
			$_stat = chmod($file,self::GetDefaultFilePerms());
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
	 * Appends content to the given file.
	 *
	 * Opens a file in append mode and appends content to the end of it.
	 *
	 * @param string $file name of the file to append the $content to
	 * @param string $content
	 * @param boolean &$error flag indicating that something went wrong
	 * @param string &$error_str message describing the error
	 * @return int number of bytes written to the file
	 */
	static function AppendToFile($file,$content,&$error = null,&$error_str = null){
		return Files::WriteToFile($file,$content,$error,$error_str,array("file_open_mode" => "a"));
	}

	/**
	 * Checks if a file was uploaded via HTTP POST request.
	 *
	 * @param string 	$filename Name of a file
	 * @return bool	true => file was securely uploaded; false => file was not uploaded
	 * @see is_uploaded_file
	 *
	 */
	static function IsUploadedFile($filename){
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
	 * Moves a file or a directory
	 *
	 * ```
	 *	Files::MoveFile("/path/to/file","/path/to/another/file",$error,$error_str);
	 *	Files::MoveFile("/path/to/file","/path/to/directory/",$error,$error_str);
	 *	Files::MoveFile("/path/to/directory/","/path/to/another/directory/",$error,$error_str);
	 * ```
	 *
	 * @param string $from_file Source file
	 * @param string $to_file Target file
	 * @param boolean &$error Error flag
	 * @param string &$error_str Error description
	 * @return int number of moved files; ie. on success return 1
	 */
	static function MoveFile($from_file,$to_file,&$error = null,&$error_str = null){
		$error = false;
		$error_str = "";

		settype($from_file,"string");
		settype($to_file,"string");

		if(is_dir($to_file) && file_exists($from_file) && !is_dir($from_file)){
			// copying a file to an existing directory
			preg_match('/([^\/]+)$/',$from_file,$matches);
			$to_file .= "/$matches[1]";
			
		}elseif(is_dir($to_file) && file_exists($from_file) && is_dir($from_file)){
			// copying a directory to an existing directory
			preg_match('/([^\/]+)\/*$/',$from_file,$matches);
			$to_file .= "/$matches[1]";
		}

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
	}

	/**
	 * Removes a file from filesystem.
	 *
	 * @param string $file Name of a file
	 * @param boolean &$error Error flag
	 * @param string &$error_str Error description
	 * @return int Number of deleted files; on success returns 1; otherwise 0
	 */
	static function Unlink($file,&$error = null,&$error_str = null){
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
	 * @param string $dir Directory name
	 * @param boolean &$error Error flag
	 * @param string &$error_str Error description
	 * @return int Number of deleted directories and files
	 */
	static function RecursiveUnlinkDir($dir,&$error = null,&$error_str = null){
		$error = false;
		$error_str = "";
		settype($dir,"string");
		return Files::_RecursiveUnlinkDir($dir,$error,$error_str);
	}

	/**
	 * Internal method making main part of RecursiveUnlinkDir call
	 *
	 * @param string $dir Directory name
	 * @param boolean &$error Error flag
	 * @param string &$error_str Error description
	 * @return int pocet smazanych souboru a adresaru
	 * @ignore
	 * @internal We should consider making this method private
	 *
	 */
	static function _RecursiveUnlinkDir($dir,&$error,&$error_str){
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
		if(!$dir_handle){
			return 0;
		}
		while(($item = readdir($dir_handle))!==false){
			if($item=="." || $item==".." || $item==""){
				continue;
			}
			if(is_dir($dir.$item)){
				$out += Files::_RecursiveUnlinkDir($dir.$item,$error,$error_str);
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
	 * @param string $filename Name of a file
	 * @param boolean &$error Error flag
	 * @param string &$error_str Error description
	 * @return string Content of a file
	 */
	static function GetFileContent($filename,&$error = null,&$error_str = null){
		$error = false;
		$error_str = "";

		settype($filename,"string");

		if(!is_file($filename)){
			$error = true;
			$error_str = "$filename is not a file";
			return null;		
		}

		if(!is_readable($filename)){
			$error = false;
			$error_str = "file $filename is not readable";
			return null;
		}

		$filesize = filesize($filename);
		if($filesize==0){ return ""; }

		$f = fopen($filename,"r");
		if(!$f){
			$error = false;
			$error_str = "can't open file $filename for reading";
			return null;
		}
		$out = fread($f,$filesize);
		fclose($f);

		if(strlen($out)==0){
			$error = true;
			$error_str = "can't read from file $filename";
			return null;
		}

		if(strlen($out)!=$filesize){
			$error = true;
			$error_str = "can't read $filesize bytes from $filename (it was read ".strlen($out).")";
			return null;
		}

		return $out;
	}

	/**
	 * Checks if a file is both readable and writable.
	 *
	 * @param string 	$filename Name of a file
	 * @param boolean 	&$error Error flag
	 * @param string 	&$error_str Error description
	 * @return int	1 - is readable and writable; 0 - is not
	 */
	static function IsReadableAndWritable($filename,&$error = null,&$error_str = null){
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
	 * Example
	 * ```
	 *	list($width,$height) = Files::GetImageSize($image_content,$err,$err_str);
	 * ```
	 * 
	 * @param string $image_content Binary image data
	 * @param boolean $error Error flag
	 * @param string $error_str Error description
	 * @return array Image dimensions
	 *
	 */
	static function GetImageSize($image_content,&$error = null,&$error_str = null){
		$temp = defined("TEMP") ? TEMP : "/tmp";
		$filename = $temp."/get_image_filename_".posix_getpid();
		if(!Files::WriteToFile($filename,$image_content,$error,$error_str)){ return null; }
		$out = getimagesize($filename);
		Files::Unlink($filename,$error,$error_str);
		if(!is_array($out)){ $out = null; }
		return $out;
	}

	/**
	 * Write a content to a temporary file.
	 *
	 * Example
	 * ```
	 *	$tmp_filename = Files::WriteToTemp($hot_content);
	 * ```
	 *
	 * ... do some work with $tmp_filename
	 * ```
	 *	Files::Unlink($tmp_filename);
	 * ```
	 *
	 * @param string $content content to write to the temporary file
	 * @param boolean &$error flag indicating a problem when calling this method
	 * @param string &$error_str
	 * @return string name of the temporary file
	 */
	static function WriteToTemp($content, &$error = null, &$error_str = null){
		$temp_filename = Files::GetTempFilename();
		
		$out = Files::WriteToFile($temp_filename,$content,$error,$error_str);
		if(!$error){
			return $temp_filename;
		}
	}

	/**
	 * Returns the temporary directory
	 *
	 * ```
	 * $tmp_dir = Files::GetTempDir(); // e.g. "/tmp"
	 * ```
	 *
	 * It is NOT ensured whether returned is ending with "/" or not.
	 *
	 * @return string
	 */
	static function GetTempDir(){
		$temp_dir = (defined("TEMP") && strlen("TEMP")) ? (string)TEMP : sys_get_temp_dir();
		if(!strlen($temp_dir)){
			$temp_dir = "/tmp";
		}
		return $temp_dir;
	}

	/**
	 * Returns a filename for a new temporary file.
	 *
	 * @return string name of the newly created file
	 */
	static function GetTempFilename($prefix = "files_tmp_"){
		$prefix = preg_replace('/[^0-9a-z_.-]/i','_',$prefix); // sanitization

		// Might be better, but oddly it breaks tests
		//return tempnam(self::GetTempDir(), $prefix);

		$temp_filename = self::GetTempDir() . "/$prefix".uniqid("",true);
		return $temp_filename;
	}

	/**
	 * Determines the mime type of the file.
	 *
	 * !! Note that it actualy runs the shell command file.
	 * If it is unable to run the command, 'application/octet-string' is returned.
	 *
	 *		$mime_type = Files::DetermineFileType($_FILES['userfile']['tmp_name'],array(
	 *			"original_filename" => $_FILES['userfile']['name']) // like "strawber.jpeg"
	 *		),$preferred_suffix);
	 *		echo $mime_type; // "image/jpg"
	 *		echo $preferred_suffix; // "jpg"
	 *
	 * @param string $filename name of the file to be examined
	 * @param array $options
   * @param string &$preferred_suffix
	 */
	static function DetermineFileType($filename, $options = array(), &$preferred_suffix = null){
		$options += array(
			"original_filename" => null,
		);

		if(!file_exists($filename) || is_dir($filename)){
			return null;
		}
		

		if(function_exists("mime_content_type")){
			$mime_type = mime_content_type($filename);
		}elseif(function_exists("finfo_open")){
			$finfo = finfo_open(FILEINFO_MIME_TYPE);
			$mime_type = finfo_file($finfo, $filename);
		}else{
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
		}

		$original_filename = $options["original_filename"];
		if(!$original_filename){
			preg_match('/([^\/]+)$/',$filename,$matches); // "/path/to/file.jpg" -> "file.jpg"
			$original_filename = $matches[1];
		}
		$original_suffix = "";
		if(preg_match('/\.([^.]{1,10})$/i',$original_filename,$matches)){
			$original_suffix = strtolower($matches[1]); // "FILE.JPG" -> "jpg"
		}
		$preferred_suffix = $original_suffix;

		$tr_table = array(
			// most desirable suffix is on the first position
			// 										the most desirable type is on the first position
			"xls" =>				array("application/vnd.ms-excel","application/msexcel","application/vnd.ms-office","application/msword"),
			"xlsx" =>				array("application/vnd.openxmlformats-officedocument.spreadsheetml.sheet","application/zip","application/octet-stream"), // !! application/octet-stream seems to be a little odd
			"doc" =>				array("application/msword","application/vnd.ms-office"),
			"ppt" =>				array("application/vnd.ms-powerpoint","application/vnd.ms-office","application/msword"),
			"jpg|jpeg" =>		array("image/jpeg","image/jpg"),
			"svg" =>				array("image/svg+xml","image/svg","text/plain"),
			"bmp" =>				array("image/bmp","image/x-bmp","image/x-ms-bmp","application/octet-stream"),
			"eps" =>				array("application/postscript","application/eps"),
			"csv" =>				array("text/csv","text/plain"),
			"docx" => 			array("application/vnd.openxmlformats-officedocument.wordprocessingml.document","application/zip"),
		);

		foreach($tr_table as $suffixes => $mime_types){
			$suffixes = explode("|",$suffixes);
			if(in_array($original_suffix,$suffixes) && in_array($mime_type,$mime_types)){
				$mime_type = $mime_types[0];
				$preferred_suffix = $suffixes[0];
				break;
			}
		}
		
		return $mime_type;
	}

	/**
	 * Find files in the given directory according to a regular pattern and other criteria
	 *
	 * TODO: Currently only regular files are being found just in the given directory
	 *
	 *	$files = Files::FindFiles('./log/',array(
	 * 		'pattern' => '/^.*\.(log|err)$/'
	 *	));
	 *	// array('./log/application.log', './log/application.err')
	 * 
	 * @return string[]
	 */
	static function FindFiles($directory,$options = array()){
		$options += array(
			"pattern" => null, // '/^.*\.(log|err)$/'
			"invert_pattern" => null, // '/^\./' - do not find files starting with dot
			"min_mtime" => null, // time() - 2 * 60 * 60
			"max_mtime" => null, // time() - 60 * 60

			"maxdepth" => null // TODO: add maxdepth like in system command find
		);

		if(!preg_match('/\/$/',$directory)){
			$directory = "$directory/"; // "./tmp" -> "./tmp/"
		}

		$pattern = $options["pattern"];
		$invert_pattern = $options["invert_pattern"];
		$min_mtime = $options["min_mtime"];
		$max_mtime = $options["max_mtime"];
		$maxdepth = $options["maxdepth"];

		if(isset($maxdepth) && $maxdepth<=0){
			return array();
		}

		// getting file list
		$files = array();
		$dir = opendir($directory);
		while(is_string($item = readdir($dir))){
			if($item=="." || $item==".."){ continue; }
			$files[] = $item;
		}
		closedir($dir);
		asort($files);

		$out = array();

		foreach($files as $file){
			$_f = $file; // "application.log"
			$file = "$directory$file"; // "./log/application.log"

			if(is_dir($file) && !is_link($file)){
				$_options = $options;
				if(isset($_options["maxdepth"])){ $_options["maxdepth"]--; }
				foreach(self::FindFiles($file,$_options) as $_file){
					$out[] = $_file;
				}
			}

			if(!is_file($file)){
				continue;
			}
		
			if($pattern && !preg_match($pattern,$_f)){
				continue;
			}

			if($invert_pattern && preg_match($invert_pattern,$_f)){
				continue;
			}

			if(isset($min_mtime) && filemtime($file)<$min_mtime){
				continue;
			}

			if(isset($max_mtime) && filemtime($file)>$max_mtime){
				continue;
			}

			$out[] = $file;
		}

		return $out;
	}

	/**
	 * $filename = Files::_NormalizeFilename("/path/to//project//../tmp//attachments//"); // "/path/to/tmp/attachments/"
	 */
	static function _NormalizeFilename($filename){
		$_filename = "";
		while(1){
			$_filename = preg_replace('/[^\/]+\/+\.\.\//','/',$filename); // "/path/to/dir/../tmp/images/" -> "/path/to/tmp/images/"
			if($_filename==$filename){ break; }
			$filename = $_filename;
		}
		$filename = $_filename;

		$filename = preg_replace('/\/\/+/','/',$filename); // "/path/to//tmp/dir//" -> "/path/to/tmp/dir/"

		return $filename;
	}
}
