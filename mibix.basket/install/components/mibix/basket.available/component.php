<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arParams["PATH_TO_BASKET"] = Trim($arParams["PATH_TO_BASKET"]);
if (strlen($arParams["PATH_TO_BASKET"]) <= 0) $arParams["PATH_TO_BASKET"] = "/personal/cart/";

$arParams["PATH_TO_ORDER_MAKE"] = Trim($arParams["PATH_TO_ORDER_MAKE"]);
if (strlen($arParams["PATH_TO_ORDER_MAKE"]) <= 0) $arParams["PATH_TO_ORDER_MAKE"] = "/personal/order/make/";

$arParams["PATH_TO_AUTH"] = Trim($arParams["PATH_TO_AUTH"]);
if (strlen($arParams["PATH_TO_AUTH"]) <= 0) $arParams["PATH_TO_AUTH"] = "/auth/";

$arParams["PATH_TO_REGISTRATION"] = Trim($arParams["PATH_TO_REGISTRATION"]);
if (strlen($arParams["PATH_TO_REGISTRATION"]) <= 0) $arParams["PATH_TO_REGISTRATION"] = "/login/?register=yes";

$arParams["PATH_TO_PERSONAL"] = Trim($arParams["PATH_TO_PERSONAL"]);
if (strlen($arParams["PATH_TO_PERSONAL"]) <= 0) $arParams["PATH_TO_PERSONAL"] = "/personal/";

$arParams["SHOW_SCROLL_LINK"] = ($arParams["SHOW_SCROLL_LINK"] == "N" ? "N" : "Y" );

// Проверка модуля
if(!CModule::IncludeModule("sale"))
{
    ShowError(GetMessage("SALE_MODULE_NOT_INSTALL"));
    return;
}

// Определяем параметры
$num_products = 0;
$totalPrice = 0;
$totalWeight = 0.0;
$arBasketItems = array();
$arSetParentWeight = array();
$fUserID = IntVal(CSaleBasket::GetBasketUserID(True));

// Валюта из настроек компонента
$currencyPrice = $arParams['CURRENCY'];
if($arParams['CURRENCY']=='self') $currencyPrice = $arParams['MAIN_CURRENCY'];

// Значение, откуда будет браться картинка товара
$imageSetting = $arParams['PROPERTY_IMAGE_CODE'];
if($arParams['PROPERTY_IMAGE_CODE']=='self') $imageSetting = $arParams['SELF_IMAGE_CODE'];

