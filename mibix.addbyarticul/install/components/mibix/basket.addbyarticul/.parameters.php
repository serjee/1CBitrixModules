<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if(!CModule::IncludeModule("iblock"))
    return;

$arYesNo = Array(
    "Y" => GetMessage("MIBIX_DESC_YES"),
    "N" => GetMessage("MIBIX_DESC_NO"),
);

// type of iblock
$arTypesEx = CIBlockParameters::GetIBlockTypes(Array("-"=>" "));

// id iblock
$arIBlocks=Array();
$db_iblock = CIBlock::GetList(Array("SORT"=>"ASC"), Array("SITE_ID"=>$_REQUEST["site"], "TYPE" => ($arCurrentValues["IBLOCK_TYPE"]!="-"?$arCurrentValues["IBLOCK_TYPE"]:"")));
while($arRes = $db_iblock->Fetch())
{
    $arIBlocks[$arRes["ID"]] = $arRes["NAME"];
}

// id iblock for sku
$arIBlocksSKU=Array();
$db_iblock_SKU = CIBlock::GetList(Array("SORT"=>"ASC"), Array("SITE_ID"=>$_REQUEST["site"], "TYPE" => ($arCurrentValues["IBLOCK_TYPE_SKU"]!="-"?$arCurrentValues["IBLOCK_TYPE_SKU"]:"")));
while($arResSKU = $db_iblock_SKU->Fetch())
{
    $arIBlocksSKU[$arResSKU["ID"]] = $arResSKU["NAME"];
}

// property iblock
$arProperty_Data = array();
$rsProp = CIBlockProperty::GetList(Array("sort"=>"asc", "name"=>"asc"), Array("ACTIVE"=>"Y", "IBLOCK_ID"=>(isset($arCurrentValues["IBLOCK_ID"])?$arCurrentValues["IBLOCK_ID"]:$arCurrentValues["ID"])));
while ($arr=$rsProp->Fetch())
{
    $arProperty[$arr["CODE"]] = $arr["NAME"];
    if (in_array($arr["PROPERTY_TYPE"], array("L", "N", "S")))
    {
        $arProperty_Data[$arr["CODE"]] = $arr["NAME"];
    }
}

// property iblock for sku
$arProperty_Data_SKU = array();
$rsPropSKU = CIBlockProperty::GetList(Array("sort"=>"asc", "name"=>"asc"), Array("ACTIVE"=>"Y", "IBLOCK_ID"=>(isset($arCurrentValues["IBLOCK_ID_SKU"])?$arCurrentValues["IBLOCK_ID_SKU"]:$arCurrentValues["ID"])));
while ($arrSKU=$rsPropSKU->Fetch())
{
    $arPropertySKU[$arrSKU["CODE"]] = $arrSKU["NAME"];
    if (in_array($arrSKU["PROPERTY_TYPE"], array("L", "N", "S")))
    {
        $arProperty_Data_SKU[$arrSKU["CODE"]] = $arrSKU["NAME"];
    }
}

$arComponentParameters = Array(
    "GROUPS" => array(
        "ABASKU_SETTINGS" => array(
            "NAME" => GetMessage("MIBIX_GROUP_ABASKU_SETTINGS"),
        ),
    ),
    "PARAMETERS" => Array(
        "IBLOCK_TYPE" => Array(
            "PARENT" => "BASE",
            "NAME" => GetMessage("MIBIX_IBLOCK_DESC_PRICE_LIST_TYPE"),
            "TYPE" => "LIST",
            "VALUES" => $arTypesEx,
            "DEFAULT" => "catalog",
            "REFRESH" => "Y",
        ),
        "IBLOCK_ID" => Array(
            "PARENT" => "BASE",
            "NAME" => GetMessage("MIBIX_IBLOCK_DESC_PRICE_LIST_ID"),
            "TYPE" => "LIST",
            "VALUES" => $arIBlocks,
            "DEFAULT" => '={$_REQUEST["ID"]}',
            "REFRESH" => "Y",
        ),
        "PROPERTY_CODE" => array(
            "PARENT" => "DATA_SOURCE",
            "NAME" => GetMessage("MIBIX_IBLOCK_PROPERTY_PRICE_LIST"),
            "TYPE" => "LIST",
            "MULTIPLE" => "N",
            "VALUES" => $arProperty_Data,
            "REFRESH" => "Y",
        ),
        "USE_SKU_SETTINGS" => Array(
            "PARENT" => "ABASKU_SETTINGS",
            "NAME" => GetMessage("MIBIX_USE_SKU_SETTINGS"),
            "TYPE" => "LIST",
            "MULTIPLE" => "N",
            "DEFAULT" => "Y",
            "VALUES"=>$arYesNo,
        ),
        "IBLOCK_TYPE_SKU" => Array(
            "PARENT" => "ABASKU_SETTINGS",
            "NAME" => GetMessage("MIBIX_IBLOCK_SKU_DESC_PRICE_LIST_TYPE"),
            "TYPE" => "LIST",
            "VALUES" => $arTypesEx,
            "DEFAULT" => "offers",
            "REFRESH" => "Y",
        ),
        "IBLOCK_ID_SKU" => Array(
            "PARENT" => "ABASKU_SETTINGS",
            "NAME" => GetMessage("MIBIX_IBLOCK_SKU_DESC_PRICE_LIST_ID"),
            "TYPE" => "LIST",
            "VALUES" => $arIBlocksSKU,
            "DEFAULT" => '={$_REQUEST["ID"]}',
            "REFRESH" => "Y",
        ),
        "PROPERTY_CODE_SKU" => array(
            "PARENT" => "ABASKU_SETTINGS",
            "NAME" => GetMessage("MIBIX_IBLOCK_SKU_PROPERTY_PRICE_LIST"),
            "TYPE" => "LIST",
            "MULTIPLE" => "N",
            "VALUES" => $arProperty_Data_SKU,
            "REFRESH" => "Y",
        )
    )
);

?>