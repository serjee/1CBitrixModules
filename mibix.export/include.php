<?php
if (!CModule::IncludeModule("iblock")) return false;
IncludeModuleLangFile(__FILE__);

global $DBType;

/**
 * Класс модели для работы с таблицей "Шаблоны"
 */
class CMibixExportTemplateModel implements iMibixExportModel
{
    /**
     * @var array - информация об ошибках
     */
    private $ERRORS = array();

    /**
     * Провера полей формы
     *
     * @param $arFields - массив полей формы
     * @param $ID - ID значения
     * @return mixed - результат проверки
     */
    public function CheckFields($arFields, $ID)
    {
        $this->setErrors();

        $aMsg = array_merge(
            CMibixExportBaseModel::CheckByRules(Array(
                    "name" => $arFields["name"],
                    "encoding" => $arFields["encoding"]
                ),
                Array("required"=>true, "minlen"=>1, "maxlen"=>100)
            ),
            CMibixExportBaseModel::CheckByRules(Array(
                    "template" => $arFields["template"]
                ),
                Array("required"=>true, "minlen"=>1, "maxlen"=>65534)
            )
        );

        // Есть ли ошибки
        if(!empty($aMsg))
        {
            $this->setErrors($aMsg);
            return false;
        }

        return true;
    }

    /**
     * Получаем запись из базы по ID
     *
     * @param $ID - ID значения
     * @return mixed - результат выполнения запроса
     */
    public function GetByID($ID)
    {
        return CMibixExportBaseModel::BaseGetByID($ID, array("*"), self::getTableName());
    }

    /**
     * Получаем записи из базы с учетом фильтра и навигации
     *
     * @param array $aSort - массив сортировки
     * @param array $arFilter - фильтр выборки
     * @param bool $arNavStartParams - параметры постраничной навигации
     * @return mixed - результат выполнения запроса
     */
    public function GetList($aSort = Array(), $arFilter = Array(), $arNavStartParams = false)
    {
        global $DB;

        $from1 = "";
        $arSqlSearch = Array();

        if(is_array($arFilter))
        {
            foreach($arFilter as $key => $val)
            {
                if(!is_array($val))
                {
                    if( (strlen($val) <= 0) || ($val === "NOT_REF") )
                        continue;
                }
                switch(strtoupper($key))
                {
                    case "ID":
                        $arSqlSearch[] = GetFilterQuery("ps.id", $val, "N");
                        break;
                    case "NAME":
                        $arSqlSearch[] = GetFilterQuery("ps.name", $val, "Y", array("@", ".", "_"));
                        break;
                    case "UPDATE_1":
                        $arSqlSearch[] = "ps.date_update>=".$DB->CharToDateFunction($val);
                        break;
                    case "UPDATE_2":
                        $arSqlSearch[] = "ps.date_update<=".$DB->CharToDateFunction($val." 23:59:59");
                        break;
                    case "INSERT_1":
                        $arSqlSearch[] = "ps.date_insert>=".$DB->CharToDateFunction($val);
                        break;
                    case "INSERT_2":
                        $arSqlSearch[] = "ps.date_insert<=".$DB->CharToDateFunction($val." 23:59:59");
                        break;
                    case "ACTIVE":
                        $arSqlSearch[] = ($val=="Y") ? "ps.active='Y'" : "ps.active='N'";
                        break;
                }
            }
        }
        $strSqlSearch = GetFilterSqlSearch($arSqlSearch);

        $arOrder = array();
        foreach($aSort as $by => $ord)
        {
            $by = strtoupper($by);
            $ord = (strtoupper($ord) <> "ASC"? "DESC": "ASC");
            switch($by)
            {
                case "ID": $arOrder[$by] = "ps.id ".$ord; break;
                case "NAME": $arOrder[$by] = "ps.name ".$ord; break;
                case "DATE_INSERT": $arOrder[$by] = "ps.date_insert ".$ord; break;
                case "DATE_UPDATE": $arOrder[$by] = "ps.date_update ".$ord; break;
                case "ACT": $arOrder[$by] = "ps.active ".$ord; break;
            }
        }
        if(count($arOrder) <= 0) $arOrder["ID"] = "ps.id DESC";

        if(is_array($arNavStartParams))
        {
            $strSql = "
				SELECT count(".($from1 <> ""? "DISTINCT ps.id": "'x'").") as C
				FROM
					".self::getTableName()." ps
					$from1
				WHERE
				".$strSqlSearch;

            $res_cnt = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
            $res_cnt = $res_cnt->Fetch();
            $cnt = $res_cnt["C"];

            $strSql = "
				SELECT
					ps.id, ps.active, ps.name,
					".$DB->DateToCharFunction("ps.date_update")." date_update,
					".$DB->DateToCharFunction("ps.date_insert")." date_insert
				FROM
					".self::getTableName()." ps
					$from1
				WHERE
				$strSqlSearch
				".($from1 <> ""?
                    "GROUP BY ps.id, ps.active, ps.name":
                    ""
                )."
				ORDER BY ".implode(", ", $arOrder);

            $res = new CDBResult();
            $res->NavQuery($strSql, $cnt, $arNavStartParams);
            $res->is_filtered = (IsFiltered($strSqlSearch));

            return $res;
        }
        else
        {
            $strSql = "
				SELECT
					ps.id, ps.active, ps.name,
					".$DB->DateToCharFunction("ps.date_update")." date_update,
					".$DB->DateToCharFunction("ps.date_insert")." date_insert
				FROM
					".self::getTableName()." ps
					$from1
				WHERE
				$strSqlSearch
				".($from1 <> ""?
                    "GROUP BY ps.id, ps.active, ps.name":
                    ""
                )."
				ORDER BY ".implode(", ", $arOrder);

            $res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
            $res->is_filtered = (IsFiltered($strSqlSearch));

            return $res;
        }
    }

    /**
     * Добавление записи в базу данных
     *
     * @param $arFields - массив полей формы
     * @param $SITE_ID - код сайта
     * @return mixed - результат выполнения запроса (ID новой записи или false)
     */
    public function Add($arFields, $SITE_ID = SITE_ID)
    {
        $arFields["active"] = "Y";

        // Проверка данных перед добавлением
        if(!$this->CheckFields($arFields, 0)) return false;

        return CMibixExportBaseModel::BaseAdd($arFields, self::getTableName());
    }

    /**
     * Обновление записи по ID
     *
     * @param $ID - ID значения
     * @param $arFields - массив полей формы
     * @param $SITE_ID - код сайта
     * @return mixed - результат выполнения запроса
     */
    public function Update($ID, $arFields, $SITE_ID = SITE_ID)
    {
        // Проверяем заполненные поля на ошибки и возвращаем false в случае их наличия, при этом сами ошибки сохраняем в переменной класса
        if(!$this->CheckFields($arFields, $ID)) return false;

        return CMibixExportBaseModel::BaseUpdate($ID, $arFields, self::getTableName());
    }

    /**
     * Удаление записи по ID
     *
     * @param $ID - ID значения
     * @return mixed - результат выполнения запроса
     */
    public function Delete($ID)
    {
        return CMibixExportBaseModel::BaseDeleteByID($ID, self::getTableName());
    }

    /**
     * Устанавливает массив ошибок при проверке
     *
     * @param $ERRORS - добавляет массив ошибок
     */
    public function setErrors($ERRORS=array())
    {
        $this->ERRORS = $ERRORS;
    }

    /**
     * Возвращает массив ошибок возникших при проверке
     *
     * @return array - массив ошибок
     */
    public function getErrors()
    {
        return $this->ERRORS;
    }

    /**
     * Возвращает название таблицы базы данных, к которой относится модель
     *
     * @return string - название таблицы
     */
    public function getTableName()
    {
        return "b_mibix_export_template";
    }
}

/**
 * Класс модели для работы с таблицей "Сущности"
 */
class CMibixExportEntityModel implements iMibixExportModel
{
    /**
     * @var array - информация об ошибках
     */
    private $ERRORS = array();

    /**
     * Провера полей формы
     *
     * @param $arFields - массив полей формы
     * @param $ID - ID значения
     * @return mixed - результат проверки
     */
    public function CheckFields($arFields, $ID)
    {
        $this->setErrors();

        $aMsg = array_merge(
            CMibixExportBaseModel::CheckByRules(Array(
                    "name_entity" => $arFields["name_entity"],
                    "code_entity" => $arFields["code_entity"]
                ),
                Array("required"=>true, "minlen"=>1, "maxlen"=>100)
            ),
            CMibixExportBaseModel::CheckByRules(Array(
                    "template_id" => $arFields["template_id"],
                    //"entity_id" => $arFields["entity_id"],
                    "iblock_id" => $arFields["iblock_id"],
                ),
                Array("required"=>true, "min"=>1)
            )
        );

        // Есть ли ошибки
        if(!empty($aMsg))
        {
            $this->setErrors($aMsg);
            return false;
        }

        return true;
    }

    /**
     * Получаем запись из базы по ID
     *
     * @param $ID - ID значения
     * @return mixed - результат выполнения запроса
     */
    public function GetByID($ID)
    {
        return CMibixExportBaseModel::BaseGetByID($ID, array("*"), self::getTableName());
    }

    /**
     * Получаем записи из базы с учетом фильтра и навигации
     *
     * @param array $aSort - массив сортировки
     * @param array $arFilter - фильтр выборки
     * @param bool $arNavStartParams - параметры постраничной навигации
     * @return mixed - результат выполнения запроса
     */
    public function GetList($aSort = Array(), $arFilter = Array(), $arNavStartParams = false)
    {
        global $DB;
        $arSqlSearch = Array();
        $from1 = "";
        if(is_array($arFilter))
        {
            foreach($arFilter as $key => $val)
            {
                if(!is_array($val))
                {
                    if( (strlen($val) <= 0) || ($val === "NOT_REF") )
                        continue;
                }
                switch(strtoupper($key))
                {
                    case "ID":
                        $arSqlSearch[] = GetFilterQuery("ds.id", $val, "N");
                        break;
                    case "ENTITY_ID":
                        $arSqlSearch[] = GetFilterQuery("ds.entity_id", $val, "N");
                        break;
                    case "TEMPLATE_ID":
                        $arSqlSearch[] = GetFilterQuery("ds.template_id", $val, "N");
                        break;
                    case "IBLOCK_ID":
                        $arSqlSearch[] = GetFilterQuery("ds.iblock_id", $val, "N");
                        break;
                    case "NAME_ENTITY":
                        $arSqlSearch[] = GetFilterQuery("ds.name_entity", $val, "Y", array("@", ".", "_"));
                        break;
                    case "CODE_ENTITY":
                        $arSqlSearch[] = GetFilterQuery("ds.code_entity", $val, "Y", array("@", ".", "_"));
                        break;
                    case "UPDATE_1":
                        $arSqlSearch[] = "ds.date_update>=".$DB->CharToDateFunction($val);
                        break;
                    case "UPDATE_2":
                        $arSqlSearch[] = "ds.date_update<=".$DB->CharToDateFunction($val." 23:59:59");
                        break;
                    case "INSERT_1":
                        $arSqlSearch[] = "ds.date_insert>=".$DB->CharToDateFunction($val);
                        break;
                    case "INSERT_2":
                        $arSqlSearch[] = "ds.date_insert<=".$DB->CharToDateFunction($val." 23:59:59");
                        break;
                    case "ACTIVE":
                        $arSqlSearch[] = ($val=="Y") ? "ds.active='Y'" : "ds.active='N'";
                        break;
                }
            }
        }
        $strSqlSearch = GetFilterSqlSearch($arSqlSearch);

        $arOrder = array();
        foreach($aSort as $by => $ord)
        {
            $by = strtoupper($by);
            $ord = (strtoupper($ord) <> "ASC"? "DESC": "ASC");
            switch($by)
            {
                case "ID": $arOrder[$by] = "ds.id ".$ord; break;
                case "ENTITY_ID": $arOrder[$by] = "ds.entity_id ".$ord; break;
                case "TEMPLATE_ID": $arOrder[$by] = "ds.template_id ".$ord; break;
                case "IBLOCK_ID": $arOrder[$by] = "ds.iblock_id ".$ord; break;
                case "NAME_ENTITY": $arOrder[$by] = "ds.name_entity ".$ord; break;
                case "CODE_ENTITY": $arOrder[$by] = "ds.code_entity ".$ord; break;
                case "DATE_INSERT": $arOrder[$by] = "ds.date_insert ".$ord; break;
                case "DATE_UPDATE": $arOrder[$by] = "ds.date_update ".$ord; break;
                case "ACT": $arOrder[$by] = "ds.active ".$ord; break;
            }
        }
        if(count($arOrder) <= 0) $arOrder["ID"] = "ds.id DESC";

        if(is_array($arNavStartParams))
        {
            $strSql = "
				SELECT count(".($from1 <> ""? "DISTINCT ds.id": "'x'").") as C
				FROM
					".self::getTableName()." ds
					LEFT JOIN ".CMibixExportTemplateModel::getTableName()." g ON (ds.template_id=g.id)
					$from1
				WHERE
				".$strSqlSearch;

            $res_cnt = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
            $res_cnt = $res_cnt->Fetch();
            $cnt = $res_cnt["C"];

            $strSql = "
				SELECT
					ds.id, ds.entity_id, ds.template_id, ds.iblock_id, ds.active, ds.name_entity, ds.code_entity,
					".$DB->DateToCharFunction("ds.date_update")." date_update,
					".$DB->DateToCharFunction("ds.date_insert")." date_insert,
					g.name
				FROM
					".self::getTableName()." ds
				LEFT JOIN ".CMibixExportTemplateModel::getTableName()." g ON (ds.template_id=g.id)
					$from1
				WHERE
				$strSqlSearch
				".($from1 <> ""?
                    "GROUP BY ds.id, ds.template_id, ds.iblock_id, ds.active, ds.name_entity, g.name":
                    ""
                )."
				ORDER BY ".implode(", ", $arOrder);

            $res = new CDBResult();
            $res->NavQuery($strSql, $cnt, $arNavStartParams);
            $res->is_filtered = (IsFiltered($strSqlSearch));

            return $res;
        }
        else
        {
            $strSql = "
				SELECT
					ds.id, ds.entity_id, ds.template_id, ds.iblock_id, ds.active, ds.name_data, ds.code_entity,
					".$DB->DateToCharFunction("ds.date_update")." date_update,
					".$DB->DateToCharFunction("ds.date_insert")." date_insert,
					g.name
				FROM
					".self::getTableName()." ds
					LEFT JOIN ".CMibixExportTemplateModel::getTableName()." g ON (ds.template_id=g.id)
					$from1
				WHERE
				$strSqlSearch
				".($from1 <> ""?
                    "GROUP BY ds.id, ds.template_id, ds.iblock_id, ds.active, ds.name_entity, g.name":
                    ""
                )."
				ORDER BY ".implode(", ", $arOrder);

            $res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
            $res->is_filtered = (IsFiltered($strSqlSearch));

            return $res;
        }
    }

    /**
     * Добавление записи в базу данных
     *
     * @param $arFields - массив полей формы
     * @param $SITE_ID - код сайта
     * @return mixed - результат выполнения запроса (ID новой записи или false)
     */
    public function Add($arFields, $SITE_ID = SITE_ID)
    {
        $arFields["active"] = "Y";
        //$arFields["shop_id"] = 1;

        // Преобразуем поля для записи в базу
        $arFields["include_sections"] = CMibixExportTools::multiselectPrepare($arFields["include_sections"]);
        $arFields["include_items"] = CMibixExportTools::multiselectPrepare($arFields["include_items"]);
        $arFields["exclude_items"] = CMibixExportTools::multiselectPrepare($arFields["exclude_items"]);

        // Проверка данных перед добавлением
        if(!$this->CheckFields($arFields, 0)) return false;

        return CMibixExportBaseModel::BaseAdd($arFields, self::getTableName());
    }

    /**
     * Обновление записи по ID
     *
     * @param $ID - ID значения
     * @param $arFields - массив полей формы
     * @param $SITE_ID - код сайта
     * @return mixed - результат выполнения запроса
     */
    public function Update($ID, $arFields, $SITE_ID = SITE_ID)
    {
        // Проверяем заполненные поля на ошибки и возвращаем false в случае их наличия, при этом сами ошибки сохраняем в переменной класса
        if(!$this->CheckFields($arFields, $ID)) return false;

        // Преобразуем поля для записи в базу
        if(!empty($arFields["include_sections"]))
            $arFields["include_sections"] = CMibixExportTools::multiselectPrepare($arFields["include_sections"]);
        if(!empty($arFields["exclude_sections"]))
            $arFields["exclude_sections"] = CMibixExportTools::multiselectPrepare($arFields["exclude_sections"]);
        if(!empty($arFields["include_items"]))
            $arFields["include_items"] = CMibixExportTools::multiselectPrepare($arFields["include_items"]);
        if(!empty($arFields["exclude_items"]))
            $arFields["exclude_items"] = CMibixExportTools::multiselectPrepare($arFields["exclude_items"]);

        return CMibixExportBaseModel::BaseUpdate($ID, $arFields, self::getTableName());
    }

    /**
     * Удаление записи по ID
     *
     * @param $ID - ID значения
     * @return mixed - результат выполнения запроса
     */
    public function Delete($ID)
    {
        return CMibixExportBaseModel::BaseDeleteByID($ID, self::getTableName());
    }

    /**
     * Устанавливает массив ошибок при проверке
     *
     * @param $ERRORS - добавляет массив ошибок
     */
    public function setErrors($ERRORS=array())
    {
        $this->ERRORS = $ERRORS;
    }

    /**
     * Возвращает массив ошибок возникших при проверке
     *
     * @return array - массив ошибок
     */
    public function getErrors()
    {
        return $this->ERRORS;
    }

    /**
     * Возвращает название таблицы базы данных, к которой относится модель
     *
     * @return string - название таблицы
     */
    public function getTableName()
    {
        return "b_mibix_export_entity";
    }
}

class CMibixeExport
{
    private static $bCreate = false;
    private static $urlShop = "";
    private static $bStepEnd = false;

