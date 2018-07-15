<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("MIBIX_ABA_NAME"),
	"DESCRIPTION" => GetMessage("MIBIX_ABA_DESCRIPTION"),
	"PATH" => array(
		"ID" => "MIBIX",
		"CHILD" => array(
			"ID" => "basket-addbyarticul",
			"NAME" => GetMessage("MIBIX_ABA_NAME_CHILD")
		)
	),
    "ICON" => "/images/icon.gif",
);
?>