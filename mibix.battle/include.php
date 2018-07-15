<?php
if (!CModule::IncludeModule("iblock")) return false;
IncludeModuleLangFile(__FILE__);

// глобальные типы
global $DBType;

/**
 * Класс описывающий форму и список групп
 */
class CMibixBattleGroupModel
{
    public $LAST_ERROR="";
    public $LAST_MESSAGE="";

    /**
     * Проверка корректности заполненных полей формы для групп
     *
     * @param $arFields
     * @param $ID
     * @return bool
     */
    private function CheckFields($arFields, $ID)
    {
        global $DB;

        $this->LAST_ERROR = "";
        $aMsg = array();

        if(is_set($arFields, "name_group")) // Проверка: название
        {
            if(strlen($arFields["name_group"]) == 0)
            {
                $aMsg[] = array("id"=>"name_group", "text"=>GetMessage("MIBIX_BATTLE_ERR_GROUP_NAME_NULL"));
            }
            elseif(strlen($arFields["name_group"]) > 255)
            {
                $aMsg[] = array("id"=>"name_group", "text"=>GetMessage("MIBIX_BATTLE_ERR_GROUP_NAME_LIMIT255"));
            }
        }

        if(is_set($arFields, "code_group")) // Проверка: название
        {
            if (!preg_match('/^[a-zA-Z0-9\-\_]{1,10}$/',$arFields["code_group"]))
            {
                $aMsg[] = array("id"=>"code_group", "text"=>GetMessage("MIBIX_BATTLE_ERR_GROUP_CODE_WRONG"));
            }
        }


        // Если ошибок нет, то возвращаем true
        if(!empty($aMsg))
        {
            $e = new CAdminException($aMsg);
            $GLOBALS["APPLICATION"]->ThrowException($e);
            $this->LAST_ERROR = $e->GetString();
            return false;
        }
        return true;
    }

    /**
     * Добавление новой записи в таблицу "Группы"
     *
     * @param $arFields
     * @return bool|int
     */
    public function Add($arFields)
    {
        global $DB;

        // временные метки
        $arFields["~date_insert"] = $DB->CurrentTimeFunction();
        $arFields["~date_update"] = $DB->CurrentTimeFunction();

        // Проверяем заполненные поля на ошибки и возвращаем false в случае их наличия, при этом сами ошибки сохраняем в переменной класса
        if(!$this->CheckFields($arFields, 0)) return false;

        // Если ошибок нет, то добавляем данные в таблицу
        $ID = $DB->Add("b_mibix_battle_group", $arFields);
        if($ID > 0)
        {
            // дополнительные действия при добавлении записи
        }
        return $ID;
    }