    /**
     * Создание YML файла для Яндекс.Маркета
     *
     * @param $YML_FILE
     * @param $STEP_LIMIT
     * @return bool
     */
    public function CreateYML($YML_FILE, $STEP_LIMIT, $CRON=false, $SHOP_ID=1)
    {
        self::$bCreate = true;

        // текущее состояние выгрузки
        $curStatus = self::get_saved_steps($SHOP_ID);
        $TMP_YML_FILE = $YML_FILE . ".tmp";

        // если запуск через CRON + заполнено поле даты последней выгрузки (если нет, то делаем выгрузку без этих проверок)
        if($CRON && !empty($curStatus["last_run_time"]))
        {
            // Проверка, наступило ли время для срабатывания выгрузки
            $stepTime = self::get_profile_step_settings($SHOP_ID);

            // время последнего запуска + заданный интервал (в секундах)
            $next_time_run = strtotime($curStatus["last_run_time"]) + ($stepTime["step_interval_run"] * 60);

            // если время срабатывания не наступило, то выходим из функции
            if(time() < $next_time_run)
                return false;
        }

        // Проверка на подвисший скрипт (если
        self::check_freeze_process($SHOP_ID);

        // Проверка блокировки (если выгрузка уже проходит в данный момент)
        if($curStatus["in_blocked"] == "Y")
            return false;

        // Ставим блокировку (на случай повторного запуска скрипта во время выгрузки)
        self::set_status("blocked", "Y", $SHOP_ID);

        // инициализация новой пошаговой выгрузки
        if($curStatus["in_proccess"] != "Y")
        {
            // если есть временный файл, то удаляем его
            if (file_exists($TMP_YML_FILE)) unlink($TMP_YML_FILE);

            // создаем новый временный файл
            if ($fp = @fopen($TMP_YML_FILE, "wb"))
            {
                @fwrite($fp, "<?xml version=\"1.0\"?>\n");
                @fwrite($fp, "<rss xmlns:g=\"http://base.google.com/ns/1.0\" version=\"2.0\">\n");
                @fwrite($fp, "<channel>\n");
                foreach(self::get_yml_shop($SHOP_ID) as $elShop)
                {
                    @fwrite($fp, $elShop . "\n");
                }
                @fclose($fp);

                // устанавливаем статус "в процессе" для сайта 1
                self::set_status("proccess", "Y", $SHOP_ID);

                // Снимаем блокировку
                self::set_status("blocked", "N", $SHOP_ID);

                // Обновление времени шага
                self::set_last_time("step", $SHOP_ID);

                return true;
            }
        }
        else // очередной шаг выгрузки
        {
            if ($fp = @fopen($TMP_YML_FILE, "ab")) // дописываем файл
            {
                // DEBUG
                if (defined('MIBIX_DEBUG_GLEXPORT') && MIBIX_DEBUG_GLEXPORT==true)
                    CMibixExportTools::writeLOG("[INFO] function:".__FUNCTION__." (STEP_1)", self::$bStepEnd);

                foreach(self::get_yml_offers($STEP_LIMIT, $SHOP_ID) as $elOffer)
                {
                    @fwrite($fp, $elOffer . "\n");
                }

                // DEBUG
                if (defined('MIBIX_DEBUG_GLEXPORT') && MIBIX_DEBUG_GLEXPORT==true)
                    CMibixExportTools::writeLOG("[INFO] function:".__FUNCTION__." (STEP_2)", self::$bStepEnd);

                // если скрипт завершен по достижению лимита шага, генерируем следующий шаг
                if(self::$bStepEnd)
                {
                    // DEBUG
                    if (defined('MIBIX_DEBUG_GLEXPORT') && MIBIX_DEBUG_GLEXPORT==true)
                        CMibixExportTools::writeLOG("[INFO] function:".__FUNCTION__." (STEP_3)", self::$bStepEnd);

                    // закрываем запись в файл и редирект на следующий шаг
                    @fclose($fp);

                    // Снимаем блокировку
                    self::set_status("blocked", "N", $SHOP_ID);

                    // Обновление времени шага
                    self::set_last_time("step", $SHOP_ID);

                    return true;
                }
                else // вставляем "футер" для yml-файла
                {
                    // генерация "футера" yml-файла
                    @fwrite($fp, "</channel>\n");
                    @fwrite($fp, "</rss>\n");
                    @fclose($fp);

                    // Удаляем старый оригинальный YML-файл и на его место ставим новый
                    if (file_exists($YML_FILE)) unlink($YML_FILE);
                    rename($TMP_YML_FILE, $YML_FILE);

                    // чистим историю пошаговой выгрузки
                    self::steps_update(0,0,0,0,0,$SHOP_ID);

                    // устанавливаем статус "завершено" для сайта 1
                    self::set_status("proccess", "N", $SHOP_ID);

                    // ставим временную метку окончания выгрузки для сайта 1
                    self::set_last_time("run", $SHOP_ID);

                    // Обновление времени шага
                    self::set_last_time("step", $SHOP_ID);
                }
            }
        }

        // Снимаем блокировку
        self::set_status("blocked", "N", $SHOP_ID);

        // возврат ф-ии исп в ajax
        return false;
    }

    // Генерация YML файла "на лету"
    public function GetYML($SHOP_ID=1)
    {
        self::$bCreate = false;

        // чистка на случай не законченной генерации пошаговой выгрузки
        self::steps_update_noncheck(0,0,0,0,0,$SHOP_ID);

        ob_clean(); // очищаем буфер вывода

        // Устанавливаем заголовок
        header("Content-Type: text/xml; charset=UTF-8");
        echo "<"."?xml version=\"1.0\"?".">\n";
        echo "<rss xmlns:g=\"http://base.google.com/ns/1.0\" version=\"2.0\">\n";
        echo "<channel>\n";
        foreach(self::get_yml_shop($SHOP_ID) as $elShop)
        {
            echo $elShop . "\n";
        }
        foreach(self::get_yml_offers(0, $SHOP_ID) as $elOffer)
        {
            echo $elOffer . "\n";
        }
        echo "</channel>\n";
        echo "</rss>\n";
    }

    /**
     * Получаем XML тегов в дереве <shop>, кроме <offers>
     *
     * @param int $shop_id
     * @return array
     */
    private function get_yml_shop($shop_id=1)
    {
        global $DB, $APPLICATION;

        // Ищем в настройках параметры для доступа к соц.сетям
        $arShopData = array();
        $rsShop = $DB->Query("SELECT name,company,url FROM b_mibix_gl_profile WHERE id='".$shop_id."' AND active='Y'", true);
        if ($rowShop = $rsShop->Fetch())
        {
            // Определяем кодировку сайта
            $siteCharset = 'windows-1251';
            if (defined('BX_UTF') && BX_UTF==true)
            {
                $siteCharset = 'UTF-8';
            }

            // имя, компания, url (обязательные) // COption::GetOptionString("main", "site_name", "")
            $arShopData["title"] = "<title>".$APPLICATION->ConvertCharset(htmlspecialcharsbx($rowShop["name"]), $siteCharset, 'UTF-8')."</title>";
            $arShopData["link"] = "<link>".htmlspecialcharsbx($rowShop["url"])."</link>";
            $arShopData["description"] = "<description>".$APPLICATION->ConvertCharset(htmlspecialcharsbx($rowShop["company"]), $siteCharset, 'UTF-8')."</description>";

            // устанавливаем URL магазина в глобальную переменную
            self::$urlShop = htmlspecialcharsbx($rowShop["url"]);
        }

        return $arShopData;
    }

    /**
     * Получаем офферов, на основе всех правил и прикрепленных к ним источников данных
     */
    private function get_yml_offers($STEP_LIMIT=0, $SHOP_ID=1)
    {
        global $DB;

        $arOffers = array();
        $COUNTER = 0;
        self::$bStepEnd = false;

        // если задан лимит, то учитываем его (значит скрипт вызван пошаговым способом)
        $nTopCount = false;
        if($STEP_LIMIT>0) {
            $nTopCount = array("nTopCount" => $STEP_LIMIT);
        }

        // DEBUG
        if (defined('MIBIX_DEBUG_GLEXPORT') && MIBIX_DEBUG_GLEXPORT==true)
            CMibixExportTools::writeLOG("[INFO] function:".__FUNCTION__." (nTopCount)", $nTopCount);

        // Параметры из таблицы текущего шага выгрузки
        $arSaveSteps = self::get_saved_steps($SHOP_ID);

        // DEBUG
        if (defined('MIBIX_DEBUG_GLEXPORT') && MIBIX_DEBUG_GLEXPORT==true)
            CMibixExportTools::writeLOG("[INFO] function:".__FUNCTION__." (arSaveSteps)", $arSaveSteps);

        // Обходим все активные правила выгрузки
        $strRulesSQL = "
				SELECT
					ds.iblock_id, ds.include_sections, ds.exclude_sections, ds.include_items, ds.exclude_items, ds.include_sku, ds.dpurl_use_sku, ds.filters,
					r.*,
					g.url as url_shop, g.utm
				FROM
					b_mibix_gl_datasource ds
				JOIN b_mibix_gl_rules r ON (ds.id=r.datasource_id)
				JOIN b_mibix_gl_profile g ON (ds.shop_id=g.id)
				WHERE
                    ds.active = 'Y' AND ds.shop_id = ".$SHOP_ID." AND r.active = 'Y' AND r.id >= ".$arSaveSteps["rule_id"]."
                ORDER BY r.id ASC";

        $dbRulesRes = $DB->Query($strRulesSQL);
        while($arRule = $dbRulesRes->Fetch())
        {
            // разделы с товарами, которые выбрал пользователь
            $arIncSections = array_diff(explode(",", $arRule["include_sections"]), array(''));

            // товары, которые выбрал пользователь
            $arIncItems = array_diff(explode(",", $arRule["include_items"]), array(''));

            // разделы с товарами, которые исключил пользователь
            $arExcSections = array_diff(explode(",", $arRule["exclude_sections"]), array(''));

            // товары, которые исключил пользователь
            $arExcItems = array_diff(explode(",", $arRule["exclude_items"]), array(''));

            // Формируем фильтр для выборки элементов
            $arFilterOffers = Array(
                "IBLOCK_ID"=>IntVal($arRule["iblock_id"]),
                "ACTIVE"=>"Y"
            );

            // Обновляем данные параметров из таблицы текущего шага выгрузки
            $arSaveSteps = self::get_saved_steps($SHOP_ID);

            // Прервался ли шаг на элементе со SKU? Если да, то продолжаем использовать ID основного элемента (условие ">=")
            if (intval($arSaveSteps["sku_element_id"])>0)
                $arFilterOffers[">=ID"] = $arSaveSteps["element_id"];
            else
                $arFilterOffers[">ID"] = $arSaveSteps["element_id"];

            // DEBUG
            if (defined('MIBIX_DEBUG_GLEXPORT') && MIBIX_DEBUG_GLEXPORT==true)
                CMibixExportTools::writeLOG("[INFO] function:".__FUNCTION__." (arFilterOffers)", $arFilterOffers);

            // Определяем параметры для торговых предложений
            // D - инфоблок является торговым каталогом
            // O - инфоблок содержит торговые предложения (SKU)
            // +P - инфоблок товаров, имеющих торговые предложения, но сам торговым каталогом не является
            // +X - инфоблок товаров, имеющих торговые предложения, при это сам инфоблок тоже является торговым каталогом.
            $intOfferIBlockID = 0;
            $boolOffersSKU = false;
            $arOffersSKU = array('SKU_PROPERTY_ID'=>0); // по умолчанию
            $arCatalog = NULL;
            if(CModule::IncludeModule("sale") && CModule::IncludeModule("catalog"))
                $arCatalog = CCatalog::GetByIDExt(IntVal($arRule["iblock_id"]));
            if (!empty($arCatalog))
            {
                $arOffersSKU = CCatalogSKU::GetInfoByProductIBlock(IntVal($arRule["iblock_id"]));
                if (!empty($arOffersSKU['IBLOCK_ID']))
                {
                    $intOfferIBlockID = $arOffersSKU['IBLOCK_ID'];
                    $rsOfferIBlocks = CIBlock::GetByID($intOfferIBlockID);
                    if (($arOfferIBlock = $rsOfferIBlocks->Fetch()))
                    {
                        $boolOffersSKU = true; // есть инфоблок с торговыми предложениями
                    }
                }
            }

            // DEBUG
            if (defined('MIBIX_DEBUG_GLEXPORT') && MIBIX_DEBUG_GLEXPORT==true)
                CMibixExportTools::writeLOG("[INFO] function:".__FUNCTION__." (boolOffersSKU)", $boolOffersSKU);

            // Обходим все элементы данного правила с сортировкой по ID
            $resItems = CIBlockElement::GetList(array("ID" => "ASC"), $arFilterOffers, false, $nTopCount);
            while ($obItem = $resItems->GetNextElement()) {
                $emptyItem = false;
                $arItem = $obItem->GetFields();
                $arItem["PROPERTIES"] = $obItem->GetProperties();

                // Находим все группы, принадлежащие элементу
                $arItemGroups = Array();
                if (IntVal($arItem["IBLOCK_SECTION_ID"]) > 0)
                    $arItemGroups[] = $arItem["IBLOCK_SECTION_ID"];

                $dbGroups = CIBlockElement::GetElementGroups($arItem["ID"], true);
                while ($arGroup = $dbGroups->Fetch()) {
                    $arItemGroups[] = $arGroup["ID"];
                }

                // === ФИЛЬТРАЦИЯ ПО РАЗДЕЛАМ И ЭЛЕМЕНТАМ ===
                // Пропускаем элемент если он не принадленит ни одному разделу
                if (empty($arItemGroups))
                {
                    $emptyItem = true;
                }
                // Если не выбран ни один раздел и явно не указан элемент, то не фильтруем
                elseif (empty($arIncSections) && !in_array($arItem["ID"], $arIncItems))
                {
                    $emptyItem = true;
                }
                // Не фильтруем элемент если явно выбран пользователем в список включенных
                elseif (empty($arIncItems) || !in_array($arItem["ID"], $arIncItems))
                {
                    // Пропускаем элемент, если он явно указан в списке исключаемых
                    if(count($arExcItems)>0 && in_array($arItem["ID"], $arExcItems))
                        $emptyItem = true;

                    // Обходим все разделы, принадлежающие элементу
                    foreach ($arItemGroups as $itemGroupID)
                    {
                        if ($itemGroupID > 0)
                        {
                            // Пропускаем элемент, если он не принадлежит хотя бы одному разделу, которые выбрал пользователь
                            if (count($arIncSections) > 0 && !in_array($itemGroupID, $arIncSections)) {
                                $emptyItem = true;
                            } else { // Если все же есть раздел, который выбрал пользователь, то отменяем предыдущее условие и выходим из цикла
                                $emptyItem = false;
                                break;
                            }

                            // Пропускаем элемент, если он принадлежит хотя бы одному разделу, который пользователь установил в исключениях
                            if (count($arExcSections) > 0 && in_array($itemGroupID, $arExcSections))
                                $emptyItem = true;
                        }
                    }
                }
                // === ~ФИЛЬТРАЦИЯ ПО РАЗДЕЛАМ И ЭЛЕМЕНТАМ ===

                // DEBUG
                if (defined('MIBIX_DEBUG_GLEXPORT') && MIBIX_DEBUG_GLEXPORT==true)
                {
                    CMibixExportTools::writeLOG("[INFO] function:" . __FUNCTION__ . " (COUNTER_1)", $COUNTER);
                    CMibixExportTools::writeLOG("[INFO] function:" . __FUNCTION__ . " (STEP_4)", self::$bStepEnd);
                }

                // Получаем YML-описание в зависимости от типа элемента
                if( $emptyItem )
                {
                    $COUNTER++; //counter for empty elements
                }
                elseif (('P' == $arCatalog['CATALOG_TYPE'] || 'X' == $arCatalog['CATALOG_TYPE']) && $boolOffersSKU && IntVal($arOffersSKU['SKU_PROPERTY_ID'])>0)
                {
                    // DEBUG
                    if (defined('MIBIX_DEBUG_GLEXPORT') && MIBIX_DEBUG_GLEXPORT==true)
                        CMibixExportTools::writeLOG("[INFO] function:".__FUNCTION__." (CATALOG_TYPE)", "SKU");

                    // Получаем YML-описание для элемента offer и его торговых предложений, если они есть
                    $arTmpOffersSku = self::get_yml_offer_sku($intOfferIBlockID, $arOffersSKU['SKU_PROPERTY_ID'], $arRule, $arItem, $nTopCount, $COUNTER, $arSaveSteps, $SHOP_ID);
                    if(!empty($arTmpOffersSku))
                        $arOffers = array_merge($arOffers, $arTmpOffersSku);
                }
                else
                {
                    // DEBUG
                    if (defined('MIBIX_DEBUG_GLEXPORT') && MIBIX_DEBUG_GLEXPORT==true)
                        CMibixExportTools::writeLOG("[INFO] function:".__FUNCTION__." (CATALOG_TYPE)", "SIMPLE");

                    $COUNTER++; // обновляем счетчик обработанного элемента

                    // Получаем YML-описание для элемента offer (не содержащий торговых предложений)
                    $arTmpOffers = self::get_yml_offer($arRule, $arItem);
                    if(!empty($arTmpOffers))
                        $arOffers[] = $arTmpOffers;
                }

                // DEBUG
                if (defined('MIBIX_DEBUG_GLEXPORT') && MIBIX_DEBUG_GLEXPORT==true)
                {
                    CMibixExportTools::writeLOG("[INFO] function:" . __FUNCTION__ . " (COUNTER_2)", $COUNTER);
                    CMibixExportTools::writeLOG("[INFO] function:" . __FUNCTION__ . " (STEP_5)", self::$bStepEnd);
                }

                // проверяем, сгенерирован ли через sku весь допустимый лимит элементов
                if(self::$bStepEnd)
                {
                    break 2; // принудительно выходим из всех (двух) циклов
                }
                else
                {
                    // запись в базу текущего состояния генерируемого элемента
                    $elementID = IntVal($arItem["ID"]);
                    self::steps_update($arRule["id"], $arRule["iblock_id"], $elementID, 0, 0, $SHOP_ID);

                    // проверка лимита шага
                    if(is_array($nTopCount) && isset($nTopCount["nTopCount"]) && intval($nTopCount["nTopCount"])>0)
                    {
                        // если счетчик достиг лимита, делаем редирект на следующий шаг (параметры хранятсья в базе)
                        if($COUNTER >= intval($nTopCount["nTopCount"]))
                        {
                            // DEBUG
                            if (defined('MIBIX_DEBUG_GLEXPORT') && MIBIX_DEBUG_GLEXPORT==true)
                                CMibixExportTools::writeLOG("[INFO] function:".__FUNCTION__." (STEP_7)", self::$bStepEnd);

                            self::$bStepEnd = true; // помечаем, что цикл завершен из-за достижения установленного лимита
                            break 2; // принудительно выходим из всех (двух) циклов
                        }
                    }
                }

            } // while end elements of rule

            self::steps_update($arRule["id"], $arRule["iblock_id"], 0, 0, 0, $SHOP_ID);

        } // while end rules

        return $arOffers;
    }

