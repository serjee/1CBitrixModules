<?
set_time_limit(0);

define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);
define("LANG", "ru");
define('NO_AGENT_CHECK', true);
define("STATISTIC_SKIP_ACTIVITY_CHECK", true);

$MODULE_ID = "mibix.battledis";
$DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT'] = realpath(dirname(__FILE__).'/../../../../');
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');
if (!CModule::IncludeModule($MODULE_ID)) return;

// Обновляем счетчики и скидки
CMibixDisBattleComponentModel::updateAllItemCounters();
CMibixDisBattleComponentModel::updateDicountItems();

require($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/epilog_after.php");
?>
