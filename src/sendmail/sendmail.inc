<?php
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
	define("SENDMAIL_USE_TESTING_ADDRESS_TO","");
}

if(!defined("SENDMAIL_DO_NOT_SEND_MAILS")){
	define("SENDMAIL_DO_NOT_SEND_MAILS",((defined("DEVELOPMENT") && DEVELOPMENT) || (defined("TEST") && TEST)));
}

if(!defined("SENDMAIL_EMPTY_TO_REPLACE")){
	define("SENDMAIL_EMPTY_TO_REPLACE","");
}

if(!defined("SENDMAIL_DEFAULT_TRANSFER_ENCODING")){
	define("SENDMAIL_DEFAULT_TRANSFER_ENCODING","8bit"); // "8bit" or "quoted-printable"
}


/**
* Sends an e-mail.
*
* <code>
*		// a typical usage
*		sendmail(array(
*			"from" => "info@example.org",
*			"to" => "user@gmailer.com",
*			"subject" => "An Subject",
*			"body" => "Hi!",
*		));
*
*		sendmail(array(
*			"from" => "info@example.org",
*			"from_name" => "Our Big Company",
*			// ...
*		));
*
*		sendmail(array(
*			"from" => "Our Big Company <info@example.org>",
*			// ...
*		));
*
*		PHP`s mail() compatible usage
* 	sendmail("user@gmailer.com","Hello","Hello dear user...");
*	</code>
*
* Note that there is an another function sendhtmlmail()
* intended for sending well formatted HTML e-mails.
*
* There are a couple of parameters and constants which affect formatting and sending.
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
*			"mime_type" => "",
*			"charset" => "",
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
* Pokud je nstavena konstanta SENDMAIL_USE_TESTING_ADDRESS_TO na nejaky e-mail,
*	zprava je odeslana pouze na adresu uvedenou v teto konstante.
*
*	@param array			$params				pole parametru
*
*	@return	void
*/
function sendmail($params = array(),$subject = "",$message = "",$additional_headers = null){
	$to = SENDMAIL_EMPTY_TO_REPLACE;
	if(is_string($params)){
		$to = $params;
		$params = array();
	}
	$orig_params = $params;
	$params = array_merge(array(
		"from" => SENDMAIL_DEFAULT_FROM, // john.doe@example.com
		"from_name" => null, // "John Doe"
		"to" => $to,
		"cc" => null,
		"bcc" => null,
		"return_path" => null,
		"subject" => $subject,
		"body" => $message,
		"transfer_encoding" => SENDMAIL_DEFAULT_TRANSFER_ENCODING, // zpusob kodovani body (nikoli prilohy): "8bit" nebo "quoted-printable"
		"charset" => SENDMAIL_DEFAULT_BODY_CHARSET,
		"mime_type" => SENDMAIL_DEFAULT_BODY_MIME_TYPE,
		"attachments" => array(),
		"attachment" => null,
		"build_message_only" => false,

		"headers" => $additional_headers, // pozor! pokud bude toto nastaveno, probiha odesilani jinak, viz nize
	),$params);

	// toto jsou zastarale parametry...
	if(isset($params["body_charset"])){ $params["charset"] = $params["body_charset"]; }
	if(isset($params["body_mime_type"])){ $params["mime_type"] = $params["body_mime_type"]; }

	if(isset($params["attachment"])){ $params["attachments"][] = $params["attachment"]; }

	//if(is_array($params["to"])){ $params["to"] = join(", ",array_unique($params["to"])); }
	//if(is_array($params["cc"])){ $params["cc"] = join(", ",array_unique($params["cc"])); }
	//if(is_array($params["bcc"])){ $params["bcc"] = join(", ",array_unique($params["bcc"])); }

	$FROM = trim($params['from']);
	$FROM_NAME = $params['from_name'];
	
	// "John Doe <john.doe@example.com>" -> "John Doe", "john.doe@example.com"
	if(preg_match('/^[\'"]?(.+?)[\'"]?\s+<([^@<>"\']+@[^@<>"\']+)>$/',$FROM,$matches)){
		$FROM = $matches[2];
		$FROM_NAME = $matches[1];
	}

	$BCC = array();
	if(isset($params['bcc'])){ $BCC[] = _sendmail_correct_address($params['bcc']);}
	if(defined("SENDMAIL_BCC_TO") && SENDMAIL_BCC_TO!=""){
		$BCC[] = SENDMAIL_BCC_TO;
	}elseif(defined("BCC_EMAIL") && BCC_EMAIL!=""){
		$BCC[] = BCC_EMAIL;
	}
	$BCC = join(", ",$BCC);
	$CC = _sendmail_correct_address($params['cc']);
	$RETURN_PATH = isset($params["return_path"]) ? $params["return_path"] : $FROM;
	$SUBJECT = _sendmail_escape_subject($params['subject'],$params["charset"]);
	$BODY = $params['body'];
	if(SENDMAIL_BODY_AUTO_PREFIX!=""){
		$BODY = SENDMAIL_BODY_AUTO_PREFIX.$BODY;
	}
	$TO = _sendmail_correct_address($params['to']);
	if(strlen(SENDMAIL_USE_TESTING_ADDRESS_TO)>0){
		$BODY = "PUVODNI ADRESAT: $TO\nPUVODNI CC: $CC\nPUVODNI BCC: $BCC\n\n$BODY"; // TODO: put this information into messages header
		$TO = SENDMAIL_USE_TESTING_ADDRESS_TO;
		$CC = "";
		$BCC = "";
	}

	// pokud mame v parametrech headers, jedna se o predem pripraveny e-mail....
	// TODO: dodelat - je to zatim neciste reseni...
	if(isset($params["headers"])){
		$BODY = $params["body"];
		$HEADERS = $params["headers"];

		$out = array(
			"to" => $TO,
			"subject" => $SUBJECT,
			"headers" => $HEADERS,
			"body" => $BODY,
		);

		if($params["build_message_only"]){
			return $out;
		}

		if(function_exists("sendmail_hook_send")){
			return sendmail_hook_send($out,$orig_params);
		}

		if(SENDMAIL_DO_NOT_SEND_MAILS==false){
			if(preg_match("/([^@<>\"']+)@([^@<>\"']+)/",$RETURN_PATH,$matches)){
				putenv("QMAILUSER=$matches[1]");
				putenv("QMAILHOST=$matches[2]");
			}
			mail($TO,$SUBJECT,$BODY,$HEADERS);
		}
		return $out;
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
		$_from = $FROM_NAME ? _sendmail_escape_subject($FROM_NAME,$BODY_CHARSET)." <$FROM>" : $FROM;
		$HEADERS .= "From: $_from\n";
		$HEADERS .= "Reply-To: $FROM\n";
		if($BCC!=""){
			$HEADERS .= "bcc: $BCC\n";
		}
		if($CC!=""){
			$HEADERS .= "cc: $CC\n";
		}

		$HEADERS .= "MIME-Version: 1.0\n";
		$HEADERS .= "Content-Type: $BODY_MIME_TYPE".($BODY_CHARSET!="" ? "; charset=$BODY_CHARSET" : "")."\n";
		$HEADERS .= "Content-Transfer-Encoding: $params[transfer_encoding]\n";
		if ($RETURN_PATH) {
			$HEADERS .= "Return-Path: $RETURN_PATH\n";
		}

		if($params["transfer_encoding"]=="quoted-printable"){
			$BODY = _sendmail_quoted_printable_encode(_sendmail_lf_to_crlf($BODY));
		}

	}else{
		$mailfile = new _CMailFile($ATTACHMENTS[0]["body"],array(
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

		// pokud jsou, vlozime do e-mailu dalsi prilohy
		for($i=1;$i<sizeof($ATTACHMENTS);$i++){
			$mailfile->attach_file($ATTACHMENTS[$i]['filename'],$ATTACHMENTS[$i]['body'],$ATTACHMENTS[$i]['mime_type']);
		}

		$mail_ar = $mailfile->getfile();
		$BODY = $mail_ar["body"];
		$HEADERS = $mail_ar["headers"];
	}

	$HEADERS = trim($HEADERS); // na konci hlavicky byl prazdny radek, ve zprave tak byly hlavicky a telo oddeleny 2 radky

	$out = array(
		"to" => $TO,
		"from" => $FROM,
		"bcc" => $BCC,
		"cc" => $CC,
		"return_path" => $RETURN_PATH,
		"subject" => $SUBJECT,
		"headers" => $HEADERS,
		"body" => $BODY,
	);

	// v $BODY nechceme sekvence \r\n, funguje to spatne
	// problemy nastavaly v pripade pouziti quoted-printable kodovani
	$BODY = str_replace("\r","",$BODY);

	if($params["build_message_only"]){
		return $out;
	}

	if(function_exists("sendmail_hook_send")){
		return sendmail_hook_send($out,$orig_params);
	}

	if(SENDMAIL_DO_NOT_SEND_MAILS==false){
		if(preg_match("/([^@<>\"']+)@([^@<>\"']+)/",$RETURN_PATH,$matches)){
			putenv("QMAILUSER=$matches[1]");
			putenv("QMAILHOST=$matches[2]");
		}
		mail($TO,$SUBJECT,$BODY,$HEADERS);
	}

	return $out;
}

/**
* sendhtmlmail(array(
*		"from" => "test@test.com",
*		"to" => "user@gmail.com",
*		"charset" => "ISO-8859-2",
*
*		"plain" => "Plain text version",
*		"html" => "<html>Html version<img src="cid:c8792dkQW"><br><img src="cid:tytdk2392981"></html>",
*		"images" => array(
*			array(
*				"filename" => "sea.gif",
*				"content" => $binary_content,
*				"cid" => "c8792dkQW",
*			),
*			array(
*				"filename" => "mountain.jpg",
*				"content" => $binary_content_2,
*				"cid" => "tytdk2392981",
*			)
*		)
*	))
*/
function sendhtmlmail($options){
	$options = array_merge(array(
		"subject" => "",
		"charset" => SENDMAIL_DEFAULT_BODY_CHARSET,
		"plain" => "",
		"html" => "",
		"images" => array(),
	),$options);


	$rel_boundary = _sendmail_get_boundary();
	$alt_boundary = _sendmail_get_boundary();

	$body = array();
	$body[] = "Content-Type: multipart/related; boundary=\"$rel_boundary\"";
	$body[] = "";
	$body[] = "--$rel_boundary";
	$body[] = "Content-Type: multipart/alternative; boundary=\"$alt_boundary\"";
	$body[] = "";
	$body[] = "--$alt_boundary";
	$body[] = "Content-Type: text/plain; charset=$options[charset]";
	$body[] = "Content-Transfer-Encoding: quoted-printable";
	$body[] = "";
	$body[] = _sendmail_quoted_printable_encode($options["plain"]);
	$body[] = "";
	$body[] = "--$alt_boundary";
	$body[] = "Content-Type: text/html; charset=$options[charset]";
	$body[] = "Content-Transfer-Encoding: quoted-printable";
	$body[] = "";
	$body[] = _sendmail_quoted_printable_encode($options["html"]);
	$body[] = "";
	$body[] = "--$alt_boundary--";

	foreach($options["images"] as $im){
		$_suffix = strtolower(preg_replace('/.*\.([^.]+)$/',"\\1",$im["filename"]));
		$_suffix=="jpg" && ($_suffix = "jpeg");
		$_mtype = "image/$_suffix";

		$body[] = "--$rel_boundary";
		$body[] = "Content-Type: image/$_suffix; name=\"$im[filename]\"";
		$body[] = "Content-Transfer-Encoding: base64";
		$body[] = "X-Attachment-Id: $im[cid]";
		$body[] = "Content-ID: <$im[cid]>";
		$body[] = "";
		$body[] = _sendmail_base64_encode($im["content"]);
	}
	$body[] = "--$rel_boundary--";

	//$boundary = _sendmail_get_boundary();
	//$body = "--$boundary\n$body\n--$boundary--";

	$options["mime_type"] = array_shift($body); // v prvnim radku je mime_type - momentalne to bude vzdy multipart/related; v budoucnu to muze byt multipart/mixed, pokud budou nejake prilohy
	$options["mime_type"] = preg_replace("/^Content-Type: /","",$options["mime_type"]);
	array_shift($body); // prazdny radek
	$options["body"] = join("\n",$body);
	$options["subject"] = _sendmail_escape_subject($options["subject"],$options["charset"]);
	$options["charset"] = "";
	$options["transfer_encoding"] = "7bit";
	unset($options["plain"]);
	unset($options["html"]);
	unset($options["images"]);
	return sendmail($options);
}

function _sendmail_get_boundary(){
	static $counter;
	if(!isset($counter)){ $counter = 0; }
	$counter++;
	return "----=_".$counter."a".substr(md5(uniqid('').$counter),0,27-strlen($counter));
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
	if(Translate::CheckEncoding($subject,"ascii")){ return $subject; }
	$out = array();
	$escape_in_use = false;
	$out[] = "=?$charset?Q?";
	for($i=0;$i<strlen($subject);$i++){
		$c = $subject[$i];
		if(in_array($c,array("=","?",":","/","_","[","]")) || !Translate::CheckEncoding($c,"ascii")){
			$out[] = "=".strtoupper(dechex(ord($c)));
			$escape_in_use = true;
		}else{
			// RFC 2047 dovoluje mezeru nahradit podtrzitkem
			$out[] = ($c==" ")?"_":$c;
			if ($c==" ") $escape_in_use = true;
		}
	}
	if(!$escape_in_use){ return $subject; }

	$out[] = "?=";
	return join("",$out);
}

function _sendmail_quoted_printable_encode($string,$options = array()){
	$options = array_merge(array(
		"split_up_long_lines_in_spaces" => true,
	),$options);

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
	
	if($options["split_up_long_lines_in_spaces"]){
		$ar = preg_split('/\r\n/',$string);
		$out = array();
		$max_length = 72; // takto nizka hodnota byla zjistena zkusmo - souvisi to se rozmezim {71,73}, ktere je pouzito nize
		for($i=0;$i<sizeof($ar);$i++){
			$line = $ar[$i];
			if(strlen($line)<=$max_length){ $out[] = $line; continue; }
			$_line = "";
			$_first = true;
			$words = preg_split("/ /",$line);
			for($j=0;$j<sizeof($words);$j++){
				$word = $words[$j];
				$last_word = ($j+1)==sizeof($words);
				if((strlen($_line) + strlen($word) + ($_first ? 0 : 1)) >= $max_length){
					if(!$_first){ $out[] = $_line." ="; }
					$_line = "";
					$_first = true;
				}
				if(strlen($word)>=$max_length){ $out[] = $word.($last_word ? "" : " ="); continue; }
				$_line .= ($_first ? "" : " ").$word;
				$_first = false;
			}
			if(strlen($_line)){ $out[] = $_line; }
		}
		$string = join("\r\n",$out);
	}

	$string = preg_replace('/[^\r\n]{71,73}[^=\r\n]{2}/', "$0=\r\n", $string);
  return $string;
}

function _sendmail_base64_encode($content){
	return chunk_split(base64_encode($content),76,"\n");
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
// $mailfile = new _CMailFile($subject,$sendto,$replyto,$message,$filename,$mimetype);
// $mailfile->sendfile();
// TODO: zbavit se zavislosti na teto tride
class _CMailFile {
	var $subject;
	var $addr_to;
	var $text_body;
	var $text_body_mimetype;
	var $text_body_charset;
	var $text_encoded;
	var $mime_headers;
	var $mime_boundary;
	var $smtp_headers;
	
	//2009-01-06: konstruktor byl predelan
	//$subject,$to,$from,$cc,$bcc,$msg,$msg_mimetype,$msg_charset,$filename,&$file_content,$mimetype = "application/octet-stream", $mime_filename = false) 
	function _CMailFile(&$file_content,$options = array()){
		$this->mime_boundary = _sendmail_get_boundary();
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
		$this->_first_attachment = true;
		$this->text_encoded = $this->_attach_file($options["filename"],$file_content,$options["mime_type"]);
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

	function attach_file($filename,&$file_content,$mimetype = "application/octet-strlen"){
		$this->text_encoded .= $this->_attach_file($filename,$file_content,$mimetype);
	}

	function _attach_file($filename,&$file_content,$mimetype) {
		$encoded = $this->encode_file($filename,$file_content);

		$out = $this->_first_attachment ? "--$this->mime_boundary\n" : "\n";
		$out = $out . "Content-type: " . $mimetype . "; name=\"$filename\";\n";		
		$out = $out . "Content-Transfer-Encoding: base64\n";
		$out = $out . "Content-disposition: attachment; filename=\"$filename\"\n\n";
		$out = $out . $encoded . "\n";
		$out = $out . "--" . $this->mime_boundary;
		$this->_first_attachment = false;
		return $out; 
// added -- to notify email client attachment is done
	}

	function encode_file($sourcefile,&$contents) {
		$encoded = $this->my_chunk_split(base64_encode($contents));
		return $encoded;
	}

	function getfile(){
		$headers = $this->smtp_headers . $this->mime_headers;		
		$message = $this->text_body . $this->text_encoded. "--";
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
		//$out = $out . "X-attachments: $filename;\n\n"; // TODO: tady muze byt vice priloh....
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