    /**
     * Возвращаем XML-описание элемента
     *
     * @param $arRule array Массив с данными правила
     * @param $arItem array Массив с данными элемента
     * @param $arOfferItemSKU array Массив с данными торгового предложения (если оно передано)
     * @return string Элемент <offer> с заполненными значениями согласно типу
     */
    private function get_yml_offer($arRule, $arItem, $arOfferItemSKU=array())
    {
        // Возвращаемое значение пустое по умолчанию
        $arOffer = array();
        $strOffers = '';

        // Проверка пользовательской фильтрации
        if(!self::check_filter($arRule, $arItem, $arOfferItemSKU))
            return $strOffers;

        // <title> (Название - обязательный)
        $tagTmp = self::get_yml_offer_title($arRule["title"], $arItem, $arOfferItemSKU);
        if(!empty($tagTmp))
            $arOffer[] = $tagTmp;
        else
            return array(); // прерываем если пустой результат

        // <link> + utm (Ссылка на товар - обязательный)
        $tagTmp = self::get_yml_offer_url($arRule, $arItem, $arOfferItemSKU);
        if(!empty($tagTmp))
            $arOffer[] = $tagTmp;
        else
            return array(); // прерываем если пустой результат

        // <description> (Описание - обязательный)
        $tagTmp = self::get_yml_offer_description($arRule, $arItem, $arOfferItemSKU);
        if(!empty($tagTmp))
            $arOffer[] = $tagTmp;
        else
            return array(); // прерываем если пустой результат

        // <g:id> (ID - обязательный)
        $tagTmp = self::get_yml_offer_id($arItem, $arOfferItemSKU);
        if(!empty($tagTmp))
            $arOffer[] = $tagTmp;
        else
            return array(); // прерываем формирование <item> - обязательный параметр

        // <g:condition>
        $tagTmp = self::get_property_value_tag("g:condition", $arRule["condition"], $arItem, $arOfferItemSKU);
        if(!empty($tagTmp))
            $arOffer[] = $tagTmp;
        else
            return array(); // прерываем если пустой результат

        // <price> (Цена + Валюта -> обязательный)
        $tagTmp = self::get_yml_offer_price($arRule, $arItem, $arOfferItemSKU);
        if(!empty($tagTmp) && $tagTmp["minprice"]>0)
        {
            $arOffer[] = $tagTmp["price"];
            if(array_key_exists('oldprice', $tagTmp)) $arOffer[] = $tagTmp["oldprice"]; // тег <oldprice> если есть
            $arOffer[] = $tagTmp["currency"];
        }
        else
            return array(); // без цены не формируем

        // <g:image_link> (Картинки. Поля и свойства для картинок, которые указал польватель)
        $tagTmp = self::get_yml_offer_pictures($arRule, $arItem, $arOfferItemSKU);
        if(!empty($tagTmp))
            $arOffer = array_merge($arOffer, $tagTmp);
        else
            return array(); // прерываем если пустой результат

        // <g:additional_image_link> (Дополнительные картинки. Поля и свойства для картинок, которые указал польватель)
        $tagTmp = self::get_yml_offer_pictures($arRule, $arItem, $arOfferItemSKU, true);
        if(!empty($tagTmp))
            $arOffer = array_merge($arOffer, $tagTmp);

        // <g:product_type> (Тип продукта)
        $tagTmp = self::get_property_value_tag("g:product_type", $arRule["product_type"], $arItem, $arOfferItemSKU);
        if(!empty($tagTmp))
            $arOffer[] = $tagTmp;

        // <g:availability> (Доступность)
        $tagTmp = self::get_property_value_tag("g:availability", $arRule["available"], $arItem, $arOfferItemSKU);
        if(!empty($tagTmp))
            $arOffer[] = $tagTmp;
        else
            return array(); // прерываем если пустой результат

        // <g:mpn> (Код производителя)
        $tagTmp = self::get_property_value_tag("g:mpn", $arRule["mpn"], $arItem, $arOfferItemSKU);
        if(!empty($tagTmp))
            $arOffer[] = $tagTmp;

        // <g:brand> (Бренд)
        $tagTmp = self::get_property_value_tag("g:brand", $arRule["brand"], $arItem, $arOfferItemSKU);
        if(!empty($tagTmp))
            $arOffer[] = $tagTmp;

        // <g:item_group_id> (Группа товаров)
        $tagTmp = self::get_yml_offer_group_id($arRule["item_group"], $arItem, $arOfferItemSKU);
        if(!empty($tagTmp))
            $arOffer[] = $tagTmp;

        // <g:color> (Цвет)
        $tagTmp = self::get_property_value_tag("g:color", $arRule["color"], $arItem, $arOfferItemSKU);
        if(!empty($tagTmp))
            $arOffer[] = $tagTmp;

        // <g:gender> (Пол)
        $tagTmp = self::get_property_value_tag("g:gender", $arRule["gender"], $arItem, $arOfferItemSKU);
        if(!empty($tagTmp))
            $arOffer[] = $tagTmp;

        // <g:age_group> (Возрастная группа)
        $tagTmp = self::get_property_value_tag("g:age_group", $arRule["age_group"], $arItem, $arOfferItemSKU);
        if(!empty($tagTmp))
            $arOffer[] = $tagTmp;

        // <g:material> (Материал)
        $tagTmp = self::get_property_value_tag("g:material", $arRule["material"], $arItem, $arOfferItemSKU);
        if(!empty($tagTmp))
            $arOffer[] = $tagTmp;

        // <g:pattern> (Узор)
        $tagTmp = self::get_property_value_tag("g:pattern", $arRule["pattern"], $arItem, $arOfferItemSKU);
        if(!empty($tagTmp))
            $arOffer[] = $tagTmp;

        // <g:size> (Размер)
        $tagTmp = self::get_property_value_tag("g:size", $arRule["size"], $arItem, $arOfferItemSKU);
        if(!empty($tagTmp))
            $arOffer[] = $tagTmp;

        // <g:size_type> (Тип размера)
        $tagTmp = self::get_property_value_tag("g:size_type", $arRule["size_type"], $arItem, $arOfferItemSKU);
        if(!empty($tagTmp))
            $arOffer[] = $tagTmp;

        // <g:size_system> (Система размера)
        $tagTmp = self::get_property_value_tag("g:size_system", $arRule["size_system"], $arItem, $arOfferItemSKU);
        if(!empty($tagTmp))
            $arOffer[] = $tagTmp;

        // <g:shipping> (Доставка)
        $tagTmp = self::get_yml_shipping($arRule["shipping"], $arItem, $arOfferItemSKU);
        if(!empty($tagTmp))
            $arOffer[] = $tagTmp;

        // <g:shipping_weight> (Система размера)
        $tagTmp = self::get_property_value_tag("g:shipping_weight", $arRule["shipping_weight"], $arItem, $arOfferItemSKU);
        if(!empty($tagTmp))
            $arOffer[] = $tagTmp;

        // <g:adult> (Система размера)
        $tagTmp = self::get_property_value_tag("g:adult", $arRule["adult"], $arItem, $arOfferItemSKU);
        if(!empty($tagTmp))
            $arOffer[] = $tagTmp;

        // Если получены параметры элемента
        if(!empty($arOffer))
        {
            // Формируем атрибуты и значения для <offer>
            $strOffers = "<item>\n";
            foreach($arOffer as $ofParam)
            {
                if (count($ofParam)>0)
                    $strOffers .= $ofParam . "\n";
            }
            $strOffers .= "</item>";
        }

        return $strOffers;
    }

    /**
     * Возвращаем XML-описание торговых предложений для элемента SKU
     *
     * @param $skuIBlockID int ID инфоблока с торговыми предложениями
     * @param $skuPropertyID int ID свойства привязки к торговым предложениям
     * @param $arRule array Массив с данными правила
     * @param $arItem array Массив с данными элемента
     * @param $COUNTER int Счетчик количества обработанных жлементов (параметр по ссылке)
     * @param $nTopCount array|bool содержит информацию о количестве выбираемых элементов
     * @param $arSaveSteps array Массив с данными поэтапоной выгрузки
     * @return string Элемент <offer> с заполненными значениями согласно типу
     */
    private function get_yml_offer_sku($skuIBlockID, $skuPropertyID, $arRule, $arItem, $nTopCount, &$COUNTER, $arSaveSteps, $SHOP_ID=1)
    {
        $arOffers = array();
        $existOffers = false;

        // DEBUG
        if (defined('MIBIX_DEBUG_GLEXPORT') && MIBIX_DEBUG_GLEXPORT==true)
            CMibixExportTools::writeLOG("[INFO] function:".__FUNCTION__." (COUNTER_3)", $COUNTER);

        // Проходимся по всем торговым предложениями текущего элемента
        if ($arRule["include_sku"]=="Y")
        {
            $arOfferFilter = array(
                'IBLOCK_ID' => $skuIBlockID,
                'PROPERTY_'.$skuPropertyID => 0,
                ">ID" => $arSaveSteps["sku_element_id"],
                "ACTIVE" => "Y",
                "ACTIVE_DATE" => "Y"
            );
            $arOfferFilter['PROPERTY_'.$skuPropertyID] = $arItem['ID'];
            $rsOfferItems = CIBlockElement::GetList(array(), $arOfferFilter, false, $nTopCount/*,$arOfferSelect*/);
            while ($obOfferItem = $rsOfferItems->GetNextElement())
            {
                $existOffers = true;
                $arOfferItem = $obOfferItem->GetFields();
                $arOfferItem["PROPERTIES"] = $obOfferItem->GetProperties();

                $tmpOffer = self::get_yml_offer($arRule, $arItem, $arOfferItem);
                if(!empty($tmpOffer))
                    $arOffers[] = $tmpOffer;

                // запись в базу текущего состояния генерируемого элемента
                $elementID = IntVal($arItem["ID"]);
                self::steps_update($arRule["id"], $arRule["iblock_id"], $elementID, $skuIBlockID, $arOfferItem["ID"], $SHOP_ID);

                // увеличиваем счетчик до проверки лимита шага
                $COUNTER++;

                // DEBUG
                if (defined('MIBIX_DEBUG_GLEXPORT') && MIBIX_DEBUG_GLEXPORT==true)
                {
                    CMibixExportTools::writeLOG("[INFO] function:" . __FUNCTION__ . " (ITEM_ID)", $arItem["ID"]);
                    CMibixExportTools::writeLOG("[INFO] function:" . __FUNCTION__ . " (COUNTER_4)", $COUNTER);
                    CMibixExportTools::writeLOG("[INFO] function:" . __FUNCTION__ . " (nTopCount_sku)", $nTopCount);
                }

                // проверка на лимит шага
                if(is_array($nTopCount) && isset($nTopCount["nTopCount"]) && intval($nTopCount["nTopCount"])>0)
                {
                    // если счетчик достиг лимита, делаем редирект на следующий шаг (параметры хранятсья в базе)
                    if($COUNTER >= intval($nTopCount["nTopCount"]))
                    {
                        // DEBUG
                        if (defined('MIBIX_DEBUG_GLEXPORT') && MIBIX_DEBUG_GLEXPORT==true)
                            CMibixExportTools::writeLOG("[INFO] function:".__FUNCTION__." (STEP_6)", self::$bStepEnd);

                        self::$bStepEnd = true; // помечаем, что цикл завершен из-за достижения установленного лимита
                        break; // прерываем цикл при достижении лимита
                    }
                }
            }
        }

        // DEBUG
        if (defined('MIBIX_DEBUG_GLEXPORT') && MIBIX_DEBUG_GLEXPORT==true)
            self::writeLOG("[INFO] function:".__FUNCTION__." (COUNTER_5)", $COUNTER);

        // Если у элемента отсутствуют предложения, то пытаемся обработать его как обычный элемент
        if(!$existOffers)
        {
            $tmpOffer = self::get_yml_offer($arRule, $arItem);
            if(!empty($tmpOffer))
                $arOffers[] = $tmpOffer;

            // запись в базу текущего состояния генерируемого элемента
            $elementID = IntVal($arItem["ID"]);
            self::steps_update($arRule["id"], $arRule["iblock_id"], $elementID, 0, 0, $SHOP_ID);

            $COUNTER++;
        }

        // DEBUG
        if (defined('MIBIX_DEBUG_GLEXPORT') && MIBIX_DEBUG_GLEXPORT==true)
            self::writeLOG("[INFO] function:".__FUNCTION__." (COUNTER_6)", $COUNTER);

        return $arOffers;
    }

    /**
     * Получаем xml для URL
     *
     * @param $arRule array Массив со значениями правила
     * @param $arItem array Массив со значениями обрабатываемого элемента
     * @param $arOfferItemSKU array Массив со значениями торгового предложения (если оно передано)
     * @return string сформированный тег или пустая строка
     */
    private function get_yml_offer_url($arRule, $arItem, $arOfferItemSKU=array())
    {
        $strReturn = '';

        if($arRule["salon"]=="Y" && $arRule["url"]!="Y") {}
        else
        {
            // Формируем ссылку
            $tmpURL = $arItem["DETAIL_PAGE_URL"];
            $outURL = htmlspecialcharsbx($arItem["~DETAIL_PAGE_URL"]);

            // Если пользователь выбрал "брать ссылку на товар из SKU-элементов"
            if(!empty($arOfferItemSKU) && $arRule["dpurl_use_sku"]=="Y")
            {
                $tmpURL = $arOfferItemSKU["DETAIL_PAGE_URL"];
                $outURL = htmlspecialcharsbx($arOfferItemSKU["~DETAIL_PAGE_URL"]);
            }

            // UTM-метка если есть
            if(strlen($arRule["utm"]))
            {
                // [динамическая utm] - подмена шаблона на оригинальный ID элемента (для простого товара или sku)
                if(empty($arOfferItemSKU))
                    $arRule["utm"] = str_replace("#ITEM_ID#", $arItem["ID"], $arRule["utm"]);
                else
                    $arRule["utm"] = str_replace("#ITEM_ID#", $arOfferItemSKU["ID"], $arRule["utm"]);

                // [динамическая utm] - подмена шаблона на родительский ID элемента (для sku)
                $arRule["utm"] = str_replace("#PARENT_ID#", $arItem["ID"], $arRule["utm"]);

                $outURL = $outURL . (strstr($tmpURL, '?') === false ? '?' : '&amp;') . $arRule["utm"];
            }

            // Уникальная ссылка для торгового предложения
            if(!empty($arOfferItemSKU))
            {
                $outURL = $outURL . (strstr($outURL, '?') === false ? '?' : '&amp;') . "offer=" . $arOfferItemSKU["ID"];
            }

            $strReturn = "<link>".CMibixExportTools::getFixURL($arRule["url_shop"], $outURL, false)."</link>";
        }

        return $strReturn;
    }

    /**
     * Получаем xml для Цены
     *
     * @param $arRule array Массив со значениями правила
     * @param $arItem array Массив со значениями обрабатываемого элемента
     * @param $arOfferItemSKU array Массив со значениями торгового предложения (если оно передано)
     * @return string сформированный тег или пустая строка
     */
    private function get_yml_offer_price($arRule, $arItem, $arOfferItemSKU=array())
    {
        $arReturn = array();

        // определяем ID товара или торгового предложения, у которого необходимо узнать цену
        $tmpItemID = $arItem['ID'];
        if(!empty($arOfferItemSKU))
            $tmpItemID = $arOfferItemSKU['ID'];

        // Формирование тега <g:oldprice>
        if (!empty($arRule["oldprice"]))
        {
            // Числовые типы относим к ID типов цен, остальное к символьным кодам свойств
            if(is_numeric($arRule["oldprice"])) // выбран тип цены
            {
                $arReturn = self::get_need_price($tmpItemID, $arRule["oldprice"], $arItem["LID"], $arRule["oldprice_optimal"], true);
                $maxPrice = $arReturn["maxprice"];
            }
            else // выбрано свойство
            {
                $strTag = self::get_property_value($arRule["oldprice"], $arItem, false, 0, $arOfferItemSKU);
                if(!empty($strTag))
                {
                    $maxPrice = $strTag;
                    $arReturn["oldprice"] = "<g:oldprice>" . $maxPrice . "</g:oldprice>";
                }
            }
        }

        // Формирование тега <price>
        if(is_numeric($arRule["price"])) // выбран тип цены
        {
            $arReturn = self::get_need_price($tmpItemID, $arRule["price"], $arItem["LID"], $arRule["price_optimal"]);
            $minPrice = $arReturn["minprice"];
        }
        else // выбрано свойство
        {
            $strTag = self::get_property_value($arRule["price"], $arItem, false, 0, $arOfferItemSKU);
            if(!empty($strTag))
            {
                $minPrice = $strTag;

                if(!empty($arRule["price_currency"]))
                    $minPriceCurrency = $arRule["price_currency"]; // код валюты определяется пользователем в настройках
                else
                    $minPriceCurrency = "RUB"; // рубль по умолчанию

                $arReturn["minprice"] = $minPrice;
                $arReturn["price"] = "<g:price>".$minPrice." ".$minPriceCurrency."</g:price>";
            }
        }

        // Защита цен от равенства и обратного превышения
        if(!empty($maxPrice) && !empty($minPrice))
        {
            if($minPrice >= $maxPrice) unset($arReturn["oldprice"]);
        }

        return $arReturn;
    }

    /**
     * Определяем и получаем цену товара с учетом переданных параметров
     *
     * @param $itemID - ID товара
     * @param $catalogID - ID ценовой категории
     * @param $siteLID - LID сайта
     * @param $optimalPrice - использовать ли оптимальную цену
     * @param bool $isOld - вывести старую цену
     *
     * @return array - массив с параметрами цены
     */
    private function get_need_price($itemID, $catalogID, $siteLID, $optimalPrice, $isOld=false)
    {
        $arReturn = Array();

        if(CModule::IncludeModule("sale") && CModule::IncludeModule("catalog"))
        {
            $rsPrices = CPrice::GetListEx(array(), array(
                    'PRODUCT_ID' => $itemID,
                    'CATALOG_GROUP_ID' => $catalogID,
                    'CAN_BUY' => 'Y'
                )
            );
            if ($arPrice = $rsPrices->Fetch())
            {
                // включена оптимальная цена для вывода тега <oldprice>
                $mPrice = 0;
                if($optimalPrice=="Y")
                {
                    if ($arOptimalOldPrice = CCatalogProduct::GetOptimalPrice(
                        $itemID,
                        1,
                        array(2), // anonymous
                        'N',
                        array($arPrice),
                        $siteLID
                    )
                    ) {
                        $mPrice = $arOptimalOldPrice['DISCOUNT_PRICE'];
                    }
                } else {
                    $mPrice = $arPrice["PRICE"];
                }

                // новая или старая цена
                $minPriceCurrency = CCurrency::GetBaseCurrency();
                if($isOld)
                {
                    $arReturn["maxprice"] = $mPrice;
                    $arReturn["oldprice"] = "<g:oldprice>" . $mPrice . " ".$minPriceCurrency. "</g:oldprice>";
                }
                else
                {
                    $arReturn["minprice"] = $mPrice;
                    $arReturn["price"] = "<g:price>".$mPrice. " ".$minPriceCurrency."</g:price>";
                }
            }
        }

        return $arReturn;
    }

