<?
IncludeModuleLangFile(__FILE__);

$aMenu = array(
    "parent_menu" => "global_menu_services",
    "section" => "mibix.battledis",
    "sort" => 300,
    "text" => GetMessage("battledis_mnu_sect"),
    "title" => GetMessage("battledis_mnu_sect_title"),
    "url" => "mibix.battledis_service_index.php?lang=".LANGUAGE_ID,
    "icon" => "mibix_battledis_menu_icon",
    "page_icon" => "mibix_battledis_page_icon",
    "items_id" => "menu_mibix_battledis",
    "items" => array(
        array(
            "text" => GetMessage("battledis_mnu_battle"),
            "url" => "mibix.battledis_battle_list.php?lang=".LANGUAGE_ID,
            "more_url" => array("mibix.battledis_battle_edit.php"),
            "title" => GetMessage("battledis_mnu_battle_alt")
        ),
        array(
            "text" => GetMessage("battledis_mnu_group"),
            "url" => "mibix.battledis_group_list.php?lang=".LANGUAGE_ID,
            "more_url" => array("mibix.battledis_group_edit.php"),
            "title" => GetMessage("battledis_mnu_group_alt")
        )
    )
);

return $aMenu;
?>