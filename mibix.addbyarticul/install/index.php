<?php

global $MESS;
IncludeModuleLangFile(__FILE__);

if (class_exists("mibix.addbyarticul")) return;
class mibix_addbyarticul extends CModule
{
    var $MODULE_ID = "mibix.addbyarticul";
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;
    var $MODULE_CSS;

	// Конструктор
    function mibix_addbyarticul()
    {
        $arModuleVersion = array();
        include(dirname(__FILE__)."/version.php");
        $this->MODULE_VERSION = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        $this->MODULE_NAME = GetMessage("MIBIX_BASKET_MODULE_NAME");
        $this->MODULE_DESCRIPTION = GetMessage("MIBIX_BASKET_MODULE_DESCRIPTION");

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
		return true;
	}

	// Удаление событий
	function UnInstallEvents()
	{
		return true;
	}

	// Установка файлов
	function InstallFiles()
	{
        CopyDirFiles(
            $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$this->MODULE_ID."/install/components/mibix/",
            $_SERVER["DOCUMENT_ROOT"]."/bitrix/components/mibix/",
            true, true
        );
		return true;
	}

	function UnInstallFiles()
	{
        DeleteDirFilesEx("/bitrix/components/mibix/basket.addbyarticul");
		return true;
	}

    function DoInstall()
    {
        global $DOCUMENT_ROOT, $APPLICATION;

		// Регистрация модуля в системе
        $this->InstallDB();
        $this->InstallEvents();
        $this->InstallFiles();
		
		// Процесс инстралляции
        $APPLICATION->IncludeAdminFile(GetMessage("MIBIX_BASKET_INSTALL_TITLE") . $this->MODULE_ID, $DOCUMENT_ROOT."/bitrix/modules/".$this->MODULE_ID."/install/step.php");
        return true;
    }

    function DoUninstall()
    {
        global $DOCUMENT_ROOT, $APPLICATION;

        // Удаление модуля из системы
        $this->UnInstallFiles();
        $this->UnInstallEvents();
        $this->UnInstallDB();

        // Процесс деинстралляции
        $APPLICATION->IncludeAdminFile(GetMessage("MIBIX_BASKET_UNINSTALL_TITLE") . $this->MODULE_ID, $DOCUMENT_ROOT."/bitrix/modules/" . $this->MODULE_ID . "/install/unstep.php");
        return true;
    }
}