    /**
     * Обновляет (запоминает) информацию об обработанном ID элемента (товара)
     *
     * @param $ruleID
     * @param $IBlockID
     * @param $elementID
     * @param int $skuIBlockID
     * @param int $skuElementID
     */
    private function steps_update($ruleID, $IBlockID, $elementID, $skuIBlockID=0, $skuElementID=0, $shop=1)
    {
        // выход, если выбран метод прямого получения файла (без шаговой генерации)
        if(!self::$bCreate) return;

        self::steps_update_noncheck($ruleID, $IBlockID, $elementID, $skuIBlockID, $skuElementID, $shop);
    }

    /**
     * Получаем названия товара
     *
     * @param $rule_name string Строка со значениям имени или тайтла
     * @param $arItem array Массив со значениями обрабатываемого элемента
     * @param $arOfferItemSKU array Массив со значениями торгового предложения (если оно передано)
     * @return string сформированный тег или пустая строка
     */
    private function get_yml_offer_title($rule_name, $arItem, $arOfferItemSKU=array())
    {
        $strReturn = '';

        if($strTag = self::get_property_value($rule_name, $arItem, false, 0, $arOfferItemSKU))
        {
            if(strlen($strTag))
            {
                if($strTag=="titleitem" || $strTag=="titlesku" || $strTag=="titleitemsku") // из названия элемента
                {
                    if(!empty($arOfferItemSKU) && $strTag=="titlesku")
                        $strReturn = "<title>".CMibixExportTools::googleText2xml($arOfferItemSKU["NAME"], true)."</title>";
                    elseif(!empty($arOfferItemSKU) && $strTag=="titleitemsku")
                        $strReturn = "<title>".CMibixExportTools::googleText2xml($arItem["NAME"], true) . " " . CMibixExportTools::googleText2xml($arOfferItemSKU["NAME"], true)."</title>";
                    else
                        $strReturn = "<title>".CMibixExportTools::googleText2xml($arItem["NAME"], true)."</title>";
                }
                else // из значения свойства
                {
                    $strReturn = "<title>".$strTag."</title>";
                }
            }
        }

        return $strReturn;
    }

    /**
     * Возвращаем тег <picture> с URL картинки по коду изображения битрикс
     *
     * @param $pictNo
     * @param $urlShop
     * @param $showTag
     * @return string
     */
    private function get_yml_picture_by_code($pictNo, $urlShop, $showTag=true, $isAdditional=false)
    {
        $strFile = '';

        // получаем ссылку по номеру файла
        if ($arFile = CFile::GetFileArray($pictNo))
            $strFile = CMibixExportTools::getFixURL($urlShop, $arFile["SRC"]);

        // если заполнена ссылка и нужно верунть тег
        if (!empty($strFile) && $showTag) {
            if ($isAdditional)
                return "<g:additional_image_link>" . $strFile . "</g:additional_image_link>";
            else
                return "<g:image_link>" . $strFile . "</g:image_link>";
        }

        return $strFile;
    }

    /**
     * Получаем тег описания
     *
     * @param $arRule array Массив со значениями правила
     * @param $arItem array Массив со значениями обрабатываемого элемента
     * @return string сформированный тег или пустая строка
     */
    private function get_yml_offer_description($arRule, $arItem, $arOfferItemSKU=Array())
    {
        $strReturn = '';

        if(strlen($arRule["description"]))
        {
            if($arRule["description"] == "PREVIEW_TEXT")
            {
                if(strlen($arItem["PREVIEW_TEXT"]))
                {
                    if(strlen($arItem["PREVIEW_TEXT"]))
                        $strReturn = CMibixExportTools::googleText2xml($arItem["~PREVIEW_TEXT"], true, false, true, 1000);
                }
            }
            elseif($arRule["description"] == "DETAIL_TEXT")
            {
                if(strlen($arItem["DETAIL_TEXT"]))
                    $strReturn = CMibixExportTools::googleText2xml($arItem["~DETAIL_TEXT"], true, false, true, 1000);
            }
            else
            {
                if($strTag = self::get_property_value($arRule["description"], $arItem, true, 1000, $arOfferItemSKU))
                    if(strlen($strTag))
                        $strReturn = $strTag;
            }

            // исправление описания
            if (strlen($strReturn) && $arRule["description_frm"]=="Y")
            {
                $strReturn = CMibixExportTools::sentenceCap($strReturn);
            }

            // оборачиваем в тег
            if(strlen($strReturn))
            {
                $strReturn = "<description>".$strReturn."</description>";
            }
        }

        return $strReturn;
    }

    /**
     * Получаем тег ID
     *
     * @param $arItem array Массив со значениями обрабатываемого элемента
     * @return string сформированный тег или пустая строка
     */
    private function get_yml_offer_id($arItem, $arOfferItemSKU=Array())
    {
        // Id-товара
        $offerID = $arItem['ID'];
        if(!empty($arOfferItemSKU))
            $offerID = $arOfferItemSKU['ID'];

        // оборачиваем в тег
        $strReturn = "<g:id>".$offerID."</g:id>";

        return $strReturn;
    }

    /**
     * Получаем yml картинок товара
     *
     * @param $arRule array Массив со значениями правила
     * @param $arItem array Массив со значениями обрабатываемого элемента
     * @param $isAdditional bool Флаг для определения основной или дополнительных картинок товара
     * @return string сформированный тег или пустая строка
     */
    private function get_yml_offer_pictures($arRule, $arItem, $arOfferItemSKU=Array(), $isAdditional=false)
    {
        $arReturn = array();

        // Инициилизируем значение из базы
        $arPictureSettings = array();
        if (!$isAdditional && !empty($arRule["picture"]))
            $arPictureSettings = array($arRule["picture"]);
        elseif ($isAdditional && !empty($arRule["picture_additional"]))
            $arPictureSettings = explode(",", $arRule["picture_additional"]);

        if(count($arPictureSettings)>0)
        {
            foreach($arPictureSettings as $picSetting)
            {
                if($picSetting == "PREVIEW_PICTURE")
                {
                    if (intval($arItem["PREVIEW_PICTURE"])>0)
                    {
                        $pictNo = intval($arItem["PREVIEW_PICTURE"]);
                        $arReturn[] = self::get_yml_picture_by_code($pictNo, $arRule["url_shop"], true, $isAdditional);
                    }
                }
                elseif($picSetting == "DETAIL_PICTURE")
                {
                    if (intval($arItem["DETAIL_PICTURE"])>0)
                    {
                        $pictNo = intval($arItem["DETAIL_PICTURE"]);
                        $arReturn[] = self::get_yml_picture_by_code($pictNo, $arRule["url_shop"], true, $isAdditional);
                    }
                }
                elseif($picSetting == "sku@PREVIEW_PICTURE")
                {
                    if (intval($arOfferItemSKU["PREVIEW_PICTURE"])>0)
                    {
                        $pictNo = intval($arOfferItemSKU["PREVIEW_PICTURE"]);
                        $arReturn[] = self::get_yml_picture_by_code($pictNo, $arRule["url_shop"], true, $isAdditional);
                    }
                }
                elseif($picSetting == "sku@DETAIL_PICTURE")
                {
                    if (intval($arOfferItemSKU["DETAIL_PICTURE"])>0)
                    {
                        $pictNo = intval($arOfferItemSKU["DETAIL_PICTURE"]);
                        $arReturn[] = self::get_yml_picture_by_code($pictNo, $arRule["url_shop"], true, $isAdditional);
                    }
                }
                else
                {
                    $arProperty = self::get_property_array($picSetting, $arItem, $arOfferItemSKU); // возвращает свойство товара или sku
                    if(!empty($arProperty))
                    {
                        if($arProperty["PROPERTY_TYPE"]=="F") {

                            if(is_array($arProperty["VALUE"]) && count($arProperty["VALUE"])>0)
                            {
                                foreach($arProperty["VALUE"] as $pictNo)
                                {
                                    $arReturn[] = self::get_yml_picture_by_code($pictNo, $arRule["url_shop"], true, $isAdditional);
                                }
                            }
                            elseif(intval($arProperty["VALUE"])>0)
                            {
                                $pictNo = intval($arProperty["VALUE"]);
                                $arReturn[] = self::get_yml_picture_by_code($pictNo, $arRule["url_shop"], true, $isAdditional);
                            }
                        } elseif($arProperty["PROPERTY_TYPE"]=="S" && strlen($arProperty["VALUE"])) {

                            if ($isAdditional)
                                $arReturn[] = "<g:additional_image_link>".CMibixExportTools::getFixURL($arRule["url_shop"], $arProperty["VALUE"])."</g:additional_image_link>";
                            else
                                $arReturn[] = "<g:image_link>".CMibixExportTools::getFixURL($arRule["url_shop"], $arProperty["VALUE"])."</g:image_link>";
                        }
                    }
                }
            }
        }

        // Если количество элементов более 10, то обрезаем до 10 (ограничение Google Merchant)
        if (count($arReturn) > 10)
            $arReturn = array_slice($arReturn, 0, 10);

        return $arReturn;
    }

    /**
     * Получаем группу для офферов
     *
     * @param $item_group string Флаг группировки
     * @param $arItem array Массив со значениями обрабатываемого элемента
     * @return string сформированный тег или пустая строка
     */
    private function get_yml_offer_group_id($item_group, $arItem, $arOfferItemSKU=Array())
    {
        $strReturn = '';

        if($item_group=="Y" && !empty($arOfferItemSKU))
            $strReturn = "<g:item_group_id>".$arItem['ID']."</g:item_group_id>";

        return $strReturn;
    }

    /**
     * Получаем значение свойства, формируем его в виде YML-тега и возращаем его
     *
     * @param $PROPERTY
     * @param $arItem
     * @return string
     */
    private function get_yml_shipping($PROPERTY, $arItem, $arOfferItemSKU=Array())
    {
        $strProperty = '';

        // получаем значение
        $value = self::get_property_value($PROPERTY, $arItem, false, 0, $arOfferItemSKU);
        $arValue = explode("::", $value);
        if (count($arValue)>0)
        {
            if (count($arValue)==1 && !empty($arValue[0])) {
                $strProperty .= '<g:price>'.$arValue[0].'</g:price>';
            } elseif (count($arValue)==2 && !empty($arValue[0]) && !empty($arValue[1])) {
                $strProperty .= '<g:country>'.$arValue[0].'</g:country>';
                $strProperty .= '<g:price>'.$arValue[1].'</g:price>';
            } elseif (count($arValue)==3 && !empty($arValue[0]) && !empty($arValue[1]) && !empty($arValue[2])) {
                $strProperty .= '<g:country>'.$arValue[0].'</g:country>';
                $strProperty .= '<g:service>'.$arValue[1].'</g:service>';
                $strProperty .= '<g:price>'.$arValue[2].'</g:price>';
            }
        }

        if (!empty($strProperty))
            $strProperty = '<g:shipping>'.$strProperty.'</g:shipping>';

        return $strProperty;
    }

    /**
     * Получаем значение свойства, формируем его в виде YML-тега и возращаем его
     *
     * @param $PARAM
     * @param $PROPERTY
     * @param $arItem
     * @return string
     */
    private function get_property_value_tag($PARAM, $PROPERTY, $arItem, $arOfferItemSKU=Array())
    {
        $strProperty = '';

        // получаем значение
        $value = self::get_property_value($PROPERTY, $arItem, false, 0, $arOfferItemSKU);
        if(strlen($value))
        {
            // если нужно вернуть имя каталога для typePrefix
            //if($PARAM=="typePrefix" && $value=="catname")
            //{
            //    $resSection = CIBlockSection::GetByID($arItem["IBLOCK_SECTION_ID"]);
            //    if($arResSection = $resSection->GetNext())
            //        $value = CMibixExportTools::googleText2xml($arResSection['NAME'], true);
            //    else
            //        $value = "";
            //}

            // возвращаем обработанные значения
            $param_h = CMibixExportTools::googleText2xml($PARAM, true);
            $strProperty = '<'.$param_h.'>'.$value.'</'.$param_h.'>';
        }

        return $strProperty;
    }

    /**
     * Получаем значение свойства в зависимости от его типа и возвращаем его
     *
     * @param $PROPERTY
     * @param $arItem
     * @param bool $bSR
     * @param int $iTryncate
     * @return string
     */
    private function get_property_value($PROPERTY, $arItem, $bSR=false, $iTryncate=0, $arOfferItemSKU=Array())
    {
        // Если свойство содержит собственное значение "self@", то возвращаем его
        if (preg_match("/^self@(.*?)/isU", $PROPERTY, $matches))
        {
            if(!empty($matches) && isset($matches[1]))
            {
                return CMibixExportTools::googleText2xml($matches[1], true);
            }
        }

        // Если свойство содержит установленное для всех свойств значение "val@", то возвращаем его
        if (preg_match("/^val@(.*?)/isU", $PROPERTY, $matches))
        {
            if(!empty($matches) && isset($matches[1]))
            {
                $matches[1] = str_replace("_", " ", $matches[1]); // подчеркивание меняем на пробелы
                return CMibixExportTools::googleText2xml($matches[1], true);
            }
        }

        // Проверяем свойство, к какому инфоблоку оно отноится (обычному или sku)
        $arProperty = self::get_property_array($PROPERTY, $arItem, $arOfferItemSKU);

        // Получаем значение свойства в зависимости от типа
        $value = '';
        if (isset($arProperty) && !empty($arProperty))
        {
            // Проверка на пользователький тип HTML
            $userTypeFormat = "";
            if (strlen($arProperty["USER_TYPE"]))
            {
                $arUserType = CIBlockProperty::GetUserType($arProperty["USER_TYPE"]);
                if (array_key_exists("GetPublicViewHTML", $arUserType))
                {
                    $userTypeFormat = $arUserType["GetPublicViewHTML"];
                    $arProperty['PROPERTY_TYPE'] = 'USER_TYPE';
                }
            }

            // Обрабатываем свойство в зависимости от его типа
            switch ($arProperty['PROPERTY_TYPE'])
            {
                // Пользовательский тип
                case 'USER_TYPE':
                    if (!empty($arProperty['VALUE']))
                    {
                        if (is_array($arProperty['VALUE']))
                        {
                            $arValues = array();
                            foreach($arProperty["VALUE"] as $oneValue)
                            {
                                $arValues[] = call_user_func_array($userTypeFormat,
                                    array(
                                        $arProperty,
                                        array("VALUE" => $oneValue),
                                        array('MODE' => 'SIMPLE_TEXT'),
                                    ));
                            }
                            $value = implode(', ', $arValues);
                        }
                        else
                        {
                            $value = call_user_func_array($userTypeFormat,
                                array(
                                    $arProperty,
                                    array("VALUE" => $arProperty["VALUE"]),
                                    array('MODE' => 'SIMPLE_TEXT'),
                                ));
                        }
                    }
                    break;
                case 'E':
                    if (!empty($arProperty['VALUE']))
                    {
                        $arCheckValue = array();
                        if (!is_array($arProperty['VALUE']))
                        {
                            $arProperty['VALUE'] = intval($arProperty['VALUE']);
                            if (0 < $arProperty['VALUE'])
                                $arCheckValue[] = $arProperty['VALUE'];
                        }
                        else
                        {
                            foreach ($arProperty['VALUE'] as &$intValue)
                            {
                                $intValue = intval($intValue);
                                if (0 < $intValue)
                                    $arCheckValue[] = $intValue;
                            }
                            if (isset($intValue))
                                unset($intValue);
                        }
                        if (!empty($arCheckValue))
                        {
                            $dbRes = CIBlockElement::GetList(array(), array('IBLOCK_ID' => $arProperty['LINK_IBLOCK_ID'], 'ID' => $arCheckValue), false, false, array('NAME'));
                            while ($arRes = $dbRes->Fetch())
                            {
                                $value .= ($value ? ', ' : '').$arRes['NAME'];
                            }
                        }
                    }
                    break;
                case 'G':
                    if (!empty($arProperty['VALUE']))
                    {
                        $arCheckValue = array();
                        if (!is_array($arProperty['VALUE']))
                        {
                            $arProperty['VALUE'] = intval($arProperty['VALUE']);
                            if (0 < $arProperty['VALUE'])
                                $arCheckValue[] = $arProperty['VALUE'];
                        }
                        else
                        {
                            foreach ($arProperty['VALUE'] as &$intValue)
                            {
                                $intValue = intval($intValue);
                                if (0 < $intValue)
                                    $arCheckValue[] = $intValue;
                            }
                            if (isset($intValue))
                                unset($intValue);
                        }
                        if (!empty($arCheckValue))
                        {
                            $dbRes = CIBlockSection::GetList(array(), array('IBLOCK_ID' => $arProperty['LINK_IBLOCK_ID'], 'ID' => $arCheckValue), false, array('NAME'));
                            while ($arRes = $dbRes->Fetch())
                            {
                                $value .= ($value ? ', ' : '').$arRes['NAME'];
                            }
                        }
                    }
                    break;
                case 'L':
                    if (!empty($arProperty['VALUE']))
                    {
                        if (is_array($arProperty['VALUE']))
                            $value .= implode(', ', $arProperty['VALUE']);
                        else
                            $value .= $arProperty['VALUE'];
                    }
                    break;
                case 'F';
                    if (!empty($arProperty['VALUE']))
                    {
                        $value = self::get_yml_picture_by_code(IntVal($arProperty['VALUE']), self::$urlShop, false);
                    }
                    break;
                default:
                    $value = is_array($arProperty['VALUE']) ? implode(', ', $arProperty['VALUE']) : $arProperty['VALUE'];
            }
        }

        if(strlen($value))
            return CMibixExportTools::googleText2xml($value, true, false, $bSR, $iTryncate);

        return '';
    }

    /**
     * Получаем массив свойств товара или торгового предложения
     * в зависимости от установленного значения
     *
     * @param $PROPERTY
     * @param $arItem
     * @param $arOfferItemSKU
     * @return array
     */
    private function get_property_array($PROPERTY, $arItem, $arOfferItemSKU)
    {
        $arProperty = Array();
        if (preg_match("/^offer@(.*?)/isU", $PROPERTY, $matches))
        {
            if(!empty($matches) && isset($matches[1]) && !empty($arOfferItemSKU))
            {
                $arProperty = $arOfferItemSKU["PROPERTIES"][$matches[1]];
            }
        }
        if(empty($arProperty)) // Если не SKU, то получаем значение как обычного свойства
        {
            $arProperty = $arItem["PROPERTIES"][$PROPERTY];
        }

        return $arProperty;
    }

