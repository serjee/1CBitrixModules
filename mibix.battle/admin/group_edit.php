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
    array("DIV" => "edit1", "TAB" => GetMessage("MIBIX_BATTLE_TAB_GROUP"), "ICON" => "main_user_edit", "TITLE" => GetMessage("MIBIX_BATTLE_TAB_GROUP_TITLE")),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs);

$ID = intval($ID); // идентификатор редактируемой записи
$strError = ""; // сообщение об ошибке
$bVarsFromForm = false; // флаг "Данные получены с формы", обозначающий, что выводимые данные получены с формы, а не из БД.

// ОБРАБОТКА ИЗМЕНЕНИЙ ФОРМЫ
if($REQUEST_METHOD == "POST" && ($save != "" || $apply != "") && $POST_RIGHT >= "W" && check_bitrix_sessid())
{
    $group = new CMibixBattleGroupModel();

    // обработка данных формы
    $arFields = Array(
        "name_group"		=> $f_name_group,
        "code_group"		=> $f_code_group,
        "active"		    => ($f_active <> "Y"? "N":"Y"),
    );

    // сохранение данных (обновление или добавление)
    if($ID > 0)
    {
        $res = $group->Update($ID, $arFields);
    }
    else
    {
        $ID = $group->Add($arFields);
        $res = ($ID>0);
    }

    if($res)
    {
        // если сохранение прошло удачно - перенаправим на новую страницу
        // (в целях защиты от повторной отправки формы нажатием кнопки "Обновить" в браузере)
        if($apply!="")
            LocalRedirect("/bitrix/admin/mibix.battle_group_edit.php?ID=".$ID."&mess=ok&lang=".LANG."&".$tabControl->ActiveTabParam());
        else
            LocalRedirect("/bitrix/admin/mibix.battle_group_list.php?lang=".LANG);
    }
    else
    {
        // если в процессе сохранения возникли ошибки - получаем текст ошибки и меняем вышеопределённые переменные
        if($e = $APPLICATION->GetException())
        {
            $message = new CAdminMessage(GetMessage("MIBIX_BATTLE_GROUP_SAVE_ERROR"), $e);
        }
        $bVarsFromForm = true;
    }
}

// Удаление глобальных переменных с префиксом str_
ClearVars();

// Выберем данные из базы и сохранем в переменные с префиксом str_
if($ID > 0)
{
    $group = CMibixBattleGroupModel::GetByID($ID);
    if(!$group->ExtractFields("str_"))
    {
        $ID=0;
    }
}

// если данные переданы из формы, инициализируем их
if($bVarsFromForm)
{
    $DB->InitTableVarsForEdit("b_mibix_battle_group", "", "str_");
}

// Устанавливаем заголовок в зависимости от ее типа (обновление/добавление)
$APPLICATION->SetTitle(($ID > 0 ? GetMessage("MIBIX_BATTLE_GROUP_EDIT_TITLE").$ID : GetMessage("MIBIX_BATTLE_GROUP_ADD_TITLE")));

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
        "TEXT"=>GetMessage("MIBIX_BATTLE_GROUP_LIST_TEXT"),
        "TITLE"=>GetMessage("MIBIX_BATTLE_GROUP_LIST"),
        "LINK"=>"mibix.battle_group_list.php?lang=".LANG,
        "ICON"=>"btn_list",
    )
);

// В режиме редактирования добавляем дополнительные пункты меню (Добавить/Удалить)
if($ID>0)
{
    $aMenu[] = array("SEPARATOR"=>"Y");
    $aMenu[] = array(
        "TEXT"=>GetMessage("MIBIX_BATTLE_GROUP_ADD_TEXT"),
        "TITLE"=>GetMessage("MIBIX_BATTLE_GROUP_MNU_ADD"),
        "LINK"=>"mibix.battle_group_edit.php?lang=".LANG,
        "ICON"=>"btn_new",
    );
    $aMenu[] = array(
        "TEXT"=>GetMessage("MIBIX_BATTLE_GROUP_DEL_TEXT"),
        "TITLE"=>GetMessage("MIBIX_BATTLE_GROUP_MNU_DEL"),
        "LINK"=>"javascript:if(confirm('".GetMessage("MIBIX_BATTLE_GROUP_MNU_DEL_CONF")."'))window.location='mibix.battle_group_list.php?ID=".$ID."&action=delete&lang=".LANG."&".bitrix_sessid_get()."';",
        "ICON"=>"btn_delete",
    );
}

