<?php
error_reporting(255);
define("SENDMAIL_DO_NOT_SEND_MAILS",true);
define("SENDMAIL_DEFAULT_FROM","info@somewhere.com");
define("SENDMAIL_EMPTY_TO_REPLACE","dummy@localhost");
define("SENDMAIL_BCC_TO","big.brother@somewhere.com");

require("../../files/files.inc");
require("../../translate/translate.inc");
require("../sendmail.inc");
