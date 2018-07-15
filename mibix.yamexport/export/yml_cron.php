<?
set_time_limit(0);

define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);
define('NO_AGENT_CHECK', true);
define("STATISTIC_SKIP_ACTIVITY_CHECK", true);

$MODULE_ID = "mibix.yamexport";
$DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT'] = realpath(dirname(__FILE__).'/../../../../');
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');
if (!CModule::IncludeModule($MODULE_ID) || !CModule::IncludeModule("iblock")) return;

// Получаем значения и вызываем функцию генерации XML-файла
$YAM_EXPORT = CMibixYandexExport::get_step_settings(1);
if(is_array($YAM_EXPORT) && count($YAM_EXPORT) > 0)
{
    $YAM_EXPORT_LIMIT = $YAM_EXPORT["step_limit"]; // количество элементов, обрабатываемых за 1 шаг
    $YAM_EXPORT_PATH = $DOCUMENT_ROOT . $YAM_EXPORT["step_path"]; // путь сохранения экспортируемого xml-файл

    CMibixYandexExport::CreateYML($YAM_EXPORT_PATH, $YAM_EXPORT_LIMIT, true);
}
else
{
    echo "ERROR CONFIG LOAD";
}

require($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/epilog_after.php");
?>
