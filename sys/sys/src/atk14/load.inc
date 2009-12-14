<?
$__PATH__ = dirname(__FILE__);
require_once("$__PATH__/atk14_timer.inc");
require_once("$__PATH__/atk14_controller.inc");
require_once("$__PATH__/atk14_dispatcher.inc");
require_once("$__PATH__/atk14_locale.inc");
require_once("$__PATH__/atk14_global.inc");
require_once("$__PATH__/atk14_url.inc");
require_once("$__PATH__/atk14_session.inc");
require_once("$__PATH__/atk14_flash.inc");
require_once("$__PATH__/atk14_form.inc");
require_once("$__PATH__/atk14_utils.inc");
require_once("$__PATH__/atk14_require.inc");
require_once("$__PATH__/atk14_mailer.inc");
require_once("$__PATH__/atk14_sorting.inc");
require_once("$__PATH__/atk14_client.inc");

$ATK14_GLOBAL = &Atk14Global::GetInstance();

Atk14Utils::LoadConfig();

if(file_exists($ATK14_GLOBAL->getApplicationPath()."forms/fields.inc")){
	require_once($ATK14_GLOBAL->getApplicationPath()."forms/fields.inc");
}

if(file_exists($ATK14_GLOBAL->getApplicationPath()."forms/widgets.inc")){
	require_once($ATK14_GLOBAL->getApplicationPath()."forms/widgets.inc");
}
?>