    /**
     * Обновление записи об группе
     *
     * @param $ID
     * @param $arFields
     * @return bool
     */
    public function Update($ID, $arFields)
    {
        global $DB;
        $ID = intval($ID);
        $this->LAST_MESSAGE = "";

        if(!$this->CheckFields($arFields, $ID)) return false;

        $strUpdate = $DB->PrepareUpdate("b_mibix_battle_group", $arFields);
        if (strlen($strUpdate)>0)
        {
            $strSql =
                "UPDATE b_mibix_battle_group SET ".
                $strUpdate.", ".
                "	date_update=".$DB->GetNowFunction()." ".
                "WHERE id=".$ID;
            if(!$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__)) return false;
        }
        return true;
    }

    /**
     * Получаем группу по ID
     *
     * @param $ID
     * @return mixed
     */
    public function GetByID($ID)
    {
        global $DB;
        $ID = intval($ID);

        $strSql =
            "SELECT gr.*, ".
            "	".$DB->DateToCharFunction("gr.date_update", "FULL")." AS date_update, ".
            "	".$DB->DateToCharFunction("gr.date_insert", "FULL")." AS date_insert ".
            "FROM b_mibix_battle_group gr ".
            "WHERE gr.id='".$ID."' ";

        return $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
    }

    /**
     * Список "Групп"
     *
     * @param array $aSort
     * @param array $arFilter
     * @param bool $arNavStartParams
     * @return CDBResult|mixed
     */
    public function GetList($aSort=Array(), $arFilter=Array(), $arNavStartParams=false)
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
                        $arSqlSearch[] = GetFilterQuery("gr.id", $val, "N");
                        break;
                    case "NAME_GROUP":
                        $arSqlSearch[] = GetFilterQuery("gr.name_group", $val, "Y", array("@", ".", "_"));
                        break;
                    case "CODE_GROUP":
                        $arSqlSearch[] = GetFilterQuery("gr.code_group", $val, "Y", array("@", ".", "_"));
                        break;
                    case "UPDATE_1":
                        $arSqlSearch[] = "gr.date_update>=".$DB->CharToDateFunction($val);
                        break;
                    case "UPDATE_2":
                        $arSqlSearch[] = "gr.date_update<=".$DB->CharToDateFunction($val." 23:59:59");
                        break;
                    case "INSERT_1":
                        $arSqlSearch[] = "gr.date_insert>=".$DB->CharToDateFunction($val);
                        break;
                    case "INSERT_2":
                        $arSqlSearch[] = "gr.date_insert<=".$DB->CharToDateFunction($val." 23:59:59");
                        break;
                    case "ACTIVE":
                        $arSqlSearch[] = ($val=="Y") ? "gr.active='Y'" : "gr.active='N'";
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
                case "ID": $arOrder[$by] = "gr.id ".$ord; break;
                case "NAME_GROUP": $arOrder[$by] = "gr.name_group ".$ord; break;
                case "CODE_GROUP": $arOrder[$by] = "gr.code_group ".$ord; break;
                case "DATE_INSERT": $arOrder[$by] = "gr.date_insert ".$ord; break;
                case "DATE_UPDATE": $arOrder[$by] = "gr.date_update ".$ord; break;
                case "ACT": $arOrder[$by] = "gr.active ".$ord; break;
            }
        }
        if(count($arOrder) <= 0) $arOrder["ID"] = "gr.id DESC";

        if(is_array($arNavStartParams))
        {
            $strSql = "
				SELECT count(".($from1 <> ""? "DISTINCT gr.id": "'x'").") as C
				FROM
					b_mibix_battle_group gr
					$from1
				WHERE
				".$strSqlSearch;

            $res_cnt = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
            $res_cnt = $res_cnt->Fetch();
            $cnt = $res_cnt["C"];

            $strSql = "
				SELECT
					gr.id, gr.active, gr.name_group, gr.code_group,
					".$DB->DateToCharFunction("gr.date_update")." date_update,
					".$DB->DateToCharFunction("gr.date_insert")." date_insert
				FROM
					b_mibix_battle_group gr
					$from1
				WHERE
				$strSqlSearch
				".($from1 <> ""?
                    "GROUP BY gr.id, gr.active, gr.name_group, gr.code_group":
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
					gr.id, gr.active, gr.name_group, gr.code_group,
					".$DB->DateToCharFunction("gr.date_update")." date_update,
					".$DB->DateToCharFunction("gr.date_insert")." date_insert
				FROM
					b_mibix_battle_group gr
					$from1
				WHERE
				$strSqlSearch
				".($from1 <> ""?
                    "GROUP BY gr.id, gr.active, gr.name_group, gr.code_group":
                    ""
                )."
				ORDER BY ".implode(", ", $arOrder);

            $res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
            $res->is_filtered = (IsFiltered($strSqlSearch));

            return $res;
        }
    }

    /**
     * Удаляем группу из базы по его ID
     *
     * @param $ID
     * @return mixed
     */
    public function Delete($ID)
    {
        global $DB;
        $ID = intval($ID);

        $DB->StartTransaction();
        $res = $DB->Query("DELETE FROM b_mibix_battle_group WHERE id='".$ID."'", false, "File: ".__FILE__."<br>Line: ".__LINE__);

        if($res)
            $DB->Commit();
        else
            $DB->Rollback();

        return $res;
    }
}

/**
 * Класс описывающий форму и список групп
 */
class CMibixBattleBattleModel
{
    public $LAST_ERROR="";
    public $LAST_MESSAGE="";

    /**
     * Проверка корректности заполненных полей формы для битв
     *
     * @param $arFields
     * @param $ID
     * @return bool
     */
    private function CheckFields($arFields, $ID)
    {
        global $DB;

        $this->LAST_ERROR = "";
        $aMsg = array();

        // Инфоблок
        if($arFields["iblock_id"] < 1)
        {
            $this->arMsg[] = array("id"=>"iblock_id", "text"=>GetMessage("MIBIX_BATTLE_ERR_BATTLE_IBLOCK_EMPTY"));
        }
        // Даты битвы
        if(array_key_exists("date_start", $arFields) && $arFields["date_start"]!==false)
        {
            if($DB->IsDate($arFields["date_start"], false, false, "FULL")!==true)
                $aMsg[] = array("id"=>"date_start", "text"=>GetMessage("MIBIX_BATTLE_ERR_BATTLE_DATE_START_NULL"));
        }
        if(array_key_exists("date_finish", $arFields) && $arFields["date_finish"]!==false)
        {
            if($DB->IsDate($arFields["date_finish"], false, false, "FULL")!==true)
                $aMsg[] = array("id"=>"date_finish", "text"=>GetMessage("MIBIX_BATTLE_ERR_BATTLE_DATE_FINISH_NULL"));
        }
        // Название
        if(is_set($arFields, "name_battle"))
        {
            if(strlen($arFields["name_battle"]) == 0)
            {
                $aMsg[] = array("id"=>"name_battle", "text"=>GetMessage("MIBIX_BATTLE_ERR_BATTLE_NAME_NULL"));
            }
            elseif(strlen($arFields["name_battle"]) > 255)
            {
                $aMsg[] = array("id"=>"name_battle", "text"=>GetMessage("MIBIX_BATTLE_ERR_BATTLE_NAME_LIMIT255"));
            }
        }
        // Группа
        if(is_set($arFields, "group_id"))
        {
            if(IntVal($arFields["group_id"])<1)
            {
                $aMsg[] = array("id"=>"group_id", "text"=>GetMessage("MIBIX_BATTLE_ERR_BATTLE_GROUP_NULL"));
            }
        }
        // Элементы
        if(is_set($arFields, "battle_items"))
        {
            if(count($arFields["battle_items"]) == 0)
            {
                $aMsg[] = array("id"=>"battle_items", "text"=>GetMessage("MIBIX_BATTLE_ERR_BATTLE_ITEMS_NULL"));
            }
        }

        // Если ошибок нет, то возвращаем true
        if(!empty($aMsg))
        {
            $e = new CAdminException($aMsg);
            $GLOBALS["APPLICATION"]->ThrowException($e);
            $this->LAST_ERROR = $e->GetString();
            return false;
        }
        return true;
    }