    /**
     * Обновляет (запоминает) информацию об обработанном ID элемента (товара) без проверки на метод запуска
     * (метод без проверки требуется для чистки таблицы при запуске напрямую)
     *
     * @param $ruleID
     * @param $IBlockID
     * @param $elementID
     * @param int $skuIBlockID
     * @param int $skuElementID
     */
    private function steps_update_noncheck($ruleID, $IBlockID, $elementID, $skuIBlockID=0, $skuElementID=0, $shop=1)
    {
        global $DB;

        $arSaveField = array(
            "rule_id" => $ruleID,
            "iblock_id" => $IBlockID,
            "element_id" => $elementID,
            "sku_iblock_id" => $skuIBlockID,
            "sku_element_id" => $skuElementID,
        );
        $dbResYMC = $DB->Query("SELECT id FROM b_mibix_gl_steps_load WHERE id='".$shop."'");
        if($dbArYMC = $dbResYMC->Fetch()) // update
        {
            $strStepsUpdateSQL = $DB->PrepareUpdate("b_mibix_gl_steps_load", $arSaveField);
            if (strlen($strStepsUpdateSQL)>0)
            {
                $strSql = "UPDATE b_mibix_gl_steps_load SET ".$strStepsUpdateSQL." WHERE id='".$dbArYMC["id"]."'";
                //echo $strSql;
                $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
            }
        }
        else // insert
        {
            $DB->Add("b_mibix_gl_steps_load", $arSaveField);
        }
    }

    /**
     * Получаем настройки пошаговой выгрузки из профиля магазина
     *
     * @param int $shop - ID профиля магазина
     * @return array - массив лимитов из настроек профиля магазина
     */
    public function get_profile_step_settings($shop=1)
    {
        global $DB;

        $arStep = array();

        $dbStepsLoadRes = $DB->Query("SELECT step_limit, step_path, step_interval_run FROM b_mibix_gl_profile WHERE id='".$shop."'");
        if ($arStepsLoadRes = $dbStepsLoadRes->Fetch())
        {
            $arStep["step_limit"] = $arStepsLoadRes["step_limit"];
            $arStep["step_path"] = $arStepsLoadRes["step_path"];
            $arStep["step_interval_run"] = $arStepsLoadRes["step_interval_run"];
        }

        return $arStep;
    }

    /**
     * Получаем текущие данные пошаговой выгрузки
     *
     * @param int $shop - ID профиля магазина
     * @return array - массив значений шага
     */
    public function get_saved_steps($shop=1)
    {
        global $DB;
        $arSaveSteps = array(
            "id" => 0,
            "in_proccess" => 'N',
            "in_blocked"  => 'N',
            "last_run_time" => 0,
            "last_step_time" => 0,
            "rule_id" => 0,
            "iblock_id" => 0,
            "element_id" => 0,
            "sku_iblock_id" => 0,
            "sku_element_id" => 0
        );

        $dbStepsLoadRes = $DB->Query("SELECT * FROM b_mibix_gl_steps_load WHERE id='".$shop."'");
        if ($arStepsLoadRes = $dbStepsLoadRes->Fetch()) {
            $arSaveSteps = $arStepsLoadRes;
        }

        return $arSaveSteps;
    }

    /**
     * Устанавливаем текущий статус блокировки при пошаговой выгрузке
     *
     * @param string $field - (blocked) статус блокировки; (proccess) статус поэтапной выгрузки
     * @param string $value - устанавливаемое значение
     * @param int $shop - ID профиля магазина
     */
    private function set_status($field, $value, $shop=1)
    {
        global $DB;

        if ($field=="blocked" || $field=="proccess") {
            $strSql = "UPDATE b_mibix_gl_steps_load SET in_".$field."='" . $value . "' WHERE id='" . $shop . "'";
            $DB->Query($strSql, false, "File: " . __FILE__ . "<br>Line: " . __LINE__);
        }
    }

    /**
     * Обновление метки времени исполнения
     *
     * @param string $field - (step) время выполнения последнего шага; (run) время последнего запуска
     * @param int $shop - ID профиля магазина
     */
    private function set_last_time($field, $shop=1)
    {
        global $DB;

        if ($field=="run" || $field=="step") {
            $strSql = "UPDATE b_mibix_gl_steps_load SET last_".$field."_time=" . $DB->GetNowFunction() . " WHERE id='" . $shop . "'";
            $DB->Query($strSql, false, "File: " . __FILE__ . "<br>Line: " . __LINE__);
        }
    }

    /**
     * Проверка и обновление подвисших процессов (если работа скрипта прервалась по каким-то причинам)
     * (600 -> 10 минут, - время больше которого скрипт считается зависшим)
     *
     * @param int $shop
     */
    private function check_freeze_process($shop=1)
    {
        global $DB;

        $dbStepsLoadRes = $DB->Query("SELECT last_step_time FROM b_mibix_gl_steps_load WHERE in_proccess='Y' AND in_blocked='Y' AND id='".$shop."'");
        if ($arStepsLoadRes = $dbStepsLoadRes->Fetch())
        {
            $strTimeDiff = time() - strtotime($arStepsLoadRes["last_step_time"]);
            if($strTimeDiff > 600)
            {
                self::set_status("proccess", "N", $shop);
                self::set_status("blocked", "N", $shop);
            }
        }
    }

    /**
     * Пользовательская фильтрация установленных ограничений выгрузки
     *
     * @param $arRule array Массив со значениями правила
     * @param $arItem array Массив со значениями обрабатываемого элемента
     * @param $arOfferItemSKU array Массив со значениями SKU элементов
     * @return string сформированный тег или пустая строка
     */
    private function check_filter($arRule, $arItem, $arOfferItemSKU=array())
    {
        if(strlen($arRule["filters"])>0)
        {
            // из строки формируем массив параметров
            $arFilters = explode("|", $arRule["filters"]);
            if(count($arFilters)>0)
            {
                // проходимся по каждому правилу
                foreach($arFilters as $str_filter)
                {
                    // формируем отдельный массива для элементов каждого параметра
                    $arParamFilters = explode(",", $str_filter);
                    if(count($arParamFilters)==3)
                    {
                        // если нет какого либо параметра, то не обрабатываем
                        if(!isset($arParamFilters[0]) || !strlen($arParamFilters[0])) continue;
                        if(!isset($arParamFilters[1]) || !strlen($arParamFilters[1])) continue;
                        if(!isset($arParamFilters[2]) || !strlen($arParamFilters[2])) continue;

                        // Фильтр по шаблонам свойств инфоблока и SKU
                        if (preg_match("/^offer@(.*?)/isU", $arParamFilters[0], $matches))
                        {
                            if(!empty($matches) && isset($matches[1]) && !empty($arOfferItemSKU))
                            {
                                $arProperty = $arOfferItemSKU["PROPERTIES"][$matches[1]];
                            }
                        }
                        elseif (preg_match("/^prop@(.*?)/isU", $arParamFilters[0], $matches))
                        {
                            if(!empty($matches) && isset($matches[1]))
                            {
                                $arProperty = $arItem["PROPERTIES"][$matches[1]];
                            }
                        }
                        // Если найдено свойство и его значение, применяем фильтр
                        if (isset($arProperty["PROPERTY_TYPE"]) && count($arProperty["PROPERTY_TYPE"])>0)
                        {
                            // Обработчики типов: S - строка; N - число; L - список;
                            if ($arProperty["PROPERTY_TYPE"]=="S" || $arProperty["PROPERTY_TYPE"]=="N" || ($arProperty["PROPERTY_TYPE"]=="L" && !is_array($arProperty["VALUE"])))
                            {
                                if ($arParamFilters[1] == "equal") { // равно
                                    if (!(trim($arProperty["VALUE"]) == trim($arParamFilters[2]))) return false;
                                } elseif ($arParamFilters[1] == "notequal") { // не равно
                                    if (!(trim($arProperty["VALUE"]) != trim($arParamFilters[2]))) return false;
                                } elseif ($arParamFilters[1] == "more") { // больше
                                    if (!(intval($arProperty["VALUE"]) > intval($arParamFilters[2]))) return false;
                                } elseif ($arParamFilters[1] == "less") { // меньше
                                    if (!(intval($arProperty["VALUE"]) < intval($arParamFilters[2]))) return false;
                                }
                            }
                            elseif ($arProperty["PROPERTY_TYPE"]=="L" && is_array($arProperty["VALUE"])) // фильтр по значениям списка (совпадает или не совпадает)
                            {
                                if ($arParamFilters[1] == "equal") { // равно
                                    if (!in_array(trim($arParamFilters[2]), $arProperty["VALUE"])) return false;
                                } elseif ($arParamFilters[1] == "notequal") { // не равно
                                    if (in_array(trim($arParamFilters[2]), $arProperty["VALUE"])) return false;
                                }
                            }
                        }

                        // Обработка каждого установленного фильтра
                        switch($arParamFilters[0])
                        {
                            // ЦЕНА (фильтр)
                            case "filter_price":
                                // получаем цену товара
                                $arPrice = self::get_yml_offer_price($arRule, $arItem, $arOfferItemSKU);
                                // фильтрация установленных пользователем значений
                                switch($arParamFilters[1])
                                {
                                    case "equal"; // равен
                                        if(!(intval($arPrice["minprice"])==intval($arParamFilters[2]))) return false;
                                        break;
                                    case "notequal"; // не равен
                                        if(!(intval($arPrice["minprice"])!=intval($arParamFilters[2]))) return false;
                                        break;
                                    case "more";
                                        if(!(intval($arPrice["minprice"])>intval($arParamFilters[2]))) return false;
                                        break;
                                    case "less";
                                        if(!(intval($arPrice["minprice"])<intval($arParamFilters[2]))) return false;
                                        break;
                                    //case "empty";
                                    //case "notempty";
                                }
                                break;

                            // КОЛИЧЕСТВО НА СКЛАДЕ (фильтр)
                            case "filter_quantity":
                                // используем только для редакций малый бизнес и бизнес
                                if(CModule::IncludeModule("sale") && CModule::IncludeModule("catalog"))
                                {
                                    $tmpItemID = $arItem['ID'];
                                    if(!empty($arOfferItemSKU))
                                        $tmpItemID = $arOfferItemSKU['ID'];

                                    // получаем параметры товара
                                    $arResProduct = CCatalogProduct::GetByID($tmpItemID);

                                    // фильтрация установленных пользователем значений
                                    switch($arParamFilters[1])
                                    {
                                        case "equal"; // равен
                                            if(!(intval($arResProduct["QUANTITY"])==intval($arParamFilters[2]))) return false;
                                            break;
                                        case "notequal"; // не равен
                                            if(!(intval($arResProduct["QUANTITY"])!=intval($arParamFilters[2]))) return false;
                                            break;
                                        case "more";
                                            if(!(intval($arResProduct["QUANTITY"])>intval($arParamFilters[2]))) return false;
                                            break;
                                        case "less";
                                            if(!(intval($arResProduct["QUANTITY"])<intval($arParamFilters[2]))) return false;
                                            break;
                                        //case "empty";
                                        //case "notempty";
                                    }
                                }
                                break;
                        }
                    }
                }
            }
        }

        return true;
    }

}

/**
 * Класс с функциями формирования конторолов
 */
class CMibixExportControls
{
    /**
     * @var string - код модуля
     */
    private $module_id = '';

    /**
     * @var string - имя формы
     */
    private $form_name = '';

    /**
     * @var array - массив табов формы
     */
    private $arTabs = array();

    /**
     * @var array - массив групп полей формы
     */
    private $arGroups = array();

    /**
     * @var array - массив полей формы
     */
    private $arFields = array();

    /**
     * Конструктор, инициализирует необходимые данные для вывода формы
     *
     * @param $module_id - код модуля для авто формирования строковых значений формы
     * @param $arTabs - массив табов формы
     * @param $arGroups - массив групп полей формы
     * @param $arFields - массив полей формы
     */
    public function CMibixExportControls($module_id, $form_name, $arTabs, $arGroups, $arFields)
    {
        $this->module_id = strtolower($module_id);
        $this->form_name = strtolower($form_name);
        $this->arTabs = $arTabs;
        $this->arGroups = $arGroups;
        $this->arFields = $arFields;
    }

    /**
     * Формирует форму редактирования для административного интерфейса
     *
     * @param $ID - номер записи
     * @param $POST_RIGHT - права доступа
     * @param $message - сообщение об ошибке
     * @param $backUrl - страница возврата
     * @return string - сформированный HTML-код формы
     */
    public function ShowForm($ID=0, $POST_RIGHT="D", $message="", $backUrl="")
    {
        global $APPLICATION;

        $arParams = array();
        foreach($this->arGroups as $grId => $grParams)
            $arParams[$grParams['TAB']][$grId] = array();

        // Формирование полей из массива
        foreach ($this->arFields as $fname => $field)
        {
            // Код вывода сообщения
            $msg_code = strtoupper($this->module_id).'_'.strtoupper($this->form_name).'_'.strtoupper($fname);

            // Не обязательное поле (по умолчанию)
            if (!isset($field["required"])) $field["required"] = false;

            // Сортировка (по умолчанию)
            if($field['sort'] < 0 || !isset($field['sort'])) $field['sort'] = 0;

            // Размеры поля (по умолчанию)
            if (!isset($field["size"])) $field["size"] = 50;
            if (!isset($field["maxlength"])) $field["maxlength"] = 255;

            // Тип поля выборки и группировка (по умолчанию)
            if (!isset($field["ptype"]))  $field["ptype"] = false;
            if (!isset($field["use_group"])) $field["use_group"] = true;

            // Не выводим поле, если есть условие его вывода и оно не выполяется
            if (isset($field["condition"]) && $field["condition"] == false)
                continue;

            // Формируем поле в зависимости от типа
            $input = self::GetFormControl($fname, $field);

            // Не выводим поле, если оно пустое
            if (empty($input))
                continue;

            // Если есть примечание к полю, получаем
            $msg_code_note = GetMessage($msg_code.'_NOTE');

            // Массив полей исключений, к котором нужно применить стиль "adm-detail-valign-top"
            $arExcluding = array('include_sections','exclude_sections','include_items', 'exclude_items');

            // === Формирование HTML-блока поля ===
            $strHTML = "";
            $strHTML .= '<tr><td width="40%" class="'.((!empty($msg_code_note) || in_array($fname, $arExcluding))?'adm-detail-valign-top ':'').'adm-detail-content-cell-l">';

            // Обязательно (жирным со звездочкой)/Необязательное поле
            if ($field["required"])
                $strHTML .= '<span class="required">*</span><span class="adm-required-field">' . GetMessage($msg_code) . '</span>';
            else
                $strHTML .= GetMessage($msg_code);

            $strHTML .= ':</td><td width="60%" class="adm-detail-content-cell-r">'.$input;

            // Если определен комментарий, то выводим его
            if (!empty($msg_code_note))
                $strHTML .= '<div class="adm-info-message-wrap"><div class="adm-info-message">'.$msg_code_note.'</div></div>';

            $strHTML .= '</td></tr>';
            // === /Формирование HTML-блока поля ===

            $arParams[$this->arGroups[$field["group"]]['TAB']][$field["group"]]['FIELDS'][] = $strHTML;
            $arParams[$this->arGroups[$field["group"]]['TAB']][$field["group"]]['FIELDS_SORT'][] = $field['sort'];
        }

        // сформируем список закладок
        $tabControl = new CAdminTabControl('tabControl', $this->arTabs);
        $tabControl->Begin();
        echo '<form name="'.$this->module_id.'" method="POST" action="'.$APPLICATION->GetCurPage().'?mid='.$this->module_id.'&lang='.LANGUAGE_ID.'" enctype="multipart/form-data">'.bitrix_sessid_post();

        // вывод групп и привязанных полей
        foreach($arParams as $tab => $groups)
        {
            $tabControl->BeginNextTab();

            foreach($groups as $groupId => $group)
            {
                if(sizeof($group['FIELDS_SORT']) > 0)
                {
                    echo '<tr class="heading"><td colspan="2">'.$this->arGroups[$groupId]['TITLE'].'</td></tr>';

                    array_multisort($group['FIELDS_SORT'], $group['FIELDS']);
                    foreach($group['FIELDS'] as $opt)
                        echo $opt;
                }
            }
        }

        // Кнопки
        $tabControl->Buttons(array("disabled"=>($POST_RIGHT<"W"), "back_url"=>$backUrl.".php?lang=".LANG));
        echo '<input type="hidden" name="lang" value="'.LANG.'">';

        if($ID > 0) echo '<input type="hidden" name="ID" value="'.$ID.'">';

        echo '</form>';
        bitrix_sessid_post();
        $tabControl->End();

        $tabControl->ShowWarnings($this->form_name, $message);
    }

