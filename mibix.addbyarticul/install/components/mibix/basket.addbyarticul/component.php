<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

// Проверка модуля
if(!CModule::IncludeModule("sale"))
{
    ShowError(GetMessage("SALE_MODULE_NOT_INSTALL"));
    return;
}

// Обработка параметров
$arParams["USE_SKU_SETTINGS"] = ($arParams["USE_SKU_SETTINGS"] == "N" ? "N" : "Y" );

// Обрабатываем запрос
if (check_bitrix_sessid() && $_SERVER['REQUEST_METHOD'] == "POST" && !empty($_REQUEST["id_articuls"]))
{
    // Обрабатываем входящие переменные
    $reqIds = mysql_real_escape_string( trim($_REQUEST["id_articuls"]) );
    $reqIds = str_replace(" ", "", $reqIds); // Удаляем пробелы
    $arReqIds = explode (",", $reqIds); // Получаем массив артикулов

    // Выход, если массив с артикулами не получен
    if (!is_array($arReqIds) || empty($arReqIds))
    {
        ShowError(GetMessage("MIBIX_REQUESTED_FILED"));
        return;
    }

    // Подключаем модуль iblock и catalog
    CModule::IncludeModule("iblock");
    CModule::IncludeModule("catalog");
    CModule::IncludeModule("sale");

    // Проходимся по товарам в базе сайта и находим товары, у которых артикул включает нужный нам префикс
    $goodAdded = false;
    $propArCode = "PROPERTY_".$arParams["PROPERTY_CODE"];
    $arFilter = Array("IBLOCK_ID"=>$arParams["IBLOCK_ID"],$propArCode=>$arReqIds);
    $res = CIBlockElement::GetList(Array(), $arFilter, false, false, Array("ID"));
    while($ob = $res->GetNextElement())
    {
        // Вытаскиваем основные поля элемента
        $arFields = $ob->GetFields();

        // Добавляем найденный элемент в корзину и помечаем в случае успешного добавления
        $NEW_PRODUCT_CODE = Add2BasketByProductID($arFields["ID"], 1);
        if($NEW_PRODUCT_CODE)
        {
            $goodAdded = true;
        }
    }

    // Поиск по SKU
    if ($arParams["USE_SKU_SETTINGS"]=="Y")
    {
        $propArCodeSKU = "PROPERTY_".$arParams["PROPERTY_CODE_SKU"];
        $arFilterSKU = Array("IBLOCK_ID"=>$arParams["IBLOCK_ID_SKU"],$propArCodeSKU=>$arReqIds);
        $resSKU = CIBlockElement::GetList(Array(), $arFilterSKU, false, false, Array("ID"));
        while ($obSKU = $resSKU->GetNextElement())
        {
            // Вытаскиваем основные поля элемента
            $arFieldsSKU = $obSKU->GetFields();

            // Добавляем найденный элемент в корзину и помечаем в случае успешного добавления
            $NEW_PRODUCT_CODE = Add2BasketByProductID($arFieldsSKU["ID"], 1);
            if($NEW_PRODUCT_CODE)
            {
                $goodAdded = true;
            }
        }
    }

    // Если в корзину добавились товары, то делаем принудительный редирект
    if ($goodAdded)
    {
        LocalRedirect($APPLICATION->GetCurPage());
    }
}

$this->IncludeComponentTemplate();