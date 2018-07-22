<?
$iForm = "entity";
$sForm = strtoupper($iForm);
$iModuleID = "mibix.export";
$sModuleID = strtoupper(str_replace(".","_",$iModuleID));
$pageEdit = "entity_edit";
$pageList = "entity_list";

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php"); // первый общий пролог
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$iModuleID."/include.php"); // инициализация модуля
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$iModuleID."/prolog.php"); // пролог модуля

IncludeModuleLangFile(__FILE__);

// Устанавливаем заголовок в зависимости от ее типа (обновление/добавление)
$APPLICATION->SetTitle(($ID > 0 ? GetMessage($sModuleID.'_'.$sForm.'_EDIT_TITLE').$ID : GetMessage($sModuleID.'_'.$sForm.'_ADD_TITLE')));

// Проверка прав доступа
$POST_RIGHT = $APPLICATION->GetGroupRight($iModuleID);
if($POST_RIGHT=="D") {
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

$ID = intval($ID); // идентификатор редактируемой записи
$arErrors = array(); // сообщение об ошибке
$bVarsFromForm = false; // флаг "Данные получены с формы", обозначающий, что выводимые данные получены с формы, а не из БД.

// === ОБРАБОТКА ИЗМЕНЕНИЙ ФОРМЫ ===
if($REQUEST_METHOD == "POST" && ($save != "" || $apply != "") && $POST_RIGHT >= "W" && check_bitrix_sessid())
{
    $model = new CMibixExportEntityModel();

    // обработка переменных filter
    $strFilters = "";
    if(isset($f_filter_name[0]) && isset($f_filter_unit[0]) && isset($f_filter_value[0]) && strlen($f_filter_name[0])>0 && strlen($f_filter_unit[0])>0 && strlen($f_filter_value[0])>0)
    {
        // количество параметров в массиве
        $cntPv = count($f_filter_name);

        // Проходимся по всем параметрам в массиве и преобразуем их к виду "name,unit,value|name,unit,value|name,unit,value.." для записи в базу
        $arStrFilters = array();
        for($i=0;$i<$cntPv;$i++)
        {
            if(isset($f_filter_name[$i]) && isset($f_filter_unit[$i]))
            {
                $strFiltName = (isset($f_filter_name[$i]))?$f_filter_name[$i]:"";
                $strFiltUnit = (isset($f_filter_unit[$i]))?$f_filter_unit[$i]:"";
                $strFiltValue = (isset($f_filter_value[$i]))?$f_filter_value[$i]:"";

                if(strlen($strFiltName)>0 && strlen($strFiltUnit)>0)
                    $arStrFilters[] = $strFiltName.",".$strFiltUnit.",".$strFiltValue;
            }
        }
        if(count($arStrFilters)>0) $strFilters = implode("|", $arStrFilters);
    }

    // обработка данных формы
    $arFields = Array(
        "template_id"           => intval($f_template_id),
        "entity_id"		        => intval($f_entity_id),
        "name_entity"		    => $f_name_entity,
        "code_entity"		    => $f_code_entity,
        "value"		            => $f_value,
        "site_id"		        => $f_site_id,
        "iblock_type"		    => $f_iblock_type,
        "iblock_id"		        => intval($f_iblock_id),
        "include_sections"      => $f_include_sections, // array
        "include_items"	        => $f_include_items, // array
        "exclude_items"		    => $f_exclude_items, // array
        //"include_sku"           => ($f_include_sku <> "Y"? "N":"Y"),
        "filters"               => $strFilters,
        "active"		        => ($f_active <> "Y"? "N":"Y"),
    );

    // сохранение данных (обновление или добавление)
    if($ID > 0)
    {
        if(!($res = $model->Update($ID, $arFields)))
            $arErrors = $model->getErrors();
    }
    else
    {
        if(!($ID = $model->Add($arFields)))
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
        // Редирект  в зависимости от нажатия Сохранить или Применить
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
    $model = CMibixExportEntityModel::GetByID($ID);
    if(!$model->ExtractFields("str_"))
        $ID=0;
}

// если данные переданы из формы, инициализируем их
if($bVarsFromForm) {
    $DB->InitTableVarsForEdit(CMibixExportEntityModel::getTableName(), "", "str_");
}

// определяем значения для параметров
if(strlen($str_filters)>0)
{
    // из строки формируем массив параметров
    $arFilters = explode("|", $str_filters);
    if(count($arFilters)>0)
    {
        $str_filter_name = array();
        $str_filter_unit = array();
        $str_filter_value = array();
        foreach($arFilters as $str_filter)
        {
            // формируем отдельный массива для элементов каждого параметра
            $arFilterElements = explode(",", $str_filter);
            if(count($arFilterElements)==3 && isset($arFilterElements[0]) && isset($arFilterElements[1]) && isset($arFilterElements[2]))
            {
                $str_filter_name[] = $arFilterElements[0];
                $str_filter_unit[] = $arFilterElements[1];
                $str_filter_value[] = $arFilterElements[2];
            }
        }
    }
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

// В режиме редактирования добавляем дополнительные пункты меню (Добавить/Удалить)
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
    )
);

// Группы
$arGroups = array(
    'GROUP_100' => array('TITLE' => GetMessage($sModuleID.'_'.$sForm.'_GROUP_100'), 'TAB' => 0),
    'GROUP_200' => array('TITLE' => GetMessage($sModuleID.'_'.$sForm.'_GROUP_200'), 'TAB' => 0),
    'GROUP_300' => array('TITLE' => GetMessage($sModuleID.'_'.$sForm.'_GROUP_300'), 'TAB' => 0),
);

// Форма
$isIM = (CModule::IncludeModule("sale") && CModule::IncludeModule("catalog"));
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
    // название правила
    'name_entity' => array(
        'type'  => 'text',
        'group' => 'GROUP_100',
        'sort'  => '40',
        'required' => true,
        "value" => array(
            'selected'  => $str_name_entity
        ),
    ),
    // название правила
    'code_entity' => array(
        'type'  => 'text',
        'group' => 'GROUP_100',
        'sort'  => '45',
        'required' => true,
        "value" => array(
            'selected'  => $str_code_entity
        ),
    ),
    // профиль магазина
    'template_id' => array(
        'type'  => 'shop',
        'group' => 'GROUP_100',
        'sort'  => '50',
        'required' => true,
        "value" => array(
            'selected'  => $str_template_id
        ),
    ),
    // профиль магазина
    'site_id' => array(
        'type'  => 'site',
        'group' => 'GROUP_100',
        'sort'  => '60',
        "value" => array(
            'selected'  => $str_site_id
        ),
    ),
    // тип инфоблока
    'iblock_type' => array(
        'type'  => 'iblock_type',
        'group' => 'GROUP_100',
        'sort'  => '70',
        'required' => true,
        "value" => array(
            'selected'  => $str_iblock_type
        ),
    ),
    // инфоблок
    'iblock_id' => array(
        'type'  => 'iblock',
        'group' => 'GROUP_100',
        'sort'  => '80',
        'required' => true,
        "value" => array(
            'selected'  => $str_iblock_id,
            "site_id"   => $str_site_id,
            "iblock_type"   => $str_iblock_type
        ),
    ),
    // выбрать разделы
    'include_sections' => array(
        'type'  => 'select_sections',
        'group' => 'GROUP_100',
        'sort'  => '90',
        "value" => array(
            'selected'  => $str_include_sections,
            "iblock_id"   => $str_iblock_id
        ),
    ),
    // включить разделы
    'include_items' => array(
        'type'  => 'select_elements',
        'group' => 'GROUP_100',
        'sort'  => '110',
        "value" => array(
            'selected'  => $str_include_items,
            "bvff"   => $bVarsFromForm
        ),
    ),
    // включить разделы
    'exclude_items' => array(
        'type'  => 'select_elements',
        'group' => 'GROUP_100',
        'sort'  => '120',
        "value" => array(
            'selected'  => $str_exclude_items,
            "bvff"   => $bVarsFromForm
        ),
    ),

    // выгружать SKU
    /*
    'include_sku' => array(
        'type'  => 'checkbox',
        'group' => 'GROUP_200',
        'sort'  => '10',
        'condition' => $isIM,
        "value" => array(
            'selected'  => $str_include_sku
        ),
    ),*/

    // фильтр
    'filter' => array(
        'type'  => 'filter',
        'group' => 'GROUP_300',
        'sort'  => '10',
        "value" => array(
            "iblock_id" => $str_iblock_id,
            'filter_name'  => $str_filter_name,
            "filter_unit"   => $str_filter_unit,
            "filter_value"  => $str_filter_value
        ),
    ),
);

// Вывод формы
$form = new CMibixExportControls($sModuleID, $sForm, $arTabs, $arGroups, $arForm);
$form->ShowForm($ID, $POST_RIGHT, $message, $iModuleID."_".$pageList);

echo '<script src="/bitrix/js/'.$iModuleID.'/script.js"></script>';
echo BeginNote();
echo '<span class="required">*</span> '.GetMessage("REQUIRED_FIELDS");
echo EndNote();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>