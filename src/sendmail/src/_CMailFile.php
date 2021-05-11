<?php
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
	function __construct(&$file_content,$options = array()){
		$this->mime_boundary = _sendmail_get_boundary();
		$options = array_merge(array(
			"subject" => "",

			"to" => "",
			"from" => "",
			"from_name" => "",
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
		$this->smtp_headers = $this->write_smtpheaders($options["from"],$options["cc"],$options["bcc"],$options["from_name"]);
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
		$out = $out . "Content-Type: " . $mimetype . "; name=\"$filename\";\n";
		$out = $out . "Content-Transfer-Encoding: base64\n";
		$out = $out . "Content-Disposition: attachment; filename=\"$filename\"\n\n";
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

	// Notice: method _CMailFile::sendfile() is not being used
	function sendfile() {
		$headers = $this->smtp_headers . $this->mime_headers;		
		$message = $this->text_body . $this->text_encoded;
		_sendmail_mail($this->addr_to,$this->subject,$message,$headers);
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

	function write_smtpheaders($addr_from,$cc,$bcc,$from_name = "") {
		$_from = $from_name ? _sendmail_escape_email_name($from_name,$this->text_body_charset)." <$addr_from>" : $addr_from;
		$out = "From: $_from\n";
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
		if(function_exists("chunk_split")){
			return chunk_split($str,76,"\n");
		}

		// this is way too slow
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