    /**
     * Возвращает необходимый контрол формы
     *
     * @param $fname - наименование поля
     * @param $field - массив с параметрами запрашиваемого поля
     * @return string - контрол в HTML
     */
    public function GetFormControl($fname, $field)
    {
        $input = "";

        switch ($field["type"])
        {
            case "label":
                $input = $field["value"]["selected"];
                break;
            case "checkbox":
                $arDefaultChecked = array("active", "include_sku");
                $flagChB = false; // По умолчанию выключен
                if (in_array($fname, $arDefaultChecked) && empty($field["value"]["selected"])) $flagChB = true;
                $input = '<input type="checkbox" name="f_'.$fname.'" value="Y"' . (($field["value"]["selected"]=="Y" || $flagChB)?' checked':'').'>';
                break;
            case "text":
                $input = '<input type="text" size="'.$field["size"].'" maxlength="'.$field["maxlength"].'" value="'.$field["value"]["selected"].'" name="f_'.$fname.'" />';
                break;
            case "textarea":
                $input = '<textarea rows="'.$field["rows"].'" cols="'.$field["cols"].'" name="f_'.$fname.'">'.$field["value"]["selected"].'</textarea>';
                break;
            case "select_params":
                $input = CMibixExportControls::getSelectBoxParams($fname, $field["value"]["selected"], $field["value"]["iblock_id"], $field["ptype"], $field["use_group"]);
                break;
            case "select_sections":
                $input = CMibixExportControls::getSelectBoxSections($fname, $field["value"]["selected"], $field["value"]["iblock_id"]);
                break;
            case "select_elements":
                ob_start();
                $property_fields = array("PROPERTY_TYPE"=>"E", "MULTIPLE"=>"Y", "MULTIPLE_CNT"=>1);
                _ShowPropertyField("f_".$fname, $property_fields, explode(",",$field["value"]["selected"]), false, $field["value"]["bvff"]);
                $input = ob_get_clean();
                break;
            case "site":
                $input = CMibixExportControls::getSelectBoxSiteId($fname, $field["value"]["selected"]);
                break;
            case "iblock_type":
                $input = CMibixExportControls::getSelectBoxIBlockType($fname, $field["value"]["selected"]);
                break;
            case "iblock":
                $input = CMibixExportControls::getSelectBoxIBlockId($fname, $field["value"]["selected"], $field["value"]["site_id"], $field["value"]["iblock_type"]);
                break;
            case "datasource":
                $input = CMibixExportControls::getSelectBoxTableData($fname, $field["value"]["selected"], CMibixExportEntityModel::getTableName(), "id", "name_data", true);
                break;
            case "shop":
                $input = CMibixExportControls::getSelectBoxTableData($fname, $field["value"]["selected"], CMibixExportTemplateModel::getTableName(), "id", "name");
                break;
            case "market_category":
                //$input = CMibixModelRules::getSelectBoxYMCategories($field["value"]["selected"]);
                break;
            case "price":
                $input = CMibixExportControls::getSelectBoxPriceType($fname, $field["value"]["selected"], $field["value"]["iblock_id"], $field["value"]["inc_none"]);
                break;
            case "picture_additional":
                $input = CMibixExportControls::getMultiSelectBoxPictures($fname, $field["value"]["selected"], $field["value"]["iblock_id"]);
                break;
            case "filter":
                $input = CMibixExportControls::getControlFilter($field["value"]["iblock_id"], $field["value"]["filter_name"], $field["value"]["filter_unit"], $field["value"]["filter_value"]);
                break;
        }

        return $input;
    }

    /**
     * SelectBox со указанными данными таблицы
     *
     * @param $fname - название поля
     * @param $selected - выделенный элемент
     * @param $table - таблица базы данных
     * @param $selKey - поле таблицы в качестве ключа <option>
     * @param $selVal - поле таблицы в качестве значения <option>
     * @return string - сформированный список элементов
     */
    public function getSelectBoxTableData($fname, $selected, $table, $selKey, $selVal, $iEmpty=false)
    {
        $select = $selKey . ", " . $selVal;
        $dbRes = CMibixExportBaseModel::BaseGetAll($select, $table);
        $emptyValue = '<option value="">('.GetMessage("MIBIX_EXPORT_INCLUDE_DS_SEL").')</option>';
        return '<select name="f_'.$fname.'" id="f_'.$fname.'" size="1">'.($iEmpty?$emptyValue:'').self::GetDataOptions($dbRes, $selected, array("id"=>$selKey,"value"=>$selVal)).'</select>';
    }

    /**
     * SelectBox со списком доступных сайтов
     *
     * @param $fname - название поля
     * @param $selected - выделенный элемент
     * @return string - сформированный список элементов
     */
    public function getSelectBoxSiteId($fname, $selected)
    {
        $dbRes = CSite::GetList(($by='sort'),($order='asc'));
        return '<select name="f_'.$fname.'" id="f_'.$fname.'" size="1"><option value="">('.GetMessage("MIBIX_EXPORT_INCLUDE_DS_ANY").')</option>'.self::GetDataOptions($dbRes, $selected, array("id"=>"LID","value"=>"NAME")).'</select>';
    }

    /**
     * SelectBox с типами инфоблоков
     *
     * @param $fname - название поля
     * @param $selected - элемент, который нужно выделить
     * @return string - сформированный список элементов
     */
    public function getSelectBoxIBlockType($fname, $selected)
    {
        $dbRes = CIBlockType::GetList();
        return '<select name="f_'.$fname.'" id="f_'.$fname.'" size="1"><option value="">('.GetMessage("MIBIX_EXPORT_INCLUDE_DS_IBLOCK").')</option>'.self::GetDataOptions($dbRes, $selected, array("id"=>"ID","value"=>"NAME"), "iblock_type").'</select>';
    }

    /**
     * SelectBox с инфоблоками выбранного типа и сайта
     *
     * @param $fname - название поля
     * @param $selected - выделенный элемент
     * @param $site_id - ID сайта
     * @param $iblock_type - тип информационного блока
     * @param $incSelect - вывести полный <select>
     * @return string - сформированный список элементов
     */
    public function getSelectBoxIBlockId($fname, $selected, $site_id, $iblock_type, $incSelect=true)
    {
        $strHTML = "";

        // На редактировании возвращаем все инфоблоки
        if($selected > 0 || $selected == -1)
        {
            $arParams = array();
            $arParams['TYPE'] = $iblock_type;
            if ($site_id != "") $arParams['SITE_ID'] = $site_id;

            $dbRes = CIBlock::GetList(array(), $arParams, false, false, array("ID","NAME"));
            $strHTML .= self::GetDataOptions($dbRes, $selected, array("id"=>"ID","value"=>"NAME"), "simple", false);
        }
        else // При добавлении - пустой список
            $strHTML .= '<option>('.GetMessage("MIBIX_EXPORT_INCLUDE_DS_TYPE").')</option>';

        if (!empty($strHTML) && $incSelect)
            $strHTML = '<select name="f_'.$fname.'" id="f_'.$fname.'" size="1">'.$strHTML.'</select>';

        return $strHTML;
    }

    /**
     * SelectBox с разделами запрашиваемого инфоблока
     *
     * @param $fname - название поля
     * @param $sections - выделенные пользователем разделы
     * @param $iblock_id - ID инфоблока
     * @param $incSelect - вывести полный <select>
     * @return string - сформированный список элементов
     */
    public function getSelectBoxSections($fname, $sections, $iblock_id, $incSelect=true)
    {
        $strHTML = $iblock_id;

        if($iblock_id > 0)
        {
            // Получаем разделы инфоблока по его ID
            $dbRes = CIBlockSection::GetList(array('LEFT_MARGIN'=>'ASC'), array('IBLOCK_ID'=>$iblock_id));
            while ($arRes = $dbRes->GetNext())
            {
                $selectField = "";
                if (strlen($sections)>0)
                    if (in_array($arRes['ID'], explode(",", $sections)))
                        $selectField = " selected";

                $strHTML .= '<option value="'.$arRes['ID'].'"'.$selectField.'>'.str_repeat("..", ($arRes['DEPTH_LEVEL']-1)).trim($arRes['NAME']).'</option>';
            }
        }

        if ($incSelect)
            $strHTML = '<select class="typeselect" multiple="" name="f_'.$fname.'[]" id="f_'.$fname.'" size="10">'.$strHTML.'</select>';

        return $strHTML;
    }

    /**
     * Контрол для вывода и добавления параметров
     *
     * @param $iblock_id - ID инфоблока
     * @param $filter_name - массив наименований фильтра
     * @param $filter_unit -  массив системы измерения значений фильтра
     * @param $filter_value - массив значений фильтра
     * @return string - сформированный HTML-код контрола фильтра
     */
    public function getControlFilter($iblock_id, $filter_name, $filter_unit, $filter_value)
    {
        $strHTML = '<div id="div_filter">';

        // Если есть заполненные поля
        if($iblock_id>0 && count($filter_name)>0 && count($filter_value)>0)
        {
            foreach($filter_name as $pKey=>$pName)
            {
                // Проверки для значений элементов
                if(!isset($filter_unit[$pKey])) continue;
                if(!isset($filter_value[$pKey])) continue;
                $pUnit = $filter_unit[$pKey];
                $pValue = $filter_value[$pKey];

                // Устанавливаем метку на первый элемент (на основе нее в js делаем копии при добавлении новых полей)
                if ($pKey == 0)
                    $strHTML .= '<div id="first_filter">';
                else
                    $strHTML .= '<div>';

                // остальные поля контрола
                $strHTML .= self::getSelectBoxFilterName($iblock_id, $pName);
                $strHTML .= self::getSelectBoxFilterUnit($pUnit);
                $strHTML .= '<input type="text" name="f_filter_value[]" size="12" placeholder="'.GetMessage("MIBIX_EXPORT_INCLUDE_DS_FILTER_VALUE").'" value="'.$pValue.'" />';
                //$strHTML .= '<select name="f_filter_value[]" id="f_filter" size="1">'.self::getSelectBoxProperty($pValue, $iblock_id, array(""=>GetMessage("MIBIX_EXPORT_INCLUDE_DS_PARAMVALUE")), "S", false).'</select>';
                $strHTML .= '</div>';
            }
        }
        else
        {
            $strHTML .= '<div id="first_filter">';
            $strHTML .= self::getSelectBoxFilterName($iblock_id);
            $strHTML .= self::getSelectBoxFilterUnit();
            $strHTML .= '<input type="text" name="f_filter_value[]" size="12" placeholder="'.GetMessage("MIBIX_EXPORT_INCLUDE_DS_FILTER_VALUE").'" value="" />';
            $strHTML .= '</div>';
        }

        $strHTML .= '</div>';
        $strHTML .= '<div><a href="javascript:void(0);" id="filter_add">'.GetMessage("MIBIX_EXPORT_INCLUDE_DS_FILTER_ADDNEW").'</a></div>';

        return $strHTML;
    }

    /**
     * Контрол для выбора свойств иноблока или задания собственных значений
     *
     * @param $fname - название поля
     * @param $selected - выделенный элемент
     * @param $iblock_id - ID инфоблока
     * @param bool $pType - указание конкретного типа выборки свойств (S - строка; N - число; L - список; F - файл; G - привязка к разделу; E - привязка к элементу)
     * @param bool $useGroup - использовать группировку свойств
     * @return string - сформированный HTML-код контрола
     */
    public function getSelectBoxParams($fname, $selected, $iblock_id, $pType=false, $useGroup=true)
    {
        // Проверяем на пользовательское значение, если оно установлено, то показываем его (определяется по префиксу "self@")
        $inputSelf = "";
        if (preg_match("/^self@(.*?)/isU", $selected, $matches))
        {
            if(!empty($matches) && isset($matches[1]))
            {
                $selected = "self";
                $inputSelf = '<input type="text" name="self_'.$fname.'" size="30" value="'.trim($matches[1]).'">';
            }
        }

        return '<select name="f_'.$fname.'" id="f_'.$fname.'" size="1">'.
                self::getSelectBoxProperty($fname, $selected, $iblock_id, $pType, $useGroup).
                '</select><div id="selfField_'.$fname.'">'.$inputSelf.'</div>';
    }

    /**
     * Тело которола <select> для указанного поля правил выгрузки (индивидуальные значения + свойства инфоблока и SKU)
     *
     * @param $fname - название поля
     * @param $selected - выделенный элемент
     * @param $iblock_id - ID инфоблока
     * @param $pType  - указание конкретного типа выборки свойств (S - строка; N - число; L - список; F - файл; G - привязка к разделу; E - привязка к элементу)
     * @param $useGroup - использовать группировку свойств
     * @return string - сформированный HTML-код контрола
     */
    private function getSelectBoxProperty($fname, $selected, $iblock_id=0, $pType=false, $useGroup=true)
    {
        $strHTML = '';
        $emptyOption = "";

        // тип поля
        $strTypeInfo = '';
        $arTypeInfo = Array("S"=>" (строка)", "N"=>" (число)", "L"=>" (список)", "F"=>" (файл)", "G"=>" (привязка к разделу)", "E"=>" (привязка к элементу)");
        if ($pType) if (array_key_exists($pType, $arTypeInfo)) $strTypeInfo = $arTypeInfo[$pType];

        $arParams = self::GetArrayParamsByCODE($fname, $pType);

        if (isset($arParams["none"]) && $arParams["none"] == "")
            $arParams["none"] = GetMessage("MIBIX_EXPORT_INCLUDE_SELECT_VALUE");

        if (array_key_exists("none", $arParams))
        {
            $emptyOption = '<option value="">'.$arParams["none"].'</option>'; // Запоминаем значение (текст) элемента
            unset($arParams["none"]); // Убираем его основного массива
        }

        // Опции из доп. параметров
        foreach($arParams as $kParam=>$vParam)
        {
            // Определяем выделение для одиночного (строка) и множественных (массив) значений
            $selectField = "";
            if ((is_array($selected) && in_array($kParam, $selected)) || $kParam == $selected) {
                $selectField = " selected";
            }

            $strHTML .= '<option value="'.$kParam.'"'.$selectField.'>'.$vParam.'</option>';
        }

        // Если есть параметры в массиве, оборачиваем их в группу
        if(count($arParams)>0 && $useGroup)
            $strHTML = '<optgroup label="'.GetMessage("MIBIX_EXPORT_INCLUDE_OPTGROUP").':">'.$strHTML.'</optgroup>';

        // Вставляем пустой элемент перед первой группой
        $strHTML = $emptyOption.$strHTML;

        // Свойства выбранного инфоблока (вторая группа)
        if ($iblock_id > 0)
        {
            // доступные пользовательские типы свойст
            $arUserTypes = array("UserID","DateTime","EList","FileMan","map_yandex","HTML","map_google","ElementXmlID","Sequence","EAutocomplete","SKU","video","TopicID");

            $strIBlockHTML = ""; // свойства основного ифноблока
            $iblockFilter = Array("ACTIVE"=>"Y", "IBLOCK_ID"=>$iblock_id);
            if ($pType)
            {
                // если значение типа свойств состоит из одного символа
                if(strlen($pType)==1)
                    $iblockFilter["PROPERTY_TYPE"] = $pType;
                elseif(in_array($pType,$arUserTypes)) // пользовательские типы свойств
                    $iblockFilter["USER_TYPE"] = $pType;
            }

            $iblockProps = CIBlockProperty::GetList(Array("sort"=>"asc","name"=>"asc"), $iblockFilter);
            while ($arRes = $iblockProps->GetNext())
            {
                $selectField = "";
                if ((is_array($selected) && in_array($arRes["CODE"], $selected)) || $arRes["CODE"] == $selected) {
                    $selectField = " selected";
                }

                $strIBlockHTML .= '<option value="'.$arRes["CODE"].'"'.$selectField.'>['.$arRes["CODE"].'] '.$arRes["NAME"].'</option>';
            }
            if(strlen($strIBlockHTML)>0)
            {
                if($useGroup)
                    $strHTML .= '<optgroup label="'.GetMessage("MIBIX_EXPORT_INCLUDE_OPTGROUPPROP").(!empty($strTypeInfo)?" ".$strTypeInfo:"").':">'.$strIBlockHTML.'</optgroup>';
                else
                    $strHTML .= $strIBlockHTML;
            }

            // Cвойства инфоблока товарных предложений SKU (третья группа)
            $strIBlockOffersHTML = ""; // свойства ифноблока торговых предложений
            $arOffersSKU = NULL;
            if(CModule::IncludeModule("sale") && CModule::IncludeModule("catalog"))
                $arOffersSKU = CCatalogSKU::GetInfoByProductIBlock($iblock_id);

            if (!empty($arOffersSKU['IBLOCK_ID']))
            {
                $rsOfferIBlocks = CIBlock::GetByID($arOffersSKU['IBLOCK_ID']);
                if (($arOfferIBlock = $rsOfferIBlocks->Fetch()))
                {
                    $iblockOfferFilter = Array("ACTIVE"=>"Y", "IBLOCK_ID"=>$arOffersSKU['IBLOCK_ID']);

                    // Фильтрация по типу
                    if ($pType)
                    {
                        // если значение типа свойств состоит из одного символа
                        if(strlen($pType) == 1)
                            $iblockOfferFilter["PROPERTY_TYPE"] = $pType;
                        elseif(in_array($pType,$arUserTypes)) // пользовательские типы свойств
                            $iblockOfferFilter["USER_TYPE"] = $pType;
                    }

                    $iblockOfferProps = CIBlockProperty::GetList(Array("sort"=>"asc","name"=>"asc"), $iblockOfferFilter);
                    while ($arResOffers = $iblockOfferProps->GetNext())
                    {
                        if($arOffersSKU["SKU_PROPERTY_ID"] == $arResOffers["ID"]) continue; // пропускаем свойство если оно является привязкой к инфоблоку

                        $selectField = "";
                        if ((is_array($selected) && in_array('offer@'.$arResOffers["CODE"], $selected)) || ('offer@'.$arResOffers["CODE"] == $selected)) {
                            $selectField = " selected";
                        }

                        $strIBlockOffersHTML .= '<option value="offer@'.$arResOffers["CODE"].'"'.$selectField.'>['.$arResOffers["CODE"].']'.($pType=='F'?'[SKU] ':' ').$arResOffers["NAME"].'</option>';
                    }
                    if(strlen($strIBlockOffersHTML)>0)
                    {
                        if($useGroup)
                            $strHTML .= '<optgroup label="'.GetMessage("MIBIX_EXPORT_INCLUDE_OPTGROUPPROPSKU").(!empty($strTypeInfo)?" ".$strTypeInfo:"").':">'.$strIBlockOffersHTML.'</optgroup>';
                        else
                            $strHTML .= $strIBlockOffersHTML;
                    }
                }
            }
        }

        return $strHTML;
    }

    /**
     * SelectBox со списком типов цен
     *
     * @param $fname - название поля
     * @param $selected - выбранный тип цены
     * @param $iblock_id - ID инфоблока
     * @param $incNone - включать ли элемент для установки пустого значения
     * @return string - сформированный HTML-код контрола
     */
    public function getSelectBoxPriceType($fname, $selected, $iblock_id, $incNone=false)
    {
        return '<select name="f_'.$fname.'" id="f_'.$fname.'" size="1">'.
                self::getOptionsPriceType($selected, $incNone).
                self::getSelectBoxProperty($fname, $selected, $iblock_id, Array()).
                "</select>";
    }

    /**
     * Получить опции для типов цен
     *
     * @param $selected - выбранный тип цены
     * @return string - заполненный набор тегов <option>
     */
    public function getOptionsPriceType($selected, $incNone=false)
    {
        $strHTML = '';
        if($incNone)
            $strHTML .= '<option value="">'.GetMessage("MIBIX_EXPORT_INCLUDE_SEL_NONE").'</option>';

        if(CModule::IncludeModule("sale") && CModule::IncludeModule("catalog"))
        {
            $dbRes = CCatalogGroup::GetList(array("SORT" => "ASC"));
            $strHTML .= self::GetDataOptions($dbRes, $selected, array("id"=>"ID","value"=>"NAME","value2"=>"NAME_LANG"), "price_type");
        }

        return $strHTML;
    }

