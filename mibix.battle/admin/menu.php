<?
IncludeModuleLangFile(__FILE__);

$aMenu = array(
    "parent_menu" => "global_menu_services",
    "section" => "mibix.battle",
    "sort" => 300,
    "text" => GetMessage("battle_mnu_sect"),
    "title" => GetMessage("battle_mnu_sect_title"),
    "url" => "mibix.battle_service_index.php?lang=".LANGUAGE_ID,
    "icon" => "mibix_battle_menu_icon",
    "page_icon" => "mibix_battle_page_icon",
    "items_id" => "menu_mibix_battle",
    "items" => array(
        array(
            "text" => GetMessage("battle_mnu_battle"),
            "url" => "mibix.battle_battle_list.php?lang=".LANGUAGE_ID,
            "more_url" => array("mibix.battle_battle_edit.php"),
            "title" => GetMessage("battle_mnu_battle_alt")
        ),
        array(
            "text" => GetMessage("battle_mnu_group"),
            "url" => "mibix.battle_group_list.php?lang=".LANGUAGE_ID,
            "more_url" => array("mibix.battle_group_edit.php"),
            "title" => GetMessage("battle_mnu_group_alt")
        )
    )
);

return $aMenu;
?>