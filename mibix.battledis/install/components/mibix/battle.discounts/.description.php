<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("MIBIX_BATTLEDIS_COMPONENT_NAME"),
	"DESCRIPTION" => GetMessage("MIBIX_BATTLEDIS_COMPONENT_DESCRIPTION"),
	"PATH" => array(
		"ID" => "MIBIX",
		"CHILD" => array(
			"ID" => "battle-discounts",
			"NAME" => GetMessage("MIBIX_BATTLEDIS_COMPONENT_NAME_CHILD")
		)
	),
    "ICON" => "/images/icon.gif",
);
?>