<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arParams["CODE_GROUP"] = trim($arParams["CODE_GROUP"]);
if (strlen($arParams["CODE_GROUP"]) <= 0) $arParams["CODE_GROUP"] = "";

// Проверка подключения модуля
if(!CModule::IncludeModule("mibix.battledis"))
{
    ShowError(GetMessage("MIBIX_BATTLEDIS_MODULE_NOT_INSTALL"));
    return;
}
if (!CModule::IncludeModule("sale") || !CModule::IncludeModule("iblock") || !CModule::IncludeModule("catalog"))
{
    ShowError(GetMessage("MIBIX_BATTLEDIS_MAIN_MOUDLES_NOT_EXIST"));
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
$rsBattleList = CMibixDisBattleComponentModel::getBattleList($arParams["CODE_GROUP"]);
if ($arBattleItem = $rsBattleList->Fetch())
{
    // Выводим информацию о каждом элементе
    $sumVoteItems = 0;
    $arSelectItems = Array("ID", "IBLOCK_ID", "NAME", "DATE_ACTIVE_FROM", "PREVIEW_TEXT", "PREVIEW_PICTURE", "DETAIL_TEXT", "DETAIL_PICTURE", "DETAIL_PAGE_URL", "PROPERTY_*");
    $arBattleItemsIDs = explode(",", $arBattleItem["battle_items"]);
    $arFilterItems = Array("IBLOCK_ID"=>IntVal($arBattleItem["iblock_id"]), "ACTIVE"=>"Y", "ID"=>$arBattleItemsIDs);
    $resItems = CIBlockElement::GetList(Array(), $arFilterItems, false, false, $arSelectItems);
    while($obItem = $resItems->GetNextElement())
    {
        $arFieldsItem = $obItem->GetFields();
        $arFieldsItem["PROPERTIES"] = $obItem->GetProperties();

        // Название
        $arBattleElements[$arFieldsItem["ID"]]["NAME"] = CMibixDisBattleComponentModel::getBattleTextValue($arBattleItem["battle_title"], $arFieldsItem);

        // Описание
        $arBattleElements[$arFieldsItem["ID"]]["DESC"] = TruncateText(CMibixDisBattleComponentModel::getBattleTextValue($arBattleItem["battle_text"], $arFieldsItem), 70);

        // Массив URL изображений элемента
        $arBattleElements[$arFieldsItem["ID"]]["IMAGES"] = CMibixDisBattleComponentModel::getBattlePictures($arBattleItem["battle_pictures"], $arFieldsItem);

        // Ссылка на товар
        $strPostedLink = $urlLink . CMibixDisBattleComponentModel::getBattleTextValue($arBattleItem["battle_links"], $arFieldsItem) . "#" . $arBattleItem["id"];
        $arBattleElements[$arFieldsItem["ID"]]["LINK"] = $strPostedLink;

        // Обновление счетчика голосов для элемента в базе если не используется подсчет через cron
        if($arBattleItem["is_cron_count"] != "Y")
            CMibixDisBattleComponentModel::updateItemCounters($arBattleItem, $arFieldsItem["ID"], $strPostedLink);

        // Количество голосов из таблицы b_mibix_disbattle_votes
        $numCounts = CMibixDisBattleComponentModel::getBattleVotes($arBattleItem["id"], $arFieldsItem["ID"]);
        $sumVoteItems += $numCounts;
        $arBattleElements[$arFieldsItem["ID"]]["VOTES"] = $numCounts;
        $arBattleElements[$arFieldsItem["ID"]]["VOTES_STRING"] = CMibixDisBattleComponentModel::getWordOfNum($numCounts, GetMessage("MIBIX_BATTLEDIS_CMP_VOTES"));

        // Запоминаем название для формирования title в соц.сети
        $arTitles[$arFieldsItem["ID"]] = $arFieldsItem["NAME"];
    }

    // Зная общее кол. голосов, вычисляем % скидки для каждого элмента и обновляем в базе
    foreach ($arBattleElements as $itID => $itData)
    {
        // Рассчет скидки для элемента (в процентах)
        $newDiscount = IntVal($arBattleItem["discount_all"])/count($arBattleItemsIDs);
        $viewTempDiscount = 100/count($arBattleItemsIDs);
        if($sumVoteItems>0) {
            $newDiscount = IntVal($itData["VOTES"] * IntVal($arBattleItem["discount_all"])) / $sumVoteItems;
            $viewTempDiscount = IntVal($itData["VOTES"] * 100) / $sumVoteItems; // для визуального отображения рассчитыаем от ста
            if($arBattleItem["discount_max"] > 0 && $newDiscount > $arBattleItem["discount_max"]) $newDiscount = $arBattleItem["discount_max"];
        }
        $newDiscount = round($newDiscount, 2);
        $arBattleElements[$itID]["DISCOUNT"] = $newDiscount;
        $arBattleElements[$itID]["DISCOUNT_VIEW"] = round($viewTempDiscount, 0);

        // Обновление скидки (если не через CRON)
        if($arBattleItem["is_cron_count"] != "Y")
            CMibixDisBattleComponentModel::updateDiscountForItem($arBattleItem, $itID, $newDiscount);

        // Цена товара
        $arBattleElements[$itID]["PRICE"] = CMibixDisBattleComponentModel::getItemPrice(IntVal($arBattleItem["iblock_id"]), $itID, $arBattleItem["price"]);
        $arBattleElements[$itID]["PRICE_DISCOUNT"] = CMibixDisBattleComponentModel::getItemPrice(IntVal($arBattleItem["iblock_id"]), $itID, $arBattleItem["price"], true);
    }
}
else
{
    ShowMessage(GetMessage("MIBIX_BATTLEDIS_CMP_EMPTY_BATTLE"));
    return;
}

// Стилизация колонок (2 или 3)
$count_col = 4;
$countItems = count($arBattleItemsIDs);
if($countItems<3 || ($countItems%3!=0))
    $count_col = 6;

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
    'IS_PRICE' => $arBattleItem["price"],
    'IS_INDICATOR' => $arBattleItem["is_indicator"],
    'IS_PROTECTED' => $arBattleItem["is_protection"],
    'VK_ENABLED' => $arBattleItem["enabled_vk"],
    'FB_ENABLED' => $arBattleItem["enabled_fb"],
    'TW_ENABLED' => $arBattleItem["enabled_tw"],
    'OK_ENABLED' => $arBattleItem["enabled_ok"],
    'ML_ENABLED' => $arBattleItem["enabled_ml"],
    'PI_ENABLED' => $arBattleItem["enabled_pi"],
    'AR_TITLES' => $arTitles,
    'SITE_HOST' => $urlLink,
    'COUNT_COL' => $count_col,
    'DISCOUNT_ALL' => $arBattleItem["discount_all"]
);

// Дополнительные параметры
$arResult['JQUERY_ENABLED'] = 'N';
if ($arParams['JQUERY_ENABLED'] == 'Y') $arResult['JQUERY_ENABLED'] = 'Y';

$arResult['FANCYBOX_ENABLED'] = 'N';
if ($arParams['FANCYBOX_ENABLED'] == 'Y') $arResult['FANCYBOX_ENABLED'] = 'Y';

$arResult['SHOW_PROGRESS_TOP_ACTIVE'] = 'N';
if ($arParams['SHOW_PROGRESS_TOP_ACTIVE'] == 'Y') $arResult['SHOW_PROGRESS_TOP_ACTIVE'] = 'Y';

$arResult['SHOW_PROGRESS_ACTIVE'] = 'N';
if ($arParams['SHOW_PROGRESS_ACTIVE'] == 'Y') $arResult['SHOW_PROGRESS_ACTIVE'] = 'Y';

$this->IncludeComponentTemplate();