    /**
     * Подготавливает данные, полученные из multiselect для записи в базу
     *
     * @param $mselect_field
     * @return string
     */
    private function MSelectPrepare($mselect_field)
    {
        if(!empty($mselect_field) && is_array($mselect_field))
        {
            return implode(",", array_diff($mselect_field, array("")));
        }

        return "";
    }

    /**
     * Добавление новой записи в таблицу "Битвы"
     *
     * @param $arFields
     * @return bool|int
     */
    public function Add($arFields)
    {
        global $DB;

        // поля с множественным выбором
        $arFields["battle_items"] = self::MSelectPrepare($arFields["battle_items"]);
        $arFields["battle_pictures"] = self::MSelectPrepare($arFields["battle_pictures"]);

        // временные метки
        $arFields["~date_insert"] = $DB->CurrentTimeFunction();
        $arFields["~date_update"] = $DB->CurrentTimeFunction();

        // Проверяем заполненные поля на ошибки и возвращаем false в случае их наличия, при этом сами ошибки сохраняем в переменной класса
        if(!$this->CheckFields($arFields, 0)) return false;

        // Если ошибок нет, то добавляем данные в таблицу
        $ID = $DB->Add("b_mibix_battle_battle", $arFields);
        if($ID > 0)
        {
            // дополнительные действия при добавлении записи
        }
        return $ID;
    }