    /**
     * Контрол (мульти) для выбора одного или нескольких параметров изображений
     * @param $fname - название поля
     * @param $selected - выделенное значение
     * @param $iblock_id - ID информационного блока
     * @return string - сформированный HTML-код контрола
     */
    public function getMultiSelectBoxPictures($fname, $selected, $iblock_id)
    {
        return  '<select multiple="" name="f_'.$fname.'[]" id="f_'.$fname.'" size="5">'.
                '<optgroup label="'.GetMessage("MIBIX_EXPORT_INCLUDE_OPTGROUP_ALL").':">'.self::getSelectBoxProperty($fname, $selected, $iblock_id, "F", false).'</optgroup>'.
                self::getSelectBoxProperty($fname, $selected, $iblock_id, array(), "S", true).
                '</select>';
    }

    /**
     * SelectBox вывода типов фильтров
     *
     * @param $IBLOCK_ID - инфоблок
     * @param $pName - элемент
     * @param $incSelect - включать тег <select> в результат
     * @return string - сформированный список элементов
     */
    public function getSelectBoxFilterName($IBLOCK_ID, $pName="", $incSelect=true)
    {
        $strHTML_main = '';
        $strHTML_prop = '';
        $strHTML_prop_sku = '';

        // --- Общие фильтры ---
        $arFilterNameMain = array(
            //"" => GetMessage("MIBIX_YAM_IRU_SEL_FILTER_NAME"),
            "filter_price" => GetMessage("MIBIX_EXPORT_INCLUDE_DS_FILTER_PRICE"),
            "filter_quantity" => GetMessage("MIBIX_EXPORT_INCLUDE_DS_FILTER_QUANTITY"),
        );
        foreach($arFilterNameMain as $fNameKey => $fNameValue)
        {
            $selectField = "";
            if ($pName==$fNameKey) $selectField = " selected";
            $strHTML_main .= '<option value="'.$fNameKey.'"'.$selectField.'>'.$fNameValue.'</option>';
        }
        // ---/ Общие фильтры ---

        if ($IBLOCK_ID>0)
        {
            // --- Фильтр по свойствам ---
            $iblockProps = CIBlockProperty::GetList(Array("sort" => "asc", "name" => "asc"), Array("ACTIVE" => "Y", "IBLOCK_ID" => $IBLOCK_ID));
            while ($arRes = $iblockProps->GetNext()) {
                $selectField = "";
                if ('prop@' . $arRes["CODE"] == $pName) $selectField = " selected";
                $strHTML_prop .= '<option value="prop@' . $arRes["CODE"] . '"' . $selectField . '>[' . $arRes["CODE"] . '] ' . $arRes["NAME"] . '</option>';
            }
            // ---/ Фильтр по свойсмтвам ---

            // --- Фильтр по свойствам SKU (если торговый каталог)
            $arOffersSKU = NULL;
            if(CModule::IncludeModule("sale") && CModule::IncludeModule("catalog"))
            {
                $arOffersSKU = CCatalogSKU::GetInfoByProductIBlock($IBLOCK_ID);
            }
            if (!empty($arOffersSKU['IBLOCK_ID']))
            {
                $rsOfferIBlocks = CIBlock::GetByID($arOffersSKU['IBLOCK_ID']);
                if (($arOfferIBlock = $rsOfferIBlocks->Fetch()))
                {
                    $iblockOfferProps = CIBlockProperty::GetList(Array("sort"=>"asc","name"=>"asc"), Array("ACTIVE"=>"Y", "IBLOCK_ID"=>$arOffersSKU['IBLOCK_ID']));
                    while ($arResOffers = $iblockOfferProps->GetNext())
                    {
                        if($arOffersSKU["SKU_PROPERTY_ID"] == $arResOffers["ID"]) continue; // пропускаем свойство если оно является привязкой к инфоблоку

                        $selectField = "";
                        if ('offer@'.$arResOffers["CODE"]==$pName) $selectField = " selected";
                        $strHTML_prop_sku .= '<option value="offer@'.$arResOffers["CODE"].'"'.$selectField.'>['.$arResOffers["CODE"].'] '.$arResOffers["NAME"].'</option>';
                    }
                }
            }
            // ---/ Фильтр по свойствам SKU (если торговый каталог) ---
        }

        $strHTML = '<option value="">' . GetMessage("MIBIX_EXPORT_INCLUDE_DS_FILTER_NAME") . '</option>';
        if (!empty($strHTML_main))
            $strHTML .= '<optgroup label="' . GetMessage("MIBIX_EXPORT_INCLUDE_DS_FILTER_MAIN") . ':">' . $strHTML_main . '</optgroup>';
        if (!empty($strHTML_prop))
            $strHTML .= '<optgroup label="' . GetMessage("MIBIX_EXPORT_INCLUDE_DS_FILTER_SETTINGS") . ':">' . $strHTML_prop . '</optgroup>';
        if (!empty($strHTML_prop_sku))
            $strHTML .= '<optgroup label="' . GetMessage("MIBIX_EXPORT_INCLUDE_DS_FILTER_SETTINGS_SKU").':">' . $strHTML_prop_sku.'</optgroup>';

        if ($incSelect)
            $strHTML = '<select name="f_filter_name[]" id="f_filter" size="1">'.$strHTML.'</select>&nbsp;';

        return $strHTML;
    }

    /**
     * SelectBox вывода действий фильтрации
     *
     * @param $selected - выделенный элемент
     * @return string - сформированный список элементов
     */
    private function getSelectBoxFilterUnit($selected="")
    {
        $arFilterUnit = array(
            "equal" => GetMessage("MIBIX_EXPORT_INCLUDE_DS_FILTER_EQUAL"),
            "notequal" => GetMessage("MIBIX_EXPORT_INCLUDE_DS_FILTER_NOTEQUAL"),
            "more" => GetMessage("MIBIX_EXPORT_INCLUDE_DS_FILTER_MORE"),
            "less" => GetMessage("MIBIX_EXPORT_INCLUDE_DS_FILTER_LESS"),
            //"empty" => GetMessage("MIBIX_EXPORT_INCLUDE_DS_FILTER_EMPTY"),
            //"notempty" => GetMessage("MIBIX_EXPORT_INCLUDE_DS_FILTER_NOTEMPTY"),
        );

        $strHTML = '<select name="f_filter_unit[]" size="1">';
        foreach($arFilterUnit as $fUnitKey => $fUnitValue)
        {
            $selectField = "";
            if ($selected == $fUnitKey) $selectField = " selected";

            $strHTML .= '<option value="'.$fUnitKey.'"'.$selectField.'>'.$fUnitValue.'</option>';
        }
        $strHTML .= '</select>&nbsp;';

        return $strHTML;
    }

    /**
     * Формирование списка <option> для SelectBox
     *
     * @param $dbRes - объект ресурсов для выборки данных
     * @param $selected - элемент, который нужно выделить
     * @param $arParam - массив для хранения ключей для выборки в <option> (Array("id"=>"ID", "value"=>"NAME"...))
     * @param $type - дополнительный тип формирования <option>
     * @param $viewId - отображать ли перед значением ID
     * @return string - сформированный список элементов <option>
     */
    private function GetDataOptions($dbRes, $selected, $arParam, $type="simple", $viewId=true)
    {
        $strHTML = "";
        while($arRes = $dbRes->Fetch())
        {
            $selectField = "";
            if ($arRes[$arParam["id"]] == $selected) $selectField = " selected";

            // формирование <option> в зависимости от типа
            switch ($type)
            {
                case "iblock_type":
                    if($arResType = CIBlockType::GetByIDLang($arRes[$arParam["id"]], LANG))
                        $strHTML .= '<option value="'.$arRes[$arParam["id"]].'"'.$selectField.'>'.($viewId?'['.$arRes[$arParam["id"]].'] ':'').htmlspecialcharsEx($arResType[$arParam["value"]]).'</option>';
                    else
                        $strHTML .= '<option value="'.$arRes[$arParam["id"]].'"'.$selectField.'>['.$arRes[$arParam["id"]].']</option>';
                    break;
                case "price_type":
                    $strHTML .= '<option value="' . $arRes[$arParam["id"]] . '"' . $selectField . '>[' . $arRes[$arParam["value"]] . '] ' . $arRes[$arParam["value2"]] . '</option>';
                    break;
                case "simple":
                    $strHTML .= '<option value="'.$arRes[$arParam["id"]].'"'.$selectField.'>'.($viewId?'['.$arRes[$arParam["id"]].'] ':'').htmlspecialcharsEx($arRes[$arParam["value"]]).'</option>';
                    break;
            }
        }
        return $strHTML;
    }

    /**
     * Получаем массив дополнительных параметров по названию поля
     *
     * @param $fname - код поля
     * @return array - массив доп. полей, которые относятся к указанному полю
     */
    private function GetArrayParamsByCODE($fname, $pType=false)
    {
        $arParams = array(
            "none" => GetMessage("MIBIX_EXPORT_INCLUDE_SEL_NONE"),
        );
        switch($fname)
        {
            case "title":
                unset($arParams["none"]);
                $arParams["val@titleitem"] = GetMessage("MIBIX_EXPORT_INCLUDE_SEL_".strtoupper($fname)."_ITEM");
                $arParams["val@titlesku"] = GetMessage("MIBIX_EXPORT_INCLUDE_SEL_".strtoupper($fname)."_ITEM_SKU");
                $arParams["val@titleitemsku"] = GetMessage("MIBIX_EXPORT_INCLUDE_SEL_".strtoupper($fname)."_ITEM_AND_SKU");
                break;

            case "description":
                unset($arParams["none"]);
                $arParams["PREVIEW_TEXT"] = GetMessage("MIBIX_EXPORT_INCLUDE_SEL_PREVIEW_TEXT");
                $arParams["DETAIL_TEXT"] = GetMessage("MIBIX_EXPORT_INCLUDE_SEL_DETAIL_TEXT");
                break;

            case "product_type":
                //$arParams["self"] = GetMessage("MIBIX_EXPORT_INCLUDE_SEL_SELF_VALUE");
                break;

            case "picture":
                unset($arParams["none"]);
                $arParams["PREVIEW_PICTURE"] = GetMessage("MIBIX_EXPORT_INCLUDE_SEL_PIC_PREVIEW");
                $arParams["DETAIL_PICTURE"] = GetMessage("MIBIX_EXPORT_INCLUDE_SEL_PIC_DETAIL");
                $arParams["sku@PREVIEW_PICTURE"] = GetMessage("MIBIX_EXPORT_INCLUDE_SEL_PIC_PREVIEW_SKU");
                $arParams["sku@DETAIL_PICTURE"] = GetMessage("MIBIX_EXPORT_INCLUDE_SEL_PIC_DETAIL_SKU");
                break;

            case "picture_additional":
                unset($arParams["none"]);
                if ($pType=="F")
                {
                    $arParams["PREVIEW_PICTURE"] = GetMessage("MIBIX_EXPORT_INCLUDE_SEL_PIC_PREVIEW");
                    $arParams["DETAIL_PICTURE"] = GetMessage("MIBIX_EXPORT_INCLUDE_SEL_PIC_DETAIL");
                    $arParams["sku@PREVIEW_PICTURE"] = GetMessage("MIBIX_EXPORT_INCLUDE_SEL_PIC_PREVIEW_SKU");
                    $arParams["sku@DETAIL_PICTURE"] = GetMessage("MIBIX_EXPORT_INCLUDE_SEL_PIC_DETAIL_SKU");
                }
                break;

            case "condition":
                unset($arParams["none"]);
                $arParams["val@new"] = GetMessage("MIBIX_EXPORT_INCLUDE_SEL_".strtoupper($fname)."_NEW");
                $arParams["val@used"] = GetMessage("MIBIX_EXPORT_INCLUDE_SEL_".strtoupper($fname)."_USED");
                $arParams["val@refurbished"] = GetMessage("MIBIX_EXPORT_INCLUDE_SEL_".strtoupper($fname)."_REF");
                break;

            case "available": // доступность
                unset($arParams["none"]);
                $arParams["val@in_stock"] = GetMessage("MIBIX_EXPORT_INCLUDE_SEL_".strtoupper($fname)."_IN_STOCK");
                $arParams["val@out_of_stock"] = GetMessage("MIBIX_EXPORT_INCLUDE_SEL_".strtoupper($fname)."_OUT_OF_STOCK");
                $arParams["val@preorder"] = GetMessage("MIBIX_EXPORT_INCLUDE_SEL_".strtoupper($fname)."_PREORDER");
                break;

            case "price":
                unset($arParams["none"]);
                break;

            case "gender":
                $arParams["val@male"] = GetMessage("MIBIX_EXPORT_INCLUDE_SEL_".strtoupper($fname)."_MALE");
                $arParams["val@female"] = GetMessage("MIBIX_EXPORT_INCLUDE_SEL_".strtoupper($fname)."_FEMALE");
                $arParams["val@unisex"] = GetMessage("MIBIX_EXPORT_INCLUDE_SEL_".strtoupper($fname)."_UNISEX");
                break;

            case "age_group":
                $arParams["val@newborn"] = GetMessage("MIBIX_EXPORT_INCLUDE_SEL_".strtoupper($fname)."_NEWBORN");
                $arParams["val@infant"] = GetMessage("MIBIX_EXPORT_INCLUDE_SEL_".strtoupper($fname)."_INFANT");
                $arParams["val@toddler"] = GetMessage("MIBIX_EXPORT_INCLUDE_SEL_".strtoupper($fname)."_TODDLER");
                $arParams["val@kids"] = GetMessage("MIBIX_EXPORT_INCLUDE_SEL_".strtoupper($fname)."_KIDS");
                $arParams["val@adult"] = GetMessage("MIBIX_EXPORT_INCLUDE_SEL_".strtoupper($fname)."_ADULT");
                break;

            case "size_type":
                $arParams["val@regular"] = GetMessage("MIBIX_EXPORT_INCLUDE_SEL_".strtoupper($fname)."_REGULAR");
                $arParams["val@petite"] = GetMessage("MIBIX_EXPORT_INCLUDE_SEL_".strtoupper($fname)."_PETITE");
                $arParams["val@plus"] = GetMessage("MIBIX_EXPORT_INCLUDE_SEL_".strtoupper($fname)."_PLUS");
                $arParams["val@big_and_tall"] = GetMessage("MIBIX_EXPORT_INCLUDE_SEL_".strtoupper($fname)."_BIGANDTALL");
                $arParams["val@maternity"] = GetMessage("MIBIX_EXPORT_INCLUDE_SEL_".strtoupper($fname)."_MATERNITY");
                break;

            case "size_system":
                $arParams["val@US"] = GetMessage("MIBIX_EXPORT_INCLUDE_SEL_".strtoupper($fname)."_US");
                $arParams["val@UK"] = GetMessage("MIBIX_EXPORT_INCLUDE_SEL_".strtoupper($fname)."_UK");
                $arParams["val@EU"] = GetMessage("MIBIX_EXPORT_INCLUDE_SEL_".strtoupper($fname)."_EU");
                $arParams["val@DE"] = GetMessage("MIBIX_EXPORT_INCLUDE_SEL_".strtoupper($fname)."_DE");
                $arParams["val@FR"] = GetMessage("MIBIX_EXPORT_INCLUDE_SEL_".strtoupper($fname)."_FR");
                $arParams["val@JP"] = GetMessage("MIBIX_EXPORT_INCLUDE_SEL_".strtoupper($fname)."_JP");
                $arParams["val@CN_(China)"] = GetMessage("MIBIX_EXPORT_INCLUDE_SEL_".strtoupper($fname)."_CN");
                $arParams["val@IT"] = GetMessage("MIBIX_EXPORT_INCLUDE_SEL_".strtoupper($fname)."_IT");
                $arParams["val@BR"] = GetMessage("MIBIX_EXPORT_INCLUDE_SEL_".strtoupper($fname)."_BR");
                $arParams["val@MEX"] = GetMessage("MIBIX_EXPORT_INCLUDE_SEL_".strtoupper($fname)."_MEX");
                $arParams["val@AU"] = GetMessage("MIBIX_EXPORT_INCLUDE_SEL_".strtoupper($fname)."_AU");
                break;

            case "adult":
                $arParams["val@true"] = GetMessage("MIBIX_EXPORT_INCLUDE_SEL_".strtoupper($fname)."_TRUE");
                break;
        }

        return $arParams;
    }

    /**
     * Получаем массив дополнительных параметров по названию поля
     *
     * @param $fname - код поля
     * @return string - название соответствующее коду поля
     */
    public function GetLangFormField($fname)
    {
        switch(strtolower($fname))
        {
            case "name":
                return GetMessage("MIBIX_EXPORT_INCLUDE_FIELD_NAME");
            case "company":
                return GetMessage("MIBIX_EXPORT_INCLUDE_FIELD_COMPANY");
            case "url":
                return GetMessage("MIBIX_EXPORT_INCLUDE_FIELD_URL");
            case "iblock_id":
                return GetMessage("MIBIX_EXPORT_INCLUDE_FIELD_IBLOCK_ID");
            case "shop_id":
                return GetMessage("MIBIX_EXPORT_INCLUDE_FIELD_SHOP_ID");
            case "name_data":
                return GetMessage("MIBIX_EXPORT_INCLUDE_FIELD_NAME_DATA");
            //case "":
            //    return GetMessage("MIBIX_EXPORT_INCLUDE_FIELD_");
        }

        return strtoupper($fname); // если поле не определено
    }

