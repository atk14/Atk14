<?
if(!defined("SENDMAIL_DEFAULT_FROM")){
	define("SENDMAIL_DEFAULT_FROM","sendmail");
}

if(!defined("SENDMAIL_DEFAULT_BODY_CHARSET")){
	define("SENDMAIL_DEFAULT_BODY_CHARSET","us-ascii");
}

if(!defined("SENDMAIL_DEFAULT_BODY_MIME_TYPE")){
	define("SENDMAIL_DEFAULT_BODY_MIME_TYPE","text/plain");
}

if(!defined("SENDMAIL_BODY_AUTO_PREFIX")){
	define("SENDMAIL_BODY_AUTO_PREFIX","");
}

if(!defined("SENDMAIL_USE_TESTING_ADDRESS_TO")){
	define("SENDMAIL_USE_TESTING_ADDRESS_TO",false);
}

if(!defined("SENDMAIL_TESTING_ADDRESS_TO")){
	define("SENDMAIL_TESTING_ADDRESS_TO","");
}

if(!defined("SENDMAIL_DO_NOT_SEND_MAILS")){
	if(defined("DEVELOPMENT") && DEVELOPMENT){
		define("SENDMAIL_DO_NOT_SEND_MAILS",true);
	}else{
		define("SENDMAIL_DO_NOT_SEND_MAILS",false);
	}
}

if(!defined("SENDMAIL_EMPTY_TO_REPLACE")){
	define("SENDMAIL_EMPTY_TO_REPLACE","");
}

if(!defined("SENDMAIL_DEFAULT_TRANSFER_ENCODING")){
	define("SENDMAIL_DEFAULT_TRANSFER_ENCODING","8bit"); // "8bit" or "quoted-printable"
}


