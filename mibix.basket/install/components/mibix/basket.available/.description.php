<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("MIBIX_BAKSET_NAME"),
	"DESCRIPTION" => GetMessage("MIBIX_BAKSET_DESCRIPTION"),
	"PATH" => array(
		"ID" => "MIBIX",
		"CHILD" => array(
			"ID" => "basket-available",
			"NAME" => GetMessage("MIBIX_BAKSET_NAME_CHILD")
		)
	),
    "ICON" => "/images/icon.gif",
);
?>