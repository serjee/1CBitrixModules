<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arColorChemes = array(
    'default'=>GetMessage("MIBIX_BATTLEDIS_COMPONENT_COLOR_DEFAULT"),
    'primary'=>GetMessage("MIBIX_BATTLEDIS_COMPONENT_COLOR_PRIMARY"),
    'success'=>GetMessage("MIBIX_BATTLEDIS_COMPONENT_COLOR_SUCCESS"),
    'info'=>GetMessage("MIBIX_BATTLEDIS_COMPONENT_COLOR_INFO"),
    'warning'=>GetMessage("MIBIX_BATTLEDIS_COMPONENT_COLOR_WARNING"),
    'danger'=>GetMessage("MIBIX_BATTLEDIS_COMPONENT_COLOR_DANGER")
);

$arComponentParameters = Array(
    "GROUPS" => array(
        "COLOR_SETTINGS" => array(
            "NAME" => GetMessage("MIBIX_BATTLEDIS_COMPONENT_SETTINGS"),
        ),
    ),
    "PARAMETERS" => Array(
        "CODE_GROUP" => Array(
            "NAME" => GetMessage("MIBIX_BATTLEDIS_COMPONENT_CODE_GROUP"),
            "TYPE" => "STRING",
            "MULTIPLE" => "N",
            "DEFAULT" => "",
            "COLS" => 25,
            "PARENT" => "ADDITIONAL_SETTINGS",
        ),
        "JQUERY_ENABLED" => array(
            "NAME" => GetMessage("MIBIX_BATTLEDIS_COMPONENT_JQUERY_ENABLED"),
            "TYPE" => "CHECKBOX",
            "DEFAULT" => "N",
            "PARENT" => "ADDITIONAL_SETTINGS",
        ),
        "FANCYBOX_ENABLED" => array(
            "NAME" => GetMessage("MIBIX_BATTLEDIS_COMPONENT_FANCYBOX_ENABLED"),
            "TYPE" => "CHECKBOX",
            "DEFAULT" => "N",
            "PARENT" => "ADDITIONAL_SETTINGS",
        ),
        "COLOR_PANEL_CHEMES" => array(
            "NAME" => GetMessage("MIBIX_BATTLEDIS_COMPONENT_CHEMES_PANEL"),
            "TYPE" => "LIST",
            "VALUES" => $arColorChemes,
            "DEFAULT" => 'default',
            "REFRESH" => "N",
            "PARENT" => "COLOR_SETTINGS",
        ),
        "COLOR_THUMBNAIL_CHEMES" => array(
            "NAME" => GetMessage("MIBIX_BATTLEDIS_COMPONENT_CHEMES_THUMBNAIL"),
            "TYPE" => "LIST",
            "VALUES" => $arColorChemes,
            "DEFAULT" => 'default',
            "REFRESH" => "N",
            "PARENT" => "COLOR_SETTINGS",
        ),
        "COLOR_PROGRESS_TOP_CHEMES" => array(
            "NAME" => GetMessage("MIBIX_BATTLEDIS_COMPONENT_CHEMES_PROGRESS_TOP"),
            "TYPE" => "LIST",
            "VALUES" => $arColorChemes,
            "DEFAULT" => 'primary',
            "REFRESH" => "N",
            "PARENT" => "COLOR_SETTINGS",
        ),
        "SHOW_PROGRESS_TOP_ACTIVE" => array(
            "NAME" => GetMessage("MIBIX_BATTLEDIS_COMPONENT_SHOW_PROGRESS_TOP_ACTIVE"),
            "TYPE" => "CHECKBOX",
            "DEFAULT" => "N",
            "PARENT" => "COLOR_SETTINGS",
        ),
        "COLOR_PROGRESS_CHEMES" => array(
            "NAME" => GetMessage("MIBIX_BATTLEDIS_COMPONENT_CHEMES_PROGRESS"),
            "TYPE" => "LIST",
            "VALUES" => $arColorChemes,
            "DEFAULT" => 'primary',
            "REFRESH" => "N",
            "PARENT" => "COLOR_SETTINGS",
        ),
        "SHOW_PROGRESS_ACTIVE" => array(
            "NAME" => GetMessage("MIBIX_BATTLEDIS_COMPONENT_SHOW_PROGRESS_ACTIVE"),
            "TYPE" => "CHECKBOX",
            "DEFAULT" => "N",
            "PARENT" => "COLOR_SETTINGS",
        ),
        "COLOR_BUTTON_CHEMES" => array(
            "NAME" => GetMessage("MIBIX_BATTLEDIS_COMPONENT_CHEMES_BUTTON"),
            "TYPE" => "LIST",
            "VALUES" => $arColorChemes,
            "DEFAULT" => 'danger',
            "REFRESH" => "N",
            "PARENT" => "COLOR_SETTINGS",
        ),
    )
);
?>