// Если получаем корзину пользователя
if ($fUserID > 0)
{
    $rsBasket = CSaleBasket::GetList(
        array(),
        array(
            "FUSER_ID" => $fUserID,
            "LID" => SITE_ID,
            "ORDER_ID" => "NULL",
            "CAN_BUY" => "Y",
            "DELAY" => "N",
            "SUBSCRIBE" => "N"
        ),
        false,
        false,
        array(
            "ID", "NAME", "CALLBACK_FUNC", "MODULE", "PRODUCT_ID", "QUANTITY", "DELAY", "CAN_BUY",
            "PRICE", "WEIGHT", "DETAIL_PAGE_URL", "NOTES", "CURRENCY", "VAT_RATE", "CATALOG_XML_ID",
            "PRODUCT_XML_ID", "SUBSCRIBE", "DISCOUNT_PRICE", "PRODUCT_PROVIDER_CLASS", "TYPE", "SET_PARENT_ID"
        )
    );
    while ($arItem = $rsBasket->Fetch())
    {
        $arBasketItems[] = $arItem;

        //if (CSaleBasketHelper::isSetItem($arItem))
        //{
            $arSetParentWeight[$arItem["SET_PARENT_ID"]] += $arItem["WEIGHT"] * $arItem['QUANTITY'];
        //}
    }

    // Общий вес для продуктов-родителей
    foreach ($arBasketItems as &$arItem)
    {
        //if (CSaleBasketHelper::isSetParent($arItem))
        $arItem["WEIGHT"] = $arSetParentWeight[$arItem["ID"]] / $arItem["QUANTITY"];
    }
    unset($arItem);

    // Подсчитываем сумму корзины
    foreach ($arBasketItems as &$arItem)
    {
        //if (CSaleBasketHelper::isSetItem($arItem))
        //{
        //    continue;
        //}

        // Количество, цена и вес
        $num_products++;
        $totalPrice += $arItem["PRICE"] * $arItem["QUANTITY"];
        $totalWeight += $arItem["WEIGHT"] * $arItem["QUANTITY"];
    }

    // Массив с данными корзины
    $arOrder = array(
        'SITE_ID' => SITE_ID,
        'USER_ID' => $GLOBALS["USER"]->GetID(),
        'ORDER_PRICE' => $totalPrice,
        'ORDER_CURRENCY' => $currencyPrice,
        'ORDER_WEIGHT' => $totalWeight,
        'BASKET_ITEMS' => $arBasketItems
    );

    // Если продуктов больше одного, то рассчитываем сумму корзины
    if ($num_products > 0)
    {
        // Применяем скидку
        $arOptions = array();
        $arErrors = array ();
        CSaleDiscount::DoProcessOrder($arOrder, $arOptions, $arErrors);

        // Проходимся по массиву с элементами
        foreach ($arOrder['BASKET_ITEMS'] as &$arOneItem)
        {
            // Устанавливаем формат цен
            $arOneItem["PRICE_FORMATED"] = SaleFormatCurrency($arOneItem["PRICE"], $arOneItem["CURRENCY"]);

            // Устанавливаем картинку, в зависимости от выбранного в свойствах месторасположения
            $arOneItem["IMAGE_SRC"] = "";
            $arElementResult = CIBlockElement::GetByID($arOneItem["PRODUCT_ID"]);
            if($arElement = $arElementResult->GetNext())
            {
                // Вытаскиваем картинку из полей превью или детальной
                if ($imageSetting == 'PREVIEW_PICTURE' || $imageSetting == 'DETAIL_PICTURE')
                {
                    // Если в настройках выбрано PREVIEW_PICTURE и картинка в этом поле реально существует, определяем ссылку к ней
                    if ($imageSetting == 'PREVIEW_PICTURE' && isset($arElement["PREVIEW_PICTURE"]) && intval($arElement["PREVIEW_PICTURE"]) > 0)
                    {
                        $arImage = CFile::GetFileArray($arElement["PREVIEW_PICTURE"]);
                        if ($arImage)
                        {
                            $arFileTmp = CFile::ResizeImageGet(
                                $arImage,
                                array("width" => "24", "height" =>"24"),
                                BX_RESIZE_IMAGE_PROPORTIONAL,
                                true
                            );
                            $arOneItem["IMAGE_SRC"] = $arFileTmp["src"];
                        }
                    }
                    elseif ($imageSetting == 'DETAIL_PICTURE' && isset($arElement["DETAIL_PICTURE"]) && intval($arElement["DETAIL_PICTURE"]) > 0)
                    {
                        $arImage = CFile::GetFileArray($arElement["DETAIL_PICTURE"]);
                        if ($arImage)
                        {
                            $arFileTmp = CFile::ResizeImageGet(
                                $arImage,
                                array("width" => "24", "height" =>"24"),
                                BX_RESIZE_IMAGE_PROPORTIONAL,
                                true
                            );
                            $arOneItem["IMAGE_SRC"] = $arFileTmp["src"];
                        }
                    }
                }
                else // Если картинку нужно брать из свойства
                {
                    $IBLOCK_ID = CIBlockElement::GetIBlockByID($arOneItem["PRODUCT_ID"]);
                    $dbElProps = CIBlockElement::GetProperty($IBLOCK_ID, $arOneItem["PRODUCT_ID"], Array(), Array("CODE"=>$imageSetting));
                    if($obElement = $dbElProps->Fetch())
                    {
                        $arImage = CFile::GetFileArray($obElement["VALUE"]);
                        if ($arImage)
                        {
                            $arFileTmp = CFile::ResizeImageGet(
                                $arImage,
                                array("width" => "24", "height" =>"24"),
                                BX_RESIZE_IMAGE_PROPORTIONAL,
                                true
                            );
                            $arOneItem["IMAGE_SRC"] = $arFileTmp["src"];
                        }
                    }
                }
            }
        }
        if (isset($arOneItem)) unset($arOneItem);

        // Заносим в массив
        $arResult["SUM_PRODUCTS"] = SaleFormatCurrency($arOrder['ORDER_PRICE'], $arOrder["ORDER_CURRENCY"]);
    }
    $_SESSION["SALE_BASKET_NUM_PRODUCTS"][SITE_ID] = intval($num_products);
}

$arResult = array(
    'SUM_PRODUCTS' => $arResult["SUM_PRODUCTS"],
    'NUM_PRODUCTS' => $num_products,
    'ITEMS' => $arOrder['BASKET_ITEMS'],
    'PARAM_CURRENCY' => $currencyPrice,
    'PARAM_IMAGE_SETTING' => $imageSetting
);

$this->IncludeComponentTemplate();