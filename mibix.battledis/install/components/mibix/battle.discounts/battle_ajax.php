<?
define("STOP_STATISTICS", true);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
CComponentUtil::__IncludeLang(dirname($_SERVER["SCRIPT_NAME"]), "/battle_ajax.php");

if (!CModule::IncludeModule("mibix.battledis") || !CModule::IncludeModule("sale") || !CModule::IncludeModule("iblock") || !CModule::IncludeModule("catalog")) return;

global $USER, $APPLICATION;
if (!check_bitrix_sessid() || $_SERVER["REQUEST_METHOD"] != "POST") return;

CUtil::JSPostUnescape();

$RESULT_VOTED = false;

// Проверка запрашиваемого действия
if (isset($_POST["action"]) && strlen($_POST["action"]) > 0)
{
    // Добавление элемента в корзину
    if ( $_POST["action"] == "vote_check" )
    {
        $BATTLE_VOTE = trim($_POST["socid"]);
        $BATTLE_ID = intval($_POST["id_branddis"]);
        $RESULT_VOTED = CMibixDisBattleComponentModel::voteAccessCheck($BATTLE_VOTE, $BATTLE_ID);
    }
}

$APPLICATION->RestartBuffer();
header('Content-Type: application/json; charset='.LANG_CHARSET);
echo CUtil::PhpToJSObject($RESULT_VOTED);
die();
?>
