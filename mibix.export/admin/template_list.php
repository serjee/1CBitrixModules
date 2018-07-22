<?
$nList = "TEMPLATE_LIST";
$pageEdit = "template_edit";
$iModuleID = "mibix.export";
$sModuleID = strtoupper(str_replace(".","_",$iModuleID));

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$iModuleID."/include.php");

// подключим языковой файл
IncludeModuleLangFile(__FILE__);

// получим права доступа текущего пользователя на модуль
$POST_RIGHT = $APPLICATION->GetGroupRight($iModuleID);
if($POST_RIGHT <= "D") {
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

$sTableID = "tbl_gl_profile";
$oSort = new CAdminSorting($sTableID, "id", "desc");
$lAdmin = new CAdminList($sTableID, $oSort);

// элементы фильтра
$FilterArr = Array(
    "find",
    "find_type",
    "find_id",
    "find_name",
    "find_active",
    "find_update_1",
    "find_update_2",
    "find_insert_1",
    "find_insert_2",
);

// инициализируем фильтр
$lAdmin->InitFilter($FilterArr);

// если все значения фильтра корректны, обработаем его
if(CMibixExportControls::CheckFilter($sModuleID, $find_insert_1, $find_update_1, $find_insert_2, $find_update_2))
{
    $arFilter = Array(
        "ID"		=> ($find!="" && $find_type == "id"? $find : $find_id),
        "NAME"	=> ($find!="" && $find_type == "name"? $find : $find_name),
        "UPDATE_1"	=> $find_update_1,
        "UPDATE_2"	=> $find_update_2,
        "INSERT_1"	=> $find_insert_1,
        "INSERT_2"	=> $find_insert_2,
        "ACTIVE"	=> $find_active,
    );
}

// Обработка действий над элементами
if($lAdmin->EditAction() && $POST_RIGHT == "W")
{
    // пройдем по списку переданных элементов
    foreach($FIELDS as $ID=>$arFields)
    {
        if(!$lAdmin->IsUpdated($ID)) continue;

        // сохраним изменения каждого элемента
        $DB->StartTransaction();
        $ID = IntVal($ID);
        $ob = new CMibixExportTemplateModel();
        if(!$ob->Update($ID, $arFields))
        {
            $lAdmin->AddUpdateError(GetMessage($sModuleID."_".$nList."_POST_SAVE_ERROR").$ID.": ".$ob->getLastError(), $ID);
            $DB->Rollback();
        }
        $DB->Commit();
    }
}

$strError = $strOk = "";

// обработка одиночных и групповых действий
if(($arID = $lAdmin->GroupAction()) && $POST_RIGHT=="W")
{
    // если выбрано "Для всех элементов"
    if($_REQUEST['action_target']=='selected')
    {
        $cData = new CMibixExportTemplateModel();
        $rsData = $cData->GetList(array($by=>$order), $arFilter);
        while($arRes = $rsData->Fetch())
            $arID[] = $arRes['ID'];
    }

    // пройдем по списку элементов
    foreach($arID as $ID)
    {
        $ID = IntVal($ID);
        if($ID <= 0) continue;

        // для каждого элемента совершим требуемое действие
        switch($_REQUEST['action'])
        {
            // удаление
            case "delete":
                $DB->StartTransaction();
                if(!CMibixExportTemplateModel::Delete($ID))
                {
                    $DB->Rollback();
                    $lAdmin->AddGroupError(GetMessage($sModuleID."_".$nList."_DEL_ERR"), $ID);
                }
                $DB->Commit();
                break;
            // активация/деактивация
            case "activate":
            case "deactivate":
                $ob = new CMibixExportTemplateModel();
                $arFields = Array("active"=>($_REQUEST['action']=="activate"?"Y":"N"));
                if(!$ob->Update($ID, $arFields))
                    $lAdmin->AddGroupError(GetMessage($sModuleID."_".$nList."_SAVE_ERROR").$ob->getLastError(), $ID);
                break;
        }
    }
}

// Выборка элементов
$cData = new CMibixExportTemplateModel();
$rsData = $cData->GetList(array($by=>$order), $arFilter, array("nPageSize" => CAdminResult::GetNavSize($sTableID)));

// преобразуем список в экземпляр класса CAdminResult
$rsData = new CAdminResult($rsData, $sTableID);

// аналогично CDBResult инициализируем постраничную навигацию.
$rsData->NavStart();

// отправим вывод переключателя страниц в основной объект $lAdmin
$lAdmin->NavText($rsData->GetNavPrint(GetMessage($sModuleID."_".$nList."_NAV")));

// Подготовка списка элементов к выводу (загловки)
$lAdmin->AddHeaders(array(
    array(
        "id"		=> "id", // Идентификатор колонки
        "content"	=> "ID", // Заголовок колонки
        "sort"		=> "id", // Значение параметра GET-запроса для сортировки
        "align"		=> "right", // Будет ли колонка по умолчанию отображаться в списке
        "default"	=> true,
    ),
    array(
        "id"		=> "date_insert",
        "content"	=> GetMessage($sModuleID."_".$nList."_POST_DATE_INSERT"),
        "sort"		=> "date_insert",
        "default"	=> true,
    ),
    array(
        "id"		=> "name",
        "content"	=> GetMessage($sModuleID."_".$nList."_NAME"),
        "sort"		=> "name",
        "default"	=> true,
    ),
    array(
        "id"		=> "active",
        "content"	=> GetMessage($sModuleID."_".$nList."_ACT"),
        "sort"		=> "act",
        "default"	=> true,
    ),
    array(
        "id"		=> "date_update",
        "content"	=> GetMessage($sModuleID."_".$nList."_POST_DATE_UPDATE"),
        "sort"		=> "date_update",
        "default"	=> false,
    ),
));

// Передача списка элементов в основной объект
while($arRes = $rsData->NavNext(true, "f_"))
{
    // создаем строку. результат - экземпляр класса CAdminListRow
    $row =& $lAdmin->AddRow($f_id, $arRes);

    // редактируется как текст
    $row->AddInputField("name", array("size"=>20));
    $row->AddViewField("name", '<a href="'.$iModuleID."_".$pageEdit.'.php?ID='.$f_id.'&lang='.LANG.'">'.$f_name.'</a>');
    // редактируется как чекбокс
    $row->AddCheckField("active");

    $arActions = Array();
    $arActions[] = array(
        "ICON"=>"edit",
        "DEFAULT"=>true,
        "TEXT"=>GetMessage($sModuleID."_".$nList."_UPD"),
        "ACTION"=>$lAdmin->ActionRedirect($iModuleID."_".$pageEdit.".php?ID=".$f_id)
    );
    if ($POST_RIGHT>="W")
    {
        $arActions[] = array(
            "ICON"=>"delete",
            "TEXT"=>GetMessage($sModuleID."_".$nList."_DEL"),
            "ACTION"=>"if(confirm('".GetMessage($sModuleID."_".$nList."_DEL_CONF")."')) ".$lAdmin->ActionDoGroup($f_id, "delete")
        );
    }

    // Формируем контекстное меню для строки
    $row->AddActions($arActions);
}

// резюме таблицы
$lAdmin->AddFooter(
    array(
        array("title" => GetMessage("MAIN_ADMIN_LIST_SELECTED"), "value" => $rsData->SelectedRowsCount()),
        array("counter" => true, "title" => GetMessage("MAIN_ADMIN_LIST_CHECKED"), "value" => "0"),
    )
);

// групповые действия
$lAdmin->AddGroupActionTable(Array(
    "activate" => GetMessage("MAIN_ADMIN_LIST_ACTIVATE"),
    "deactivate" => GetMessage("MAIN_ADMIN_LIST_DEACTIVATE"),
    "delete" => GetMessage("MAIN_ADMIN_LIST_DELETE"),
));

// сформируем меню из одного пункта - добавить запись
$aContext = array(
    array(
        "TEXT" => GetMessage("MAIN_ADD"),
        "LINK" => $iModuleID."_".$pageEdit.".php?lang=".LANG,
        "TITLE" => GetMessage("MAIN_ADD"),
        "ICON" => "btn_new",
    ),
);
// и прикрепим его к списку
$lAdmin->AddAdminContextMenu($aContext);

// отобразим альтернтативные методы вывода списка
$lAdmin->CheckListMode();

// установим заголовок страницы
$APPLICATION->SetTitle(GetMessage($sModuleID."_".$nList."_TITLE"));

// разделяем подготовку данных и вывод подключением административного файла
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

// инициализация фильтра и передача в него списка полей
$oFilter = new CAdminFilter(
    $sTableID."_filter",
    array(
        GetMessage($sModuleID."_".$nList."_POST_F_ID"),
        GetMessage($sModuleID."_".$nList."_POST_F_INSERT"),
        GetMessage($sModuleID."_".$nList."_POST_F_UPDATE"),
        GetMessage($sModuleID."_".$nList."_POST_F_NAME"),
        GetMessage($sModuleID."_".$nList."_POST_F_PROFILE"),
        GetMessage($sModuleID."_".$nList."_POST_F_ACTIVE"),
    )
);

// далее ручное формирвоание формы фильтра
?>
    <form name="find_form" method="get" action="<?=$APPLICATION->GetCurPage();?>">
        <?$oFilter->Begin();?>
        <tr>
            <td><b><?=GetMessage($sModuleID."_".$nList."_POST_F_FIND")?>:</b></td>
            <td>
                <input type="text" size="25" name="find" value="<?=htmlspecialchars($find)?>" title="<?=GetMessage($sModuleID."_".$nList."_POST_F_FIND_TITLE")?>">
                <?
                $arr = array(
                    "reference" => array(
                        GetMessage($sModuleID."_".$nList."_POST_F_NAME"),
                        GetMessage($sModuleID."_".$nList."_POST_F_ID"),
                    ),
                    "reference_id" => array(
                        "name",
                        "id",
                    )
                );
                echo SelectBoxFromArray("find_type", $arr, $find_type, "", "");
                ?>
            </td>
        </tr>
        <tr>
            <td><?=GetMessage($sModuleID."_".$nList."_POST_F_ID")?>:</td>
            <td><input type="text" name="find_id" size="47" value="<?=htmlspecialchars($find_id)?>">&nbsp;<?=ShowFilterLogicHelp()?></td>
        </tr>
        <tr>
            <td><?=GetMessage($sModuleID."_".$nList."_POST_F_INSERT")." (".FORMAT_DATE."):"?></td>
            <td><?=CalendarPeriod("find_insert_1", htmlspecialchars($find_insert_1), "find_insert_2", htmlspecialchars($find_insert_2), "find_form","Y")?></td>
        </tr>
        <tr>
            <td><?=GetMessage($sModuleID."_".$nList."_POST_F_UPDATE")." (".FORMAT_DATE."):"?></td>
            <td><?=CalendarPeriod("find_update_1", htmlspecialchars($find_update_1), "find_update_2", htmlspecialchars($find_update_2), "find_form","Y")?></td>
        </tr>
        <tr>
            <td><?=GetMessage($sModuleID."_".$nList."_POST_F_NAME")?>:</td>
            <td><input type="text" name="find_name" size="47" value="<?=htmlspecialchars($find_name)?>">&nbsp;<?=ShowFilterLogicHelp()?></td>
        </tr>
        <tr>
            <td><?=GetMessage($sModuleID."_".$nList."_POST_F_ACTIVE")?>:</td>
            <td><?
                $arr = array("reference"=>array(GetMessage("MAIN_YES"), GetMessage("MAIN_NO")), "reference_id"=>array("Y","N"));
                echo SelectBoxFromArray("find_active", $arr, htmlspecialchars($find_active), GetMessage("MAIN_ALL"));
                ?></td>
        </tr>
        <?
        $oFilter->Buttons(array("table_id"=>$sTableID,"url"=>$APPLICATION->GetCurPage(),"form"=>"find_form"));
        $oFilter->End();
        ?>
    </form>
<?
// выведем таблицу списка элементов
$lAdmin->DisplayList();
?>

<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>