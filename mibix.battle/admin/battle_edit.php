<?
$iModuleID = "mibix.battle";
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php"); // первый общий пролог
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$iModuleID."/include.php"); // инициализация модуля
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$iModuleID."/prolog.php"); // пролог модуля

IncludeModuleLangFile(__FILE__);

// получим права доступа текущего пользователя на модуль
$POST_RIGHT = $APPLICATION->GetGroupRight($iModuleID);
if($POST_RIGHT=="D")
{
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

// сформируем список закладок
$aTabs = array(
    array("DIV" => "edit1", "TAB" => GetMessage("MIBIX_BATTLE_TAB_BATTLE"), "ICON" => "main_user_edit", "TITLE" => GetMessage("MIBIX_BATTLE_TAB_BATTLE_TITLE")),
    array("DIV" => "edit2", "TAB" => GetMessage("MIBIX_BATTLE_TAB_BATTLE_SOC"), "ICON" => "main_user_edit", "TITLE" => GetMessage("MIBIX_BATTLE_TAB_BATTLE_SOC_TITLE")),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs);

$ID = intval($ID); // идентификатор редактируемой записи
$strError = ""; // сообщение об ошибке
$bVarsFromForm = false; // флаг "Данные получены с формы", обозначающий, что выводимые данные получены с формы, а не из БД.

// ОБРАБОТКА ИЗМЕНЕНИЙ ФОРМЫ
if($REQUEST_METHOD == "POST" && ($save != "" || $apply != "") && $POST_RIGHT >= "W" && check_bitrix_sessid())
{
    $battle = new CMibixBattleBattleModel();

    // обработка данных формы
    $arFields = Array(
        "group_id"		    => intval($f_group_id),
        "iblock_id"		    => intval($f_iblock_id),
        "date_start"		=> $f_date_start,
        "date_finish"		=> $f_date_finish,
        "name_battle"		=> $f_name_battle,
        "battle_items"	    => $f_battle_items, // array
        "battle_title"		=> $f_battle_title,
        "battle_text"		=> $f_battle_text,
        "battle_pictures"	=> $f_battle_pictures, // array
        "battle_links"	    => $f_battle_links, // array
        "battle_site"	    => $f_battle_site, // array
        "time_format"	    => $f_time_format,
        "is_protection"     => ($f_is_protection <> "Y"? "N":"Y"),
        "is_cron_count"     => ($f_is_cron_count <> "Y"? "N":"Y"),
        "enabled_vk"        => ($f_enabled_vk <> "Y"? "N":"Y"),
        "enabled_fb"        => ($f_enabled_fb <> "Y"? "N":"Y"),
        "enabled_tw"        => ($f_enabled_tw <> "Y"? "N":"Y"),
        "enabled_ok"        => ($f_enabled_ok <> "Y"? "N":"Y"),
        "enabled_ml"        => ($f_enabled_ml <> "Y"? "N":"Y"),
        "enabled_pi"        => ($f_enabled_pi <> "Y"? "N":"Y"),
        "active"		    => ($f_active <> "Y"? "N":"Y"),
    );

    // сохранение данных (обновление или добавление)
    if($ID > 0)
    {
        $res = $battle->Update($ID, $arFields);
    }
    else
    {
        $ID = $battle->Add($arFields);
        $res = ($ID>0);
    }

    if($res)
    {
        // если сохранение прошло удачно - перенаправим на новую страницу
        // (в целях защиты от повторной отправки формы нажатием кнопки "Обновить" в браузере)
        if($apply!="")
            LocalRedirect("/bitrix/admin/mibix.battle_battle_edit.php?ID=".$ID."&mess=ok&lang=".LANG."&".$tabControl->ActiveTabParam());
        else
            LocalRedirect("/bitrix/admin/mibix.battle_battle_list.php?lang=".LANG);
    }
    else
    {
        // если в процессе сохранения возникли ошибки - получаем текст ошибки и меняем вышеопределённые переменные
        if($e = $APPLICATION->GetException())
        {
            $message = new CAdminMessage(GetMessage("MIBIX_BATTLE_BATTLE_SAVE_ERROR"), $e);
        }
        $bVarsFromForm = true;
    }
}

// Удаление глобальных переменных с префиксом str_
ClearVars();
$str_date_start = ConvertTimeStamp(time()+CTimeZone::GetOffset(), "FULL");
$str_date_finish = ConvertTimeStamp(time()+CTimeZone::GetOffset(), "FULL");
$str_iblock_id = intval($f_iblock_id);

// Выберем данные из базы и сохранем в переменные с префиксом str_
if($ID > 0)
{
    $battle = CMibixBattleBattleModel::GetByID($ID);
    if(!$battle->ExtractFields("str_"))
    {
        $ID=0;
    }
}

// если данные переданы из формы, инициализируем их
if($bVarsFromForm)
{
    $DB->InitTableVarsForEdit("b_mibix_battle_battle", "", "str_");
}

// Устанавливаем заголовок в зависимости от ее типа (обновление/добавление)
$APPLICATION->SetTitle(($ID > 0 ? GetMessage("MIBIX_BATTLE_BATTLE_EDIT_TITLE").$ID : GetMessage("MIBIX_BATTLE_BATTLE_ADD_TITLE")));

// второй общий пролог
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

// Проверка статуса модуля
switch(CModule::IncludeModuleEx($iModuleID))
{
    case MODULE_NOT_FOUND:
        echo '<div style="padding-bottom:10px;color:red;">'.GetMessage("MIBIX_BATTLE_MODULE_NOT_FOUND").'</div>';
        return;
    case MODULE_DEMO:
        echo '<div style="padding-bottom:10px;color:red;">'.GetMessage("MIBIX_BATTLE_MODULE_DEMO").'</div>';
        break;
    case MODULE_DEMO_EXPIRED:
        echo '<div style="padding-bottom:10px;color:red;">'.GetMessage("MIBIX_BATTLE_MODULE_DEMO_EXPIRED").'</div>';
}

// Административное меню, которое будет отображаться над таблицей со списком (Вернуться к списку)
$aMenu = array(
    array(
        "TEXT"=>GetMessage("MIBIX_BATTLE_BATTLE_LIST_TEXT"),
        "TITLE"=>GetMessage("MIBIX_BATTLE_BATTLE_LIST"),
        "LINK"=>"mibix.battle_battle_list.php?lang=".LANG,
        "ICON"=>"btn_list",
    )
);

// В режиме редактирования добавляем дополнительные пункты меню (Добавить/Удалить)
if($ID>0)
{
    $aMenu[] = array("SEPARATOR"=>"Y");
    $aMenu[] = array(
        "TEXT"=>GetMessage("MIBIX_BATTLE_BATTLE_ADD_TEXT"),
        "TITLE"=>GetMessage("MIBIX_BATTLE_BATTLE_MNU_ADD"),
        "LINK"=>"mibix.battle_battle_edit.php?lang=".LANG,
        "ICON"=>"btn_new",
    );
    $aMenu[] = array(
        "TEXT"=>GetMessage("MIBIX_BATTLE_BATTLE_DEL_TEXT"),
        "TITLE"=>GetMessage("MIBIX_BATTLE_BATTLE_MNU_DEL"),
        "LINK"=>"javascript:if(confirm('".GetMessage("MIBIX_BATTLE_BATTLE_MNU_DEL_CONF")."'))window.location='mibix.battle_battle_list.php?ID=".$ID."&action=delete&lang=".LANG."&".bitrix_sessid_get()."';",
        "ICON"=>"btn_delete",
    );
}

// создадим экземпляр класса административного меню
$context = new CAdminContextMenu($aMenu);

// выведем меню
$context->Show();

// если есть сообщения об ошибках или об успешном сохранении - выведем их
if($_REQUEST["mess"] == "ok" && $ID>0)
    CAdminMessage::ShowMessage(array("MESSAGE"=>GetMessage("MIBIX_BATTLE_BATTLE_SAVED"), "TYPE"=>"OK"));
if($message)
    echo $message->Show();
?>
    <form method="POST" action="<?=$APPLICATION->GetCurPage();?>"  enctype="multipart/form-data" name="battleform">
        <?
        $tabControl->Begin();
        $tabControl->BeginNextTab();
        ?>
        <tr class="heading">
            <td colspan="2"><?=GetMessage("MIBIX_BATTLE_BATTLE_DATA_TITLE")?></td>
        </tr>
        <?if($ID > 0):?>
            <tr>
                <td width="40%" class="adm-detail-content-cell-l"><?=GetMessage("MIBIX_BATTLE_BATTLE_DATE_ADD");?>:</td>
                <td width="60%" class="adm-detail-content-cell-r"><?=$str_date_insert;?></td>
            </tr>
            <?if($str_date_update <> ""):?>
                <tr>
                    <td width="40%" class="adm-detail-content-cell-l"><?=GetMessage("MIBIX_BATTLE_BATTLE_DATE_UPD");?>:</td>
                    <td width="60%" class="adm-detail-content-cell-r"><?=$str_date_update;?></td>
                </tr>
            <?endif?>
        <?endif?>
        <tr>
            <td width="40%" class="adm-detail-content-cell-l"><span class="required">*</span>
                <span class="adm-required-field"><?=GetMessage("MIBIX_BATTLE_BATTLE_IBLOCK_ID");?></span>:
            </td>
            <td width="60%" class="adm-detail-content-cell-r">
                <?echo CMibixBattleBattleModel::getSelectBoxIBlockId($str_iblock_id);?>
            </td>
        </tr>
        <tr>
            <td width="40%" class="adm-detail-content-cell-l"><?=GetMessage("MIBIX_BATTLE_BATTLE_TITLE_NAME");?>:</td>
            <td width="60%" class="adm-detail-content-cell-r">
                <?echo CMibixBattleBattleModel::getSelectBoxBattleTitle($str_iblock_id, $str_battle_title);?>
            </td>
        </tr>
        <tr>
            <td width="40%" class="adm-detail-content-cell-l"><?=GetMessage("MIBIX_BATTLE_BATTLE_TEXT");?>:</td>
            <td width="60%" class="adm-detail-content-cell-r">
                <?echo CMibixBattleBattleModel::getSelectBoxBattleText($str_iblock_id, $str_battle_text);?>
            </td>
        </tr>
        <tr>
            <td width="40%" class="adm-detail-valign-top adm-detail-content-cell-l"><?=GetMessage("MIBIX_BATTLE_BATTLE_PICTURES");?>:</td>
            <td width="60%" class="adm-detail-content-cell-r">
                <?echo CMibixBattleBattleModel::getSelectBoxPropertyPictures($str_iblock_id, $str_battle_pictures);?>
            </td>
        </tr>
        <tr>
            <td width="40%" class="adm-detail-content-cell-l"><?=GetMessage("MIBIX_BATTLE_BATTLE_LINKS");?>:</td>
            <td width="60%" class="adm-detail-content-cell-r">
                <?echo CMibixBattleBattleModel::getSelectBoxBattleLink($str_iblock_id, $str_battle_links);?>
            </td>
        </tr>
        <tr>
            <td width="40%" class="adm-detail-content-cell-l"><?=GetMessage("MIBIX_BATTLE_BATTLE_SITE");?>:</td>
            <td width="60%">
                <input type="text" size="50" maxlength="50" value="<?=$str_battle_site;?>" name="f_battle_site" />
            </td>
        </tr>
        <tr class="heading">
            <td colspan="2"><?=GetMessage("MIBIX_BATTLE_BATTLE_TITLE")?></td>
        </tr>
        <tr>
            <td width="40%" class="adm-detail-content-cell-l"><?=GetMessage("MIBIX_BATTLE_BATTLE_ACTIVE");?>:</td>
            <td width="60%" class="adm-detail-content-cell-r">
                <input type="checkbox" name="f_active" value="Y"<?if($str_active=="Y" || empty($str_active)) echo " checked";?>>
            </td>
        </tr>
        <tr>
            <td width="40%" class="adm-detail-content-cell-l"><span class="required">*</span>
                <span class="adm-required-field"><?=GetMessage("MIBIX_BATTLE_BATTLE_DATE_START")?></span>:
            </td>
            <td width="60%">
                <?echo CalendarDate("f_date_start", $str_date_start, "battleform", "20")?>
            </td>
        </tr>
        <tr>
            <td width="40%" class="adm-detail-content-cell-l"><span class="required">*</span>
                <span class="adm-required-field"><?=GetMessage("MIBIX_BATTLE_BATTLE_DATE_FINISH")?></span>:
            </td>
            <td width="60%">
                <?echo CalendarDate("f_date_finish", $str_date_finish, "battleform", "20")?>
            </td>
        </tr>
        <tr>
            <td width="40%" class="adm-detail-content-cell-l"><span class="required">*</span>
                <span class="adm-required-field"><?=GetMessage("MIBIX_BATTLE_BATTLE_NAME")?></span>:
            </td>
            <td width="60%">
                <input type="text" size="50" maxlength="255" value="<?=$str_name_battle;?>" name="f_name_battle" />
            </td>
        </tr>
        <tr>
            <td width="40%" class="adm-detail-valign-top adm-detail-content-cell-l"><span class="required">*</span>
                <span class="adm-required-field"><?=GetMessage("MIBIX_BATTLE_BATTLE_GROUP");?></span>:
            </td>
            <td width="60%" class="adm-detail-content-cell-r">
                <?echo CMibixBattleBattleModel::getSelectBoxGroups($str_group_id);?>
            </td>
        </tr>
        <tr>
            <td width="40%" class="adm-detail-valign-top adm-detail-content-cell-l"><span class="required">*</span>
                <span class="adm-required-field"><?=GetMessage("MIBIX_BATTLE_BATTLE_ITEMS");?></span>:
            </td>
            <td width="60%" class="adm-detail-content-cell-r">
                <?
                $property_fields = array("PROPERTY_TYPE"=>"E", "MULTIPLE"=>"Y", "MULTIPLE_CNT"=>1);
                _ShowPropertyField("f_battle_items", $property_fields, explode(",",$str_battle_items), false, $bVarsFromForm);
                ?>
            </td>
        </tr>
        <tr>
            <td width="40%" class="adm-detail-valign-top adm-detail-content-cell-l"><?=GetMessage("MIBIX_BATTLE_BATTLE_IS_CRON_COUNT");?>:</td>
            <td width="60%" class="adm-detail-content-cell-r">
                <input type="checkbox" name="f_is_cron_count" value="Y"<?if($str_is_cron_count=="Y" || empty($str_is_cron_count)) echo " checked";?>>
                <div class="adm-info-message">
                    <?$ROOT_PATH = realpath(dirname(__FILE__).'/../../../../');?>
                    <?=GetMessage("MIBIX_BATTLE_BATTLE_IS_CRON_COUNT_NOTE", array('#SCRIPT#'=>'<b>mibix_battle_cron.php</b>', '#ROOT#'=>$ROOT_PATH));?>
                </div>
            </td>
        </tr>
        <!--tr>
            <td width="40%" class="adm-detail-content-cell-l"><?=GetMessage("MIBIX_BATTLE_BATTLE_IS_PROTECTION");?>:</td>
            <td width="60%" class="adm-detail-content-cell-r">
                <input type="checkbox" name="f_is_protection" value="Y"<?if($str_is_protection=="Y" || empty($str_is_protection)) echo " checked";?>>
            </td>
        </tr-->
        <?$tabControl->BeginNextTab();?>
        <tr class="heading">
            <td colspan="2"><?=GetMessage("MIBIX_BATTLE_BATTLE_SOCNET_BLOCK")?></td>
        </tr>
        <tr>
            <td width="40%" class="adm-detail-content-cell-l"><?=GetMessage("MIBIX_BATTLE_BATTLE_SOCNET_VK_TITLE");?>:</td>
            <td width="60%" class="adm-detail-content-cell-r">
                <input type="checkbox" name="f_enabled_vk" value="Y"<?if($str_enabled_vk=="Y" || empty($str_enabled_vk)) echo " checked";?>>
            </td>
        </tr>
        <tr>
            <td width="40%" class="adm-detail-content-cell-l"><?=GetMessage("MIBIX_BATTLE_BATTLE_SOCNET_FB_TITLE");?>:</td>
            <td width="60%" class="adm-detail-content-cell-r">
                <input type="checkbox" name="f_enabled_fb" value="Y"<?if($str_enabled_fb=="Y" || empty($str_enabled_fb)) echo " checked";?>>
            </td>
        </tr>
        <tr>
            <td width="40%" class="adm-detail-content-cell-l"><?=GetMessage("MIBIX_BATTLE_BATTLE_SOCNET_TW_TITLE");?>:</td>
            <td width="60%" class="adm-detail-content-cell-r">
                <input type="checkbox" name="f_enabled_tw" value="Y"<?if($str_enabled_tw=="Y" || empty($str_enabled_tw)) echo " checked";?>>
            </td>
        </tr>
        <tr>
            <td width="40%" class="adm-detail-content-cell-l"><?=GetMessage("MIBIX_BATTLE_BATTLE_SOCNET_OK_TITLE");?>:</td>
            <td width="60%" class="adm-detail-content-cell-r">
                <input type="checkbox" name="f_enabled_ok" value="Y"<?if($str_enabled_ok=="Y" || empty($str_enabled_ok)) echo " checked";?>>
            </td>
        </tr>
        <tr>
            <td width="40%" class="adm-detail-content-cell-l"><?=GetMessage("MIBIX_BATTLE_BATTLE_SOCNET_ML_TITLE")?>:</td>
            <td width="60%" class="adm-detail-content-cell-r">
                <input type="checkbox" name="f_enabled_ml" value="Y"<?if($str_enabled_ml=="Y" || empty($str_enabled_ml)) echo " checked";?>>
            </td>
        </tr>
        <tr>
            <td width="40%" class="adm-detail-content-cell-l"><?=GetMessage("MIBIX_BATTLE_BATTLE_SOCNET_PI_TITLE")?>:</td>
            <td width="60%" class="adm-detail-content-cell-r">
                <input type="checkbox" name="f_enabled_pi" value="Y"<?if($str_enabled_pi=="Y" || empty($str_enabled_pi)) echo " checked";?>>
            </td>
        </tr>
        <?
        $tabControl->Buttons(
            array(
                "disabled"=>($POST_RIGHT<"W"),
                "back_url"=>"mibix.battle_battle_list.php?lang=".LANG
            )
        );
        ?>
        <?=bitrix_sessid_post();?>
        <input type="hidden" name="lang" value="<?=LANG?>">
        <?if($ID>0):?>
            <input type="hidden" name="ID" value="<?=$ID?>">
        <?endif;?>
        <?
        $tabControl->End();
        ?>
    </form>

<?
$tabControl->ShowWarnings("battleform", $message);
?>

<?=BeginNote();?>
    <span class="required">*</span> <?=GetMessage("REQUIRED_FIELDS");?>
<?=EndNote();?>
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>