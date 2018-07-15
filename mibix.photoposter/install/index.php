<?php

global $MESS;
IncludeModuleLangFile(__FILE__);

if (class_exists("mibix.photoposter")) return;

class mibix_photoposter extends CModule
{
    const MODULE_ID = "mibix.photoposter";
    var $MODULE_ID = "mibix.photoposter";
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;
    var $MODULE_CSS;

	// Конструктор
    function mibix_photoposter()
    {
        $arModuleVersion = array();
        include(dirname(__FILE__)."/version.php");
        $this->MODULE_VERSION = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        $this->MODULE_NAME = GetMessage("MIBIX_PP_MODULE_NAME");
        $this->MODULE_DESCRIPTION = GetMessage("MIBIX_PP_MODULE_DESCRIPTION");

		$this->PARTNER_NAME = "MIBIX";
		$this->PARTNER_URI = "http://www.mibix.ru";
    }
	
	// Установка данных в базу
	function InstallDB()
	{
        global $DB, $DBType, $APPLICATION;
        $errors = false;

        // Установка базы данных
        $errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".self::MODULE_ID."/install/db/".$DBType."/install.sql");

        // Проверка на ошибки установки базы данных
        if($errors !== false)
        {
            $APPLICATION->ThrowException(implode("<br>", $errors));

            return false;
        }
        else
        {
            // Регистрация модуля в системе
            RegisterModule(self::MODULE_ID);
            CModule::IncludeModule(self::MODULE_ID); //TODO:

            return true;
        }
	}

	// Удаление данных из базы
	function UnInstallDB()
	{
        global $DB, $DBType, $APPLICATION;
        $errors = false;

        $errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".self::MODULE_ID."/install/db/".$DBType."/uninstall.sql");
        $strSql = "SELECT ID FROM b_file WHERE MODULE_ID='".self::MODULE_ID."'";
        $rsFile = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
        while($arFile = $rsFile->Fetch())
        {
            CFile::Delete($arFile["ID"]);
        }

        // Удаляем информацию о модуле из системы
        UnRegisterModule(self::MODULE_ID);

        // Если есть ошибки, сообщаем о них
        if($errors !== false)
        {
            $APPLICATION->ThrowException(implode("<br>", $errors));
            return false;
        }

		return true;
	}

	// Установка событий
	function InstallEvents()
	{
        RegisterModuleDependences("catalog","OnProductAdd",self::MODULE_ID,"CMibixPhotoposterPhotoExport","PostOnProductAddHandler");

		return true;
	}

	// Удаление событий
	function UnInstallEvents()
	{
        UnRegisterModuleDependences("catalog","OnProductAdd",self::MODULE_ID,"CMibixPhotoposterPhotoExport","PostOnProductAddHandler");

		return true;
	}

	// Установка файлов
	function InstallFiles()
	{
        CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".self::MODULE_ID."/install/tools", $_SERVER["DOCUMENT_ROOT"]."/");

		return true;
	}

    // Удаление файлов
	function UnInstallFiles()
	{
        if (file_exists($_SERVER["DOCUMENT_ROOT"]."/mibix_photoposter.php"))
        {
            unlink($_SERVER["DOCUMENT_ROOT"]."/mibix_photoposter.php");
        }
		return true;
	}

    // Установка модуля
    function DoInstall()
    {
        global $APPLICATION;

		// Регистрация модуля в системе
        $this->InstallDB();
        $this->InstallEvents();
        $this->InstallFiles();
		
		// Процесс инстралляции
        $APPLICATION->IncludeAdminFile(GetMessage("MIBIX_PP_INSTALL_TITLE") . self::MODULE_ID, $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".self::MODULE_ID."/install/step.php");
        return true;
    }

    // Удаление модуля
    function DoUninstall()
    {
        global $APPLICATION;

        // Удаление модуля из системы
        $this->UnInstallFiles();
        $this->UnInstallEvents();
        $this->UnInstallDB();

        // Процесс деинстралляции
        $APPLICATION->IncludeAdminFile(GetMessage("MIBIX_PP_UNINSTALL_TITLE") . self::MODULE_ID, $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/" . self::MODULE_ID . "/install/unstep.php");
        return true;
    }
}