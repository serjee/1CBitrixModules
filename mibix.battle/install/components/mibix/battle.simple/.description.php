<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("MIBIX_BATTLE_COMPONENT_NAME"),
	"DESCRIPTION" => GetMessage("MIBIX_BATTLE_COMPONENT_DESCRIPTION"),
	"PATH" => array(
		"ID" => "MIBIX",
		"CHILD" => array(
			"ID" => "battle-simple",
			"NAME" => GetMessage("MIBIX_BATTLE_COMPONENT_NAME_CHILD")
		)
	),
    "ICON" => "/images/icon.gif",
);
?>