    /**
     * Проверка фильтра на странице вывода элементов
     *
     * @param $sModuleID - название модуля в верхнем регистре (для строковых полей)
     * @param $find_insert_1 - поиск 1 для добавления
     * @param $find_update_1 - поиск 1 для обновления
     * @param $find_insert_2 - поиск 2 для добавления
     * @param $find_update_2 - поиск 2 для обновления
     * @return bool - результат проверки фильтра
     */
    public function CheckFilter($sModuleID, $find_insert_1, $find_update_1, $find_insert_2, $find_update_2)
    {
        global $FilterArr, $lAdmin;
        foreach ($FilterArr as $f) global $$f;

        if(strlen(trim($find_update_1)) > 0 || strlen(trim($find_update_2)) > 0)
        {
            $date_1_ok = false;
            $date1_stm = MkDateTime(FmtDate($find_update_1,"D.M.Y"),"d.m.Y");
            $date2_stm = MkDateTime(FmtDate($find_update_2,"D.M.Y")." 23:59","d.m.Y H:i");
            if(!$date1_stm && strlen(trim($find_update_1)) > 0)
            {
                $lAdmin->AddFilterError(GetMessage($sModuleID."_POST_WRONG_UPDATE_FROM"));
            }
            else
            {
                $date_1_ok = true;
            }
            if(!$date2_stm && strlen(trim($find_update_2)) > 0)
            {
                $lAdmin->AddFilterError(GetMessage($sModuleID."_POST_WRONG_UPDATE_TILL"));
            }
            elseif($date_1_ok && $date2_stm <= $date1_stm && strlen($date2_stm) > 0)
            {
                $lAdmin->AddFilterError(GetMessage($sModuleID."_POST_FROM_TILL_UPDATE"));
            }

        }
        if(strlen(trim($find_insert_1)) > 0 || strlen(trim($find_insert_2)) > 0)
        {
            $date_1_ok = false;
            $date1_stm = MkDateTime(FmtDate($find_insert_1,"D.M.Y"),"d.m.Y");
            $date2_stm = MkDateTime(FmtDate($find_insert_2,"D.M.Y")." 23:59","d.m.Y H:i");
            if(!$date1_stm && strlen(trim($find_insert_1)) > 0)
            {
                $lAdmin->AddFilterError(GetMessage($sModuleID."_POST_WRONG_INSERT_FROM"));
            }
            else
            {
                $date_1_ok = true;
            }
            if(!$date2_stm && strlen(trim($find_insert_2)) > 0)
            {
                $lAdmin->AddFilterError(GetMessage($sModuleID."_POST_WRONG_INSERT_TILL"));
            }
            elseif($date_1_ok && $date2_stm <= $date1_stm && strlen($date2_stm) > 0)
            {
                $lAdmin->AddFilterError(GetMessage($sModuleID."_POST_FROM_TILL_INSERT"));
            }
        }

        return count($lAdmin->arFilterErrors) == 0;
    }
}

/**
 * Класс с функциями тулзов, необходимых для модуля
 */
class CMibixExportTools
{
    /**
     * Корректируем URL сайта, если в настройках выгрузки он бы указан не по формату
     *
     * @param $siteURL - адрес сайта
     * @return string - адрес сайта в нужном формате
     */
    public static function getSiteURL($siteURL)
    {
        // Убираем на конце слэш если есть
        if (substr($siteURL, -1) == '/')
            $siteURL = substr($siteURL, 0, -1);

        // Добавляем протокол, если не указан
        if (!preg_match("~^(?:f|ht)tps?://~i", $siteURL))
            $siteURL = "http://" . $siteURL;

        return $siteURL;
    }

    /**
     * Получаем корректный URL ссылки, в зависимости от переданных параметров
     *
     * @param $siteURL - адрес сайта
     * @param $srcURL - путь к странице
     * @param $urlEncode - нужно ли кодировать ссылку
     * @return string - полный адрес в нужном формате
     */
    public static function getFixURL($siteURL, $srcURL, $urlEncode=true)
    {
        $siteURL = self::getSiteURL($siteURL);

        // если url содержит только отностительный адрес, добавляем домен
        if(substr($srcURL, 0, 1) == "/" || !preg_match("/[^.]+\\.[^.]+$/", $srcURL))
        {
            $pageUrl = $srcURL;
            if ($urlEncode)
                $pageUrl = implode("/", array_map("rawurlencode", explode("/", $srcURL)));

            if (substr($srcURL, 0, 1) == "/")
                $strFile = $siteURL . $pageUrl;
            else
                $strFile = $siteURL . "/" . $pageUrl;
        }
        else
            $strFile = $srcURL;

        return $strFile;
    }

    /**
     * Приводит слова в нижний регистр и предложения начинает с заглавных букв
     *
     * @param $string - текст, который нужно отформатировать
     * @return string - форматированный текст
     */
    public function sentenceCap($string) {

        $newtext = array();
        $ready = str_replace(array(". ","? ","! "), ". ", $string);
        $textbad = explode(". ", $ready);

        foreach ($textbad as $sentence) {

            if (defined('BX_UTF') && BX_UTF==true) {
                $sentencegood = self::myUcfirst($sentence);
            } else {
                $sentencegood = ucfirst(strtolower($sentence));
            }
            $newtext[] = $sentencegood;
        }

        return implode(". ", $newtext);
    }

    /**
     * Кастомизировання функция, приводящая начало предложения к заглавной букве
     *
     * @param $string - исходное предложение
     * @return string - предложение с заглавной буквы
     */
    private function myUcfirst($string) {
        //TODO: Если некорректно выводит, то раскомментировать if вместо действующего как временное решение (исправление запланировано)
        //if (false) {
        if (function_exists('mb_strtolower') && function_exists('mb_strtoupper')) {
            $string = mb_strtolower($string);
            preg_match_all("/^(.)(.*)$/isU", $string, $arr);
            $string = mb_strtoupper($arr[1][0]).$arr[2][0];
        }
        else {
            $string = ucfirst(strtolower($string));
        }
        return $string;
    }

    /**
     * Замена специальных символов на сущности
     * (вызывается через preg_replace_callback в googleText2xml)
     *
     * @param $arg -
     * @return string
     */
    private static function googleReplaceSpecial($arg)
    {
        if (in_array($arg[0], array("&quot;", "&amp;", "&lt;", "&gt;")))
            return $arg[0];
        else
            return " ";
    }

    /**
     * Перевод текста в формат XML
     *
     * @param $text
     * @param bool $bHSC
     * @param bool $bDblQuote
     * @param bool $bSR
     * @param int $iTryncate
     * @return mixed|string
     */
    public function googleText2xml($text, $bHSC = false, $bDblQuote = false, $bSR=false, $iTryncate=0)
    {
        global $APPLICATION;

        $bHSC = (true == $bHSC ? true : false);
        $bDblQuote = (true == $bDblQuote ? true: false);

        if($bSR) // доп.обработка для HTML-текста
        {
            $text = strip_tags(preg_replace_callback("'&[^;]*;'", "self::googleReplaceSpecial", $text));

            if($iTryncate>0)
                $text = TruncateText($text, $iTryncate);
        }

        if ($bHSC)
        {
            $text = htmlspecialcharsbx($text);
            if ($bDblQuote)
                $text = str_replace('&quot;', '"', $text);
        }

        // Определяем кодировку сайта
        $siteCharset = 'windows-1251';
        if (defined('BX_UTF') && BX_UTF==true)
        {
            $siteCharset = 'UTF-8';
        }

        $text = preg_replace("/[\x1-\x8\xB-\xC\xE-\x1F]/", "", $text);
        $text = str_replace("'", "&apos;", $text);
        $text = $APPLICATION->ConvertCharset($text, $siteCharset, 'UTF-8');

        return $text;
    }

    /**
     * Функция записывает лог строки или объекта в файл в корне модуля
     *
     * @param $name - название объекта
     * @param $value - объект для записи в лог
     */
    public function writeLOG($name, $value, $logfile="/report.log")
    {
        // открываем файл для записи и ставим временную отметку
        $fp = @fopen(dirname(__FILE__).$logfile, "a+");
        if($fp)
        {
            // метка времени лога
            @fwrite($fp, "[".date("Y-m-d H:i:s.").str_pad(substr((float)microtime(), 2), 6, '0', STR_PAD_LEFT)."]\r\n");

            // если передана строка, записываем ее
            if(is_array($value))
            {
                $dumpOut = print_r($value, true);
            }
            else
            {
                ob_start();
                var_dump($value);
                $dumpOut = ob_get_clean();
            }
            @fwrite($fp, $name.": ".$dumpOut."\r\n");
            @fclose($fp);
        }
    }

    /**
     * Преобразует массив, полученный из multiselect в строку для записи в базу
     *
     * @param $mselect_field - массив, полученный из multiselect
     * @return string - строка из multiselect для записи в базу
     */
    public function multiselectPrepare($mselect_field)
    {
        if(!empty($mselect_field) && is_array($mselect_field))
            return implode(",", array_diff($mselect_field, array("")));

        return "";
    }
}

/**
 * Interface iMibixExportModel
 * (Определие формата моделей)
 */
interface iMibixExportModel
{
    /**
     * Провера полей формы
     *
     * @param $arFields - массив полей формы
     * @param $ID - ID значения
     * @return mixed - результат проверки
     */
    public function CheckFields($arFields, $ID);

    /**
     * Получаем запись из базы по ID
     *
     * @param $ID - ID значения
     * @return mixed - результат выполнения запроса
     */
    public function GetByID($ID);

    /**
     * Получаем записи из базы с учетом фильтра и навигации
     *
     * @param array $aSort - массив сортировки
     * @param array $arFilter - фильтр выборки
     * @param bool $arNavStartParams - параметры постраничной навигации
     * @return mixed - результат выполнения запроса
     */
    public function GetList($aSort=Array(), $arFilter=Array(), $arNavStartParams=false);

    /**
     * Добавление записи в базу данных
     *
     * @param $arFields - массив полей формы
     * @param $SITE_ID - код сайта
     * @return mixed - результат выполнения запроса (ID новой записи или false)
     */
    public function Add($arFields, $SITE_ID=SITE_ID);

    /**
     * Обновление записи по ID
     *
     * @param $ID - ID значения
     * @param $arFields - массив полей формы
     * @param $SITE_ID - код сайта
     * @return mixed - результат выполнения запроса
     */
    public function Update($ID, $arFields, $SITE_ID=SITE_ID);

    /**
     * Удаление записи по ID
     *
     * @param $ID - ID значения
     * @return mixed - результат выполнения запроса
     */
    public function Delete($ID);

    /**
     * Устанавливает массив ошибок при проверке
     *
     * @param $ERRORS - добавляет массив ошибок
     */
    public function setErrors($ERRORS=array());

    /**
     * Возвращает массив ошибок возникших при проверке
     *
     * @return array - массив ошибок
     */
    public function getErrors();

    /**
     * Возвращает название таблицы базы данных, к которой относится модель
     *
     * @return string - название таблицы
     */
    public function getTableName();
}

/**
 * Class CMibixExportBaseModel
 * (Базовые функции моделей администрирования модулей)
 */
class CMibixExportBaseModel
{
    /**
     * Проверка корректности заполненных полей формы согласно правилу
     *
     * @param $arFields - массив проверяемых полей array("имя"=>"значение"...)
     * @param $arRules - массив правил для проверки значений array("правило"=>"значение"...)
     * Список правил проверки:
     * "required" - обязательное поле (bool)
     * "numeric" - числовое поле (bool)
     * "min" - минимальное значение или мин. длинна строки (int)
     * "max" - максимальное значение или макс. длинна строки (int)
     * "equal" - должно быть равно значению (any)
     * "not_equal" - не должно быть равно значению (any)
     * @return bool
     */
    public function CheckByRules($arFields, $arRules)
    {
        $aMsg = array();

        foreach ($arFields as $kField => $vField)
        {
            foreach ($arRules as $kRule => $vRule)
            {
                switch ($kRule)
                {
                    case "required": // обязательное поле
                        if ($vRule && empty($vField))
                        {
                            $aMsg[] = array("id"=>$kField, "text"=>GetMessage("MIBIX_EXPORT_INCLUDE_ERROR_FIELD_REQUIRED", Array ("#FIELD#" => CMibixExportControls::GetLangFormField($kField))));
                        }
                        break;
                    case "numeric": // числовое поле
                        if ($vRule && !is_numeric($vField))
                        {
                            $aMsg[] = array("id"=>$kField, "text"=>GetMessage("MIBIX_EXPORT_INCLUDE_ERROR_FIELD_NOT_NUM", Array ("#FIELD#" => CMibixExportControls::GetLangFormField($kField))));
                        }
                        elseif (!$vRule && is_numeric($vField))
                        {
                            $aMsg[] = array("id"=>$kField, "text"=>GetMessage("MIBIX_EXPORT_INCLUDE_ERROR_FIELD_IS_NUM", Array ("#FIELD#" => CMibixExportControls::GetLangFormField($kField))));
                        }
                        break;
                    case "min": // минимальное значение
                        if ((intval($vField) < $vRule))
                        {
                            $aMsg[] = array("id"=>$kField, "text"=>GetMessage("MIBIX_EXPORT_INCLUDE_ERROR_FIELD_MIN_NUM", Array ("#FIELD#" => CMibixExportControls::GetLangFormField($kField), "#RULE#" => $vRule)));
                        }
                        break;
                    case "max": // максимальное значение
                        if ((intval($vField) > $vRule))
                        {
                            $aMsg[] = array("id"=>$kField, "text"=>GetMessage("MIBIX_EXPORT_INCLUDE_ERROR_FIELD_MAX_NUM", Array ("#FIELD#" => CMibixExportControls::GetLangFormField($kField), "#RULE#" => $vRule)));
                        }
                        break;
                    case "minlen":
                        if (strlen($vField) < $vRule)
                        {
                            $aMsg[] = array("id"=>$kField, "text"=>GetMessage("MIBIX_EXPORT_INCLUDE_ERROR_FIELD_MIN_LEN", Array ("#FIELD#" => CMibixExportControls::GetLangFormField($kField), "#RULE#" => $vRule)));
                        }
                        break;
                    case "maxlen":
                        if (strlen($vField) > $vRule)
                        {
                            $aMsg[] = array("id"=>$kField, "text"=>GetMessage("MIBIX_EXPORT_INCLUDE_ERROR_FIELD_MAX_LEN", Array ("#FIELD#" => CMibixExportControls::GetLangFormField($kField), "#RULE#" => $vRule)));
                        }
                        break;
                    case "equal": // должно быть равны значению
                        if (intval($vField) != $vRule)
                        {
                            $aMsg[] = array("id"=>$kField, "text"=>GetMessage("MIBIX_EXPORT_INCLUDE_ERROR_FIELD_NOT_EQUAL", Array ("#FIELD#" => CMibixExportControls::GetLangFormField($kField), "#RULE#" => $vRule)));
                        }
                        break;
                    case "not_equal": // не должно быть равно значению
                        if (intval($vField) == $vRule)
                        {
                            $aMsg[] = array("id"=>$kField, "text"=>GetMessage("MIBIX_EXPORT_INCLUDE_ERROR_FIELD_EQUAL", Array ("#FIELD#" => CMibixExportControls::GetLangFormField($kField), "#RULE#" => $vRule)));
                        }
                        break;
                }
            }
        }

        return $aMsg;
    }

    /**
     * Базовая функция добавления записи в таблицу
     *
     * @param $arFields - массив добавляемых полей
     * @param $table_db - таблица базы данных
     * @param $SITE_ID - код сайта
     * @return bool - статус обновления
     */
    public function BaseAdd($arFields, $table_db, $SITE_ID=SITE_ID)
    {
        global $DB;

        $arFields["~date_insert"] = $DB->CurrentTimeFunction();
        $arFields["~date_update"] = $DB->CurrentTimeFunction();

        $ID = $DB->Add($table_db, $arFields);
        if($ID > 0)
        {
            return $ID;
        }
        return false;
    }

    /**
     * Базовая функция обновления записи в таблице
     *
     * @param $ID - номер обновляемой записи
     * @param $arFields - массив обновляемых полей
     * @param $table_db - таблица базы данных
     * @param $SITE_ID - код сайта
     * @return bool - статус обновления
     */
    public function BaseUpdate($ID, $arFields, $table_db, $SITE_ID=SITE_ID)
    {
        global $DB;
        $ID = intval($ID);

        $strUpdate = $DB->PrepareUpdate($table_db, $arFields);
        if (strlen($strUpdate)>0)
        {
            $strSql = "UPDATE ".$table_db." SET ".$strUpdate.", "." date_update=".$DB->GetNowFunction()." "."WHERE id=".$ID;

            if($DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__))
                return true;
        }
        return false;
    }

    /**
     * Базовая функция удаления записи по ID
     *
     * @param $table
     * @param $ID
     * @return mixed
     */
    public function BaseDeleteByID($ID, $table)
    {
        global $DB;

        $ID = intval($ID);
        $strSql = "DELETE FROM ".$table." WHERE id='".$ID."'";

        $DB->StartTransaction();
        $res = self::BaseExecute($strSql);

        if($res)
            $DB->Commit();
        else
            $DB->Rollback();

        return $res;
    }

    /**
     * Базовая функция получения значения указанного поля из базы по ID
     *
     * @param $ID - значение ID
     * @param $select - поле выборки из базы
     * @param $table - таблица выборки
     * @return mixed - значение указанного поля выборки или 0
     */
    public function BaseGetFieldByID($ID, $select="id", $table)
    {
        $ID = intval($ID);
        if ($ID > 0)
        {
            $rsData = self::BaseExecute("SELECT ".$select." FROM ".$table." WHERE id='".$ID."'");
            if($rowData = $rsData->Fetch())
                return $rowData[$select];
        }

        return $ID;
    }

    /**
     * Базовая функция получения указанных полей из базы по ID
     *
     * @param $ID - значение ID
     * @param array $select - массив полей выборки
     * @param $table - таблица выборки
     * @return mixed - результат выполнения запроса
     */
    public function BaseGetByID($ID, $select=array("*"), $table)
    {
        global $DB;

        $ID = intval($ID);
        $strSql = "SELECT ".
            implode(", ", $select) . ", ".
            " ".$DB->DateToCharFunction("tb.date_update", "FULL")." AS date_update, ".
            " ".$DB->DateToCharFunction("tb.date_insert", "FULL")." AS date_insert ".
            "FROM ".$table." tb ".
            "WHERE tb.id='".$ID."'";

        return self::BaseExecute($strSql);
    }

    /**
     * SelectBox со списком профилей магазинов
     *
     * @param $select - поля выборки, через запятую
     * @param $table - таблица выборки
     * @return string - результат выполнения запроса
     */
    public function BaseGetAll($select="*", $table)
    {
        $strSql = "SELECT ".$select." FROM ".$table." WHERE active='Y'";
        return self::BaseExecute($strSql);
    }

    /**
     * Базовая функция получения полей из базы по собственному условию
     *
     * @param $table - таблица выборки
     * @param array $select - массив полей выборки
     * @param string $where - строка условия выборки
     * @return mixed - результат выполнения запроса
     */
    public function BaseGetByWhere($table, $select=array("*"), $where="")
    {
        global $DB;

        $strSql = "SELECT ".
            implode(", ", $select) . ", ".
            " ".$DB->DateToCharFunction("tb.date_update", "FULL")." AS date_update, ".
            " ".$DB->DateToCharFunction("tb.date_insert", "FULL")." AS date_insert ".
            "FROM ".$table." tb ";

        if (strlen($where)>0)
        {
            $strSql .= " WHERE ".$where;
        }

        return self::BaseExecute($strSql);
    }

    /**
     * Базовая функция исполнения запроса
     *
     * @param $query - sql запрос
     * @return mixed - результат выполнения запроса
     */
    public function BaseExecute($query)
    {
        global $DB;
        return $DB->Query($query, false, "File: ".__FILE__."<br>Line: ".__LINE__);
    }
}

?>