/**
* Odesle e-mail.
*
*	Parametry jsou predany v poli $params.
* Mozne klice:
*		array(
*			"from" => "",
*			"bcc" => "",
*			"cc" => "",
*			"subject" => "",
*			"body" => "",
*			"to" => "",
*			"body_mime_type" => "",
*			"body_charset" => "",
*			"customer_id" => "", //nepouziva se
*			"email_type" => "", //nepouziva se
*			"attachments" => 
*				array(
*					array(
*						"body" => ""
*						"filename" => ""
*						"mime_type" => ""
*					)
*				)
*		)
*
*	Takto odeslany e-mail je vzdycky odeslan i na adresu BCC_EMAIL,
*	pokud tato konstanta nastavena.
*
* Pokud je nstavena konstanta SENDMAIL_USE_TESTING_ADDRESS_TO na true,
*	zprava je odeslana pouze na adresu uvedenou v konstante
*	SENDMAIL_TESTING_ADDRESS_TO.
*
* TODO: Dodelat subject_charset a kodovat subjekt, pokud to bude treba.
*
*	@param array			$params				pole parametru
*
*	@return	void
*/
function sendmail($params = array()){
	$orig_params = $params;
	$params = array_merge(array(
		"from" => SENDMAIL_DEFAULT_FROM,
		"to" => SENDMAIL_EMPTY_TO_REPLACE,
		"cc" => null,
		"bcc" => null,
		"subject" => "",
		"body" => "",
		"transfer_encoding" => SENDMAIL_DEFAULT_TRANSFER_ENCODING, // zpusob kodovani body (nikoli prilohy): "8bit" nebo "quoted-printable"
		"charset" => SENDMAIL_DEFAULT_BODY_CHARSET,
		"mime_type" => SENDMAIL_DEFAULT_BODY_MIME_TYPE,
		"attachments" => array(),
		"attachment" => null
	),$params);

	// toto jsou zastarale parametry...
	if(isset($params["body_charset"])){ $params["charset"] = $params["body_charset"]; }
	if(isset($params["body_mime_type"])){ $params["mime_type"] = $params["body_mime_type"]; }

	if(isset($params["attachment"])){ $params["attachments"][] = $params["attachment"]; }

	//if(is_array($params["to"])){ $params["to"] = join(", ",array_unique($params["to"])); }
	//if(is_array($params["cc"])){ $params["cc"] = join(", ",array_unique($params["cc"])); }
	//if(is_array($params["bcc"])){ $params["bcc"] = join(", ",array_unique($params["bcc"])); }

	$FROM = $params['from'];
	$BCC = array();
	if(isset($params['bcc'])){ $BCC[] = _sendmail_correct_address($params['bcc']);}
	if(defined("SENDMAIL_BCC_TO") && SENDMAIL_BCC_TO!=""){
		$BCC[] = SENDMAIL_BCC_TO;
	}elseif(defined("BCC_EMAIL") && BCC_EMAIL!=""){
		$BCC[] = BCC_EMAIL;
	}
	$BCC = join(", ",$BCC);
	$CC = _sendmail_correct_address($params['cc']);
	$SUBJECT = _sendmail_escape_subject($params['subject'],$params["charset"]);
	$BODY = $params['body'];
	if(SENDMAIL_BODY_AUTO_PREFIX!=""){
		$BODY = SENDMAIL_BODY_AUTO_PREFIX.$BODY;
	}
	$TO = _sendmail_correct_address($params['to']);
	if(SENDMAIL_USE_TESTING_ADDRESS_TO==true){
		$BODY = "PUVODNI ADRESAT: $TO\nPUVODNI CC: $CC\nPUVODNI BCC: $BCC\n\n$BODY";
		$TO = SENDMAIL_TESTING_ADDRESS_TO;
		$CC = "";
		$BCC = "";
	}
	$BODY_MIME_TYPE = $params["mime_type"];
	$BODY_CHARSET = $params["charset"];

	$ATTACHMENTS = array();
	if(isset($params['attachments'])){
		for($i = 0; $i<sizeof($params['attachments']);$i++){
			$ATTACHMENTS[] = array(
				"body" => $params["attachments"][$i]["body"],
				"filename" => $params["attachments"][$i]["filename"],
				"mime_type" => $params["attachments"][$i]["mime_type"]
			);
		}
	}
	
	$HEADERS = "";
	if(sizeof($ATTACHMENTS)==0){
		$HEADERS .= "From: $FROM\n";
		$HEADERS .= "Reply-To: $FROM\n";
		if($BCC!=""){
			$HEADERS .= "bcc: $BCC\n";
		}
		if($CC!=""){
			$HEADERS .= "cc: $CC\n";
		}

		$HEADERS .= "MIME-Version: 1.0\n";
		$HEADERS .= "Content-Type: $BODY_MIME_TYPE; charset=$BODY_CHARSET\n";
		$HEADERS .= "Content-Transfer-Encoding: $params[transfer_encoding]\n";

		if($params["transfer_encoding"]=="quoted-printable"){
			$BODY = _sendmail_quoted_printable_encode(_sendmail_lf_to_crlf($BODY));
		}

	}else{
		$mailfile = new CMailFile($ATTACHMENTS[0]["body"],array(
			"subject" => $SUBJECT,

			"to" => $TO,
			"from" => $FROM,
			"cc" => $CC,
			"bcc" => $BCC,

			"body" => $BODY,
			"body_mime_type" => $BODY_MIME_TYPE,
			"body_charset" => $BODY_CHARSET,

			"mime_type" => $ATTACHMENTS[0]['mime_type'],
			"filename" => $ATTACHMENTS[0]['filename'],
		));
		$mail_ar = $mailfile->getfile();
		$BODY = $mail_ar["body"];
		$HEADERS = $mail_ar["headers"];
	}

	$out = array(
		"to" => $TO,
		"from" => $FROM,
		"bcc" => $BCC,
		"cc" => $CC,
		"subject" => $SUBJECT,
		"body" => $BODY,
		"headers" => $HEADERS,
	);

	if(function_exists("sendmail_hook_send")){
		return sendmail_hook_send($out,$orig_params);
	}

	if(SENDMAIL_DO_NOT_SEND_MAILS==false){
		mail($TO,$SUBJECT,$BODY,$HEADERS);
	}

	return $out;
}

