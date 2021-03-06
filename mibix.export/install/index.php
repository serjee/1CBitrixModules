<?php

global $MESS;
IncludeModuleLangFile(__FILE__);

if (class_exists("mibix.export")) return;

class mibix_export extends CModule
{
    const MODULE_ID = "mibix.export";
    var $MODULE_ID = "mibix.export";
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;
    var $MODULE_CSS;

	// Конструктор
    function mibix_export()
    {
        $arModuleVersion = array();
        include(dirname(__FILE__)."/version.php");
        $this->MODULE_VERSION = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        $this->MODULE_NAME = GetMessage("MIBIX_EXPORT_MODULE_NAME");
        $this->MODULE_DESCRIPTION = GetMessage("MIBIX_EXPORT_MODULE_DESCRIPTION");

		$this->PARTNER_NAME = "MIBIX";
		$this->PARTNER_URI = "http://www.mibix.ru";
    }
	
	// Установка данных в базу
	function InstallDB($arParams = array())
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
            CModule::IncludeModule(self::MODULE_ID);

            return true;
        }
	}

	// Удаление данных из базы
	function UnInstallDB($arParams = array())
	{
        global $DB, $DBType, $APPLICATION;
        $errors = false;

        if(!array_key_exists("save_tables", $arParams) || ($arParams["save_tables"] != "Y"))
        {
            $errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".self::MODULE_ID."/install/db/".$DBType."/uninstall.sql");
            $strSql = "SELECT ID FROM b_file WHERE MODULE_ID='".self::MODULE_ID."'";
            $rsFile = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
            while($arFile = $rsFile->Fetch())
            {
                CFile::Delete($arFile["ID"]);
            }
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
        if(is_dir($p = $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".self::MODULE_ID."/admin"))
        {
            if($dir = opendir($p))
            {
                while(false !== $item = readdir($dir))
                {
                    if($item == '..' || $item == '.' || $item == 'menu.php')
                    {
                        continue;
                    }
                    file_put_contents($file = $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin/".self::MODULE_ID."_".$item, '<'.'? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/'.self::MODULE_ID.'/admin/'.$item.'");?'.'>');
                }
                closedir($dir);
            }
        }
        CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".self::MODULE_ID."/install/images/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/images/".self::MODULE_ID, false, true);
        CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".self::MODULE_ID."/install/themes/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/themes", false, true);
        CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".self::MODULE_ID."/js/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/js/", true, true);
        CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".self::MODULE_ID."/install/tools", $_SERVER["DOCUMENT_ROOT"]."/");

		return true;
	}

    // Удаление файлов
	function UnInstallFiles()
	{
        if(is_dir($p = $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".self::MODULE_ID."/admin"))
        {
            if($dir = opendir($p))
            {
                while(false !== $item = readdir($dir))
                {
                    if($item == '..' || $item == '.' || $item == 'menu.php')
                    {
                        continue;
                    }
                    unlink($_SERVER["DOCUMENT_ROOT"]."/bitrix/admin/".self::MODULE_ID.'_'.$item);
                }
                closedir($dir);
            }
        }
        // css
        DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".self::MODULE_ID."/install/themes/.default/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/themes/.default");
        // icons
        DeleteDirFilesEx("/bitrix/themes/.default/icons/".self::MODULE_ID."/");
        // images
        DeleteDirFilesEx("/bitrix/images/".self::MODULE_ID."/");
        // js
        DeleteDirFilesEx("/bitrix/js/".self::MODULE_ID."/");
        // module's root files
        DeleteDirFilesEx("/mibix_export_show.php");
        DeleteDirFilesEx("/mibix_export_step_cron.php");
        DeleteDirFilesEx("/mibix_export_step_show.php");

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
        $APPLICATION->IncludeAdminFile(GetMessage("MIBIX_EXPORT_INSTALL_TITLE") . self::MODULE_ID, $DOCUMENT_ROOT."/bitrix/modules/".$this->MODULE_ID."/install/step.php");
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
        $APPLICATION->IncludeAdminFile(GetMessage("MIBIX_EXPORT_UNINSTALL_TITLE") . self::MODULE_ID, $DOCUMENT_ROOT."/bitrix/modules/" . $this->MODULE_ID . "/install/unstep.php");
        return true;
    }
}