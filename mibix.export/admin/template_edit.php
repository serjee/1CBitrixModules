<?
$iForm = "template";
$sForm = strtoupper($iForm);
$iModuleID = "mibix.export";
$sModuleID = strtoupper(str_replace(".","_",$iModuleID));
$pageEdit = "template_edit";
$pageList = "template_list";

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php"); // первый общий пролог
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$iModuleID."/include.php"); // инициализация модуля
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$iModuleID."/prolog.php"); // пролог модуля

IncludeModuleLangFile(__FILE__);

// Устанавливаем заголовок в зависимости от ее типа (обновление/добавление)
$APPLICATION->SetTitle(($ID > 0 ? GetMessage($sModuleID.'_'.$sForm.'_EDIT_TITLE').$ID : GetMessage($sModuleID.'_'.$sForm.'_ADD_TITLE')));

// Проверка прав доступа
$POST_RIGHT = $APPLICATION->GetGroupRight($iModuleID);
if ($POST_RIGHT == "D") {
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

$ID = intval($ID); // идентификатор редактируемой записи
$arErrors = array(); // сообщение об ошибке
$bVarsFromForm = false; // флаг "Данные получены с формы", обозначающий, что выводимые данные получены с формы, а не из БД.

// === ОБРАБОТКА ИЗМЕНЕНИЙ ФОРМЫ ===
if($REQUEST_METHOD == "POST" && ($save != "" || $apply != "") && $POST_RIGHT >= "W" && check_bitrix_sessid())
{
    $model = new CMibixExportTemplateModel();

    // обработка данных формы
    $arFields = Array(
        "name"              => $f_name,
        "encoding"          => $f_encoding,
        "template"          => $f_template,
        "step_limit"        => $f_step_limit,
        "step_path"         => $f_step_path,
        "step_interval"     => $f_step_interval
    );

    // сохранение данных (обновление или добавление)
    if($ID > 0)
    {
        if(!($res = $model->Update($ID, $arFields, $SITE_ID)))
            $arErrors = $model->getErrors();
    }
    else
    {
        if(!($ID=$model->Add($arFields)))
            $arErrors = $model->getErrors();
    }

    // Есть ли ошибки при сохранении формы
    if(count($arErrors) > 0)
    {
        $e = new CAdminException($arErrors);
        $message = new CAdminMessage(GetMessage($sModuleID.'_'.$sForm.'_SAVE_ERROR'), $e);

        $bVarsFromForm = true;
    }
    else // успешное сохранение формы
    {
        // Редирект в зависимости от нажатия Сохранить или Применить
        if($apply!="")
            LocalRedirect("/bitrix/admin/".$iModuleID."_".$pageEdit.".php?ID=".$ID."&mess=ok&lang=".LANG); // ."&".$tabControl->ActiveTabParam()
        else
            LocalRedirect("/bitrix/admin/".$iModuleID."_".$pageList.".php?lang=".LANG);
    }
}
// === /ОБРАБОТКА ИЗМЕНЕНИЙ ФОРМЫ ===

// Предварительная чистка глобальных перменных с префиксом
ClearVars();

// Получаем данные из базы и сохранем в переменные с префиксом str_
if($ID > 0)
{
    $model = CMibixExportTemplateModel::GetByID($ID);
    if(!$model->ExtractFields("str_"))
        $ID=0;
}

// Если попытка сохранить данные неуспешна, инициализируем значения полей из таблицы
if($bVarsFromForm) {
    $DB->InitTableVarsForEdit(CMibixExportTemplateModel::getTableName(), "", "str_");
}

// Второй общий пролог
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

// Проверка статуса модуля
switch(CModule::IncludeModuleEx($iModuleID))
{
    case MODULE_NOT_FOUND:
        echo '<div class="adm-info-message-wrap adm-info-message-red"><div class="adm-info-message">'.GetMessage($sModuleID.'_MODULE_NOT_FOUND').'<div class="adm-info-message-icon"></div></div></div>';
        return;
    case MODULE_DEMO:
        echo '<div class="adm-info-message-wrap adm-info-message-red"><div class="adm-info-message">'.GetMessage($sModuleID.'_MODULE_DEMO').'<div class="adm-info-message-icon"></div></div></div>';
        break;
    case MODULE_DEMO_EXPIRED:
        echo '<div class="adm-info-message-wrap adm-info-message-red"><div class="adm-info-message">'.GetMessage($sModuleID.'_MODULE_DEMO_EXPIRED').'<div class="adm-info-message-icon"></div></div></div>';
}

// Если есть сообщения об ошибках или об успешном сохранении - выведем их
if($_REQUEST["mess"] == "ok" && $ID>0)
    CAdminMessage::ShowMessage(array("MESSAGE"=>GetMessage($sModuleID.'_'.$sForm.'_SAVED'), "TYPE"=>"OK"));
if (!empty($message))
    echo $message->Show();

// Административное меню, которое будет отображаться над таблицей со списком (Вернуться к списку)
$aMenu = array(
    array(
        "TEXT"=>GetMessage($sModuleID.'_'.$sForm.'_LIST_TEXT'),
        "TITLE"=>GetMessage($sModuleID.'_'.$sForm.'_LIST'),
        "LINK"=>$iModuleID."_".$pageList.".php?lang=".LANG,
        "ICON"=>"btn_list",
    )
);

// Доп действия в форме (Добавить/Удалить)
if($ID>0)
{
    $aMenu[] = array("SEPARATOR"=>"Y");
    $aMenu[] = array(
        "TEXT"=>GetMessage($sModuleID.'_'.$sForm.'_ADD_TEXT'),
        "TITLE"=>GetMessage($sModuleID.'_'.$sForm.'_MNU_ADD'),
        "LINK"=>$iModuleID."_".$pageEdit.".php?lang=".LANG,
        "ICON"=>"btn_new",
    );
    $aMenu[] = array(
        "TEXT"=>GetMessage($sModuleID.'_'.$sForm.'_DEL_TEXT'),
        "TITLE"=>GetMessage($sModuleID.'_'.$sForm.'_MNU_DEL'),
        "LINK"=>"javascript:if(confirm('".GetMessage($sModuleID.'_'.$sForm.'_MNU_DEL_CONF')."'))window.location='".$iModuleID."_".$pageList.".php?ID=".$ID."&action=delete&lang=".LANG."&".bitrix_sessid_get()."';",
        "ICON"=>"btn_delete",
    );
}

// Вывод контекстного меню
$context = new CAdminContextMenu($aMenu);
$context->Show();

// Табы
$arTabs = array(
    array(
        'DIV' => 'edit1',
        'TAB' => GetMessage($sModuleID.'_'.$sForm.'_EDIT1'),
        'ICON' => '',
        'TITLE' => GetMessage($sModuleID.'_'.$sForm.'_EDIT1'),
        'SORT' => '10'
    ),
    array(
        'DIV' => 'edit2',
        'TAB' => GetMessage($sModuleID.'_'.$sForm.'_EDIT2'),
        'ICON' => '',
        'TITLE' => GetMessage($sModuleID.'_'.$sForm.'_EDIT2'),
        'SORT' => '20'
    )
);

// Группы
$arGroups = array(
    'GROUP_100' => array('TITLE' => GetMessage($sModuleID.'_'.$sForm.'_GROUP_100'), 'TAB' => 0),
    'GROUP_200' => array('TITLE' => GetMessage($sModuleID.'_'.$sForm.'_GROUP_200'), 'TAB' => 1),
);

// Форма
$arForm = array(
    // дата создания
    'date_insert' => array(
        'type'  => 'label',
        'group' => 'GROUP_100',
        'sort'  => '10',
        'condition' => $ID>0,
        'value' => array(
            'selected'  => $str_date_insert
        ),
    ),
    // дата обновления
    'date_update' => array(
        'type'  => 'label',
        'group' => 'GROUP_100',
        'sort'  => '20',
        'condition' => ($ID>0) && ($str_date_update <> ''),
        'value' => array(
            'selected'  => $str_date_update
        ),
    ),
    // активность
    'active' => array(
        'type'  => 'checkbox',
        'group' => 'GROUP_100',
        'sort'  => '30',
        'value' => array(
            'selected'  => $str_active
        ),
    ),
    // название магазина
    'name' => array(
        'type'  => 'text',
        'group' => 'GROUP_100',
        'sort'  => '40',
        'required' => true,
        "value" => array(
            'selected'  => $str_name
        ),
    ),
    // название компании
    'encoding' => array(
        'type'  => 'text',
        'group' => 'GROUP_100',
        'sort'  => '50',
        'required' => true,
        "value" => array(
            'selected'  => $str_encoding
        ),
    ),
    // наполнитель шаблона
    'filler' => array(
        'type'  => 'textarea',
        'group' => 'GROUP_100',
        'sort'  => '60',
        'required' => true,
        'rows' => '20',
        'cols' => '80',
        "value" => array(
            'selected'  => $str_template
        ),
    ),
    // шаблон
    'template' => array(
        'type'  => 'textarea',
        'group' => 'GROUP_100',
        'sort'  => '60',
        'required' => true,
        'rows' => '20',
        'cols' => '80',
        "value" => array(
            'selected'  => $str_template
        ),
    ),
    // Интервал шага
    'step_interval' => array(
        'type'  => 'text',
        'group' => 'GROUP_200',
        'sort'  => '10',
        "value" => array(
            'selected'  => $str_step_interval
        ),
    ),
    // Лимит шага
    'step_limit' => array(
        'type'  => 'text',
        'group' => 'GROUP_200',
        'sort'  => '20',
        'size'  => 6,
        'maxlength' => 10,
        "value" => array(
            'selected'  => $str_step_limit
        ),
    ),
    // Лимит шага
    'step_path' => array(
        'type'  => 'text',
        'group' => 'GROUP_200',
        'sort'  => '30',
        'size'  => 44,
        'maxlength' => 255,
        "value" => array(
            'selected'  => $str_step_path
        ),
    ),
);

// Вывод формы
$form = new CMibixExportControls($sModuleID, $sForm, $arTabs, $arGroups, $arForm);
$form->ShowForm($ID, $POST_RIGHT, $message, $iModuleID."_".$pageList);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>