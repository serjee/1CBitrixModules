<?php
$MODULE_ID = "mibix.minorder";
CModule::IncludeModule($MODULE_ID);

if (!CModule::IncludeModule("sale"))
{
    return false;
}

// Объявляем глобальные типы и подключаем языковый файл
global $DBType;
CModule::AddAutoloadClasses(
    $MODULE_ID,
    array(
        "CMinOrder" => "include.php",
    )
);
IncludeModuleLangFile(__FILE__);

// Проверяем и определяем опции
if(!(COption::GetOptionInt($MODULE_ID,"MO_MIN_PRICE")))
{
    COption::SetOptionInt($MODULE_ID,"MO_MIN_PRICE",0);
}
if(!(COption::GetOptionInt($MODULE_ID,"MO_MIN_COUNT_ITEM_TYPE")))
{
    COption::SetOptionInt($MODULE_ID,"MO_MIN_COUNT_ITEM_TYPE",0);
}
if(!(COption::GetOptionInt($MODULE_ID,"MO_MIN_COUNT_ITEM")))
{
    COption::SetOptionInt($MODULE_ID,"MO_MIN_COUNT_ITEM",0);
}

/**
 * Класс с основной логикой модуля. В целях защиты хранится в этом файле.
 */
class CMinOrder
{
    /**
     * Хэндлер, отслеживающий оформления заказа (вызывается перед оформлением)
     */
    function OnBeforeOrderAddHandler(&$arFields)
    {
        global $APPLICATION;

        // Определяем переменные, с которыми будем работать
        $MODULE_ID = "mibix.minorder";
        $MO_MIN_PRICE = COption::GetOptionInt($MODULE_ID, "MO_MIN_PRICE");
        $MO_MIN_COUNT_ITEM_TYPE = COption::GetOptionInt($MODULE_ID, "MO_MIN_COUNT_ITEM_TYPE");
        $MO_MIN_COUNT_ITEM = COption::GetOptionInt($MODULE_ID, "MO_MIN_COUNT_ITEM");

        // Вытаскиваем текущую корзину покупателя
        $fUserID = IntVal(CSaleBasket::GetBasketUserID(True));
        $rsBasket = CSaleBasket::GetList(
            array(),
            array(
                "FUSER_ID" => $fUserID,
                "LID" => $arFields["LID"],
                "ORDER_ID" => "NULL"
            ),
            false,
            false,
            array("ID", "QUANTITY")
        );

        // Подсчитываем количество товаров в корзине по их товарным позициям и по их общему количеству
        $quantityTypes = 0;
        $quantityItems = 0;
        while ($arItem = $rsBasket->Fetch())
        {
            $quantityTypes = $quantityTypes + $arItem['QUANTITY'];
            $quantityItems++;
        }

        // Если установлена минимальная сумма корзины
        if ($arFields["PRICE"] < $MO_MIN_PRICE)
        {
            $APPLICATION->throwException(GetMessage("MIBIX_MO_ERROR_MIN_PRICE", Array ("#MIN_PRICE#" => SaleFormatCurrency($MO_MIN_PRICE, $arFields["CURRENCY"]))));
            return false; // Отмена заказа
        }

        // Если установлено минимальное количество товарных позиций
        if ($quantityTypes < $MO_MIN_COUNT_ITEM_TYPE)
        {
            $APPLICATION->throwException(GetMessage("MIBIX_MO_ERROR_MIN_COUNT_ITEM_TYPE", Array ("#MIN_COUNT_TYPE#" => $MO_MIN_COUNT_ITEM_TYPE)));
            return false; // Отмена заказа
        }

        // Если установлено минимальное количество товарных позиций
        if ($quantityItems < $MO_MIN_COUNT_ITEM)
        {
            $APPLICATION->throwException(GetMessage("MIBIX_MO_ERROR_MIN_COUNT_ITEM", Array ("#MIN_COUNT_ITEM#" => $MO_MIN_COUNT_ITEM)));
            return false; // Отмена заказа
        }

        // Отмена заказа
        return false;
    }
}
?>