<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
    "NAME" => GetMessage("MIBIX_JOINT_DS_NAME"),
    "DESCRIPTION" => GetMessage("MIBIX_JOINT_DS_DESCRIPTION"),
    "PATH" => array(
        "ID" => "MIBIX",
        "CHILD" => array(
            "ID" => "joint-detail-simple",
            "NAME" => GetMessage("MIBIX_JOINT_DS_NAME_CHILD")
        )
    ),
    "ICON" => "/images/icon.gif",
);
?>