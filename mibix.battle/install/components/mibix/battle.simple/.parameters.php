<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arColorChemes = array(
    'gray'=>GetMessage("MIBIX_BATTLE_COMPONENT_COLOR_GRAY"),
    'blue'=>GetMessage("MIBIX_BATTLE_COMPONENT_COLOR_BLUE"),
    'green'=>GetMessage("MIBIX_BATTLE_COMPONENT_GRAY_GREEN"),
    'bluelight'=>GetMessage("MIBIX_BATTLE_COMPONENT_COLOR_BLUELIGHT"),
    'yellow'=>GetMessage("MIBIX_BATTLE_COMPONENT_COLOR_YELLOW"),
    'red'=>GetMessage("MIBIX_BATTLE_COMPONENT_COLOR_RED")
);

$arComponentParameters = Array(

    "PARAMETERS" => Array(
        "CODE_GROUP" => Array(
            "NAME" => GetMessage("MIBIX_BATTLE_COMPONENT_CODE_GROUP"),
            "TYPE" => "STRING",
            "MULTIPLE" => "N",
            "DEFAULT" => "",
            "COLS" => 25,
        ),
        "JQUERY_ENABLED" => array(
            "NAME" => GetMessage("MIBIX_BATTLE_COMPONENT_JQUERY_ENABLED"),
            "TYPE" => "CHECKBOX",
            "DEFAULT" => "N",
        ),
        "FANCYBOX_ENABLED" => array(
            "NAME" => GetMessage("MIBIX_BATTLE_COMPONENT_FANCYBOX_ENABLED"),
            "TYPE" => "CHECKBOX",
            "DEFAULT" => "N",
        ),
        "COLOR_CHEMES" => array(
            "NAME" => GetMessage("MIBIX_BATTLE_COMPONENT_CHEMES"),
            "TYPE" => "LIST",
            "VALUES" => $arColorChemes,
            "DEFAULT" => 'gray',
            "REFRESH" => "N",
        ),
    )
);
?>