    /**
     * Обновление записи об группе
     *
     * @param $ID
     * @param $arFields
     * @return bool
     */
    public function Update($ID, $arFields)
    {
        global $DB;
        $ID = intval($ID);
        $this->LAST_MESSAGE = "";

        if(!self::CheckFields($arFields, $ID)) return false;

        // проверка полей с множественным выбором
        if(!empty($arFields["battle_items"]))
            $arFields["battle_items"] = self::MSelectPrepare($arFields["battle_items"]);
        if(!empty($arFields["battle_pictures"]))
            $arFields["battle_pictures"] = self::MSelectPrepare($arFields["battle_pictures"]);

        $strUpdate = $DB->PrepareUpdate("b_mibix_battle_battle", $arFields);

        if (strlen($strUpdate)>0)
        {
            $strSql =
                "UPDATE b_mibix_battle_battle SET ".
                $strUpdate.", ".
                "	date_update=".$DB->GetNowFunction()." ".
                "WHERE id=".$ID;
            if(!$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__)) return false;
        }
        return true;
    }

    /**
     * Получаем правило по ID из базы
     *
     * @param $ID
     * @return mixed
     */
    public function GetByID($ID)
    {
        global $DB;
        $ID = intval($ID);

        $strSql =
            "SELECT bb.*, ".
            "	".$DB->DateToCharFunction("bb.date_start", "FULL")." AS date_start, ".
            "	".$DB->DateToCharFunction("bb.date_finish", "FULL")." AS date_finish, ".
            "	".$DB->DateToCharFunction("bb.date_update", "FULL")." AS date_update, ".
            "	".$DB->DateToCharFunction("bb.date_insert", "FULL")." AS date_insert ".
            "FROM b_mibix_battle_battle bb ".
            "WHERE bb.id='".$ID."' ";

        return $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
    }

    /**
     * Список "Правил" из базы
     *
     * @param array $aSort
     * @param array $arFilter
     * @param bool $arNavStartParams
     * @return CDBResult|mixed
     */
    public function GetList($aSort=Array(), $arFilter=Array(), $arNavStartParams=false)
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
                        $arSqlSearch[] = GetFilterQuery("bb.id", $val, "N");
                        break;
                    case "GROUP_ID":
                        $arSqlSearch[] = GetFilterQuery("bb.group_id", $val, "N");
                        break;
                    case "NAME_BATTLE":
                        $arSqlSearch[] = GetFilterQuery("bb.name_battle", $val, "Y", array("@", ".", "_"));
                        break;
                    case "UPDATE_1":
                        $arSqlSearch[] = "bb.date_update>=".$DB->CharToDateFunction($val);
                        break;
                    case "UPDATE_2":
                        $arSqlSearch[] = "bb.date_update<=".$DB->CharToDateFunction($val." 23:59:59");
                        break;
                    case "INSERT_1":
                        $arSqlSearch[] = "bb.date_insert>=".$DB->CharToDateFunction($val);
                        break;
                    case "INSERT_2":
                        $arSqlSearch[] = "bb.date_insert<=".$DB->CharToDateFunction($val." 23:59:59");
                        break;
                    case "ACTIVE":
                        $arSqlSearch[] = ($val=="Y") ? "bb.active='Y'" : "bb.active='N'";
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
                case "ID": $arOrder[$by] = "bb.id ".$ord; break;
                case "GROUP_ID": $arOrder[$by] = "bb.group_id ".$ord; break;
                case "NAME_BATTLE": $arOrder[$by] = "bb.name_battle ".$ord; break;
                case "DATE_INSERT": $arOrder[$by] = "bb.date_insert ".$ord; break;
                case "DATE_UPDATE": $arOrder[$by] = "bb.date_update ".$ord; break;
                case "ACT": $arOrder[$by] = "bb.active ".$ord; break;
            }
        }
        if(count($arOrder) <= 0) $arOrder["ID"] = "bb.id DESC";

        if(is_array($arNavStartParams))
        {
            $strSql = "
				SELECT count(".($from1 <> ""? "DISTINCT bb.id": "'x'").") as C
				FROM
					b_mibix_battle_battle bb
				JOIN b_mibix_battle_group gr ON (bb.group_id=gr.id)
					$from1
				WHERE
				".$strSqlSearch;

            $res_cnt = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
            $res_cnt = $res_cnt->Fetch();
            $cnt = $res_cnt["C"];

            $strSql = "
				SELECT
					bb.id, bb.group_id, bb.active, bb.name_battle,
					".$DB->DateToCharFunction("bb.date_update")." date_update,
					".$DB->DateToCharFunction("bb.date_insert")." date_insert,
					gr.name_group
				FROM
					b_mibix_battle_battle bb
				JOIN b_mibix_battle_group gr ON (bb.group_id=gr.id)
					$from1
				WHERE
				$strSqlSearch
				".($from1 <> ""?
                    "GROUP BY bb.id, bb.group_id, bb.active, bb.name_battle, gr.name_group":
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
					bb.id, bb.group_id, bb.active, bb.name_battle,
					".$DB->DateToCharFunction("bb.date_update")." date_update,
					".$DB->DateToCharFunction("bb.date_insert")." date_insert,
					gr.name_group
				FROM
					b_mibix_battle_battle bb
					LEFT JOIN b_mibix_battle_group gr ON (bb.group_id=gr.id)
					$from1
				WHERE
				$strSqlSearch
				".($from1 <> ""?
                    "GROUP BY bb.id, bb.group_id, bb.active, bb.name_battle, gr.name_group":
                    ""
                )."
				ORDER BY ".implode(", ", $arOrder);

            $res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
            $res->is_filtered = (IsFiltered($strSqlSearch));

            return $res;
        }
    }

    /**
     * Удаляем правило из базы по его ID
     *
     * @param $ID
     * @return mixed
     */
    public function Delete($ID)
    {
        global $DB;
        $ID = intval($ID);

        $DB->StartTransaction();
        $res = $DB->Query("DELETE FROM b_mibix_battle_battle WHERE id='".$ID."'", false, "File: ".__FILE__."<br>Line: ".__LINE__);

        if($res)
            $DB->Commit();
        else
            $DB->Rollback();

        return $res;
    }

    /**
     * SelectBox с инфоблоками выбранного типа и сайта
     *
     * @param $str_iblock_id
     * @return string
     */
    public function getSelectBoxIBlockId($str_iblock_id)
    {
        $strHTML = '<select name="f_iblock_id" id="f_iblock_id" onchange="this.form.submit()" size="1">';
        $strHTML .= '<option>('.GetMessage("MIBIX_BATTLE_SELECT_IBLOCK").')</option>';

        // Выводим все инфоблоки
        if(CModule::IncludeModule("iblock"))
        {
            $arParams = array();
            $dbRes = CIBlock::GetList(array(), $arParams, false, false, array("ID","NAME"));
            while ($arRes = $dbRes->Fetch())
            {
                $selectField = "";
                if ($arRes['ID']==$str_iblock_id) $selectField = " selected";

                $strHTML .= '<option value="'.$arRes['ID'].'"'.$selectField.'>['.$arRes['ID']."] ".$arRes['NAME'].'</option>';
            }
        }
        $strHTML .= '</select>';
        return $strHTML;
    }

    /**
     * Поля и свойства для поля "Название расположения"
     *
     * @param $IBLOCK_ID
     * @param $SELECTED
     * @return string
     */
    public function getSelectBoxBattleTitle($IBLOCK_ID, $SELECTED)
    {
        $strHTML = '<select name="f_battle_title" id="f_battle_title" size="1">';
        $strHTML .= '<option value="SELF@TITLE"' . (($SELECTED=="NONE") ? ' selected' : '') . '>'.GetMessage("MIBIX_BATTLE_SELECT_USE_TITLE").'</option>';
        $strHTML .= '<option value="PREVIEW_TEXT"' . (($SELECTED=="PREVIEW_TEXT") ? ' selected' : '') . '>[PREVIEW_TEXT] '.GetMessage("MIBIX_BATTLE_SELECT_PREVIEW_TEXT").'</option>';
        $strHTML .= '<option value="DETAIL_TEXT"' . (($SELECTED=="DETAIL_TEXT") ? ' selected' : '') . '>[DETAIL_TEXT] '.GetMessage("MIBIX_BATTLE_SELECT_DETAIL_TEXT").'</option>';
        $strHTML .= self::getIBlockPropertiesOptions($IBLOCK_ID, $SELECTED);
        $strHTML .= '</select>';
        return $strHTML;
    }

    /**
     * Поля и свойства для поля "Описания расположения"
     *
     * @param $IBLOCK_ID
     * @param $SELECTED
     * @return string
     */
    public function getSelectBoxBattleText($IBLOCK_ID, $SELECTED)
    {
        $strHTML = '<select name="f_battle_text" id="f_battle_text" size="1">';
        $strHTML .= '<option value="NONE"' . (($SELECTED=="NONE") ? ' selected' : '') . '>('.GetMessage("MIBIX_BATTLE_SELECT_TEXT_NONE").')</option>';
        $strHTML .= '<option value="PREVIEW_TEXT"' . (($SELECTED=="PREVIEW_TEXT") ? ' selected' : '') . '>[PREVIEW_TEXT] '.GetMessage("MIBIX_BATTLE_SELECT_PREVIEW_TEXT").'</option>';
        $strHTML .= '<option value="DETAIL_TEXT"' . (($SELECTED=="DETAIL_TEXT") ? ' selected' : '') . '>[DETAIL_TEXT] '.GetMessage("MIBIX_BATTLE_SELECT_DETAIL_TEXT").'</option>';
        $strHTML .= self::getIBlockPropertiesOptions($IBLOCK_ID, $SELECTED);
        $strHTML .= '</select>';
        return $strHTML;
    }

    /**
     * Поля и свойства для поля "Описания ссылки"
     *
     * @param $IBLOCK_ID
     * @param $SELECTED
     * @return string
     */
    public function getSelectBoxBattleLink($IBLOCK_ID, $SELECTED)
    {
        $strHTML = '<select name="f_battle_links" id="f_battle_links" size="1">';
        $strHTML .= '<option value="NONE"' . (($SELECTED=="NONE") ? ' selected' : '') . '>('.GetMessage("MIBIX_BATTLE_SELECT_LINK_NONE").')</option>';
        $strHTML .= '<option value="SELF@DETAILPAGEURL"' . (($SELECTED=="SELF@DETAILPAGEURL") ? ' selected' : '') . '>'.GetMessage("MIBIX_BATTLE_SELECT_LINK_DETAIL_URL").'</option>';
        $strHTML .= self::getIBlockPropertiesOptions($IBLOCK_ID, $SELECTED);
        $strHTML .= '</select>';
        return $strHTML;
    }

    /**
     * Получение свойств инфоблока в виде тегов <option>
     *
     * @param $IBLOCK_ID
     * @param $SELECTED
     * @return string
     */
    private function getIBlockPropertiesOptions($IBLOCK_ID, $SELECTED)
    {
        $strHTML = "";
        if(CModule::IncludeModule("iblock") && $IBLOCK_ID)
        {
            $iblockProps = CIBlockProperty::GetList(Array("sort"=>"asc","name"=>"asc"), Array("ACTIVE"=>"Y", "IBLOCK_ID"=>$IBLOCK_ID, "PROPERTY_TYPE"=>"S"));
            while ($arRes = $iblockProps->GetNext())
            {
                $selectField = "";
                if ($arRes["CODE"]==$SELECTED) $selectField = " selected";
                $strHTML .= '<option value="'.$arRes["CODE"].'"'.$selectField.'>['.$arRes["CODE"].'] '.$arRes["NAME"].'</option>';
            }
        }
        return $strHTML;
    }

    /**
     * Поля и свойства для поля "Картинки"
     *
     * @param $IBLOCK_ID
     * @param $SELECTED
     * @return string
     */
    public function getSelectBoxPropertyPictures($IBLOCK_ID, $SELECTED)
    {
        $arPictures = explode(",", $SELECTED);

        $strHTML = '<select multiple="" name="f_battle_pictures[]" id="f_battle_pictures" size="4">';

        $strHTML .= '<option value="PREVIEW_PICTURE"' . (in_array("PREVIEW_PICTURE",$arPictures) ? ' selected' : '') . '>[PREVIEW_PICTURE] '.GetMessage("MIBIX_BATTLE_PREVIEW_PICTURE").'</option>';
        $strHTML .= '<option value="DETAIL_PICTURE"' . (in_array("DETAIL_PICTURE",$arPictures) ? ' selected' : '') . '>[DETAIL_PICTURE] '.GetMessage("MIBIX_BATTLE_DETAIL_PICTURE").'</option>';

        if(CModule::IncludeModule("iblock") && $IBLOCK_ID)
        {
            $iblockProps = CIBlockProperty::GetList(Array("sort"=>"asc","name"=>"asc"), Array("ACTIVE"=>"Y", "IBLOCK_ID"=>$IBLOCK_ID, "PROPERTY_TYPE"=>"F"));
            while ($arRes = $iblockProps->GetNext())
            {
                $selectField = "";
                if (in_array($arRes["CODE"],$arPictures)) $selectField = " selected";

                $strHTML .= '<option value="'.$arRes["CODE"].'"'.$selectField.'>['.$arRes["CODE"].'] '.$arRes["NAME"].'</option>';
            }
        }
        $strHTML .= '</select>';
        return $strHTML;
    }

    /**
     * SelectBox с источниками данных
     *
     * @param $str_group_id
     * @return string
     */
    public function getSelectBoxGroups($str_group_id)
    {
        global $DB;
        $strHTML = '<select name="f_group_id" id="f_group_id" size="1">';
        $strHTML .= '<option value="">('.GetMessage("MIBIX_BATTLE_SELECT_GROUP").')</option>';

        $dbRes = $DB->Query("SELECT id, name_group FROM b_mibix_battle_group");
        while($arRes = $dbRes->Fetch())
        {
            $selectField = "";
            if ($arRes["id"]==$str_group_id) $selectField = " selected";
            $strHTML .= '<option value="'.$arRes["id"].'"'.$selectField.'>['.$arRes["id"].'] '.htmlspecialcharsEx($arRes["name_group"]).'</option>';
        }
        $strHTML .= '</select>';

        return $strHTML;
    }
}

