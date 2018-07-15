<?php
$MODULE_ID = "mibix.socposter";
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.$MODULE_ID.'/include.php');

$RIGHT = $APPLICATION->GetGroupRight($MODULE_ID);
if($RIGHT >= "R")
{
    IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/options.php");
    IncludeModuleLangFile(__FILE__);

    $arError = array();
    $bVarsFromForm = false;
    $updateIBlock = false;
    $bCron = COption::GetOptionString("main", "agents_use_crontab", "N") == 'Y' || defined('BX_CRONTAB_SUPPORT') && BX_CRONTAB_SUPPORT === true || COption::GetOptionString("main", "check_agents", "Y") != 'Y';
    $module_status = CModule::IncludeModuleEx($MODULE_ID);
    if($module_status == '0')
    {
        echo GetMessage('DEMO_MODULE');
    }
    elseif($module_status == '3')
    {
        echo GetMessage('DEMO_MODULE');
    }

    $aTabs = array(
        array("DIV" => "edit1", "TAB" => GetMessage("MIBIX_SP_TAB_MAIN"), "ICON" => "ib_settings", "TITLE" => GetMessage("MIBIX_SP_TAB_MAIN_TITLE")),
        array("DIV" => "edit2", "TAB" => GetMessage("MIBIX_SP_TAB_SOC"), "ICON" => "ib_settings", "TITLE" => GetMessage("MIBIX_SP_TAB_SOC_TITLE")),
    );
    $tabControl = new CAdminTabControl("tabControl", $aTabs);

    if($REQUEST_METHOD=="POST" && $RIGHT=="W" && check_bitrix_sessid())
    {
        if(strlen($Update.$Apply) > 0)
        {
            // обработка данных формы
            $arFields = Array(
                "iblock_id"		    => intval($f_iblock_id),
                "include_sections"  => $f_include_sections, // array
                "exclude_sections"	=> $f_exclude_sections, // array
                "public_text"	    => $f_public_text, // text
                "public_pictures"	=> $f_public_pictures,  // array
                "link_post"		    => ($f_link_post <> "Y"? "N":"Y"),
                "site_id"           => $f_site_id,
                "diff_items"		=> ($f_diff_items <> "Y"? "N":"Y"),
                "repeat"		    => ($f_repeat <> "Y"? "N":"Y"),
                "event_newitem"		=> ($f_event_newitem <> "Y"? "N":"Y"),
                "run_method"        => ($f_run_method <> "AGENT"? "CRON":"AGENT"),
                "run_time"	        => $f_run_time, // text
                "run_period"        => $f_run_period, // text
                "vk_post"		    => ($f_vk_post <> "Y"? "N":"Y"),
                "vk_token"          => $f_vk_token, // text
                "vk_wall"           => $f_vk_wall, // text
                "fb_post"		    => ($f_fb_post <> "Y"? "N":"Y"),
                "fb_token"          => $f_fb_token, // text
                "fb_wall"           => $f_fb_wall, // text
            );

            // Обновляем запись
            $settingsModel = new CMibixModelSettings();
            if($settingsModel->Update($arFields))
            {
                LocalRedirect("/bitrix/admin/settings.php?mid=".$MODULE_ID."&mess=ok&lang=".LANG."&".$tabControl->ActiveTabParam());
            }
            else
            {
                $bVarsFromForm = true;
                $arError = $settingsModel->getArMsg();
            }
        }
        else
        {
            $updateIBlock = true;
        }
    }

    ClearVars();
    $datasource = CMibixModelSettings::GetSetting();
    $datasource->ExtractFields("str_");

    // если данные переданы из формы, инициализируем их
    if($bVarsFromForm)
    {
        $DB->InitTableVarsForEdit("b_mibix_socposter_settings", "", "str_");
    }

    // если выбрали инфоблок без сохранения
    if ($updateIBlock)
    {
        $str_iblock_id = intval($f_iblock_id);
    }

    // Выводим сообщение об успешном сохранении, если
    if($_REQUEST["mess"] == "ok")
        CAdminMessage::ShowMessage(array("MESSAGE"=>GetMessage("MIBIX_SP_MAIN_SAVED"), "TYPE"=>"OK"));

    // Выводим ошибки, если они есть
    if(count($arError)>0)
    {
        $e = new CAdminException($arError);
        $message = new CAdminMessage(GetMessage("MIBIX_SP_MAIN_ERROR_TITLE"), $e);
        echo $message->Show();
    }
    ?>
    <script>
        function CheckTimeEnabled()
        {
            var on = !BX('f_run_method_cron').checked;

            document.fd1.f_run_time.disabled = !on;
            document.fd1.f_run_period.disabled = !on;

            return on;
        }
        function CheckVkEnabled()
        {
            var on = !BX('f_vk_post').checked;

            document.fd1.f_vk_token.disabled = on;
            document.fd1.f_vk_wall.disabled = on;

            return on;
        }
        function CheckFbEnabled()
        {
            var on = !BX('f_fb_post').checked;

            document.fd1.f_fb_token.disabled = on;
            document.fd1.f_fb_wall.disabled = on;

            return on;
        }
        BX.ready(CheckTimeEnabled);
        BX.ready(CheckVkEnabled);
        BX.ready(CheckFbEnabled);
    </script>
    <form name="fd1" method="post" action="<?echo $APPLICATION->GetCurPage()?>?mid=<?=urlencode($MODULE_ID)?>&amp;lang=<?=LANGUAGE_ID?>">
    <?
    $tabControl->Begin();
    $tabControl->BeginNextTab();
    ?>
        <tr class="heading">
            <td colspan="2"><b><?=GetMessage("MIBIX_SP_MAIN_SECTION")?></b></td>
        </tr>
        <tr>
            <td width="40%" class="adm-detail-content-cell-l"><span class="required">*</span>
                <span class="adm-required-field"><?=GetMessage("MIBIX_SP_MAIN_IBLOCK_ID");?></span>:
            </td>
            <td width="60%" class="adm-detail-content-cell-r">
                <?echo CMibixModelSettings::getSelectBoxIBlockId($str_iblock_id);?>
            </td>
        </tr>
        <?if($str_iblock_id>0):?>
            <tr>
                <td width="40%" class="adm-detail-valign-top adm-detail-content-cell-l"><?=GetMessage("MIBIX_SP_MAIN_SECTIONS_INC");?>:<br>(<?=GetMessage("MIBIX_SP_MAIN_SECTIONS_INC_NOTE")?>)</td>
                <td width="60%" class="adm-detail-content-cell-r">
                    <?echo CMibixModelSettings::getSelectBoxSections("f_include_sections", $str_iblock_id, $str_include_sections);?>
                </td>
            </tr>
            <tr>
                <td width="40%" class="adm-detail-valign-top adm-detail-content-cell-l"><?=GetMessage("MIBIX_SP_MAIN_SECTIONS_EXC");?>:<br>(<?=GetMessage("MIBIX_SP_MAIN_SECTIONS_EXC_NOTE")?>)</td>
                <td width="60%" class="adm-detail-content-cell-r">
                    <?echo CMibixModelSettings::getSelectBoxSections("f_exclude_sections", $str_iblock_id, $str_exclude_sections);?>
                </td>
            </tr>
            <tr>
                <td width="40%" class="adm-detail-content-cell-l"><?=GetMessage("MIBIX_SP_MAIN_TEXT");?>:</td>
                <td width="60%" class="adm-detail-content-cell-r">
                    <?echo CMibixModelSettings::getSelectBoxPropertyText($str_iblock_id, $str_public_text);?>
                </td>
            </tr>
            <tr>
                <td width="40%" class="adm-detail-content-cell-l"><?=GetMessage("MIBIX_SP_MAIN_PICTURES");?>:</td>
                <td width="60%" class="adm-detail-content-cell-r">
                    <?echo CMibixModelSettings::getSelectBoxPropertyPictures($str_iblock_id, $str_public_pictures);?>
                </td>
            </tr>
        <?endif;?>
        <tr class="heading">
            <td colspan="2"><?=GetMessage("MIBIX_SP_PUBLISH")?></td>
        </tr>
        <tr>
            <td width="40%" class="adm-detail-content-cell-l"><?=GetMessage("MIBIX_SP_PUBLISH_LINK_POST")?>:</td>
            <td width="60%"><input type="checkbox" name="f_link_post" id="f_link_post" value="Y"<?if($str_link_post=="Y" || $str_link_post=="") echo " checked";?> /></td>
        </tr>
        <tr>
            <td><?=GetMessage("MIBIX_SP_PUBLISH_SELECT_SITE")?>:</td>
            <td>
                <?echo CMibixModelSettings::getSelectSiteID($str_site_id);?>
            </td>
        </tr>
        <tr>
            <td width="40%" class="adm-detail-content-cell-l"><?=GetMessage("MIBIX_SP_PUBLISH_DIFF_ITEMS")?>:</td>
            <td width="60%"><input type="checkbox" name="f_diff_items" id="f_diff_items" value="Y"<?if($str_diff_items=="Y") echo " checked";?> /></td>
        </tr>
        <tr>
            <td width="40%" class="adm-detail-content-cell-l"><?=GetMessage("MIBIX_SP_PUBLISH_REPEAT")?>:</td>
            <td width="60%"><input type="checkbox" name="f_repeat" id="f_repeat" value="Y"<?if($str_repeat=="Y") echo " checked";?> /></td>
        </tr>
        <tr class="heading">
            <td colspan="2"><?=GetMessage("MIBIX_SP_EVENTS")?></td>
        </tr>
        <tr>
            <td width="40%" class="adm-detail-content-cell-l"><?=GetMessage("MIBIX_SP_EVENTS_ITEM_ADD")?>:</td>
            <td width="60%"><input type="checkbox" name="f_event_newitem" id="f_event_newitem" value="Y"<?if($str_event_newitem=="Y") echo " checked";?> /></td>
        </tr>
        <tr class="heading">
            <td colspan="2"><?=GetMessage("MIBIX_SP_TIME_SECTION")?></td>
        </tr>
        <tr>
            <td class="adm-detail-valign-top" width=40%><?=GetMessage('MIBIX_SP_TIME_METHOD')?></td>
            <td>
                <?
                ?>
                <div><label><input type="radio" name="f_run_method" id="f_run_method_agent" value="AGENT" <?= $str_run_method == "AGENT" ? 'checked' : '' ?> <?=$bCron ? '' : 'disabled'?> onclick="CheckTimeEnabled()"> <?=GetMessage('MIBIX_SP_TIME_METHOD_AGENT')?><span class="required"><sup>1</sup></span></label></div>
                <div><label><input type="radio" name="f_run_method" id="f_run_method_cron" value="CRON" <?= $str_run_method != "AGENT" ? 'checked' : '' ?> onclick="CheckTimeEnabled()"> <?=GetMessage('MIBIX_SP_TIME_METHOD_CRON', array('#SCRIPT#' => '<b>/mibix_socposter.php</b>'))?><span class="required"><sup>2</sup></span></label></div>
            </td>
        </tr>
        <tr>
            <td><?=GetMessage("MIBIX_SP_TIME_SPENT")?></td>
            <td>
                <?
                $min = preg_match('#^([0-9]{2}):([0-9]{2})$#', $str_run_time, $regs) ? $regs[1] * 60 + $regs[2] : 0;
                require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/tools/clock.php");
                CClock::Show(array(
                        'view' => 'select',
                        'inputName' => 'f_run_time',
                        'initTime' => sprintf('%02d:%02d',floor($min / 60),($min % 60))
                    )
                );
                ?>
            </td>
        </tr>
        <tr>
            <td><?=GetMessage("MIBIX_SP_TIME_PERIODITY")?></td>
            <td>
                <select name="f_run_period">
                    <?
                    foreach(array(
                                "PER1" => GetMessage("MIBIX_SP_TIME_PER_1"),
                                "PER2" => GetMessage("MIBIX_SP_TIME_PER_2"),
                                "PER3" => GetMessage("MIBIX_SP_TIME_PER_3"),
                                "PER4" => GetMessage("MIBIX_SP_TIME_PER_4"),
                            ) as $k => $v)
                        echo '<option value="'.$k.'" '.($str_run_period == $k ? 'selected' : '').'>'.$v.'</option>';
                    ?>
                </select>
            </td>
        </tr>
        <?$tabControl->BeginNextTab();?>
        <tr class="heading">
            <td colspan="2"><b><?=GetMessage("MIBIX_SP_VK_SECTION")?></b></td>
        </tr>
        <tr>
            <td width="40%" class="adm-detail-content-cell-l"><?=GetMessage("MIBIX_SP_VK_ENABLE")?>:</td>
            <td width="60%"><input type="checkbox" name="f_vk_post" id="f_vk_post" value="Y"<?if($str_vk_post=="Y") echo " checked";?> onclick="CheckVkEnabled()" /></td>
        </tr>
        <tr>
            <td width="40%" class="adm-detail-valign-top adm-detail-content-cell-l"><?=GetMessage("MIBIX_SP_VK_TOKEN")?> <span class="required"><sup>3</sup></span>:</td>
            <td width="60%">
                <input type="text" size="50" maxlength="255" value="<?=$str_vk_token;?>" name="f_vk_token" id="f_vk_token" />
            </td>
        </tr>
        <tr>
            <td width="40%" class="adm-detail-content-cell-l"><?=GetMessage("MIBIX_SP_VK_GROUP")?>:</td>
            <td width="60%"><input type="text" size="50" maxlength="255" value="<?=$str_vk_wall;?>" name="f_vk_wall" id="f_vk_wall" /></td>
        </tr>
        <tr class="heading">
            <td colspan="2"><b><?=GetMessage("MIBIX_SP_FB_SECTION")?></b></td>
        </tr>
        <tr>
            <td width="40%" class="adm-detail-content-cell-l"><?=GetMessage("MIBIX_SP_FB_ENABLE")?>:</td>
            <td width="60%"><input type="checkbox" name="f_fb_post" id="f_fb_post" value="Y"<?if($str_fb_post=="Y") echo " checked";?> onclick="CheckFbEnabled()" /></td>
        </tr>
        <tr>
            <td width="40%" class="adm-detail-valign-top adm-detail-content-cell-l"><?=GetMessage("MIBIX_SP_FB_TOKEN")?> <span class="required"><sup>3</sup></span>:</td>
            <td width="60%">
                <input type="text" size="50" maxlength="255" value="<?=$str_fb_token;?>" name="f_fb_token" />
            </td>
        </tr>
        <tr>
            <td width="40%" class="adm-detail-content-cell-l"><?=GetMessage("MIBIX_SP_FB_WALL")?> <span class="required"><sup>3</sup></span>:</td>
            <td width="60%"><input type="text" size="50" maxlength="255" value="<?=$str_fb_wall;?>" name="f_fb_wall" /></td>
        </tr>

        <?$tabControl->Buttons();?>
        <input <?if ($RIGHT<"W") echo "disabled" ?> type="submit" name="Update" value="<?=GetMessage("MAIN_SAVE")?>" title="<?=GetMessage("MAIN_OPT_SAVE_TITLE")?>" class="adm-btn-save">
        <input <?if ($RIGHT<"W") echo "disabled" ?> type="submit" name="Apply" value="<?=GetMessage("MAIN_OPT_APPLY")?>" title="<?=GetMessage("MAIN_OPT_APPLY_TITLE")?>">
        <?=bitrix_sessid_post();?>
    <?$tabControl->End();?>
    </form>
    <?
    $ROOT_PATH = $_SERVER['DOCUMENT_ROOT'] = realpath(dirname(__FILE__).'/../../../');
    echo BeginNote();
    echo '<div><span class=required><sup>1</sup></span> '.GetMessage("MIBIX_SP_AGENT").'<br />';
    echo '<div><span class=required><sup>2</sup></span> '.GetMessage("MIBIX_SP_CRON_SET", array('#SCRIPT#'=>'<b>mibix_socposter.php</b>', '#ROOT#'=>$ROOT_PATH)).'<br />';
    echo '<span class=required><sup>3</sup></span> '.GetMessage("MIBIX_SP_TOKEN_NOTE").'</div>';
    echo EndNote();
}
?>