// создадим экземпляр класса административного меню
$context = new CAdminContextMenu($aMenu);

// выведем меню
$context->Show();

// если есть сообщения об ошибках или об успешном сохранении - выведем их
if($_REQUEST["mess"] == "ok" && $ID>0)
    CAdminMessage::ShowMessage(array("MESSAGE"=>GetMessage("MIBIX_BATTLE_GROUP_SAVED"), "TYPE"=>"OK"));
if($message)
    echo $message->Show();
?>

    <form method="POST" action="<?=$APPLICATION->GetCurPage();?>"  enctype="multipart/form-data" name="groupform">
        <?
        $tabControl->Begin();
        $tabControl->BeginNextTab();
        ?>
        <tr class="heading">
            <td colspan="2"><?=GetMessage("MIBIX_BATTLE_GROUP_TITLE")?></td>
        </tr>
        <?if($ID > 0):?>
            <tr>
                <td width="40%" class="adm-detail-content-cell-l"><?=GetMessage("MIBIX_BATTLE_GROUP_DATE_ADD");?>:</td>
                <td width="60%" class="adm-detail-content-cell-r"><?=$str_date_insert;?></td>
            </tr>
            <?if($str_date_update <> ""):?>
                <tr>
                    <td width="40%" class="adm-detail-content-cell-l"><?=GetMessage("MIBIX_BATTLE_GROUP_DATE_UPD");?>:</td>
                    <td width="60%" class="adm-detail-content-cell-r"><?=$str_date_update;?></td>
                </tr>
            <?endif?>
        <?endif?>
        <tr>
            <td width="40%" class="adm-detail-content-cell-l"><?=GetMessage("MIBIX_BATTLE_GROUP_ACTIVE");?>:</td>
            <td width="60%" class="adm-detail-content-cell-r">
                <input type="checkbox" name="f_active" value="Y"<?if($str_active=="Y" || empty($str_active)) echo " checked";?>>
            </td>
        </tr>
        <tr>
            <td width="40%" class="adm-detail-valign-top adm-detail-content-cell-l"><span class="required">*</span>
                <span class="adm-required-field"><?=GetMessage("MIBIX_BATTLE_GROUP_NAME")?></span>:<br>(<?=GetMessage("MIBIX_BATTLE_GROUP_NAME_NOTE")?>)
            </td>
            <td width="60%">
                <input type="text" size="20" maxlength="20" value="<?=$str_name_group;?>" name="f_name_group" />
            </td>
        </tr>
        <tr>
            <td width="40%" class="adm-detail-valign-top adm-detail-content-cell-l"><span class="required">*</span>
                <span class="adm-required-field"><?=GetMessage("MIBIX_BATTLE_GROUP_CODE")?></span>:<br>(<?=GetMessage("MIBIX_BATTLE_GROUP_CODE_NOTE")?>)
            </td>
            <td width="60%">
                <input type="text" size="20" maxlength="20" value="<?=$str_code_group;?>" name="f_code_group" />
            </td>
        </tr>
        <?
        $tabControl->Buttons(
            array(
                "disabled"=>($POST_RIGHT<"W"),
                "back_url"=>"mibix.battle_group_list.php?lang=".LANG
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
$tabControl->ShowWarnings("groupform", $message);
?>

<?=BeginNote();?>
    <span class="required">*</span> <?=GetMessage("REQUIRED_FIELDS");?>
<?=EndNote();?>
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>