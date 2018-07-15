<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arParams["CODE_GROUP"] = trim($arParams["CODE_GROUP"]);
if (strlen($arParams["CODE_GROUP"]) <= 0) $arParams["CODE_GROUP"] = "";

// Проверка подключения модуля
if(!CModule::IncludeModule("mibix.battle"))
{
    ShowError(GetMessage("MIBIX_BATTLE_MODULE_NOT_INSTALL"));
    return;
}

// Формируем начальный адрес сайта
$urlLink = 'http://';
$default_port = 80;
if (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS']=='on')) {
    $urlLink = 'https://';
    $default_port = 443;
}
// Определяем домен
$urlLink .= $_SERVER['SERVER_NAME'];
// Определяем порт
if ($_SERVER['SERVER_PORT'] != $default_port) {
    $urlLink .= ':'.$_SERVER['SERVER_PORT'];
}

$arTitles = Array();
$arBattleElements = Array();
$battleExist = false;

// текущее время и дата окончания
$currentTime = time();
//$currentDate = date($DB->DateFormatToPHP(FORMAT_DATETIME), time());

// Выводим рандомную битву по году группы
$rsBattleList = CMibixBattleComponentModel::getBattleList($arParams["CODE_GROUP"]);
if ($arBattleItem = $rsBattleList->Fetch())
{
    // Выводим информацию о каждом элементе
    $arSelectItems = Array("ID", "IBLOCK_ID", "NAME", "DATE_ACTIVE_FROM", "PREVIEW_TEXT", "PREVIEW_PICTURE", "DETAIL_TEXT", "DETAIL_PICTURE", "DETAIL_PAGE_URL", "PROPERTY_*");
    $arBattleItemsIDs = explode(",", $arBattleItem["battle_items"]);
    $arFilterItems = Array("IBLOCK_ID"=>IntVal($arBattleItem["iblock_id"]), "ACTIVE"=>"Y", "ID"=>$arBattleItemsIDs);
    $resItems = CIBlockElement::GetList(Array(), $arFilterItems, false, false, $arSelectItems);
    while($obItem = $resItems->GetNextElement())
    {
        $arFieldsItem = $obItem->GetFields();
        $arFieldsItem["PROPERTIES"] = $obItem->GetProperties();

        // Название
        $arBattleElements[$arFieldsItem["ID"]]["NAME"] = CMibixBattleComponentModel::getBattleTextValue($arBattleItem["battle_title"], $arFieldsItem);

        // Описание
        $arBattleElements[$arFieldsItem["ID"]]["DESC"] = TruncateText(CMibixBattleComponentModel::getBattleTextValue($arBattleItem["battle_text"], $arFieldsItem), 74);

        // Массив URL изображений элемента
        $arBattleElements[$arFieldsItem["ID"]]["IMAGES"] = CMibixBattleComponentModel::getBattlePictures($arBattleItem["battle_pictures"], $arFieldsItem);

        // Ссылка на товар
        $strPostedLink = $urlLink . CMibixBattleComponentModel::getBattleTextValue($arBattleItem["battle_links"], $arFieldsItem) . "#" . $arBattleItem["id"];
        $arBattleElements[$arFieldsItem["ID"]]["LINK"] = $strPostedLink;

        // Обновление счетчика голосов для элемента в базе если не используется подсчет через cron
        if($arBattleItem["is_cron_count"] != "Y")
            CMibixBattleComponentModel::updateItemCounters($arBattleItem, $arFieldsItem["ID"], $strPostedLink);

        // Количество голосов из таблицы b_mibix_battle_votes
        $numCounts = CMibixBattleComponentModel::getBattleVotes($arBattleItem["id"], $arFieldsItem["ID"]);
        $arBattleElements[$arFieldsItem["ID"]]["VOTES"] = $numCounts;
        $arBattleElements[$arFieldsItem["ID"]]["VOTES_STRING"] = CMibixBattleComponentModel::getWordOfNum($numCounts, GetMessage("MIBIX_BATTLE_CMP_VOTES"));

        // Запоминаем название для формирования title в соц.сети
        $arTitles[$arFieldsItem["ID"]] = $arFieldsItem["NAME"];
    }
}
else
{
    ShowMessage(GetMessage("MIBIX_BATTLE_CMP_EMPTY_BATTLE"));
    return;
}

// Стилизация колонок (2 или 3)
$widthcolumn = 33.333333;
$countItems = count($arBattleItemsIDs);
if($countItems<3 || ($countItems%3!=0))
    $widthcolumn = 50;

// Флаг окончания битвы
$battleEnd = false;
if ($currentTime>=strtotime($arBattleItem["date_finish"])) $battleEnd = true;

// массив результатов для шаблона
$arResult = array(
    'BATTLE_ID' => $arBattleItem["id"],
    'BATTLE_NAME' => $arBattleItem["name_battle"],
    'DATE_START' => $arBattleItem["date_start"],
    'TIME_CURRENT' => $currentTime,
    'DATE_FINISH' => strtotime($arBattleItem["date_finish"]),
    'BATTLE_END' => $battleEnd,
    'ELEMENTS' => $arBattleElements,
    'ELEMENTS_CNT' => count($arBattleElements),
    'IS_PROTECTED' => $arBattleItem["is_protection"],
    'VK_ENABLED' => $arBattleItem["enabled_vk"],
    'FB_ENABLED' => $arBattleItem["enabled_fb"],
    'TW_ENABLED' => $arBattleItem["enabled_tw"],
    'OK_ENABLED' => $arBattleItem["enabled_ok"],
    'ML_ENABLED' => $arBattleItem["enabled_ml"],
    'PI_ENABLED' => $arBattleItem["enabled_pi"],
    'AR_TITLES' => $arTitles,
    'SITE_HOST' => $urlLink,
    'COLUMN_WIDTH' => $widthcolumn
);

// Дополнительные параметры
$arResult['JQUERY_ENABLED'] = 'N';
if ($arParams['JQUERY_ENABLED'] == 'Y') $arResult['JQUERY_ENABLED'] = 'Y';
$arResult['FANCYBOX_ENABLED'] = 'N';
if ($arParams['FANCYBOX_ENABLED'] == 'Y') $arResult['FANCYBOX_ENABLED'] = 'Y';

$this->IncludeComponentTemplate();