function _sendmail_correct_address($addr){
	if(is_array($addr)){
		$addr = array_unique($addr);
		$_addr = array();
		foreach($addr as $a){
			if($a == ""){ continue; }
			$_addr[] = $a;
		}
		$addr = $_addr;
		$addr = join(", ",$addr);
	}
	return (string)$addr;
}

function _sendmail_escape_subject($subject,$charset){
	if(translate::check_encoding($subject,"ascii")){ return $subject; }
	$out = array();
	$out[] = "=?$charset?Q?";
	for($i=0;$i<strlen($subject);$i++){
		$c = $subject[$i];
		if($c=="=" || $c=="?" || !translate::check_encoding($c,"ascii")){
			$out[] = "=".strtoupper(dechex(ord($c)));
		}else{
			$out[] = $c;
		}
	}
	$out[] = "?=";
	return join("",$out);
}

function _sendmail_quoted_printable_encode($string){
	$valid_non_alphanumeric_chars = "$<>[]{}()._-/,?!#@\"'&*+`;:~^|";

	$back_replaces = array(
		"%0D%0A" => "\r\n",
		"%09" => "\t",
		"%20" => " ",
	);
	foreach(preg_split("//",$valid_non_alphanumeric_chars) as $ch){
		if(strlen($ch)==0){ continue; } // to nechapu...  :( jak se to stane?
		$back_replaces["%".strtoupper(dechex(ord($ch)))] = $ch;
	}
	$string = rawurlencode($string);
	$string = strtr($string,$back_replaces);
	$string = str_replace("%","=",$string);

	// zakodovani tabulatory nebo mezery na konci radku
	$string = str_replace("\t\r\n","=09\r\n",$string);
	$string = str_replace(" \r\n","=20\r\n",$string);

	// zakodovani tabulatory nebo mezery na konci textu
	$string = preg_replace("/\\t$/","=09",$string);
	$string = preg_replace("/ $/","=20",$string);
	
	$string = preg_replace('/[^\r\n]{71,73}[^=\r\n]{2}/', "$0=\r\n", $string);
  return $string;
}

function _sendmail_lf_to_crlf($string){
	$string = str_replace("\r","",$string);
	$string = str_replace("\n","\r\n",$string);
	return $string;
}

/* notes from Dan Potter:
Sure. I changed a few other things in here too though. One is that I let
you specify what the destination filename is (i.e., what is shows up as in
the attachment). This is useful since in a web submission you often can't
tell what the filename was supposed to be from the submission itself. I
also added my own version of chunk_split because our production version of
PHP doesn't have it. You can change that back or whatever though =).
Finally, I added an extra "\n" before the message text gets added into the
MIME output because otherwise the message text wasn't showing up.
/*
note: someone mentioned a command-line utility called 'mutt' that 
can mail attachments.
*/
/* 
If chunk_split works on your system, change the call to my_chunk_split
to chunk_split 
*/
/* Note: if you don't have base64_encode on your sytem it will not work */

// simple class that encapsulates mail() with addition of mime file attachment.

// usage - mimetype example "image/gif"
// $mailfile = new CMailFile($subject,$sendto,$replyto,$message,$filename,$mimetype);
// $mailfile->sendfile();
class CMailFile {
	var $subject;
	var $addr_to;
	var $text_body;
	var $text_body_mimetype;
	var $text_body_charset;
	var $text_encoded;
	var $mime_headers;
	var $mime_boundary = "--==================_846811060==_";
	var $smtp_headers;
	