class CMibixBattleComponentModel
{
    /**
     * Вывод произвольной битвы, относящейся к указанной группе
     *
     * @param $code_group
     * @return mixed
     */
    public function getBattleList($code_group)
    {
        global $DB;

        $sqlBattleCnt = "SELECT COUNT(*) as cnt FROM b_mibix_battle_battle bb LEFT JOIN b_mibix_battle_group gr ON (bb.group_id=gr.id) WHERE gr.code_group='".$code_group."' AND bb.date_finish>".$DB->CurrentTimeFunction()." AND bb.active='Y'";
        $resBattleCnt = $DB->Query($sqlBattleCnt);
        $arBattleCnt = $resBattleCnt->Fetch();

        // Если есть хотя бы одна активная битва
        if(intval($arBattleCnt["cnt"])>0)
        {
            // Активные битвы
            $strSql = "
				SELECT bb.*
				FROM b_mibix_battle_battle bb
				LEFT JOIN b_mibix_battle_group gr ON (bb.group_id=gr.id)
				WHERE gr.code_group='".$code_group."' AND bb.date_finish>".$DB->CurrentTimeFunction()." AND bb.active='Y'
                ORDER BY RAND()";
            $res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
        }
        else // Последняя завершенная битва
        {
            $strSql = "
				SELECT bb.*
				FROM b_mibix_battle_battle bb
				LEFT JOIN b_mibix_battle_group gr ON (bb.group_id=gr.id)
				WHERE gr.code_group='".$code_group."' AND bb.date_finish<=".$DB->CurrentTimeFunction()." AND bb.active='Y'
                ORDER BY date_finish DESC LIMIT 0,1";
            $res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
        }

        return $res;
    }

