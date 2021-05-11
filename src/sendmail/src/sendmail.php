<?php
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
*
*		$mail_ar = sendmail(array(...));
*		echo $mail_ar["to"];
*		echo $mail_ar["body"];
*		print_r($mail_ar["accepted_for_delivery"]); // true, false or null when sending is suppressed by environmet
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
*			"from_name" => "",
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
function sendmail($params = array(),$subject = "",$message = "",$additional_headers = null,$additional_parameters = null){
	require_once(__DIR__ . "/constants.php");

	$to = SENDMAIL_EMPTY_TO_REPLACE;
	if(is_string($params)){
		$to = $params;
		$params = array();
	}
	$orig_params = $params;
	$params = array_merge(array(
		"from" => SENDMAIL_DEFAULT_FROM, // john.doe@example.com
		"from_name" => SENDMAIL_DEFAULT_FROM_NAME, // "John Doe"
		"to" => $to,
		"cc" => null,
		"bcc" => null,
		"return_path" => null,
		"reply_to" => null,
		"reply_to_name" => null,
		"date" => gmdate('D, d M Y H:i:s \G\M\T', time()),
		"subject" => $subject,
		"body" => $message,
		"transfer_encoding" => SENDMAIL_DEFAULT_TRANSFER_ENCODING, // zpusob kodovani body (nikoli prilohy): "8bit" nebo "quoted-printable"
		"charset" => SENDMAIL_DEFAULT_BODY_CHARSET,
		"mime_type" => SENDMAIL_DEFAULT_BODY_MIME_TYPE,
		"attachments" => array(),
		"attachment" => null,
		"build_message_only" => false,

		"headers" => $additional_headers, // pozor! pokud bude toto nastaveno, probiha odesilani jinak, viz nize

		"additional_parameters" => $additional_parameters, // additional parameters for PHP function mail()
	),$params);

	$additional_parameters = is_null($params["additional_parameters"]) ? SENDMAIL_MAIL_ADDITIONAL_PARAMETERS : $params["additional_parameters"];

	// toto jsou zastarale parametry...
	if(isset($params["body_charset"])){ $params["charset"] = $params["body_charset"]; }
	if(isset($params["body_mime_type"])){ $params["mime_type"] = $params["body_mime_type"]; }

	if(isset($params["attachment"])){ $params["attachments"][] = $params["attachment"]; }

	//if(is_array($params["to"])){ $params["to"] = join(", ",array_unique($params["to"])); }
	//if(is_array($params["cc"])){ $params["cc"] = join(", ",array_unique($params["cc"])); }
	//if(is_array($params["bcc"])){ $params["bcc"] = join(", ",array_unique($params["bcc"])); }

	list($FROM,$FROM_NAME) = _sendmail_parse_email_and_name($params["from"],$params["from_name"]);
	list($REPLY_TO,$REPLY_TO_NAME) = _sendmail_parse_email_and_name($params["reply_to"],$params["reply_to_name"]);


	$BCC = array();
	if($params['bcc']){ $BCC[] = _sendmail_correct_address($params['bcc']);}
	if(strlen(SENDMAIL_BCC_TO)){
		$BCC[] = SENDMAIL_BCC_TO;
	}elseif(defined("BCC_EMAIL") && BCC_EMAIL!=""){
		$BCC[] = BCC_EMAIL;
	}
	$BCC = join(", ",$BCC);
	$CC = _sendmail_correct_address($params['cc']);
	$RETURN_PATH = $params["return_path"] ? $params["return_path"] : $FROM;
	$DATE = $params["date"];
	$SUBJECT = _sendmail_escape_subject($params['subject'],$params["charset"]);
	$BODY = $params['body'];
	if(SENDMAIL_BODY_AUTO_PREFIX!="" && !$params["build_message_only"]){
		// we don't want to prepend SENDMAIL_BODY_AUTO_PREFIX when the message is just being built
		$BODY = SENDMAIL_BODY_AUTO_PREFIX.$BODY;
	}
	$TO = _sendmail_correct_address($params['to']);
	if(strlen(SENDMAIL_USE_TESTING_ADDRESS_TO)>0){
		$BODY = "PUVODNI ADRESAT: $TO\nPUVODNI CC: $CC\nPUVODNI BCC: $BCC\n\n$BODY"; // TODO: put this information into messages header
		$TO = SENDMAIL_USE_TESTING_ADDRESS_TO;
		$CC = "";
		$BCC = "";
	}


	if(preg_match("/([^@<>\"']+)@([^@<>\"']+)/",$RETURN_PATH,$matches)){
		putenv("QMAILUSER=$matches[1]");
		putenv("QMAILHOST=$matches[2]");
		if(is_null($additional_parameters)){
			$additional_parameters = "-f$matches[1]@$matches[2]";
		}
	}

	// pokud mame v parametrech headers, jedna se o predem pripraveny e-mail....
	// TODO: dodelat - je to zatim neciste reseni...
	if($params["headers"]){
		$BODY = $params["body"];
		$HEADERS = $params["headers"];

		$out = array(
			"to" => $TO,
			"subject" => $SUBJECT,
			"headers" => $HEADERS,
			"body" => $BODY,
			"accepted_for_delivery" => null,

			"additional_parameters" => $additional_parameters,
		);

		if($params["build_message_only"]){
			return $out;
		}

		if(function_exists("sendmail_hook_send")){
			return sendmail_hook_send($out,$orig_params);
		}

		if(SENDMAIL_DO_NOT_SEND_MAILS==false){
			$out["accepted_for_delivery"] = _sendmail_mail($TO,$SUBJECT,$BODY,$HEADERS,$additional_parameters);
		}
		return $out;
	}

	$BODY_MIME_TYPE = $params["mime_type"];
	$BODY_CHARSET = $params["charset"];

	$ATTACHMENTS = array();
	if($params['attachments']){
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
		$_from = _sendmail_render_email_address($FROM,$FROM_NAME,$BODY_CHARSET);
		$_reply_to = $REPLY_TO ? _sendmail_render_email_address($REPLY_TO,$REPLY_TO_NAME,$BODY_CHARSET) : $_from;
		$HEADERS .= "From: $_from\n";
		$HEADERS .= "Reply-To: $_reply_to\n";
		if($BCC!=""){
			$HEADERS .= "Bcc: $BCC\n";
		}
		if($CC!=""){
			$HEADERS .= "Cc: $CC\n";
		}

		$HEADERS .= "MIME-Version: 1.0\n";
		$HEADERS .= "Content-Type: $BODY_MIME_TYPE".($BODY_CHARSET!="" ? "; charset=$BODY_CHARSET" : "")."\n";
		$HEADERS .= "Content-Transfer-Encoding: $params[transfer_encoding]\n";
		if ($RETURN_PATH) {
			$HEADERS .= "Return-Path: $RETURN_PATH\n";
		}
		if($DATE){
			$HEADERS .= "Date: $DATE\n";
		}

		if($params["transfer_encoding"]=="quoted-printable"){
			$BODY = _sendmail_quoted_printable_encode(_sendmail_lf_to_crlf($BODY));
		}

	}else{
		$mailfile = new _CMailFile($ATTACHMENTS[0]["body"],array(
			"subject" => $SUBJECT,

			"to" => $TO,
			"from" => $FROM,
			"from_name" => $FROM_NAME,
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
		"accepted_for_delivery" => null,

		"additional_parameters" => $additional_parameters,
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
		$out["accepted_for_delivery"] = _sendmail_mail($TO,$SUBJECT,$BODY,$HEADERS,$additional_parameters);
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
	require_once(__DIR__ . "/constants.php");

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
	$body[] = _sendmail_quoted_printable_encode(_sendmail_lf_to_crlf($options["plain"]));
	$body[] = "";
	$body[] = "--$alt_boundary";
	$body[] = "Content-Type: text/html; charset=$options[charset]";
	$body[] = "Content-Transfer-Encoding: quoted-printable";
	$body[] = "";
	$body[] = _sendmail_quoted_printable_encode(_sendmail_lf_to_crlf($options["html"]));
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

/**
 * $headers .= "From: "._sendmail_escape_email_name('John Brus').' <john@brus.com>'; // From: "John Brus" <john@brus.com>
 */
function _sendmail_escape_email_name($from_name,$charset = null){
	$out = _sendmail_escape_subject($from_name);
	if($out==$from_name){
		$out = '"'.str_replace('"','\"',$out).'"';
	}
	return $out;
}

/**
 *	$from = "john@doe.com";
 *	$from_name = "John Doe";
 *	// or
 *	$from = "John Doe <john@doe.com>";
 *	$from_name = "";
 * 
 *	list($from,$from_name) = _sendmail_parse_email_and_name($from,$from_name);
 *
 * 	echo $from; // "john@doe.com"
 * 	echo $from_name; // "John Doe"
 */
function _sendmail_parse_email_and_name($from,$from_name){
	$FROM = trim($from);
	$FROM_NAME = $from_name;
	
	// "John Doe <john.doe@example.com>" -> "John Doe", "john.doe@example.com"
	if(preg_match('/^[\'"]?(.+?)[\'"]?\s+<([^@<>"\']+@[^@<>"\']+)>$/',$FROM,$matches)){
		$FROM = $matches[2];
		$FROM_NAME = str_replace('\"','"',$matches[1]); // ?? is this ok? 'John Doe \"aka\" John D.' -> 'John Doe "aka" John D.'
	}

	return array($FROM,$FROM_NAME);
}

/**
 *	$from = _sendmail_render_email_address("john@doe.com","John Doe","UTF-8");
 */
function _sendmail_render_email_address($from,$from_name,$charset){
	return $from_name ? _sendmail_escape_email_name($from_name,$charset)." <$from>" : $from;
}

function _sendmail_escape_subject($subject,$charset = null){
	require_once(__DIR__ . "/constants.php");

	if(Translate::CheckEncoding($subject,"ascii")){ return $subject; }

	if(!$charset){ $charset = SENDMAIL_DEFAULT_BODY_CHARSET; }

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

function _sendmail_mail($TO,$SUBJECT,$BODY,$HEADERS,$PARAMETERS = ""){
	if(!$TO){
		error_log("sendmail: no recipients (To:) were specified in the message \"$SUBJECT\"");
	}
	return mail($TO,$SUBJECT,$BODY,$HEADERS,(string)$PARAMETERS);
}