	//2009-01-06: konstruktor byl predelan
	//$subject,$to,$from,$cc,$bcc,$msg,$msg_mimetype,$msg_charset,$filename,&$file_content,$mimetype = "application/octet-stream", $mime_filename = false) 
	function CMailFile(&$file_content,$options = array()){
		$options = array_merge(array(
			"subject" => "",

			"to" => "",
			"from" => "",
			"cc" => "",
			"bcc" => "",

			"body" => "",
			"body_mime_type" => "text/plain",
			"body_charset" => "iso-8859-2",

			"mime_type" => "application/octet-stream",
			"filename" => "file.dat",
		),$options);
		
		$this->text_body_mimetype = $options["body_mime_type"];
		$this->text_body_charset = $options["body_charset"];
		
		$this->subject = $options["subject"];
		$this->addr_to = $options["to"];
		$this->smtp_headers = $this->write_smtpheaders($options["from"],$options["cc"],$options["bcc"]);
		$this->text_body = $this->write_body($options["body"]);
		$this->text_encoded = $this->attach_file($options["filename"],$file_content,$options["mime_type"]);
		$this->mime_headers = $this->write_mimeheaders($options["filename"]);
	}

	function set_text_body_mimetype($mimetype){
		settype($mimetype,"string");
		if(strlen($mimetype)>0){
			$this->text_body_mimetype = $mimetype;
		}
	}

	function set_text_body_charset($charset){
		settype($charset,"string");
		if(strlen($charset)>0){
			$this->text_body_charset = $charset;
		}
	}

	function attach_file($filename,&$file_content,$mimetype) {
		$encoded = $this->encode_file($filename,$file_content);
		$out = "--" . $this->mime_boundary . "\n";
		$out = $out . "Content-type: " . $mimetype . "; name=\"$filename\";\n";		
		$out = $out . "Content-Transfer-Encoding: base64\n";
		$out = $out . "Content-disposition: attachment; filename=\"$filename\"\n\n";
		$out = $out . $encoded . "\n";
		$out = $out . "--" . $this->mime_boundary . "--" . "\n";
		return $out; 
// added -- to notify email client attachment is done
	}

	function encode_file($sourcefile,&$contents) {
		$encoded = $this->my_chunk_split(base64_encode($contents));
		return $encoded;
	}

	function getfile(){
		$headers = $this->smtp_headers . $this->mime_headers;		
		$message = $this->text_body . $this->text_encoded;	
		return array(
			"to" => $this->addr_to,
			"subject" => $this->subject,
			"headers" => $headers,
			"body" => $message
		);
	}

	function sendfile() {
		$headers = $this->smtp_headers . $this->mime_headers;		
		$message = $this->text_body . $this->text_encoded;
		mail($this->addr_to,$this->subject,$message,$headers);
	}
	
	function write_body($msgtext) {
		$out = "--" . $this->mime_boundary . "\n";
		$out = $out . "Content-Type: $this->text_body_mimetype; charset=\"$this->text_body_charset\"\n";
		$out = $out . "Content-Transfer-Encoding: 8bit\n\n";
		$out = $out . $msgtext . "\n\n";
		return $out;
	}

	function write_mimeheaders($filename) {
		$out = "MIME-version: 1.0\n";
		$out = $out . "Content-Type: multipart/mixed; ";
		$out = $out . "boundary=\"$this->mime_boundary\"\n";
		$out = $out . "Content-Transfer-Encoding: 8bit\n";
		$out = $out . "X-attachments: $filename;\n\n";
		return $out;
	}

	function write_smtpheaders($addr_from,$cc,$bcc) {
		$out = "From: $addr_from\n";
		$out = $out . "Reply-To: $addr_from\n";
		$out = $out . "X-Mailer: mole 0.1\n";
		$out = $out . "X-Sender: $addr_from\n";
		if($cc!=""){
			$out = $out . "cc: $cc\n";
		}
		if($bcc!=""){
			$out = $out . "bcc: $bcc\n";
		}
		return $out;
	}

	// Splits a string by RFC2045 semantics (76 chars per line, end with \r\n).
	// This is not in all PHP versions so I define one here manuall.
	function my_chunk_split($str)
	{
		$stmp = $str;
		$len = strlen($stmp);
		$out = "";
		while ($len > 0) {
			if ($len >= 76) {
				$out = $out . substr($stmp, 0, 76) . "\n";
				$stmp = substr($stmp, 76);
				$len = $len - 76;
			}
			else {
				$out = $out . $stmp . "\n";
				$stmp = ""; $len = 0;
			}
		}
		return $out;
	}
}
?>
