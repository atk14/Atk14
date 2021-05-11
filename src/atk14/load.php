<?php
$__PATH__ = dirname(__FILE__);
require_once("$__PATH__/atk14_exception.php");
require_once("$__PATH__/atk14_timer.php");
require_once("$__PATH__/atk14_controller.php");
require_once("$__PATH__/atk14_dispatcher.php");
require_once("$__PATH__/atk14_locale.php");
require_once("$__PATH__/atk14_global.php");
require_once("$__PATH__/atk14_router.php");
require_once("$__PATH__/atk14_url.php");
require_once("$__PATH__/atk14_session.php");
require_once("$__PATH__/atk14_flash.php");
require_once("$__PATH__/atk14_flash_message.php");
require_once("$__PATH__/atk14_form.php");
require_once("$__PATH__/atk14_utils.php");
require_once("$__PATH__/atk14_require.php");
require_once("$__PATH__/atk14_mailer.php");
require_once("$__PATH__/atk14_mailer_proxy.php");
require_once("$__PATH__/atk14_sorting.php");
require_once("$__PATH__/atk14_client.php");
require_once("$__PATH__/atk14_migration.php");
require_once("$__PATH__/atk14_robot.php");
require_once("$__PATH__/atk14_deployment_stage.php");
require_once("$__PATH__/atk14_smarty_utils.php");
if(ATK14_USE_SMARTY3){
	require_once("$__PATH__/atk14_smarty_base_v3.php");
}else{
	require_once("$__PATH__/atk14_smarty_base_v2.php");
}
require_once("$__PATH__/atk14_smarty.php");

require_once("$__PATH__/atk14_fixture.php");
require_once("$__PATH__/atk14_fixture_list.php");

require_once("$__PATH__/tc_atk14_base.php");
require_once("$__PATH__/tc_atk14_controller.php");
require_once("$__PATH__/tc_atk14_model.php");
require_once("$__PATH__/tc_atk14_field.php");
require_once("$__PATH__/tc_atk14_router.php");

global $ATK14_GLOBAL;
$ATK14_GLOBAL = Atk14Global::GetInstance();

Atk14Utils::LoadConfig();

atk14_require_once_if_exists($ATK14_GLOBAL->getApplicationPath()."forms/fields.php");
atk14_require_once_if_exists($ATK14_GLOBAL->getApplicationPath()."forms/widgets.php");