    /**
     * Получаем все изображения элемента из выбранных полей и свойств
     *
     * @param $battle_pictures
     * @param $arFieldElement
     * @return array
     */
    public function getBattlePictures($battle_pictures, $arFieldElement)
    {
        // Помещаем все поля и свойства картинок в массив $arSrcPictures
        $arSrcPictures = array();
        $arPropertyPicture = explode(",", $battle_pictures);
        if(count($arPropertyPicture)>0)
        {
            // Обрабатываем по отдельности каждое свойство из массива
            foreach($arPropertyPicture as $propPicture)
            {
                // Собираем пути к картинкам в массив
                switch($propPicture)
                {
                    case 'PREVIEW_PICTURE':
                        if($arFieldElement["PREVIEW_PICTURE"]!=NULL || $arFieldElement["PREVIEW_PICTURE"]!="")
                            $arSrcPictures[] = CFile::GetPath($arFieldElement["PREVIEW_PICTURE"]);
                        break;
                    case 'DETAIL_PICTURE':
                        if($arFieldElement["DETAIL_PICTURE"]!=NULL || $arFieldElement["DETAIL_PICTURE"]!="")
                            $arSrcPictures[] = CFile::GetPath($arFieldElement["DETAIL_PICTURE"]);
                        break;
                    default: // пути к картинкам из свойств
                        $picFiles = $arFieldElement["PROPERTIES"][$propPicture];
                        if(is_array($picFiles["VALUE"]) && count($picFiles["VALUE"])>0)
                        {
                            foreach($picFiles["VALUE"] as $picFile)
                            {
                                if($picFile!=NULL || $picFile!="")
                                    $arSrcPictures[] = CFile::GetPath($picFile);
                            }
                        }
                        elseif(!is_array($picFiles["VALUE"]))
                        {
                            if($picFiles["VALUE"]!=NULL || $picFiles["VALUE"]!="")
                                $arSrcPictures[] = CFile::GetPath($picFiles["VALUE"]);
                        }
                        break;
                }
            }
        }

        return $arSrcPictures; // на выходе массив с путями к изображениям
    }

