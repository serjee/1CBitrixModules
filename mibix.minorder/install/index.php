<?php

global $MESS;
IncludeModuleLangFile(__FILE__);

if (class_exists("mibix.minorder")) return;
class mibix_minorder extends CModule
{
    var $MODULE_ID = "mibix.minorder";
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;
    var $MODULE_CSS;

	// Конструктор
    function mibix_minorder()
    {
        $arModuleVersion = array();
        include(dirname(__FILE__)."/version.php");
        $this->MODULE_VERSION = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        $this->MODULE_NAME = GetMessage("MIBIX_MO_MODULE_NAME");
        $this->MODULE_DESCRIPTION = GetMessage("MIBIX_MO_MODULE_DESCRIPTION");

		$this->PARTNER_NAME = "MIBIX";
		$this->PARTNER_URI = "http://www.mibix.ru";
    }
	
	// Установка данных в базу
	function InstallDB()
	{
		// Регистрация модуля в системе
		RegisterModule($this->MODULE_ID);
		
		return true;
	}

	// Удаление данных из базы
	function UnInstallDB()
	{
		// Удаление записи о модуле из системы
		UnRegisterModule($this->MODULE_ID);
		return true;
	}

	// Установка событий
	function InstallEvents()
	{
        RegisterModuleDependences("sale","OnBeforeOrderAdd",$this->MODULE_ID,"CMinOrder","OnBeforeOrderAddHandler");
		return true;
	}

	// Удаление событий
	function UnInstallEvents()
	{
        UnRegisterModuleDependences("sale","OnBeforeOrderAdd",$this->MODULE_ID,"CMinOrder","OnBeforeOrderAddHandler");
		return true;
	}

	// Установка файлов
	function InstallFiles()
	{
		return true;
	}

    // Удаление файлов
	function UnInstallFiles()
	{
		return true;
	}

    // Установка модуля
    function DoInstall()
    {
        global $DOCUMENT_ROOT, $APPLICATION;

		// Регистрация модуля в системе
        $this->InstallDB();
        $this->InstallEvents();
        $this->InstallFiles();
		
		// Процесс инстралляции
        $APPLICATION->IncludeAdminFile(GetMessage("MIBIX_MO_INSTALL_TITLE") . $this->MODULE_ID, $DOCUMENT_ROOT."/bitrix/modules/".$this->MODULE_ID."/install/step.php");
        return true;
    }

    // Удаление модуля
    function DoUninstall()
    {
        global $DOCUMENT_ROOT, $APPLICATION;

        // Удаление модуля из системы
        $this->UnInstallFiles();
        $this->UnInstallEvents();
        $this->UnInstallDB();

        // Процесс деинстралляции
        $APPLICATION->IncludeAdminFile(GetMessage("MIBIX_MO_UNINSTALL_TITLE") . $this->MODULE_ID, $DOCUMENT_ROOT."/bitrix/modules/" . $this->MODULE_ID . "/install/unstep.php");
        return true;
    }
}