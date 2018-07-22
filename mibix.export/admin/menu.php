<?
IncludeModuleLangFile(__FILE__);

$aMenu = array(
    "parent_menu" => "global_menu_services",
    "section" => "mibix.export",
    "sort" => 300,
    "text" => GetMessage("mexport_mnu_sect"),
    "title" => GetMessage("mexport_mnu_sect_title"),
    "url" => "mibix.export_service_index.php?lang=".LANGUAGE_ID,
    "icon" => "mibix_export_menu_icon",
    "page_icon" => "mibix_export_page_icon",
    "items_id" => "menu_mibix_export",
    "items" => array(
        array(
            "text" => GetMessage("mexport_mnu_template"),
            "url" => "mibix.export_template_list.php?lang=".LANGUAGE_ID,
            "more_url" => array("mibix.export_template_edit.php"),
            "title" => GetMessage("mexport_mnu_template_alt")
        ),
        array(
            "text" => GetMessage("mexport_mnu_entity"),
            "url" => "mibix.export_entity_list.php?lang=".LANGUAGE_ID,
            "more_url" => array("mibix.export_entity_edit.php"),
            "title" => GetMessage("mexport_mnu_entity_alt")
        ),
        array(
            "text" => GetMessage("mexport_mnu_instr"),
            "url" => "mibix.export_instr.php?lang=".LANGUAGE_ID,
            "title" => GetMessage("mexport_mnu_instr_alt")
        )
    )
);

return $aMenu;
?>