    /**
     * Получаем заголовок для элемента битвы
     *
     * @param $nameOption
     * @param $arItem
     * @return string
     */
    public function getBattleTextValue($nameOption, $arItem)
    {
        $strReturn = '';

        if(strlen($nameOption))
        {
            if (preg_match("/^SELF@(.*?)/isU", $nameOption, $matches))
            {
                if(!empty($matches) && isset($matches[1]))
                {
                    if($matches[1]=="TITLE")
                        $strReturn = self::getTextDecode($arItem["NAME"]);

                    if($matches[1]=="DETAILPAGEURL")
                        $strReturn = $arItem["DETAIL_PAGE_URL"];
                }
            }
            elseif($nameOption == "NONE")
            {
                $strReturn = '';
            }
            elseif($nameOption == "PREVIEW_TEXT")
            {
                if(strlen($arItem["PREVIEW_TEXT"]))
                    $strReturn = self::getTextDecode($arItem["~PREVIEW_TEXT"]);
            }
            elseif($nameOption == "DETAIL_TEXT")
            {
                if(strlen($arItem["DETAIL_TEXT"]))
                    $strReturn = self::getTextDecode($arItem["~DETAIL_TEXT"]);
            }
            else
            {
                if($strTag = self::getPropertyStringValue($nameOption, $arItem, true, 255))
                    if(strlen($strTag))
                        $strReturn = self::getTextDecode($strTag);
            }
        }

        return $strReturn;
    }

    /**
     * Получаем количество проголосовавших
     */
    public function getBattleVotes($battleId, $itemId)
    {
        global $DB;

        $votes = 0;
        $strSql = "SELECT votes FROM b_mibix_battle_votes WHERE battle_id=".$battleId." AND element_id=".$itemId;
        $res_cnt = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
        if($res_cnt = $res_cnt->Fetch())
        {
            $votes = $res_cnt["votes"];
        }

        return $votes;
    }

    /**
     * Изменяет склонение слова в зависимости от числа
     * (пример: выдает 1,21,91 дама; 2,4,24,92 дамы; 5,9,11,19,25,95 дам)
     *
     * @param int $num число, слово за которым нужно склонять
     * @param array $expr массив значений слова (array('дама','дамы','дам'))
     * @param boolean $is_full : true - возвращает число со словом ; false - только слово (по умолчанию)
     *
     * @return string число и склоненное слово
     */
    public function getWordOfNum($num, $expr, $is_full = false)
    {
        $num = intval($num);
        $count = $num % 100;

        $prefix_num = "";
        if ($is_full) { $prefix_num = $num." "; }

        if ($count >= 5 && $count <= 20) { $result = $prefix_num.$expr['2']; }
        else
        {
            $count = $count % 10;
            if ($count == 1) { $result = $prefix_num.$expr['0']; }
            elseif ($count >= 2 && $count <= 4) { $result = $prefix_num.$expr['1']; }
            else { $result = $prefix_num.$expr['2']; }
        }
        return $result;
    }

    /**
     * Обновление счетчиков для элемента битвы в базе
     *
     * @param $battleSettings
     * @param $brandId
     */
    public function updateItemCounters($battleSettings, $brandId, $strPostedLink)
    {
        // Подсчитываем количество в соц.сетях
        $share_count = 0;

        // Fb Calculate
        if($battleSettings["enabled_fb"] == "Y")
        {
            $fb_query = "select total_count from link_stat where url='{$strPostedLink}'";
            $fb_url = "https://api.facebook.com/method/fql.query?query=" . rawurlencode($fb_query) . "&format=json";
            $fb_out = self::sendCurlRequest($fb_url);
            $fb_out = json_decode($fb_out);
            $share_count += intval($fb_out[0]->total_count);
        }

        // VK Calculate
        if($battleSettings["enabled_vk"] == "Y")
        {
            $vk_url = "http://vk.com/share.php?act=count&index=1&url=" . $strPostedLink;
            $vk_out = self::sendCurlRequest($vk_url);
            $vk_out = str_replace('VK.Share.count(1, ', '', $vk_out);
            $vk_out = str_replace(');', '', $vk_out);
            $share_count += intval($vk_out);
        }

        // Tw Calculate
        if($battleSettings["enabled_tw"] == "Y")
        {
            $tw_url = "http://cdn.api.twitter.com/1/urls/count.json?url=" . $strPostedLink;
            $tw_out = self::sendCurlRequest($tw_url);
            $tw_out = json_decode($tw_out);
            $share_count += intval($tw_out->count);
        }

        // MM Calculate
        if($battleSettings["enabled_ml"] == "Y")
        {
            $mm_url = "http://connect.mail.ru/share_count?url_list=" . urlencode($strPostedLink);
            $mm_out = self::sendCurlRequest($mm_url);
            preg_match("/\"shares\":([\d]+),\"clicks\":([\d]+)/i", $mm_out, $mathes);
            $share_count += intval($mathes[1]);
        }

        // OK Calculate
        if($battleSettings["enabled_ok"] == "Y")
        {
            $ok_url = "http://ok.ru/dk?st.cmd=extLike&uid=odklcnt0&ref=" . urlencode($strPostedLink);
            $ok_out = self::sendCurlRequest($ok_url);
            if (preg_match("/ODKL\.updateCount\(\'odklcnt0\',\'(\d+)\'/i", $ok_out, $matchesOk)) {
                $share_count += intval($matchesOk[1]);
            }
        }

        // PI Calculate
        if($battleSettings["enabled_pi"] == "Y")
        {
            $pi_url = "http://api.pinterest.com/v1/urls/count.json?callback=YOUR_CALLBACK&url=" . rawurlencode($strPostedLink);
            $pi_out = self::sendCurlRequest($pi_url);
            preg_match("/\"count\":([\d]+)/i", $pi_out, $mathes);
            $share_count += intval($mathes[1]);
        }

        // Записать в базу
        self::saveCountOfVote($battleSettings["id"], $brandId, $share_count);
    }

