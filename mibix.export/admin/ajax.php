<?
define("STOP_STATISTICS", true);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
//CComponentUtil::__IncludeLang(dirname(__FILE__), "/ajax.php");
IncludeModuleLangFile(__FILE__);

$MODULE_ID = "mibix.export";
CModule::IncludeModule($MODULE_ID);
if (!CModule::IncludeModule("iblock")) return;

$arRes = array();
global $USER, $APPLICATION;
if (!check_bitrix_sessid() || $_SERVER["REQUEST_METHOD"] != "POST") return;

CUtil::JSPostUnescape();


/* === ИСТОЧНИКИ ДАННЫХ === */

// Получение списка инфоблоков на основе типа и сайта
if (!empty($_POST["action"]) && $_POST["action"]=="get_iblocks_options")
{
    $arParams = array(
        'TYPE'      => (!empty($_POST["iblock_type"]) ? $_POST["iblock_type"] : ""),
        'SITE_ID'   => ((!empty($_POST["site_id"]) && $_POST["site_id"]!="") ? $_POST["site_id"]: "")
    );

    // Инфоблоки
    $arRes["IBLOCK_OPTIONS"] = '<option value="none">'.GetMessage("MIBIX_EXPORT_AJAX_CHECK_IBLOCK").'</option>';
    $arRes["IBLOCK_OPTIONS"] .= CMibixExportControls::getSelectBoxIBlockId(false, -1, $arParams["SITE_ID"], $arParams["TYPE"], false);
}

// Получение списка разделов на основе ID инфоблока
if (!empty($_POST["action"]) && $_POST["action"]=="get_iblock_sections")
{
    $IBLOCK_ID = (!empty($_POST["iblock_id"]) ? intval($_POST["iblock_id"]) : 0);
    $arRes["IBLOCK_SECTIONS"] = CMibixExportControls::getSelectBoxSections(false, '', $IBLOCK_ID, false);
    $arRes["FILTER_NAME"] = CMibixExportControls::getSelectBoxFilterName($IBLOCK_ID, '', false);
}

/* === /ИСТОЧНИКИ ДАННЫХ === */

$APPLICATION->RestartBuffer();
header('Content-Type: application/json; charset='.LANG_CHARSET);
echo CUtil::PhpToJSObject($arRes);
die();

?>
