<?php
if (!CModule::IncludeModule("sale") || !CModule::IncludeModule("iblock")) return false;
IncludeModuleLangFile(__FILE__);

global $DBType;

/**
 * Класс с основной логикой модуля.
 */
class CMibixPhotoposterSettings
{
    // Для разных сообщений и ошибок
    private $arMsg = array();

    /**
     * Получаем массив сообщений об ошибках
     *
     * @return array
     */
    public function getArMsg()
    {
        return $this->arMsg;
    }

    /**
     * Получаем настройки из базы
     *
     * @return mixed
     */
    public function GetSetting()
    {
        global $DB;
        $strSql = "SELECT * FROM b_mibix_photoposter_settings WHERE id='1'";

        return $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
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
        $strHTML .= '<option>('.GetMessage("MIBIX_PP_INC_SELECT_IBLOCK").')</option>';

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
     * SelectBox с разделами запрашиваемого инфоблока
     *
     * @param $name string Название переменной
     * @param $str_iblock_id int ID инфоблока
     * @param $str_sections
     * @return string HTML-код элемента
     */
    public function getSelectBoxSections($name, $str_iblock_id, $str_sections)
    {
        $arSectSelected = explode(",", $str_sections);

        $strHTML = '<select class="typeselect" multiple="" name="'.$name.'[]" id="'.$name.'" size="6">';

        // На редактировании возвращаем все разделы
        if($str_iblock_id > 0)
        {
            $arParams = array();
            $arParams['IBLOCK_ID'] = $str_iblock_id;

            // получаем разделы инфоблока по его ID и генерируем SelectBox
            $dbRes = $rsSections = CIBlockSection::GetList(array('LEFT_MARGIN'=>'ASC'), $arParams);
            while ($arRes = $dbRes->GetNext())
            {
                // выделенно или нет
                $selectField = "";
                if (in_array($arRes['ID'],$arSectSelected)) $selectField = " selected";
                // генерация значения
                $strHTML .= '<option value="'.$arRes['ID'].'"'.$selectField.'>'.str_repeat("..", ($arRes['DEPTH_LEVEL']-1)).trim($arRes['NAME']).'</option>';
            }
        }
        $strHTML .= '</select>';
        return $strHTML;
    }

    /**
     * Поля и свойства для поля "Текста публикации"
     *
     * @param $IBLOCK_ID
     * @param $SELECTED
     * @return string
     */
    public function getSelectBoxPropertyText($IBLOCK_ID, $SELECTED)
    {
        $strHTML = '<select name="f_public_text" id="f_public_text" size="1">';

        if(CModule::IncludeModule("iblock") && $IBLOCK_ID)
        {
            $strHTML .= '<option value="NONE"' . (($SELECTED=="NONE") ? ' selected' : '') . '>('.GetMessage("MIBIX_PP_INC_WITHOUT_TEXT").')</option>';
            $strHTML .= '<option value="PREVIEW_TEXT"' . (($SELECTED=="PREVIEW_TEXT") ? ' selected' : '') . '>[PREVIEW_TEXT] '.GetMessage("MIBIX_PP_INC_PREVIEW_TEXT").'</option>';
            $strHTML .= '<option value="DETAIL_TEXT"' . (($SELECTED=="DETAIL_TEXT") ? ' selected' : '') . '>[DETAIL_TEXT] '.GetMessage("MIBIX_PP_INC_DETAIL_TEXT").'</option>';

            $iblockProps = CIBlockProperty::GetList(Array("sort"=>"asc","name"=>"asc"), Array("ACTIVE"=>"Y", "IBLOCK_ID"=>$IBLOCK_ID, "PROPERTY_TYPE"=>"S"));
            while ($arRes = $iblockProps->GetNext())
            {
                $selectField = "";
                if ($arRes["CODE"]==$SELECTED) $selectField = " selected";

                $strHTML .= '<option value="'.$arRes["CODE"].'"'.$selectField.'>['.$arRes["CODE"].'] '.$arRes["NAME"].'</option>';
            }
        }
        $strHTML .= '</select>';
        return $strHTML;
    }

    /**
     * Список доступных сайтов
     *
     * @param $SELECTED
     * @return string
     */
    public function getSelectSiteID($SELECTED)
    {
        $strHTML = '<select name="f_site_id" id="f_site_id">';
        $rsSites = CSite::GetList($by="sort", $order="desc", Array("ACTIVE"=>"Y"));
        while($arSite = $rsSites->Fetch())
        {
            $selectField = "";
            if ($arSite["ID"] == $SELECTED) $selectField = " selected";
            $strHTML .= '<option value="'.$arSite["ID"].'"'.$selectField.'>['.$arSite["ID"]."] ".$arSite["NAME"].'</option>';
        }
        $strHTML .= '</select>';
        return $strHTML;
    }

    /**
     * Поля и свойства для поля "Картинки для публикации"
     *
     * @param $IBLOCK_ID
     * @param $SELECTED
     * @return string
     */
    public function getSelectBoxPropertyPictures($IBLOCK_ID, $SELECTED)
    {
        $arPictures = explode(",", $SELECTED);

        $strHTML = '<select multiple="" name="f_public_pictures[]" id="f_public_pictures" size="4">';

        if(CModule::IncludeModule("iblock") && $IBLOCK_ID)
        {
            $strHTML .= '<option value="PREVIEW_PICTURE"' . (in_array("PREVIEW_PICTURE",$arPictures) ? ' selected' : '') . '>[PREVIEW_PICTURE] '.GetMessage("MIBIX_PP_INC_PREVIEW_PICTURE").'</option>';
            $strHTML .= '<option value="DETAIL_PICTURE"' . (in_array("DETAIL_PICTURE",$arPictures) ? ' selected' : '') . '>[DETAIL_PICTURE] '.GetMessage("MIBIX_PP_INC_DETAIL_PICTURE").'</option>';

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
     * Список SelectBox доступных альбомов группы из соц.сети "Вконтакте"
     *
     * @param $SELECTED
     * @return string
     */
    public function getSelectBoxPropertyAlbumVK($ALBUM_CHECK, $SELECTED)
    {
        $strAlbHTML = "";

        // Получаем список альбомов текущей группы
        CMibixPhotoposterPhotoExport::InitToken();
        $vk_group_id = CMibixPhotoposterPhotoExport::GetGroupIdVK();
        if($vk_group_id > 0)
        {
            $vkAlbums = CMibixPhotoposterPhotoExport::vkPhotosGetAlbums($vk_group_id);
            if(property_exists($vkAlbums, "response"))
            {
                foreach($vkAlbums->response as $vkAlb)
                {
                    $strAlbHTML .= '<option value="'.$vkAlb->aid.'"' . (($SELECTED==$vkAlb->aid) ? ' selected' : '') . '>'.$vkAlb->title.'</option>';
                }
            }
        }

        $strHTML = '<select name="f_vk_album_exist" id="f_vk_album_exist" size="1"'.($ALBUM_CHECK != "EXIST" ? 'disabled' : '').'>';
        $strHTML .= '<option value="NONE"' . (($SELECTED=="NONE") ? ' selected' : '') . '>('.GetMessage("MIBIX_PP_INC_SELECT_ALBUM").')</option>';
        $strHTML .= $strAlbHTML;
        $strHTML .= '</select>';
        return $strHTML;
    }

    /**
     * Список SelectBox доступных альбомов группы из соц.сети "Вконтакте"
     *
     * @param $SELECTED
     * @return string
     */
    public function getSelectBoxPropertyAlbumFB($ALBUM_CHECK, $SELECTED)
    {
        $strAlbHTML = "";

        // Получаем список альбомов текущей группы
        CMibixPhotoposterPhotoExport::InitToken();
        $fbAlbums = CMibixPhotoposterPhotoExport::fbPhotosGetAlbums();
        if($fbAlbums)
        {
            if(property_exists($fbAlbums, "data"))
            {
                foreach($fbAlbums->data as $fbAlb)
                {
                    $strAlbHTML .= '<option value="'.$fbAlb->id.'"' . (($SELECTED==$fbAlb->id) ? ' selected' : '') . '>'.$fbAlb->name.'</option>';
                    //TODO: $fbAlb->can_upload;
                }
            }
        }

        $strHTML = '<select name="f_fb_album_exist" id="f_fb_album_exist" size="1"'.($ALBUM_CHECK != "EXIST" ? 'disabled' : '').'>';
        $strHTML .= '<option value="NONE"' . (($SELECTED=="NONE") ? ' selected' : '') . '>('.GetMessage("MIBIX_PP_INC_SELECT_ALBUM").')</option>';
        $strHTML .= $strAlbHTML;
        $strHTML .= '</select>';
        return $strHTML;
    }

    /**
     * Обновление записи табилцы общих настроек
     *
     * @param $arFields
     * @return bool
     */
    public function Update($arFields)
    {
        global $DB;

        // Преобразуем поле с именем группы, если они есть (для Вконтакте)
        if(strlen($arFields["vk_wall"])>0)
        {
            // Вытаскиваем из ссылки название группы
            if(strripos($arFields["vk_wall"],"http")!==false)
            {
                $arUrlGroup = parse_url($arFields["vk_wall"]);
                $arUrlGroup["path"] = str_replace("/", "", $arUrlGroup["path"]);
                $arFields["vk_wall"] = $arUrlGroup["path"];
            }
            else
            {
                $arFields["vk_wall"] = str_replace("vk.com", "", $arFields["vk_wall"]);
                $arFields["vk_wall"] = str_replace("/", "", $arFields["vk_wall"]);
            }
        }

        // Преобразуем поле с именем группы, если они есть (для Фейсбука)
        if(strlen($arFields["fb_wall"])>0)
        {
            // Вытаскиваем из ссылки название группы
            if(strripos($arFields["fb_wall"],"http")!==false)
            {
                $arUrlGroup = parse_url($arFields["fb_wall"]);
                $arUrlGroup["path"] = str_replace("/", "", $arUrlGroup["path"]);
                $arFields["fb_wall"] = $arUrlGroup["path"];
            }
            else
            {
                $arFields["fb_wall"] = str_replace("fb.com", "", $arFields["fb_wall"]);
                $arFields["fb_wall"] = str_replace("www.", "", $arFields["fb_wall"]);
                $arFields["fb_wall"] = str_replace("/", "", $arFields["fb_wall"]);

                // избавляемся от параметров, начинающихся со знака вопроса
                $posWrongParam = strpos($arFields["fb_wall"], "?");
                if ($posWrongParam !== false)
                {
                    $arFields["fb_wall"] = substr($arFields["fb_wall"], 0, ($posWrongParam-1));
                }
            }
        }

        // Проверяем заполненные поля на ошибки и возвращаем false в случае их наличия, при этом сами ошибки сохраняем в переменной класса
        if(!$this->CheckFields($arFields)) return false;

        // Обновляем дату редактирования настроек
        $arFields["~date_update"] = $DB->CurrentTimeFunction();

        // Преобразуем массив значений разделов в строку
        $arFields["include_sections"] = $this->MSelectPrepare($arFields["include_sections"]);
        $arFields["exclude_sections"] = $this->MSelectPrepare($arFields["exclude_sections"]);
        $arFields["public_pictures"] = $this->MSelectPrepare($arFields["public_pictures"]);

        // Определяем, есть ли уже запись с настройками (создается и используется только запись с Id=1)
        $rsSettings = $DB->Query("SELECT run_method,run_time,run_period FROM b_mibix_photoposter_settings WHERE id='1'",true);
        if($rowSettings = $rsSettings->Fetch())
        {
            // Регистрируем или удаляем агента запуска задания, в зависимости от выбранных параметров
            // проверяем, изменил ли пользователь метдо запуска, если изменил на Агента:
            if($arFields["run_method"]=="AGENT" && $rowSettings["run_method"] != $arFields["run_method"]) // пользователь меняет метод запуска с CRON на АГЕНТ (добавляем агента)
            {
                self::createAgent($arFields);
            }
            elseif($arFields["run_method"]=="CRON" && $rowSettings["run_method"] != $arFields["run_method"]) // пользователь меняет метод запуска АГЕНТ на CRON (удаляем агента)
            {
                // удаляем агента, когда пользователь переключается на CRON-запуск
                self::deleteAgent();
            }
            elseif($arFields["run_method"]=="AGENT" && ($rowSettings["run_time"] != $arFields["run_time"] || $rowSettings["run_period"] != $arFields["run_period"]))
            {
                // если поменялось время запуска или период запуска, удаляем агент и создаем заново с новыми параметрами
                self::deleteAgent();
                self::createAgent($arFields);
            }

            // обновляем запись
            $strUpdate = $DB->PrepareUpdate("b_mibix_photoposter_settings", $arFields);
            if (strlen($strUpdate)>0)
            {
                $strSql = "UPDATE b_mibix_photoposter_settings SET ".
                    $strUpdate.", ".
                    "   date_update=".$DB->GetNowFunction()." ".
                    "WHERE id='1'";
                if(!$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__)) return false;
            }
        }
        else
        {
            // добавляем запись
            $DB->Add("b_mibix_photoposter_settings", $arFields);
        }

        return true;
    }

    /**
     * Создание "Агента" переодического запуска по расписанию, заданного в настройках
     *
     * @param $arFields
     */
    private function createAgent($arFields)
    {
        // Получаем из настроек время запуска в секундах, начиная с 00:00
        $tSetting = preg_match('#^([0-9]{2}):([0-9]{2})$#', $arFields["run_time"], $regs) ? $regs[1] * 60 + $regs[2] : 0;
        $setSec = $tSetting * 60;

        // Получаем количество секунд, прошедшее с 00:00 сегодняшнего дня
        $leftSecToday = ((date('H') * 60 + date('i')) * 60) + (5 * 60); // + 5 минут

        // Получаем количество секунд, с 1970 до 00:00 сегодняшнего дня
        $today = mktime(0,0,0,date("m"),date("d"),date("Y"));
        //$diff = (CTimeZone::Enabled() ? CTimeZone::GetOffset() : 0) + date('Z');
        //$today += $diff;

        // Теперь определяем время и дату следующего запуска агента
        if($setSec < $leftSecToday)
        {
            // запускаем завтра
            $yesterday = $today + 86400;
            $yesterday += $setSec;
            $startTime = date('d.m.Y H:i:s', $yesterday);
        }
        else
        {
            // запускаем сегодня
            $today += $setSec;
            $startTime = date('d.m.Y H:i:s', $today);
        }

        // Рассчитываем период запуска агента
        switch($arFields["run_period"])
        {
            case 'PER2': // через день
                $runPeriod = 86400 * 2;
                break;
            case 'PER3': // раз в 3 дня
                $runPeriod = 86400 * 3;
                break;
            case 'PER4': // еженедельно
                $runPeriod = 86400 * 7;
                break;
            default: // каждый день
                $runPeriod = 86400;
                break;
        }

        // Регистрируем агента
        CAgent::Add(
            array(
                "NAME" => "CMibixPhotoposterPhotoExport::Post();",    // имя функции
                "MODULE_ID" => "mibix.photoposter",       // идентификатор модуля
                "ACTIVE" => "Y",                        // агент активен
                "NEXT_EXEC" => $startTime,              // дата первого запуска
                "AGENT_INTERVAL" => $runPeriod,         // интервал запуска
                "IS_PERIOD" => "Y"                      // периодический
            )
        );
    }

    /**
     * Удаляем "Агент"
     */
    private function deleteAgent()
    {
        // удаляем агента, когда пользователь переключается на CRON-запуск
        CAgent::RemoveAgent("CMibixPhotoposterPhotoExport::Post();", "mibix.photoposter");
    }

    /**
     * Проверка полей формы перед сохранением
     *
     * @param $arFields
     * @return bool
     */
    private function CheckFields($arFields)
    {
        global $DB;

        // Массив ошибок
        $this->arMsg = array();

        // Инфоблок
        if($arFields["iblock_id"] < 1)
        {
            $this->arMsg[] = array("id"=>"iblock_id", "text"=>GetMessage("MIBIX_PP_ERR_IBLOCK_EMPTY"));
        }
        // Текст публикации (поле или свойство)
        if(strlen($arFields["public_text"]) > 255)
        {
            $this->arMsg[] = array("id"=>"public_text", "text"=>GetMessage("MIBIX_PP_ERR_TEXT_LIMIT"));
        }
        // Текст публикации или картинки должны быть заполнены (одно или оба)
        if(strlen($arFields["public_text"]) == 0 && count($arFields["public_pictures"]) == 0)
        {
            $this->arMsg[] = array("id"=>"public_text", "text"=>GetMessage("MIBIX_PP_ERR_TEXT_OR_PICTURE"));
        }
        // Время запуска
        if($arFields["run_method_agent"]=="AGENT" && !preg_match('/^(\d{2}):(\d{2})$/', $arFields["run_time"], $ok))
        {
            $this->arMsg[] = array("id"=>"run_time", "text"=>GetMessage("MIBIX_PP_ERR_RUNTIME_LIMIT"));
        }
        // Период запуска
        if($arFields["run_method_agent"]=="AGENT" && strlen($arFields["run_period"]) == 0)
        {
            $this->arMsg[] = array("id"=>"run_period", "text"=>GetMessage("MIBIX_PP_ERR_RUN_PERIOD"));
        }

        // Если включена возможность брать настройки подключения из другого модуля
        if($arFields["use_sp"] == "Y")
        {
            // Проверка заполненности полей в модуле "Авто постинг товаров в соц.сетях"
            $rsSettings = $DB->Query("SELECT vk_post,vk_token,vk_wall,fb_post,fb_token,fb_wall FROM b_mibix_socposter_settings WHERE id='1'",true);
            if($rowSettings = $rsSettings->Fetch())
            {
                if($rowSettings["vk_post"] != "Y" && $rowSettings["fb_post"] != "Y")
                {
                    $this->arMsg[] = array("id"=>"use_sp", "text"=>GetMessage("MIBIX_PP_ERR_SP_BOTH_DISABLED"));
                }
                if(strlen($rowSettings["vk_token"]) < 1 && strlen($rowSettings["fb_token"]) < 1)
                {
                    $this->arMsg[] = array("id"=>"use_sp", "text"=>GetMessage("MIBIX_PP_ERR_SP_BOTH_TOKEN_EMPTY"));
                }
                if(strlen($rowSettings["vk_wall"]) < 1 && strlen($rowSettings["fb_wall"]) < 1)
                {
                    $this->arMsg[] = array("id"=>"use_sp", "text"=>GetMessage("MIBIX_PP_ERR_SP_BOTH_WALL_EMPTY"));
                }
            }
            else
            {
                $this->arMsg[] = array("id"=>"use_sp", "text"=>GetMessage("MIBIX_PP_ERR_SP_TABLE_EMPTY"));
            }
        }
        else
        {
            // Настройки Вконтакте
            if($arFields["vk_post"] == "Y")
            {
                if(strlen($arFields["vk_token"]) == 0)
                {
                    $this->arMsg[] = array("id"=>"vk_token", "text"=>GetMessage("MIBIX_PP_ERR_VK_TOKEN_EMPTY"));
                }
                if(strlen($arFields["vk_token"]) > 255)
                {
                    $this->arMsg[] = array("id"=>"vk_token", "text"=>GetMessage("MIBIX_PP_ERR_VK_TOKEN_LIMIT"));
                }
                if(strlen($arFields["vk_wall"]) == 0)
                {
                    $this->arMsg[] = array("id"=>"vk_wall", "text"=>GetMessage("MIBIX_PP_ERR_VK_WALL_EMPTY"));
                }
                if(strlen($arFields["vk_wall"]) > 255)
                {
                    $this->arMsg[] = array("id"=>"vk_wall", "text"=>GetMessage("MIBIX_PP_ERR_VK_WALL_LIMIT"));
                }
            }
            // Настройки Фейсбук
            if($arFields["fb_post"] == "Y")
            {
                if(strlen($arFields["fb_token"]) == 0)
                {
                    $this->arMsg[] = array("id"=>"fb_token", "text"=>GetMessage("MIBIX_PP_ERR_FB_TOKEN_EMPTY"));
                }
                if(strlen($arFields["fb_token"]) > 255)
                {
                    $this->arMsg[] = array("id"=>"fb_token", "text"=>GetMessage("MIBIX_PP_ERR_FB_TOKEN_LIMIT"));
                }
                if(strlen($arFields["fb_wall"]) == 0)
                {
                    $this->arMsg[] = array("id"=>"fb_wall", "text"=>GetMessage("MIBIX_PP_ERR_FB_WALL_EMPTY"));
                }
                if(strlen($arFields["fb_wall"]) > 255)
                {
                    $this->arMsg[] = array("id"=>"fb_wall", "text"=>GetMessage("MIBIX_PP_ERR_FB_WALL_LIMIT"));
                }
            }
        }

        // Если ошибок нет, то возвращаем true
        if (count($this->arMsg)>0)
        {
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
}

/**
 * Class CMibixPhotoposterPhotoExport
 *
 * (для отрпавки запросов к API)
 */
class CMibixPhotoposterPhotoExport
{
    private static $POST_TO_BOTH_ELEMENT_ID;
    private static $ACCESS_VK_TOKEN;
    private static $ACCESS_FB_TOKEN;

    private static $GROUP_VK;
    private static $GROUP_FB;

    /**
     * Инициализация класса
     */
    public function InitToken()
    {
        global $DB;

        self::$ACCESS_VK_TOKEN = false;
        self::$ACCESS_FB_TOKEN = false;
        self::$GROUP_VK = false;
        self::$GROUP_FB = false;

        $rsSettings = $DB->Query("SELECT vk_post,vk_token,vk_wall,fb_post,fb_token,fb_wall,use_sp FROM b_mibix_photoposter_settings WHERE id='1'",true);
        if($rowSettings = $rsSettings->Fetch())
        {
            // Используем настройки модуля "Авто постинг товаров в соц. сети
            if($rowSettings["use_sp"] == "Y")
            {
                $rsSettingsSP = $DB->Query("SELECT vk_post,vk_token,vk_wall,fb_post,fb_token,fb_wall FROM b_mibix_socposter_settings WHERE id='1'",true);
                if($rowSettingsSP = $rsSettingsSP->Fetch())
                {
                    if($rowSettingsSP["vk_post"] == "Y" && strlen($rowSettingsSP["vk_token"]) > 0 && strlen($rowSettingsSP["vk_wall"]) > 0)
                    {
                        self::$ACCESS_VK_TOKEN = $rowSettingsSP["vk_token"];
                        self::$GROUP_VK = $rowSettingsSP["vk_wall"];
                    }
                    if($rowSettingsSP["fb_post"] == "Y" && strlen($rowSettingsSP["fb_token"]) > 0 && strlen($rowSettingsSP["fb_wall"]) > 0)
                    {
                        self::$ACCESS_FB_TOKEN = $rowSettingsSP["fb_token"];
                        self::$GROUP_FB = $rowSettingsSP["fb_wall"];
                    }
                }
            }
            else // Используем собственные настройки модуля
            {
                if($rowSettings["vk_post"] == "Y" && strlen($rowSettings["vk_token"]) > 0 && strlen($rowSettings["vk_wall"]) > 0)
                {
                    self::$ACCESS_VK_TOKEN = $rowSettings["vk_token"];
                    self::$GROUP_VK = $rowSettings["vk_wall"];
                }
                if($rowSettings["fb_post"] == "Y" && strlen($rowSettings["fb_token"]) > 0 && strlen($rowSettings["fb_wall"]) > 0)
                {
                    self::$ACCESS_FB_TOKEN = $rowSettings["fb_token"];
                    self::$GROUP_FB = $rowSettings["fb_wall"];
                }
            }
        }
    }

    /**
     * Получаем имя группы "Вконтакте"
     *
     * @return mixed
     */
    public function GetGroupIdVK()
    {
        $idGroup = 0;

        if(self::$GROUP_VK)
        {
            $resGroupId = self::groupsGetById(self::$GROUP_VK);
            if(count($resGroupId->response)>0)
            {

                $idGroup = intval($resGroupId->response[0]->id);
                if ($idGroup == 0)
                    $idGroup = intval($resGroupId->response[0]->gid);
            }
        }

        return $idGroup;


    }

    /**
     * Получаем имя группы "Фейсбук"
     *
     * @return mixed
     */
    public function GetGroupIdFB()
    {
        return self::$GROUP_FB;
    }

    /**
     * Событие, вызываемое при добавлении нового товара
     *
     * @param $ID
     * @param $arFields
     */
    public function PostOnProductAddHandler($ID, $arFields)
    {
        global $DB;

        $rsSettings = $DB->Query("SELECT include_sections,exclude_sections,event_newitem FROM b_mibix_photoposter_settings WHERE id='1'",true);
        if($rowSettings = $rsSettings->Fetch())
        {
            // Если стоит опция публикации по этому событию
            if($rowSettings["event_newitem"]=="Y" && intval($ID)>0)
            {
                $resItem = CIBlockElement::GetByID(IntVal($ID));
                if($arItem = $resItem->GetNext())
                {
                    // разделы с элементами (товарами), которые выбрал пользователь
                    $arIncSections = explode(",", $rowSettings["include_sections"]);

                    // разделы с элементами (товарами), которые исключил пользователь
                    $arExcSections = explode(",", $rowSettings["exclude_sections"]);

                    // проверка необходимости публикации
                    $needPost = false;
                    if(count($arIncSections)<1) // разделы не выбраны (по умолчанию - считаются выбранны все)
                    {
                        $needPost = true;
                    }
                    if(count($arIncSections)>0 && in_array($arItem['IBLOCK_SECTION_ID'], $arIncSections)) // выбраны некоторые разделы
                    {
                        $needPost = true;
                    }
                    if(in_array($arItem['IBLOCK_SECTION_ID'], $arExcSections)) // входит в исключенный раздел
                    {
                        $needPost = false;
                    }

                    // публикация
                    if($needPost)
                    {
                        self::Post($arItem["ID"]);
                    }

                    // DEBUG
                    if (defined('MIBIX_DEBUG_PHOTOPOSTER') && MIBIX_DEBUG_PHOTOPOSTER==true)
                    {
                        self::writeLOG("[INFO] function:".__FUNCTION__." (need_post)", $needPost);
                        self::writeLOG("[INFO] function:".__FUNCTION__." (shop_item_id:".$ID.")", $arItem);
                    }
                }
            }

            // DEBUG
            if (defined('MIBIX_DEBUG_PHOTOPOSTER') && MIBIX_DEBUG_PHOTOPOSTER==true)
            {
                self::writeLOG("[INFO] function:".__FUNCTION__." (row_settings)", $rowSettings);
            }
        }
    }

    /**
     * (Точка входа) Публикация в соц. сети
     *
     * @param int $ITEM_ID_FOR_POST - ID товара который нужно опубликовать без выбора
     * @return string
     */
    public function Post($ITEM_ID_FOR_POST=0)
    {
        global $DB;
        self::$POST_TO_BOTH_ELEMENT_ID = FALSE;

        // Ищем в настройках параметры для доступа к соц.сетям
        $rsSettings = $DB->Query("SELECT * FROM b_mibix_photoposter_settings WHERE id='1'",true);
        if($rowSettings = $rsSettings->Fetch())
        {
            // DEBUG
            if (defined('MIBIX_DEBUG_PHOTOPOSTER') && MIBIX_DEBUG_PHOTOPOSTER==true)
            {
                self::writeLOG("[INFO] function:".__FUNCTION__." [0](row_settings)", $rowSettings);
            }

            // Если установлено, что настройки берутся в модуле "Авто постинг товаров в соц.сети"
            if($rowSettings["use_sp"]=="Y")
            {
                $rsSettingsSP = $DB->Query("SELECT vk_post,vk_token,vk_wall,fb_post,fb_token,fb_wall FROM b_mibix_socposter_settings WHERE id='1'",true);
                if($rowSettingsSP = $rsSettingsSP->Fetch())
                {
                    $rowSettings["vk_post"] = $rowSettingsSP["vk_post"];
                    $rowSettings["vk_token"] = $rowSettingsSP["vk_token"];
                    $rowSettings["vk_wall"] = $rowSettingsSP["vk_wall"];
                    $rowSettings["fb_post"] = $rowSettingsSP["fb_post"];
                    $rowSettings["fb_token"] = $rowSettingsSP["fb_token"];
                    $rowSettings["fb_wall"] = $rowSettingsSP["fb_wall"];
                }
            }

            // Если включен постинг Вконтакте
            if($rowSettings["vk_post"]=="Y")
            {
                // запоминаем токен доступа
                self::$ACCESS_VK_TOKEN = $rowSettings["vk_token"];
                $postElementID = 0;

                // узнаем ID-группы по ее имени
                $resGroupId = self::groupsGetById($rowSettings["vk_wall"]);
                if(count($resGroupId->response)>0)
                {
                    // DEBUG
                    if (defined('MIBIX_DEBUG_PHOTOPOSTER') && MIBIX_DEBUG_PHOTOPOSTER==true)
                    {
                        self::writeLOG("[INFO] function:".__FUNCTION__." v[1](res_group_id)", $resGroupId);
                    }

                    $idGroup = 0;
                    $idGroup = intval($resGroupId->response[0]->id);
                    if($idGroup==0) $idGroup = intval($resGroupId->response[0]->gid);
                    if ($idGroup > 0)
                    {
                        // DEBUG
                        if (defined('MIBIX_DEBUG_PHOTOPOSTER') && MIBIX_DEBUG_PHOTOPOSTER==true)
                        {
                            self::writeLOG("[INFO] function:".__FUNCTION__." v[2](id_group)", $idGroup);
                        }

                        // Если явно указан ID, то используем его
                        if($ITEM_ID_FOR_POST > 0)
                        {
                            $postElementID = intval($ITEM_ID_FOR_POST);
                        }
                        else // Если явно не указан ID, определяем его
                        {
                            // получаем массив элементов, которые можно публиковать
                            $arElements = self::findElementsForPost($rowSettings);

                            // Если элементы найдены, выбираем произвольно один из них
                            if(count($arElements)>0)
                            {
                                $rndKeyElements = array_rand($arElements); // выбираем произвольный ключ из массива
                                $postElementID = $arElements[$rndKeyElements];

                                // если в настройках установлен постинг одинаковых элементов для всех соц.сетей
                                if($rowSettings["diff_items"]=="Y")
                                {
                                    self::$POST_TO_BOTH_ELEMENT_ID = $postElementID;
                                }
                            }
                        }

                        // DEBUG
                        if (defined('MIBIX_DEBUG_PHOTOPOSTER') && MIBIX_DEBUG_PHOTOPOSTER==true)
                        {
                            self::writeLOG("[INFO] function:".__FUNCTION__." v[2](ITEM_ID_FOR_POST)", $ITEM_ID_FOR_POST);
                            self::writeLOG("[INFO] function:".__FUNCTION__." v[2](postElementID)", $postElementID);
                        }

                        // получаем полную информацию о выбранном элементе (товаре)
                        $resFieldElement = CIBlockElement::GetByID($postElementID);
                        if($obFieldElement = $resFieldElement->GetNextElement())
                        {
                            // Получаем текст для публикации в зависимости от настроек пользователя
                            $messageText = self::getTextForPost($rowSettings["public_text"], $obFieldElement);

                            // Получаем ссылку на товар (элемент) в каталоге, если это задано в настройках пользователем (или false)
                            $linkItemPost = self::getLinkItemPost($rowSettings["link_post"], $rowSettings["site_id"], $obFieldElement);

                            // Формируем подпись к фотографии
                            $caption = $messageText . "\r\n" . $linkItemPost;

                            // Получаем все картинки элемента и записываем ссылки на них в массив
                            $arSrcPictures = self::getPicturesForPost($rowSettings["public_pictures"], $obFieldElement);

                            // Проверяем, есть ли у товара изображения
                            if (is_array($arSrcPictures) && count($arSrcPictures) > 0)
                            {
                                // Получаем ID альбома для группы Вконтакте (если альбома нет, то создается новый и возвращается его ID)
                                $album_id = self::selectOrCreateAlbumVK($rowSettings, $obFieldElement, $idGroup);

                                // Если есть картинки, загружаем их на сервер "Вконтакте" через API. Затем формируем массив аттачментов для публикации.
                                // Получаем адрес сервера для загрузки изображений
                                $uploadServer = self::photosGetUploadServer($album_id, $idGroup);
                                if($uploadServer && $album_id > 0)
                                {
                                    // DEBUG
                                    if (defined('MIBIX_DEBUG_PHOTOPOSTER') && MIBIX_DEBUG_PHOTOPOSTER==true)
                                    {
                                        self::writeLOG("[INFO] function:".__FUNCTION__." v[6](uploadServer)", $uploadServer);
                                    }

                                    // если ответ с адресом сервера на загрузку пришел
                                    if(property_exists($uploadServer, "response"))
                                    {
                                        // формируем массив аттачментов всех изображений из массива путей для картинок элемента
                                        $upload_url = $uploadServer->response->upload_url;
                                        foreach($arSrcPictures as $srcPicture)
                                        {
                                            // каждое изображение загружаем на сервер
                                            $image_url = dirname(dirname(dirname(dirname(__FILE__)))) . $srcPicture;
                                            $arImgUploaded = self::postUploadImage($upload_url, $image_url);

                                            // публикуем изображение на стене группы и запоминаем его в базе
                                            if(count($arImgUploaded)>0)
                                            {
                                                $imgPhoto = self::photosSaveVK($album_id, $idGroup, $caption, $arImgUploaded["photos_list"], $arImgUploaded["server"], $arImgUploaded["hash"]);
                                                if($imgPhoto)
                                                {
                                                    if(property_exists($imgPhoto, "response"))
                                                    {
                                                        $album_id = 0;
                                                        $photo_id = 0;
                                                        if(property_exists($imgPhoto->response[0], "id") && property_exists($imgPhoto->response[0], "album_id"))
                                                        {
                                                            $album_id = $imgPhoto->response[0]->album_id;
                                                            $photo_id = $imgPhoto->response[0]->photo_id;
                                                        }
                                                        elseif(property_exists($imgPhoto->response[0], "pid") && property_exists($imgPhoto->response[0], "aid"))
                                                        {
                                                            $album_id = $imgPhoto->response[0]->aid;
                                                            $photo_id = $imgPhoto->response[0]->pid;
                                                        }

                                                        // Если ответ получен успешно, сохраняем запись об опубликованной фотографии
                                                        if($album_id > 0 && $photo_id > 0)
                                                        {
                                                            // добавляем информацию об опубликованном изображении в базу
                                                            $arFields = array(
                                                                "iblock_id" => IntVal($rowSettings["iblock_id"]),
                                                                "item_id" => $postElementID,
                                                                "album_id" => $album_id,
                                                                "photo_id" => $photo_id,
                                                                "soc" => "VK",
                                                                "~date_post" => $DB->CurrentTimeFunction()
                                                            );
                                                            $DB->Add("b_mibix_photoposter_photo_posted", $arFields);
                                                        }

                                                        // DEBUG
                                                        if (defined('MIBIX_DEBUG_PHOTOPOSTER') && MIBIX_DEBUG_PHOTOPOSTER==true)
                                                        {
                                                            self::writeLOG("[INFO] function:".__FUNCTION__." v[7](album_id)", $album_id);
                                                            self::writeLOG("[INFO] function:".__FUNCTION__." v[7](photo_id)", $photo_id);
                                                        }
                                                    }
                                                }
                                            }

                                            // DEBUG
                                            if (defined('MIBIX_DEBUG_PHOTOPOSTER') && MIBIX_DEBUG_PHOTOPOSTER==true)
                                            {
                                                self::writeLOG("[INFO] function:".__FUNCTION__." v[8](upload_url)", $upload_url);
                                                self::writeLOG("[INFO] function:".__FUNCTION__." v[8](arImgUploaded)", $arImgUploaded);
                                            }
                                        }

                                        // добавляем информацию об опубликованной записи в базу
                                        $arFields = array(
                                            "iblock_id" => IntVal($rowSettings["iblock_id"]),
                                            "item_id" => $ITEM_ID_FOR_POST,
                                            "soc" => "VK",
                                            "~date_post" => $DB->CurrentTimeFunction()
                                        );
                                        $DB->Add("b_mibix_photoposter_item_posted", $arFields);
                                    }
                                }
                            }
                        } // end - получение инф об элементе (товаре)
                    }
                }
            } // ============================ end postin to vkontakte ============================

            // ===================================================================================
            // Постинг в "Фейсбук"
            // ===================================================================================
            if($rowSettings["fb_post"]=="Y")
            {
                // запоминаем токен доступа
                self::$ACCESS_FB_TOKEN = $rowSettings["fb_token"];

                // если нужно использовать одинаковые элементы для всех соц.сетей (ID элемента генерируется выше, при публикации Вконтакте)
                $postElementID = 0;
                if($ITEM_ID_FOR_POST>0) // Если явно указан элемент для публикации
                {
                    $postElementID = intval($ITEM_ID_FOR_POST);
                }
                elseif(self::$POST_TO_BOTH_ELEMENT_ID) // Если указано использовать для FB такой же элемент как и для VK
                {
                    $postElementID = intval(self::$POST_TO_BOTH_ELEMENT_ID);
                }
                else // в случае, если нужно сгенерировать новый ID
                {
                    // получаем массив элементов, которые можно публиковать
                    $arElements = self::findElementsForPost($rowSettings,"FB");

                    // Если элементы найдены, выбираем произвольно один из них
                    if(count($arElements)>0)
                    {
                        $rndKeyElements = array_rand($arElements); // выбираем произвольный ключ из массива
                        $postElementID = $arElements[$rndKeyElements];
                    }
                }

                // DEBUG
                if (defined('MIBIX_DEBUG_PHOTOPOSTER') && MIBIX_DEBUG_PHOTOPOSTER==true)
                {
                    self::writeLOG("[INFO] function:".__FUNCTION__." f[1](post_element_id)", $postElementID);
                }

                // получаем полную информацию о выбранном элементе (товаре)
                $resFieldElement = CIBlockElement::GetByID($postElementID);
                if($obFieldElement = $resFieldElement->GetNextElement())
                {
                    // Получаем текст для публикации в зависимости от настроек пользователя
                    $messageText = self::getTextForPost($rowSettings["public_text"], $obFieldElement);

                    // Получаем ссылку на товар (элемент) в каталоге, если это задано в настройках пользователем (или false)
                    $linkItemPost = self::getLinkItemPost($rowSettings["link_post"], $rowSettings["site_id"], $obFieldElement);

                    // Формируем подпись к фотографии
                    $caption = $messageText . "\r\n" . $linkItemPost;

                    // Получаем все картинки элемента и записываем ссылки на них в массив
                    $arSrcPictures = self::getPicturesForPost($rowSettings["public_pictures"], $obFieldElement);

                    // Проверяем, чтобы текст или картинка были не пустые (хотя бы один из них)
                    if (count($arSrcPictures)>0)
                    {
                        // Получаем ID альбома для страницы Фейсбук (если альбома нет, то создается новый и возвращается его ID)
                        $album_id = self::selectOrCreateAlbumFB($rowSettings, $obFieldElement, $rowSettings["fb_wall"]);

                        // Если есть картинки, загружаем их в альбом страницы на "Фейсбук"
                        $fbPhoto = 0;
                        if (is_array($arSrcPictures) && count($arSrcPictures) > 0)
                        {
                            foreach($arSrcPictures as $srcPicture)
                            {
                                // каждое изображение загружаем на сервер
                                //$image_url = dirname(dirname(dirname(dirname(__FILE__)))) . $srcPicture;
                                $image_url = $srcPicture;

                                // загружаем фото в альбом страницы
                                $fbPhoto = self::fbUploadPhoto($album_id, $rowSettings["fb_wall"], $caption, $image_url, $rowSettings["site_id"]);
                                if(property_exists($fbPhoto, "id"))
                                {
                                    // добавляем информацию об опубликованном изображении в базу
                                    $arFields = array(
                                        "iblock_id" => IntVal($rowSettings["iblock_id"]),
                                        "item_id" => $postElementID,
                                        "album_id" => $album_id,
                                        "photo_id" => $fbPhoto->id,
                                        "soc" => "FB",
                                        "~date_post" => $DB->CurrentTimeFunction()
                                    );
                                    $DB->Add("b_mibix_photoposter_photo_posted", $arFields);
                                }
                            }

                            // добавляем информацию об опубликованной записи в базу
                            $arFields = array(
                                "iblock_id" => IntVal($rowSettings["iblock_id"]),
                                "item_id" => $postElementID,
                                "soc" => "FB",
                                "~date_post" => $DB->CurrentTimeFunction()
                            );
                            $DB->Add("b_mibix_photoposter_item_posted", $arFields);
                        }

                        // DEBUG
                        if (defined('MIBIX_DEBUG_PHOTOPOSTER') && MIBIX_DEBUG_PHOTOPOSTER==true)
                        {
                            self::writeLOG("[INFO] function:".__FUNCTION__." f[2](fb_photo)", $fbPhoto);
                        }
                    }
                }
            }
            // ============================ end postin to facebook ============================
        }

        return "CMibixPhotoposterPhotoExport::Post();";
    }

    /**
     * Находим элементы, которые можно опубликовать
     *
     * @param $rowSettings
     * @param string $soc
     * @return array
     */
    private function findElementsForPost($rowSettings, $soc="VK")
    {
        global $DB;

        // здесь будем хранить найденные элементы (товары)
        $arElements = array();

        // сюда запишем уже опубликованные элементы
        $arIPosted = array();

        // разделы с элементами (товарами), которые выбрал пользователь
        $arIncSections = explode(",", $rowSettings["include_sections"]);

        // разделы с элементами (товарами), которые исключил пользователь
        $arExcSections = explode(",", $rowSettings["exclude_sections"]);

        // формируем фильтр, по которому будем отбирать элементы (товары)
        $arFilter = Array(
            "IBLOCK_ID"=>IntVal($rowSettings["iblock_id"]),
            "ACTIVE"=>"Y",
            "INCLUDE_SUBSECTIONS" => "Y"
        );

        // Если задан фильтр по разделам, добавляем
        if(count($arIncSections)>0)
        {
            $arFilter["SECTION_ID"] = $arIncSections;
        }

        // Вытаскиваем элементы, которые уже были опубликованы
        $rsIPosted = $DB->Query("SELECT item_id FROM b_mibix_photoposter_item_posted WHERE soc='".$soc."' AND iblock_id='".IntVal($rowSettings["iblock_id"])."'", true);
        while($rowIPosted = $rsIPosted->Fetch())
        {
            $arIPosted[] = $rowIPosted["item_id"];
        }

        // Получаем элементы инфоблока (товары) согласно фильтру пользователя и запоминаем их ID в массив
        $resIBlockElements = CIBlockElement::GetList(Array(), $arFilter, false, false, Array("ID","IBLOCK_SECTION_ID"));
        while($obIBlockElements = $resIBlockElements->GetNextElement())
        {
            $arFieldsIBlockElements = $obIBlockElements->GetFields();

            // не учитываем элементы в разделах, которые пользовтель указал исключить
            if(!in_array($arFieldsIBlockElements["IBLOCK_SECTION_ID"], $arExcSections) && !in_array($arFieldsIBlockElements["ID"], $arIPosted))
            {
                $arElements[] = $arFieldsIBlockElements["ID"];
            }
        }

        // Проверка активности повторной публикации
        if($rowSettings["repeat"]=="Y" && count($arElements)<1 && count($arIPosted)>0)
        {
            // При отсутствии элементов, удаляем список опубликованных и начинаем по новой
            $sqlWhere = "";
            if($rowSettings["diff_items"]=="Y") $sqlWhere = " WHERE soc='".$soc."'"; // при публикации различных элементов, чистим с условием соц.сети

            $DB->StartTransaction();
            $resDelete = $DB->Query("DELETE FROM b_mibix_photoposter_item_posted".$sqlWhere, false, "File: ".__FILE__."<br>Line: ".__LINE__);
            if($resDelete)
                $DB->Commit();
            else
                $DB->Rollback();

            // DEBUG
            if (defined('MIBIX_DEBUG_PHOTOPOSTER') && MIBIX_DEBUG_PHOTOPOSTER==true)
            {
                self::writeLOG("[INFO] function:".__FUNCTION__." (delete_and_recursion)", $resDelete);
            }

            // Рекурсинвый вызов после чистки списка
            $arElements = self::findElementsForPost($rowSettings, $soc);
        }

        // DEBUG
        if (defined('MIBIX_DEBUG_PHOTOPOSTER') && MIBIX_DEBUG_PHOTOPOSTER==true)
        {
            self::writeLOG("[INFO] function:".__FUNCTION__." (ar_elements)", $arElements);
        }

        return $arElements;
    }

    /**
     * Получаем текст из элемента для публикации
     *
     * @param $public_text
     * @param $obFieldElement
     * @return string
     */
    private function getTextForPost($public_text, $obFieldElement)
    {
        global $APPLICATION;

        $arFieldElement = $obFieldElement->GetFields();

        // Получаем текст для публикации в зависимости от настроек пользователя
        $messageText = "";
        switch($public_text)
        {
            case 'NONE':
                break;
            case 'PREVIEW_TEXT':
                $messageText = $arFieldElement["PREVIEW_TEXT"];
                break;
            case 'DETAIL_TEXT':
                $messageText = $arFieldElement["DETAIL_TEXT"];
                break;
            default: // вытаскиваем значения из свойств
                $arPropertyMessage = $obFieldElement->GetProperty($public_text);
                $messageText = $arPropertyMessage["NAME"] . ": " . $arPropertyMessage["VALUE"];
                break;
        }

        return self::prepareText($messageText);
    }

    /**
     * Обработка текста для публикации (кодировка и прочее)
     *
     * @param $messageText
     * @return mixed|string
     */
    private function prepareText($messageText)
    {
        global $APPLICATION;

        // DEBUG
        if (defined('MIBIX_DEBUG_PHOTOPOSTER') && MIBIX_DEBUG_PHOTOPOSTER==true)
        {
            self::writeLOG("[INFO] function:".__FUNCTION__." v[4](message_text_before)", $messageText);
        }

        // Если есть текст, то чистим его от HTML тегов перед публикацией
        if(strlen($messageText)>0) $messageText = strip_tags($messageText);

        // Удаляем сущности кавычек если они есть
        $messageText = str_replace(array("&quot;", "&amp;", "&lt;", "&gt;"), "", $messageText);

        // Удаление подряд идущих пробелов
        $messageText = trim(preg_replace('/\s{2,}/', ' ', $messageText));

        // Определяем кодировку сайта
        $siteCharset = 'windows-1251';
        if (defined('BX_UTF') && BX_UTF==true)
        {
            $siteCharset = 'UTF-8';
        }

        // Конвертируем строку в соотвествии с кодировкой сайта в UTF-8
        $messageText = $APPLICATION->ConvertCharset(html_entity_decode($messageText), $siteCharset, 'UTF-8');

        // Удаляем лишние переносы строк
        $messageText = preg_replace("/(\r\n)+(\r\n)/i", "\r\n", $messageText);

        // DEBUG
        if (defined('MIBIX_DEBUG_PHOTOPOSTER') && MIBIX_DEBUG_PHOTOPOSTER==true)
        {
            self::writeLOG("[INFO] function:".__FUNCTION__." v[4](message_text_after) [charset:".$siteCharset."]", $messageText);
        }

        return $messageText;
    }

    /**
     * Получаем картинки элемента для публикации
     *
     * @param $public_pictures
     * @param $obFieldElement
     * @return array
     */
    private function getPicturesForPost($public_pictures, $obFieldElement)
    {
        $arFieldElement = $obFieldElement->GetFields();

        // Помещаем все поля и свойства картинок в массив $arSrcPictures
        $arSrcPictures = array();
        $arPropertyPicture = explode(",", $public_pictures);
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
                        $picFiles = $obFieldElement->GetProperty($propPicture);
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

        // DEBUG
        if (defined('MIBIX_DEBUG_PHOTOPOSTER') && MIBIX_DEBUG_PHOTOPOSTER==true)
        {
            self::writeLOG("[INFO] function:".__FUNCTION__." v[4](ar_field_element)", $arFieldElement);
            self::writeLOG("[INFO] function:".__FUNCTION__." v[4](ar_src_pictures)", $arSrcPictures);
        }

        return $arSrcPictures; // на выходе массив с путями к изображениям
    }

    /**
     * Возвращаем ID альбома, выбрав его из существующих или создав новый
     *
     * @param $rowSettings
     * @param $obFieldElement
     * @param $idGroup
     * @return array
     */
    private function selectOrCreateAlbumVK($rowSettings, $obFieldElement, $idGroup)
    {
        $album_id = 0;
        $arFieldElement = $obFieldElement->GetFields();

        // Выбран ли существующий альбом или использовать имя категории
        if($rowSettings["vk_album_check"] == 'NEW')
        {
            // берем имя для нового альбома из названия категории товара
            $album_name = "";
            $resSec = CIBlockSection::GetByID($arFieldElement["IBLOCK_SECTION_ID"]);
            if($arResSec = $resSec->GetNext())
            {
                $album_name = $arResSec['NAME'];
            }

            // проверяем существование нужной группы, если она есть, запоминаем ее ID
            $vkAlbums = self::vkPhotosGetAlbums($idGroup);
            if(property_exists($vkAlbums, "response"))
            {
                foreach($vkAlbums->response as $vkAlb)
                {
                    if($album_name==$vkAlb->title)
                    {
                        $album_id = $vkAlb->aid;
                    }
                }
            }

            // если альбом еще не создан, создаем его
            if($album_id == 0 && strlen($album_name) > 0)
            {
                // включать ли описание
                $description = "";
                $comments_disabled = 0;
                if($rowSettings["vk_album_new_desc"]=="Y")
                {
                    $description = $arResSec["DESCRIPTION"];
                }
                if($rowSettings["vk_album_new_comment"]=="Y")
                {
                    $comments_disabled = 1;
                }

                // Название для альбома обрабатываем для публикации
                $album_name = self::prepareText($album_name);

                // создаем новый альбом и запоминаем его id
                $newAlbum = self::photosCreateAlbum($album_name, $idGroup, $description, $comments_disabled);
                if($newAlbum)
                {
                    // если пришел ответ о том, что создан новый альбом
                    if (property_exists($newAlbum, "response"))
                    {
                        if(property_exists($newAlbum->response, "id"))
                            $album_id = $newAlbum->response->id;
                        elseif(property_exists($newAlbum->response, "aid"))
                            $album_id = $newAlbum->response->aid;
                    }
                }

                // DEBUG
                if (defined('MIBIX_DEBUG_PHOTOPOSTER') && MIBIX_DEBUG_PHOTOPOSTER==true)
                {
                    self::writeLOG("[INFO] function:".__FUNCTION__." v[5](newAlbum)", $newAlbum);
                }
            }

            // DEBUG
            if (defined('MIBIX_DEBUG_PHOTOPOSTER') && MIBIX_DEBUG_PHOTOPOSTER==true)
            {
                self::writeLOG("[INFO] function:".__FUNCTION__." v[5](album_name)", $album_name);
                self::writeLOG("[INFO] function:".__FUNCTION__." v[5](album_id)", $album_id);
                self::writeLOG("[INFO] function:".__FUNCTION__." v[5](vkAlbums)", $vkAlbums);
            }
        }
        else
        {
            // выбираем ID сохраненного альбома
            if(intval($rowSettings["vk_album_exist"]) > 0)
            {
                $album_id = intval($rowSettings["vk_album_exist"]);
            }
        }

        return $album_id;
    }

    /**
     * Возвращаем ID альбома, выбрав его из существующих или создав новый
     *
     * @param $rowSettings
     * @param $obFieldElement
     * @param $idGroup
     * @return array
     */
    private function selectOrCreateAlbumFB($rowSettings, $obFieldElement, $idGroup)
    {
        $album_id = 0;
        $arFieldElement = $obFieldElement->GetFields();

        // Выбран ли существующий альбом или использовать имя категории
        if($rowSettings["fb_album_check"] == 'NEW')
        {
            // берем имя для нового альбома из названия категории товара
            $album_name = "";
            $resSec = CIBlockSection::GetByID($arFieldElement["IBLOCK_SECTION_ID"]);
            if($arResSec = $resSec->GetNext())
            {
                $album_name = $arResSec['NAME'];
            }

            // проверяем существование нужной группы, если она есть, запоминаем ее ID
            $fbAlbums = self::fbPhotosGetAlbums($idGroup);
            if($fbAlbums)
            {
                if(property_exists($fbAlbums, "data"))
                {
                    foreach($fbAlbums->data as $fbAlb)
                    {
                        if($album_name==$fbAlb->name)
                        {
                            $album_id = $fbAlb->id;
                        }
                    }
                }
            }

            // если альбом еще не создан, создаем его
            if($album_id == 0 && strlen($album_name) > 0)
            {
                // включать ли описание
                $description = "";
                if($rowSettings["fb_album_new_desc"]=="Y")
                {
                    $description = $arResSec["DESCRIPTION"];
                }

                // Название для альбома обрабатываем для публикации
                $album_name = self::prepareText($album_name);

                // создаем новый альбом и запоминаем его id
                $newAlbum = self::fbPhotosCreateAlbum($idGroup, $album_name, $description);
                if($newAlbum)
                {
                    // если пришел ответ о том, что создан новый альбом
                    if (property_exists($newAlbum, "id"))
                    {
                        $album_id = $newAlbum->id;
                    }
                }

                // DEBUG
                if (defined('MIBIX_DEBUG_PHOTOPOSTER') && MIBIX_DEBUG_PHOTOPOSTER==true)
                {
                    self::writeLOG("[INFO] function:".__FUNCTION__." v[5](fb_newAlbum)", $newAlbum);
                }
            }

            // DEBUG
            if (defined('MIBIX_DEBUG_PHOTOPOSTER') && MIBIX_DEBUG_PHOTOPOSTER==true)
            {
                self::writeLOG("[INFO] function:".__FUNCTION__." v[5](fb_album_name)", $album_name);
                self::writeLOG("[INFO] function:".__FUNCTION__." v[5](fb_album_id)", $album_id);
                self::writeLOG("[INFO] function:".__FUNCTION__." v[5](fb_fbAlbums)", $fbAlbums);
            }
        }
        else
        {
            // выбираем ID сохраненного альбома
            if(intval($rowSettings["fb_album_exist"]) > 0)
            {
                $album_id = intval($rowSettings["fb_album_exist"]);
            }
        }

        return $album_id;
    }

    /**
     * Возвращает ссылку на товар (элемент) в каталоге
     *
     * @param $link_post
     * @param $obFieldElement
     * @return bool
     */
    private function getLinkItemPost($link_post, $site_id, $obFieldElement)
    {
        if ($link_post=="Y")
        {
            $arFieldElement = $obFieldElement->GetFields();
            if(strlen($arFieldElement["DETAIL_PAGE_URL"])>0)
            {
                // определяем домен сайта
                $filterSite = Array();
                if(!empty($site_id) && strlen($site_id)>0) $filterSite["ID"]=$site_id;
                $rsSites = CSite::GetList($by="sort", $order="desc", $filterSite);
                if($arSite = $rsSites->Fetch())
                {
                    if (substr($arSite["SERVER_NAME"], -1) == '/')
                        return "http://".$arSite["SERVER_NAME"].$arFieldElement["DETAIL_PAGE_URL"];
                    else
                        return "http://".$arSite["SERVER_NAME"].'/'.$arFieldElement["DETAIL_PAGE_URL"];
                }
            }
        }

        // DEBUG
        if (defined('MIBIX_DEBUG_PHOTOPOSTER') && MIBIX_DEBUG_PHOTOPOSTER==true)
        {
            self::writeLOG("[INFO] function:".__FUNCTION__." v[4](link_post:".$link_post.")", $obFieldElement);
        }
        return false;
    }

    /**
     * (Фейсбук) Делаем запись на стене страницы
     *
     * @return bool|mixed
     */
    public function fbPhotosGetAlbums($idGroup=false)
    {
        $fields = array();

        if($idGroup)
        {
            return self::send_request_fb('/'.$idGroup.'/albums', $fields, false, false);
        }
        elseif(strlen(self::$GROUP_FB)>0)
        {
            return self::send_request_fb('/'.self::$GROUP_FB.'/albums', $fields, false, false);
        }

        return false;
    }

    /**
     * (Фейсбук) Создаем новый альбом
     *
     * @param $page_id
     * @param $name
     * @param $message
     * @return bool
     */
    public function fbPhotosCreateAlbum($page_id, $name, $message)
    {
        $fields = array(
            'name' => $name,
            'message' => $message
        );

        return self::send_request_fb('/'.$page_id.'/albums', $fields);
    }

    /**
     * (Фейсбук) Загружаем фотографию в альбом
     *
     * @param $album_id
     * @param $image_url
     * @return bool|mixed
     */
    private function fbUploadPhoto($album_id, $page_id, $message, $image_url, $site_id)
    {
        // определяем домен сайта
        $filterSite = Array();
        if(!empty($site_id) && strlen($site_id)>0) $filterSite["ID"]=$site_id;
        $rsSites = CSite::GetList($by="sort", $order="desc", $filterSite);
        if($arSite = $rsSites->Fetch())
        {
            // если на конце есть слеш то удаляем его
            if (substr($arSite["SERVER_NAME"], -1) == '/')
                $arSite["SERVER_NAME"] = substr($arSite["SERVER_NAME"], 0, -1);

            $fields = array(
                'url' => "http://".$arSite["SERVER_NAME"] . $image_url,
                'message' => $message,
                'place' => $page_id,
                'no_story' => 1,
            );
            return self::send_request_fb('/'.$album_id.'/photos', $fields);
        }

        return false;
    }

    /**
     * (Вконтакте) Получаем ID группы по названию
     *
     * @param $group_id - название группы (идентификатор)
     * @return bool|mixed
     */
    public function groupsGetById($group_id)
    {
        $params = array(
            "group_id" => $group_id, // идентификатор или короткое имя сообщества
        );

        return self::send_request("groups.getById", $params);
    }

    /**
     * (Вконтакте) Создаем новый альбом
     *
     * @param $title
     * @param $group_id
     * @param $description
     * @param $comments_disabled
     * @return bool|mixed
     */
    private function photosCreateAlbum($title, $group_id, $description, $comments_disabled)
    {
        $params = array(
            "title" => $title, // название альбома
            "group_id" => $group_id, // идентификатор сообщества, в котором создаётся альбом
            "description" => $description, // текст описания альбома
            "comments_disabled" => $comments_disabled, // отключено ли комментирование альбома
        );

        return self::send_request("photos.createAlbum", $params);
    }

    /**
     * (Вконтакте) Сохраняет фотографию на стене группы
     *
     * @param $album_id
     * @param $group_id
     * @param $caption
     * @param $photo_list
     * @param $server
     * @param $hash
     * @return bool|mixed
     */
    private function photosSaveVK($album_id, $group_id, $caption, $photo_list, $server, $hash)
    {
        $params = array(
            "album_id" => $album_id, // идентификатор альбома для сохранения фотографий
            "group_id" => $group_id, // идентификатор сообщества, на стену которого нужно сохранить фотографию
            "server" => $server, // параметр, возвращаемый в результате загрузки фотографии на сервер
            "photos_list" => $photo_list, // параметр, возвращаемый в результате загрузки фотографии на сервер
            "hash" => $hash, // параметр, возвращаемый в результате загрузки фотографии на сервер
            "caption" => $caption, // текст описания для фоторгафии
        );

        return self::send_request("photos.save", $params);
    }

    /**
     * (Вконтакте) Возвращаем адрес для загрузки фотографий в альбомы
     *
     * @param $album_id
     * @param $group_id
     * @return bool|mixed
     */
    private function photosGetUploadServer($album_id, $group_id)
    {
        $params = array(
            "album_id" => $album_id, // идентификатор альбома
            "group_id" => $group_id, // идентификатор сообщества
        );

        return self::send_request("photos.getUploadServer", $params);
    }

    /**
     * (Вконтакте) Возвращаем список албомов для группы
     *
     * @param $group_id
     * @return bool|mixed
     */
    public function vkPhotosGetAlbums($group_id)
    {
        if($group_id>0)
        {
            $params = array(
                "owner_id" => -$group_id, // id группы (минус впереди для группы)
                "need_system" => 1, // возвращаем системные альбомы
            );

            return self::send_request("photos.getAlbums", $params);
        }

        return false;
    }

    /**
     * (Вконтакте) Отправляет изображение на указанный URL методом post
     *
     * @param $upload_url
     * @param $image_url
     * @return array()
     */
    private function postUploadImage($upload_url, $image_url)
    {
        // отправляем POST запрос с изображением для загрузки на сервер
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $upload_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, array("file1"=>'@'.$image_url));

        // массив ответа
        $arImageParams = array();
        if (($upload = curl_exec($ch)) !== false)
        {
            curl_close($ch);
            $upload = json_decode($upload);

            $arImageParams =  array(
                'server' => $upload->server,
                'photos_list' => $upload->photos_list,
                'album_id' => $upload->album_id,
                'hash' => $upload->hash,
            );
        }

        // DEBUG
        if (defined('MIBIX_DEBUG_PHOTOPOSTER') && MIBIX_DEBUG_PHOTOPOSTER==true)
        {
            self::writeLOG("[INFO] function:".__FUNCTION__." (curl_response)", $arImageParams);
        }

        return $arImageParams;
    }

    /**
     * Отправка запроса к API VK через CURL
     */
    private function send_request($method_name, $parameters)
    {
        // Проверяем, что параметры переданы
        if (!is_array($parameters) || count($parameters)<1)
        {
            // DEBUG
            if (defined('MIBIX_DEBUG_PHOTOPOSTER') && MIBIX_DEBUG_PHOTOPOSTER==true)
            {
                self::writeLOG("[ERROR] function:".__FUNCTION__." (parameters_check)", $parameters);
            }
            return false;
        }

        if(!function_exists('curl_version'))
        {
            if(defined('MIBIX_DEBUG_PHOTOPOSTER') && MIBIX_DEBUG_SOCPOSTER==true)
            {
                self::writeLOG("[ERROR] function:".__FUNCTION__." (CURL_ERROR)[method:".$method_name."]", "curl not istalled for PHP");
            }
            return false;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.vk.com/method/" . $method_name);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_REFERER, @$_SERVER['HTTP_REFERER']);

        // Используем SSL-запрос
        //curl_setopt($ch, CURLOPT_SSLVERSION,3);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

        // Добавляем токен
        $parameters['access_token'] = self::$ACCESS_VK_TOKEN;

        // Переводим параметры из массива в строку
        $parameters = http_build_query($parameters);

        // Устанавливаем параметры POST-запроса
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $parameters);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json'));

        // Агент (Referer)
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.17 (KHTML, like Gecko) Chrome/24.0.1312.57 Safari/537.17');

        // Получаем результаты запроса
        $out = curl_exec($ch);
        $curl_error = curl_errno($ch);
        $info = curl_getinfo($ch);

        // если есть ошибки, возвращаем false
        if ($curl_error)
        {
            // DEBUG
            if (defined('MIBIX_DEBUG_PHOTOPOSTER') && MIBIX_DEBUG_PHOTOPOSTER==true)
            {
                self::writeLOG("[ERROR] function:".__FUNCTION__." (curl_post_parameters)[method:".$method_name."]", $parameters);
                self::writeLOG("[ERROR] function:".__FUNCTION__." (curl_error)", $curl_error);
            }
            return false;
        }

        // проверка других http ошибок
        if ($info['http_code']=='200')
        {
            // DEBUG
            if (defined('MIBIX_DEBUG_PHOTOPOSTER') && MIBIX_DEBUG_PHOTOPOSTER==true)
            {
                self::writeLOG("[INFO] function:".__FUNCTION__." (curl_parameters)[method:".$method_name."]", $parameters);
                self::writeLOG("[INFO] function:".__FUNCTION__." (curl_result_return)", json_decode($out));
            }

            // успешно -> 200 (отправляем результат)
            return json_decode($out);
        }

        // DEBUG
        if (defined('MIBIX_DEBUG_PHOTOPOSTER') && MIBIX_DEBUG_PHOTOPOSTER==true)
        {
            self::writeLOG("[ERROR] function:".__FUNCTION__." (curl_info)", $info['http_code']);
        }

        return false;
    }

    /**
     * Отправка запроса к API FB через CURL
     */
    private function send_request_fb($method_name, $parameters, $param_exist=true, $is_post=true)
    {
        // Проверяем, что параметры переданы
        if (!is_array($parameters) || ($param_exist && count($parameters) < 1))
        {
            // DEBUG
            if (defined('MIBIX_DEBUG_PHOTOPOSTER') && MIBIX_DEBUG_PHOTOPOSTER==true)
            {
                self::writeLOG("[ERROR] function:".__FUNCTION__." (parameters_check)", $parameters);
            }
            return false;
        }

        if(!function_exists('curl_version'))
        {
            if(defined('MIBIX_DEBUG_PHOTOPOSTER') && MIBIX_DEBUG_SOCPOSTER==true)
            {
                self::writeLOG("[ERROR] function:".__FUNCTION__." (CURL_ERROR)[method:".$method_name."]", "curl not istalled for PHP");
            }
            return false;
        }

        // Добавляем токен
        $parameters['access_token'] = self::$ACCESS_FB_TOKEN;

        // Переводим параметры из массива в строку
        $parameters = http_build_query($parameters);

        // Параметры для GET если есть
        $get_params = "";
        if(!$is_post && $param_exist)
        {
            $get_params = "?".$parameters;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://graph.facebook.com' . $method_name . $get_params);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_HEADER, false);
        if($is_post)
        {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $parameters);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // массив ответа
        $curl_error = curl_errno($ch);
        $info = curl_getinfo($ch);
        if (($result = curl_exec($ch)) !== false)
        {
            // DEBUG
            if (defined('MIBIX_DEBUG_PHOTOPOSTER') && MIBIX_DEBUG_PHOTOPOSTER==true)
            {
                self::writeLOG("[INFO] function:".__FUNCTION__." (curl_parameters)[method:".$method_name."]", $parameters);
                self::writeLOG("[INFO] function:".__FUNCTION__." (curl_result_return)", json_decode($result));
            }

            curl_close($ch);
            return json_decode($result);
        }

        // DEBUG
        if (defined('MIBIX_DEBUG_PHOTOPOSTER') && MIBIX_DEBUG_PHOTOPOSTER==true)
        {
            self::writeLOG("[ERROR] function:".__FUNCTION__." (curl_post_parameters)", $parameters);
            self::writeLOG("[ERROR] function:".__FUNCTION__." (curl_error)", $curl_error);
            self::writeLOG("[ERROR] function:".__FUNCTION__." (curl_info)", $info['http_code']);
        }

        return false;
    }

    /**
     * Функция записывает лог строки или объекта в файл в корне модуля
     *
     * @param $name
     * @param $value
     */
    private function writeLOG($name, $value)
    {
        // открываем файл для записи и ставим временную отметку
        $fp = @fopen(dirname(__FILE__)."/report.log", "a+");
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
}
?>