    /**
     * Функция для обновления всех счетчиков
     */
    public function updateAllItemCounters($CODE_GROUP="")
    {
        global $DB;

        // Если передан код группы, то выбираем по нему
        if(!empty($CODE_GROUP))
        {
            $CODE_GROUP = " AND gr.code_group='".$CODE_GROUP."'";
        }

        // Обходим список активных битв
        //TODO: Добавить условие с датой завершения больше текущей
        $dbRes = $DB->Query("SELECT bb.*
				FROM b_mibix_battle_battle bb
				LEFT JOIN b_mibix_battle_group gr ON (bb.group_id=gr.id)
				WHERE bb.active='Y'".$CODE_GROUP);
        while($arBattleItem = $dbRes->Fetch())
        {
            // Обходим каждый элемент битвы
            $arBattleItemsIDs = explode(",", $arBattleItem["battle_items"]);
            $arSelectItems = Array("ID", "IBLOCK_ID", "NAME", "DATE_ACTIVE_FROM", "PREVIEW_TEXT", "PREVIEW_PICTURE", "DETAIL_TEXT", "DETAIL_PICTURE", "DETAIL_PAGE_URL", "PROPERTY_*");
            $arFilterItems = Array("IBLOCK_ID"=>IntVal($arBattleItem["iblock_id"]), "ACTIVE"=>"Y", "ID"=>$arBattleItemsIDs);
            $resItems = CIBlockElement::GetList(Array(), $arFilterItems, false, false, $arSelectItems);
            while($obItem = $resItems->GetNextElement())
            {
                $arFieldsItem = $obItem->GetFields();

                // Ссылка на товар
                $strPostedLink = $arBattleItem["battle_site"] . CMibixBattleComponentModel::getBattleTextValue($arBattleItem["battle_links"], $arFieldsItem) . "#" . $arBattleItem["id"];

                // Обновление счетчика голосов для элемента в базе
                CMibixBattleComponentModel::updateItemCounters($arBattleItem, $arFieldsItem["ID"], $strPostedLink);
            }
        }
    }

    /**
     * Запись информации о колчичестве проголосовавших в базу
     *
     * @param $battleId
     * @param $brandId
     * @param $counts
     */
    private function saveCountOfVote($battleId, $brandId, $counts)
    {
        global $DB;

        $arFields = Array(
            "battle_id" => $battleId,
            "element_id" => $brandId,
            "votes" => $counts,
        );

        // Обновление количества в базе
        $strSql = "SELECT votes FROM b_mibix_battle_votes WHERE battle_id=".$battleId." AND element_id=".$brandId;
        $res_cnt = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
        if($res_cnt = $res_cnt->Fetch())
        {
            // Проверяем наличие записи и обновляем ее
            $strUpdate = $DB->PrepareUpdate("b_mibix_battle_votes", $arFields);
            if (strlen($strUpdate)>0)
            {
                $strUpdSql = "UPDATE b_mibix_battle_votes SET ".$strUpdate." WHERE battle_id=".$battleId." AND element_id=".$brandId;
                if(!$DB->Query($strUpdSql, false, "File: ".__FILE__."<br>Line: ".__LINE__)) return false;
            }
        }
        else
        {
            // Создаем запись, если нет
            $DB->Add("b_mibix_battle_votes", $arFields);
        }
    }

    /**
     * Отправка запроса с целью получить статистику по ссылкам
     *
     * @param $url
     * @return mixed
     */
    private function sendCurlRequest($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        $output = curl_exec($ch);
        curl_close($ch);

        return $output;
    }

    /**
     * Получаем значение нужного свойства инфоблока с типом "строка"
     *
     * @param $PROPERTY
     * @param $arItem
     * @return string
     */
    private function getPropertyStringValue($PROPERTY, $arItem)
    {
        // Если у элемента существует свойство
        $text = '';
        $arProperty = $arItem["PROPERTIES"][$PROPERTY];
        if (isset($arProperty) && !empty($arProperty))
        {
            $text = is_array($arProperty['VALUE']) ? implode(', ', $arProperty['VALUE']) : $arProperty['VALUE'];
        }
        return $text;
    }

    /**
     * Получаем валидный текст с учетом кодировки сайта
     *
     * @param $text
     * @return string
     */
    private function getTextDecode($text)
    {
        global $APPLICATION;

        if(strlen($text))
        {
            $text = TruncateText($text, 255);

            // Определяем кодировку сайта
            $siteCharset = 'windows-1251';
            if (defined('BX_UTF') && BX_UTF == true) {
                $siteCharset = 'UTF-8';
            }
            $text = $APPLICATION->ConvertCharset($text, $siteCharset, 'UTF-8');
        }
        return $text;
    }
}
?>