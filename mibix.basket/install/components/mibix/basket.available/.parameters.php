<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arYesNo = Array(
    "Y" => GetMessage("MIBIX_DESC_YES"),
    "N" => GetMessage("MIBIX_DESC_NO"),
);

$arPropertyImage = array(
    'PREVIEW_PICTURE'=>GetMessage("MIBIX_PREVIEW_PICTURE"),
    'DETAIL_PICTURE'=>GetMessage("MIBIX_DETAIL_PICTURE"),
    'self'=>GetMessage("MIBIX_SALFE_IMAGE_PROP")
);

$arCurrency = array(
    'RUB'=>GetMessage("MIBIX_RUB"),
    'USD'=>GetMessage("MIBIX_USD"),
    'EURO'=>GetMessage("MIBIX_EURO"),
    'self'=>GetMessage("MIBIX_MAIN_CUR")
);

$arColorChemes = array(
    'gray-red'=>GetMessage("MIBIX_COLOR_GRAY_RED"),
    'gray-blue'=>GetMessage("MIBIX_COLOR_GRAY_BLUE"),
    'gray-green'=>GetMessage("MIBIX_COLOR_GRAY_GREEN"),
    'gray-yellow'=>GetMessage("MIBIX_COLOR_GRAY_YELLOW"),
    'blue-bx'=>GetMessage("MIBIX_COLOR_BLUE_BX"),
    'green-bx'=>GetMessage("MIBIX_COLOR_GREEN_BX"),
    'yellow-bx'=>GetMessage("MIBIX_COLOR_YELLOW_BX"),
    'red-bx'=>GetMessage("MIBIX_COLOR_RED_BX")
);

$arComponentParameters = Array(
    "GROUPS" => array(
        "BASKET_SETTINGS" => array(
            "NAME" => GetMessage("MIBIX_GROUP_BASKET_SETTINGS"),
        ),
    ),
    "PARAMETERS" => Array(
        "PATH_TO_BASKET" => Array(
            "NAME" => GetMessage("MIBIX_PARAM_PATH_TO_BASKET"),
            "TYPE" => "STRING",
            "MULTIPLE" => "N",
            "DEFAULT" => "/personal/cart/",
            "COLS" => 25,
            "PARENT" => "ADDITIONAL_SETTINGS",
        ),
        "PATH_TO_ORDER_MAKE" => Array(
            "NAME" => GetMessage("MIBIX_PARAM_PATH_TO_ORDER_MAKE"),
            "TYPE" => "STRING",
            "MULTIPLE" => "N",
            "DEFAULT" => "/personal/order/make/",
            "COLS" => 25,
            "PARENT" => "ADDITIONAL_SETTINGS",
        ),
        "PATH_TO_AUTH" => Array(
            "NAME" => GetMessage("MIBIX_PARAM_PATH_TO_AUTH"),
            "TYPE" => "STRING",
            "MULTIPLE" => "N",
            "DEFAULT" => "/auth/",
            "COLS" => 25,
            "PARENT" => "ADDITIONAL_SETTINGS",
        ),
        "PATH_TO_REGISTRATION" => Array(
            "NAME" => GetMessage("MIBIX_PARAM_PATH_TO_REGISTRATION"),
            "TYPE" => "STRING",
            "MULTIPLE" => "N",
            "DEFAULT" => "/login/?register=yes",
            "COLS" => 25,
            "PARENT" => "ADDITIONAL_SETTINGS",
        ),
        "PATH_TO_PERSONAL" => Array(
            "NAME" => GetMessage("MIBIX_PARAM_PATH_TO_PERSONAL"),
            "TYPE" => "STRING",
            "MULTIPLE" => "N",
            "DEFAULT" => "/personal/",
            "COLS" => 25,
            "PARENT" => "ADDITIONAL_SETTINGS",
        ),
        "SHOW_SCROLL_LINK" => Array(
            "NAME" => GetMessage("MIBIX_SHOW_SCROLL_LINK"),
            "TYPE" => "LIST",
            "MULTIPLE" => "N",
            "DEFAULT" => "Y",
            "VALUES"=>$arYesNo,
            "PARENT" => "BASKET_SETTINGS",
        ),
        "COLOR_CHEMES" => array(
            "NAME" => GetMessage("MIBIX_PARAM_CHEMES"),
            "TYPE" => "LIST",
            "VALUES" => $arColorChemes,
            "DEFAULT" => 'black-red',
            "REFRESH" => "N",
            "PARENT" => "BASKET_SETTINGS",
        ),
        "PROPERTY_IMAGE_CODE" => array(
            "NAME" => GetMessage("MIBIX_PARAM_PROPERTY_LIST"),
            "TYPE" => "LIST",
            "VALUES" => $arPropertyImage,
            "DEFAULT" => 'PREVIEW_PICTURE',
            "REFRESH" => "Y",
            "PARENT" => "BASKET_SETTINGS",
        ),
        "CURRENCY" => array(
            "NAME" => GetMessage("MIBIX_PARAM_CURRENCY"),
            "TYPE" => "LIST",
            "VALUES" => $arCurrency,
            "DEFAULT" => "iblock",
            "REFRESH" => "Y",
            "PARENT" => "BASKET_SETTINGS",
        ),
    )
);

if ($arCurrentValues['CURRENCY']=='self')
{
    $arComponentParameters['PARAMETERS']['MAIN_CURRENCY'] = array(
        "PARENT" => "BASKET_SETTINGS",
        'NAME' => GetMessage("MIBIX_MAIN_CURRENCY"),
        'TYPE' => 'STRING',
        "DEFAULT" => 'RUB',
    );
}

if ($arCurrentValues['PROPERTY_IMAGE_CODE']=='self')
{
    $arComponentParameters['PARAMETERS']['SELF_IMAGE_CODE'] = array(
        "PARENT" => "BASKET_SETTINGS",
        'NAME' => GetMessage("MIBIX_SELF_IMAGE"),
        'TYPE' => 'STRING',
        "DEFAULT" => "MORE_PHOTO",
    );
}

?>